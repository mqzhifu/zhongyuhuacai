<?php
//数据库基类
class DbLib{
    //配置文件数组结构
    public $_configKey = array('host','port','user','pwd','db_name','char','conn_timeout','conn_persistence','type');
    public $_config = null;//连接配置信息
    public $_configMapKey = null;

    public $_masterFD = null;//主库TCP FD
    public $_slaveFD = null;//从库TCP FD

    public $_table = null;
    public $_pk = null;

    public $_mysqlMemPoint = null;//mysql接收到执行QUERY后，会返回一个内存地址指针，指向你QUERY的结果集
    public $_addId = 0;//每次insert时候返回的新自增ID
    public $_changeRow = 0;//delete update 影响了MYSQL 多少行记录
    public $_fetchArray = 0;
//    public $error;//错误信息
//    public $mTransaction = 0;//是否为事务，如果是则出现错误后是要抛出异常的

    public function __construct($configKey='',$table = '',$pk = ''){
        if( !$configKey ){
            $configKey = DEF_DB_CONN;
        }
        $this->authConfig($configKey);
        if($table)
            $this->_table = $table;

        $this->_configMapKey = $configKey;

        if($pk)
            $this->_pk = $pk;
    }
    //初始化数据库连接,这里是一个小优化，只有真正执行SQL语句时，才去连接数据库
    protected function checkConnect() {
        $dbFD = ContainerLib::get($this->_configMapKey);
        if(!$dbFD){
            $masterFD = $this->setOneMysqliConnect($this->_config['master']);
            $slaveFD = null;
            if( arrKeyIssetAndExist($this->_config,'masterSlave') ){//是否开启主从模式
                $slaveFD = $this->setOneMysqliConnect($this->_config['slave']);
            }
            $this->_masterFD = $masterFD;
            $this->_slaveFD = $slaveFD;

            $ContainerInfo = array(
                "connTime"=>time(),'masterFD'=>$masterFD,"slaveFD"=>$slaveFD,
            );
            ContainerLib::set($this->_configMapKey,$ContainerInfo);
        }else{
            if(!$this->_masterFD){
                $this->_masterFD = $dbFD['masterFD'];
            }

            if(!$this->_slaveFD){
                $this->_slaveFD = $dbFD['slaveFD'];
            }
//            if(time()  - $dbFD['connTime'] >=  $this->_config['timeout']){
//                self::$_poll[$this->configKey] = null;
//                $dbFD = $this->getDbFD( $this->_config);
//                $this->_masterFD = $dbFD['m'];
//                $this->_slaveFD = $dbFD['s'];
//            }elseif( !$this->_masterFD || !$this->_slaveFD ){
//                $this->mMasterFD = $dbFD['m'];
//                $this->mSlaveFD = $dbFD['s'];
//            }
        }
    }

    function setOneMysqliConnect($config){
        $host = $config['host'];
        if($config['conn_persistence']){
            $host = "p:".$host;
        }
        $connFD = mysqli_connect(
            $host,
            $config['user'],
            $config['pwd'],
            $config['db_name'],
            $config['port']
        );

        if ( !$connFD ){
            ExceptionFrameLib::throwCatch("connect db error:". mysqli_error($connFD) . " mysql_connect [connect db error]");
        }

        //设置字符集
        $this->setNames($connFD ,$config['char']);

        return $connFD;
    }

    //=================================
    function setNames($fd,$char){
        mysqli_query($fd,"SET NAMES '$char'");
    }
    //========================================================
    //执行查询 返回数据集
    public function query($sql) {
        $this->checkConnect();
        //释放前次的查询结果
        if ( $this->_mysqlMemPoint ) $this->free();
        // 记录开始执行时间
        G('queryStartTime');

        if(strpos($sql,'insert') !== false){
            ExceptionFrameLib::throwCatch(" $sql, sql include insert");
        }

        if(strpos($sql,'update') !== false){
            if(strpos($sql,'for update') !== false){

            }else{
                ExceptionFrameLib::throwCatch("  $sql, sql include update");
            }

        }

        if(strpos($sql,'delete') !== false){
            ExceptionFrameLib::throwCatch("  $sql, sql include delete");
        }

        $this->_mysqlMemPoint = mysqli_query($this->_masterFD,$sql);

        $this->debug($sql);//SQL日志
        if ( false === $this->_mysqlMemPoint ) {
            $err  = $this->error($this->_masterFD);
//            if($this->mTransaction){//这个位置是事务处理时，需要抛出异常
//           		throw new Exception($err);
//            }else{
            ExceptionFrameLib::throwCatch($sql."  ".$err);
//            }
        } else {
            return $this->getDbAll();
        }
    }
    //仅允许执行：update,delete~,返回addId upId
    public function execute($sql) {
        $this->authExecute($sql);
        $this->checkConnect();
        //释放前次的查询结果
        if ( $this->_mysqlMemPoint ) $this->free();
        // 记录开始执行时间
        G('queryStartTime');
        $result =   mysqli_query( $this->_masterFD,$sql) ;
        $this->debug($sql);
        if ( false === $result) {
            $err = $this->error($this->_masterFD);
            ExceptionFrameLib::throwCatch($err . $sql);
        } else {
            $this->_changeRow = mysqli_affected_rows($this->_masterFD);
            $this->_addId = mysqli_insert_id($this->_masterFD);
            return $this->_changeRow;
        }
    }
    //添加一条数据
    public function add( $data , $table = '' ) {
        $table = $this->getTable($table);
        // 写入数据到数据库
        $values  =  $fields    = array();
        foreach ($data as $key=>$val){
            $values[] =  $this->parseValue($val);
            $fields[] =  $this->parseKey($key);
        }

        $sql   =  'INSERT INTO `'.$table.'` ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $this->execute($sql);
        $insertId   =   $this->getLastInsID();
        return $insertId;
    }
    //添加多条数据
//    public function addAll($datas,$table = ''){
//        $table = $this->getTable($table);
//        // 写入数据到数据库
//        if(!is_array($datas[0])) return false;
//        $fields = array_keys($datas[0]);
//        array_walk($fields, array($this, 'parseKey'));
//        $values  =  array();
//        foreach ($datas as $data){
//            $value   =  array();
//            foreach ($data as $key=>$val){
//                $val   =  $this->parseValue($val);
//                if(is_scalar($val)) { // 过滤非标量数据
//                    $value[]   =  $val;
//                }
//            }
//            $values[]    = '('.implode(',', $value).')';
//        }
//        $sql   =  'INSERT INTO '.$table.' ('.implode(',', $fields).') VALUES '.implode(',',$values);
//        $result = $this->execute($sql);
//        if(false !== $result ) {
//            $insertId   =   $this->getLastInsID();
//            if($insertId) {
//                return $insertId;
//            }
//        }
//        return $result;
//    }
    //根据 IDS 删除多条记录
    function delByIds($ids ,$table ='',$fieldName = ''){
        $table = $this->getTable($table);
        $fieldName = $this->getPrimary($fieldName);

        $limit = count(explode(",",$ids));
        $sql = "delete from `$table` where `$fieldName` in ( $ids ) limit $limit";
        return $this->execute($sql);
    }
    //根据自增ID，删除一条记录
    function delById($id ,$table ='',$fieldName = ''){
        $table = $this->getTable($table);
        $fieldName = $this->getPrimary($fieldName);

        $sql = "delete from `$table` where `$fieldName` =  $id  limit 1";
        return $this->execute($sql);
    }

    function delete($where ,$table =''){
        $table = $this->getTable($table);

        $sql = "delete from `$table` where $where";
        return $this->execute($sql);
    }
    //数据库更新
    public function update($data , $where , $table = ''  ) {
        $table = $this->getTable($table);

        $data = $this->parseSet($data);
        $sql = "update `" . $table . "`" .  $data . " where " . $where;

        return $this->execute($sql);
    }
    //根据自增ID更新
    public function upById($id,$data,$table = '') {
        if(!$table){
            $table = $this->getTable();
        }

        $pk = $this->getPrimary();
        $where = " `$pk` = $id limit 1";

        $data = $this->parseSet($data);
        $sql = "update `" . $table . "`" .  $data . " where " . $where;

//        LogLib::appWriteFileHash($sql);

        return $this->execute($sql);
    }
    //获取表名
    function getTable($table = ''){
        if($table)
            return $table;

        if($this->_table)
            return $this->_table;

        ExceptionFrameLib::throwCatch('table is null....');
    }
    //获取表主键
    function getPrimary($primary = ''){
        if($primary)
            return $primary;

        if($this->_pk)
            return $this->_pk;

        ExceptionFrameLib::throwCatch('primary is null....','DB');
    }
    function selectDb($fd,$name){
        return mysqli_select_db( $fd,$name);
    }

    //更新数据时，格式化数据
    function parseSet($data) {
        foreach ($data as $key=>$val){
            if(is_scalar($val)){ // 过滤非标量数据
                $value   =  $this->parseValue($val);
                $set[]    =  "`".$key.'`='.$value;
            }else{
                $value   =  $this->parseValue($val[0]);
                $set[]    =  "`".$key.'`='."`$key` + " .$value;
            }
        }
        return ' SET '.implode(',',$set);
    }
    //给值添加单引号，防止SQL注入,数字是不需要添加的，字符串需要添加
    function parseValue($value) {
        if(is_string($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }
    //字段和表名处理添加:<`>
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
            $key = '`'.$key.'`';
        }
        return $key;
    }
    //释放查询资源
    public function free() {
        mysqli_free_result($this->_mysqlMemPoint);
        $this->_mysqlMemPoint = null;
    }
    //  SQL指令安全过滤
    public function escapeString($str) {
        if ( !$this->_masterFD ){
            $this->checkConnect();
        }

        return mysqli_real_escape_string($this->_masterFD,$str);
    }
    //debug
    protected function debug($sql) {
        global $db_sql_cnt;
        if ( DEBUG && DEBUG == 1  ) {// 记录操作结束时间
            G('queryEndTime');
            $db_sql_cnt[] =  $sql.' [ RunTime:'.G('queryStartTime','queryEndTime',6).'s ]';
            LogLib::MysqlWriteFileHash([ $sql,' [ '.G('queryStartTime','queryEndTime',6).'s ]']);
        }
    }
    //验证数据库配置文件信息
    function authConfig($db_key){
        if(!$db_key){
            ExceptionFrameLib::throwCatch('auth db key err:null','DB');
        }
        $config =  $GLOBALS[APP_NAME]['db_config'][$db_key];
        if(!$config){
            ExceptionFrameLib::throwCatch('auth db key err:$GLOBALS[db_config][$db_key]','DB');
        }

    	if(!is_array($config)){
            ExceptionFrameLib::throwCatch('auth db key err:$config is no arr','DB');
    	}
    	foreach($this->_configKey as $k=>$v){
    	    $f = 0;
    	    foreach ($config['master'] as  $re=>$reConfig) {
                if( $re == $v ){
                    $f = 1;
                    break;
                }
    	    }
    	    if(!$f){
                ExceptionFrameLib::throwCatch('master db_config para error','DB');
            }
    	}

        foreach($this->_configKey as $k=>$v){
            $f = 0;
            foreach ($config['slave'] as  $re=>$reConfig) {
                if( $re == $v ){
                    $f = 1;
                    break;
                }
            }
            if(!$f){
                ExceptionFrameLib::throwCatch('slave db_config para error','DB');
            }
        }
    	$this->_config = $config;
    }

    function getOneByOneField($field,$value){
        $where = " $field = '$value' ";
        return $this->getRow($where);
    }

    //验证execute方法，只允许执行update 和 delete 且   包含 limit 且 不能操作大于100行
    function authExecute($str){
        $str = trim($str);
        $tmp_str = strtolower(substr($str, 0 ,6));
        if('insert' == $tmp_str)return 1;
        if($tmp_str != 'delete' && $tmp_str != 'update'){
//    		echo $str;
            ExceptionFrameLib::throwCatch('execute func method: <update> or <delete>');
        }

        $location = strpos($str,'limit');
        if( $location === false){
//    		echo $str;
            ExceptionFrameLib::throwCatch('execute func update or delete : required <limit>');
        }

        if(strpos($str,'where') === false){
//    		echo $str;
            ExceptionFrameLib::throwCatch('execute func update or delete : required <where>');
        }

//    	$tmp_str2 = trim(substr($str, $location + 6 ));
//    	if($tmp_str2 > 100){
//    		echo $str;
//    		ExceptionFrameLib::throwCatch('execute func update or delete : limit numbers < 100','DB');
//    	}
    }

//事务处理--------------------------------------------------
//    function begin(){
//    	$this->mDb->mTransaction = 1;
//    	$this->querySql('SET AUTOCOMMIT=0');
//    	$this->querySql('BEGIN');
//    }
//    function rollback(){
//    	$this->querySql('rollback');
//    }
//
//    function commit(){
//    	$this->querySql('commit');
//    	$this->querySql('SET AUTOCOMMIT=1');
//    	$this->mDb->mTransaction = 0;
//    }

    //此个功能是仅仅给执行SQL不需要返回值的情况
//    function querySql($sql){
//    	N('db_query',1);//记录共执行了多少条SQL
//    	G('queryStartTime');// 记录开始执行时间
//    	$this->checkConnect();
//    	$rs = mysqli_query($this->mDb,$sql);
//    	$this->debug();//SQL日志
//    	return $rs;
//    }
//事务处理--------------------------------------------------

//-------------------各种SELECT-获取操作----get----------------------------------
    function addFormData($ignore = '',$table){
        $fields = $this->getFields('',$table);
        $ignore_arr = array();
        $ignore_arr[] = $this->getPrimary();
        $rs = array();
        foreach($fields as $k=>$v){
            if(in_array($v,$ignore))
                continue;

            $rs[$v] = _g($k);
        }
        return $rs;
    }
    function countTableTotal($where  = '',$filed = '*', $table){
        $sql = "select count($filed) as total from $table where $where ";
        $rs = $this->getOne( $sql );
        return $rs;
    }
    function getAllBySQL($sql){
        $dbRs = $this->query($sql);
        return $dbRs;
    }

    function getRowBySQL($sql ){
        $dbRs = $this->query($sql);
        if($dbRs)
            return $dbRs[0];
    }

    function getOneBySQL($sql){
        $dbRs = $this->query($sql);

        return $dbRs[0]['total'];
    }

    function getTableAll($table = ''){
        if(!$table)
            $table = $this->_table;

        $sql = "select * from `$table` where 1";
        $rs = $this->getAllBySQL( $sql );
        return $rs;

    }

    function getOne($where = ' 1 = 1 ',$table = "",$filed = '*' ){
        if(!$table)
            $table = $this->_table;

        $sql = "select $filed as total from $table where $where";
        $rs = $this->getOneBySQL( $sql );
        return $rs;
    }

    function getAll($where = ' 1 = 1 ',$table = '' ,$filed = '*'){
        if(!$table)
            $table = $this->_table;

        if(!$filed)
            $filed = "*";

        $sql = "select $filed from `$table` where $where";
        $rs = $this->getAllBySQL( $sql );
        return $rs;
    }

    function getRow($where = ' 1 = 1 ',$table = '' ,$filed = '*'){
        if(!$table)
            $table = $this->_table;

        if(!$filed)
            $filed = "*";

        $sql = "select $filed from `$table` where $where";
        $rs = $this->getRowBySQL( $sql );

        return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getRowById($id = '',$pk = '',$table = '' ,$filed = '*'){
        $table = $this->getTable($table);
        $pk_filed = $this->getPrimary($pk);

        $sql = "select $filed  from `$table` where $pk_filed = $id";
        $rs = $this->getRowBySQL( $sql );
        return $rs;
    }
    function getById($id = '',$pk = '',$table = '' ,$filed = '*'){
        $table = $this->getTable($table);
        $pk_filed = $this->getPrimary($pk);

        $sql = "select $filed  from `$table` where $pk_filed = $id";
        $rs = $this->getRowBySQL( $sql );
        return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getRowByIds($ids = '',$table = '' ,$filed = '*'){
        $table = $this->getTable($table);
        $id_filed = $this->getPrimary();

        $sql = "select $filed  from `$table` where $id_filed in ( $ids )";
        $rs = $this->getRowBySQL( $sql );
        return $rs;
    }
    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getAllByIds($ids = '',$table = '' ,$filed = '*'){
        $table = $this->getTable($table);
        $id_filed = $this->getPrimary();

        $sql = "select $filed  from `$table` where $id_filed in ( $ids )";
        $rs = $this->getAllBySQL( $sql );
        return $rs;
    }

    function getOneFieldValueById($id,$fieldName,$defaultValue = '---'){
        $row = $this->getById($id,null,$this->_table,$fieldName);
        if(!$row){
            return $defaultValue;
        }

        return $row[$fieldName];
    }

    //根据主键ID(字段名必须 为:id)，获得一条记录
    function getByIds($ids = '',$table = '' ,$filed = '*'){
        $table = $this->getTable($table);
        $id_filed = $this->getPrimary();
        $sql = "select $filed  from `$table` where $id_filed in ( $ids )";
        $rs = $this->getRowBySQL( $sql );
        return $rs;
    }


    function getCount( $where = ' 1 = 1 ',$table = ''){
        if(!$table)
            $table = $this->_table;

        $sql = "select count(*) as total from $table where $where";
        $rs = $this->getOneBySQL( $sql );
        return $rs;
    }

    public function getLastInsID() {
        return $this->_addId;
    }
    //数据库错误信息.记录并显示当前的SQL语句
    public function error( $fd ) {
        $this->error = mysqli_error($fd);
        return $this->error;
    }
    //返回数据集
    private function getDbAll() {
        $result = array();
        if($this->_mysqlMemPoint) {
            if($this->_fetchArray){
                while($row = mysqli_fetch_row($this->_mysqlMemPoint)){
                    $result[]   =   $row;
                }
            }else{
                while($row = mysqli_fetch_assoc($this->_mysqlMemPoint)){
                    $result[]   =   $row;
                }
            }

        }
        return $result;
    }

//数据库结构操作---------------------------------------------------------------------------------------------------------------     
    //获取数据库信息
    function getDbInfo($dbName=''){
        if(!$dbName)
            $dbName = $this->config['db_name'];

        $sql = "select DEFAULT_CHARACTER_SET_NAME,DEFAULT_COLLATION_NAME from information_schema.SCHEMATA where SCHEMA_name = '$dbName' ";//数据库信息
        $dbInfo = $this->getRow($sql);

    }
    //取得数据库的所有 表
    function showTablesList($dbName='') {
        if(!empty($dbName)) {
            $sql    = 'SHOW TABLES FROM '.$dbName;
        }else{
            $sql    = 'SHOW TABLES ';
        }
        $result =   $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }
    //取得数据库的所有 表
    public function getTablesList($dbName='') {
        if(!$dbName)
            $dbName = $this->config['db_name'];

        $sql = "select * from information_schema.TABLES where  table_schema = '$dbName'";//列相关信息
        $rs =  $this->getAll($sql);

//     	$rs[$v]['ENGINE'] = $row['ENGINE'];
//     	$rs[$v]['ENGINE'] = $row['ENGINE'];

        return $rs;
    }

    public function getFieldsByTable(){
        $fields =  $this->getFields("",array($this->_table));
        $fields = $fields[$this->_table];
        $typeArr = array("int",'bigint','tinyint','smallint','mediumint',);
        $rs = [];
        foreach ($fields as $k=>$v) {
            $defaultValue = "";
            foreach ($typeArr as $k2=>$v2) {
                if(strpos($v['COLUMN_TYPE'],$v2) !== false){
                    $defaultValue = 0;
                    break;
                }
            }
            $rs[$v['COLUMN_NAME']] = $defaultValue;
        }

        return $rs;
    }

    //取得数据库字段信息
    public function getFields($dbName = '',$table='') {
        if(!$dbName){
            $config =  $GLOBALS[APP_NAME]['db_config'][$this->_configMapKey];
            $dbName = $config['master']['db_name'];
        }

        if(!$table)
            $table = $this->showTablesList();

        if(!is_array($table))
            ExceptionFrameLib::throwCatch('table must array!'.'DB');

        $rs = array();
        foreach($table as $k=>$v){
            $sql = "select COLUMN_NAME,COLUMN_TYPE,COLUMN_DEFAULT,IS_NULLABLE,COLUMN_COMMENT,COLUMN_KEY from information_schema.COLUMNS where table_schema = '$dbName' and table_name = '$v' order by ORDINAL_POSITION";//列相关信息
            $row =  $this->getAllBySQL($sql);
            if($row){
                $rs[$v] = $row;
//     			foreach($row as $k2=>$v2){
//     				if($v['COLUMN_KEY']){
//     					$t = $indexDesc[$v['COLUMN_KEY']];
//     					if($v['EXTRA']){
//     						$t .= ' 自增';
//     					}
//     					$table->addCell()->addText($t);
//     				}
//     			}
            }
        }

        return $rs;
    }
    //获取表的索引信息
    function getTableIndex($dbName = '',$table = ''){
        if(!$dbName)
            $dbName = $this->config['db_name'];
        if(!$table)
            $table = $this->showTablesList();
        if(!is_array($table))
            ExceptionFrameLib::throwCatch('table must array!'.'DB');

        $rs = array();
        foreach($table as $k=>$v){
            $sql = "select * from `information_schema`.`STATISTICS` where table_schema = '$dbName' and table_name = '$v'";//索引
            $index = $this->getAllBySQL($sql);
            if($index){
                foreach($index as $k2=>$v2){
                    $rs[$v][$v2['COLUMN_NAME']] = $v2['INDEX_NAME'];
                }
            }else{
                $rs[$v] = null;
            }
        }

        return $rs;

    }

    function checkTable($table,$db = ''){
        $tables = $this->showTablesList($db);
        if($tables){
            $f = 0;
            foreach	($tables as $k=>$v){
                if($v == $table){
                    $f=1;
                    break;
                }
            }
            return $f;
        }
    }
//数据库结构操作-------------------------------------    
}
