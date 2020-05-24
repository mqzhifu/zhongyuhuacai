<?php

class RedisPHPLib{
    public $redis_obj = null;//redis实例化时静态变量
//	public $host = '127.0.0.1';
//	public $port = '6379';
    public  $conn = null;
    public  $delimiter = "##";
    public  $conn_pool = null;
    public $_config = null;
    static $_inc = null;
    static function inc(){
        if(self::$_inc){
            return self::$_inc;
        }

        self::$_inc = new self();
        return self::$_inc;
    }

    function __construct($config){
        $this->_config = $config;
    }

    function getServerConnFD($config_key = null){
        if($this->conn){
            return $this->conn;
        }
        if(!$config_key){
            $config = $this->_config;
        }else{
            $config = $GLOBALS['redis'][DEF_REDIS_CONN];
        }

        $this->conn = new Redis();
        $this->conn ->pconnect($config['host'],$config['port'] ,20 );
        if(isset($config['ps']) && $config['ps']){
            $this->conn->auth($config['ps']);
        }

        return $this->conn;
    }

     function getServerPoolConnFD($config_key = null){
        if(!$config_key){
            $config = $GLOBALS['redis'][DEF_REDIS_CONN];
            $config_key = DEF_REDIS_CONN;
        }else{
            $config = $GLOBALS['redis'][$config_key];
        }

        $pool = self::$conn_pool;
        if( arrKeyIssetAndExist($pool,$config_key)  ){
            return $pool[$config_key];
        }

        $redisLib  = new Redis();
        $redisLib->pconnect($config['host'],$config['port'] ,20 );
        if(isset($config['ps']) && $config['ps']){
            $redisLib->auth($config['ps']);
        }

        $pool[$config_key] = $redisLib;
        return $pool[$config_key];
    }


//    public function __construct($config_key) {
//        $config = $GLOBALS['redis'][$config_key];
//        $this->redis_obj = new Redis();
//        $this->redis_obj->connect($config['host'],$config['port']  );
//        if(isset($config['ps']) && $config['ps']){
//            $this->redis_obj->auth($config['ps']);
//        }
//
//        return $this->redis_obj;
//    }


     function getAppKeyById($key,$id = null,$appName = null){
        if(!$appName){
            $appName = APP_NAME;
        }

        if($id){
            $key = $appName."_".$key."_".$id;
        }else{
            $key = $appName."_".$key;
        }

        return $key;
    }


     function delLock($key){
        $script = "if redis.call('get', KEYS[1]) then return redis.call('del', KEYS[1]) else return 0 end";
        $delRs = $this->getServerConnFD()->eval($script,array($key),1);

        return $delRs;
    }

     function set($key, $value, $timeOut=0,$jsonEncode = false) {
        if($jsonEncode){
            $value = json_encode($value);
        }
        if ($timeOut > 0){
            $rs = $this->getServerConnFD()->setex($key,$timeOut,$value);
        }else{
            $rs =  $this->getServerConnFD()->set($key, $value);
        }

        return $rs;
    }

      function get($key,$jsonDecode = false){
        $rs =  $this->getServerConnFD()->get($key);//不存在返回false
        if($rs === 'false'){
            return false;
        }

        if($jsonDecode){
            $rs = json_decode($rs,true);
        }

        return $rs;
    }


     function setByDelimiter($key, $value, $timeOut=0,$delimiter = false) {
        if(!$delimiter){
            $delimiter = self::$delimiter;
        }
        if(!is_array($value)){
            return false;
        }
        $value = implode($delimiter,$value);
        if ($timeOut > 0){
            $rs = $this->getServerConnFD()->SETEX($key,$timeOut,$value);
        }else{
            $rs =  $this->getServerConnFD()->set($key, $value);
        }

        return $rs;
    }

      function getByDelimiter($key,$delimiter = false){
        $rs =  $this->getServerConnFD()->get($key);//不存在返回false
        if($rs === 'false'){
            return false;
        }

        if(!$delimiter){
            $delimiter = self::$delimiter;
        }

        $rs = explode($delimiter,$rs);

        return $rs;
    }
    //hash

    //获取一个字段值
     function hget($key,$filed,$jsonDecode = false){
        $re = $this->getServerConnFD()->hget($key,$filed);
        if(!$re){
            return false;
        }

        if($jsonDecode){
            $re = json_decode($jsonDecode,true);
        }


        return $re;
    }
    //设置一个字段值
     function hset($key,$filed,$value,$jsonEncode = false){
        if($jsonEncode){
            $value = json_encode($value);
        }
        $re = $this->getServerConnFD()->hset($key,$filed,$value);
        if(!$re){
            return false;
        }
        return $re;
    }
    //获取整个HASH 列表的全部值
//     function hgetall($key){
//        $re = $this->getServerConnFD()->hgetall($key);
//        if(!$re){
//            return false;
//        }
//        return $re;
//    }

//     function hsetAll($key,$value){
//
//    }
//
//    //设置若干个KEY-VALUES
//     function hmset($key,$data,$timeOut=0){
//    }

    //hash end
    function eval($script,$key,$num){
        return $this->getServerConnFD()->eval($script,$key,$num);
    }

    function incr($key){
        return $this->getServerConnFD()->incr($key);
    }

    //     * 增，构建一个列表(先进后去，类似栈)
     public function lpush($key,$value,$isJson = 0 ){
        if($isJson){
            $value = json_encode($value);
        }

        return $this->getServerConnFD()->lPush($key,$value);
    }

    /**
     * 增，构建一个列表(先进先去，类似队列)
     * @param string $key KEY名称
     * @param string $value 值
     * @param $timeOut |num  过期时间
     */
     function rPop($key,$isJson = 0){
        $rs = $this->getServerConnFD()->rPop($key);
        if(!$rs){
            return 0;
        }

        if($isJson){
            return json_decode($rs,true);
        }

        return $rs;
    }

     function getLockByKey($key,$expire){
        $config = array('nx', 'ex'=>$GLOBALS['rediskey']['getMoneyLock']['expire']);
        $addLockRs = RedisPHPLib::getServerConnFD()->set($key,time(),$config);
    }


    /**
     * 查，获取所有列表数据（从头到尾取）
     * @param string $key KEY名称
     * @param int $head  开始
     * @param int $tail     结束
     */
    public function lranges($key,$head,$tail){
        return $this->redis_obj->lrange($key,$head,$tail);
    }




    /*------------------------------------2.end list结构----------------------------------------------------*/






    /*------------------------------------3.start set结构----------------------------------------------------*/

    /**
     * 增，构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value  值
     * @param int $timeOut 时间  0表示无过期时间
     * @return
     */
    public function sadd($key,$value,$timeOut = 0){
        $re = $this->redis_obj->sadd($key,$value);
        if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，取集合对应元素
     * @param string $key 集合名字
     */
    public function smembers($key){
        $re =   $this->redis_obj->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;
        return $this->redis_obj->smembers($key);
    }

    /*------------------------------------3.end  set结构----------------------------------------------------*/


    /*------------------------------------4.start sort set结构----------------------------------------------------*/
    /*
     * 增，改，构建一个集合(有序集合),支持批量写入,更新
     * @param string $key 集合名称
     * @param array $score_value key为scoll, value为该权的值
     * @return int 插入操作成功返回插入数量【,更新操作返回0】
     */
    public function zadd($key,$score_value,$timeOut =0){
        if(!is_array($score_value)) return false;
        $a = 0;//存放插入的数量
        foreach($score_value as $score=>$value){
            $re =  $this->redis_obj->zadd($key,$score,$value);//当修改时，可以修改，但不返回更新数量
            $re && $a+=1;
            if ($timeOut > 0) $this->redis_obj->expire($key, $timeOut);
        }
        return $a;
    }

    /**
     * 查，有序集合查询，可升序降序,默认从第一条开始，查询一条数据
     * @param $key ,查询的键值
     * @param $min ,从第$min条开始
     * @param $max，查询的条数
     * @param $order ，asc表示升序排序，desc表示降序排序
     * @return array|bool 如果成功，返回查询信息，如果失败返回false
     */
    public function zrange($key,$min = 0 ,$num = 1,$order = 'desc'){
        $re =   $this->redis_obj->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;//不存在键值
        if('desc' == strtolower($order)){
            $re = $this->redis_obj->zrevrange($key,$min ,$min+$num-1);
        }else{
            $re = $this->redis_obj->zrange($key,$min ,$min+$num-1);
        }
        if(!$re) return false;//查询的范围值为空
        return $re;
    }

    /**
     * 返回集合key中，成员member的排名
     * @param $key，键值
     * @param $member，scroll值
     * @param $type ,是顺序查找还是逆序
     * @return bool,键值不存在返回false，存在返回其排名下标
     */
    public function zrank($key,$member,$type = 'desc'){
        $type = strtolower(trim($type));
        if($type == 'desc'){
            $re = $this->redis_obj->zrevrank($key,$member);//其中有序集成员按score值递减(从大到小)顺序排列，返回其排位
        }else{
            $re = $this->redis_obj->zrank($key,$member);//其中有序集成员按score值递增(从小到大)顺序排列，返回其排位
        }
        if(!is_numeric($re)) return false;//不存在键值
        return $re;
    }

    /**
     * 返回名称为key的zset中score >= star且score <= end的所有元素
     * @param $key
     * @param $member
     * @param $star，
     * @param $end,
     * @return array
     */
    public function zrangbyscore($key,$star,$end){
        return $this->redis_obj->ZRANGEBYSCORE($key,$star,$end);
    }

    /**
     * 返回名称为key的zset中元素member的score
     * @param $key
     * @param $member
     * @return string ,返回查询的member值
     */
    function zscore($key,$member){
        return $this->redis_obj->ZSCORE($key,$member);
    }
    /*------------------------------------4.end sort set结构----------------------------------------------------*/




    /*------------------------------------5.hash结构----------------------------------------------------*/



    /*------------------------------------end hash结构----------------------------------------------------*/




    /*------------------------------------其他结构----------------------------------------------------*/
    /**
     * 设置自增,自减功能
     * @param $key ，要改变的键值
     * @param int $num ，改变的幅度，默认为1
     * @param string $member ，类型是zset或hash，需要在输入member或filed字段
     * @param string $type，类型，default为普通增减,还有:zset,hash
     * @return bool|int 成功返回自增后的scroll整数，失败返回false
     */
    public function incre($key,$num = 1,$member = '',$type=''){
        $num = intval($num);
        switch(strtolower(trim($type))){
            case "zset":
                $re = $this->redis_obj->zIncrBy($key,$num,$member);//增长权值
                break;
            case "hash":
                $re = $this->redis_obj->hincrby($key,$member,$num);//增长hashmap里的值
                break;
            default:
                if($num > 0){
                    $re = $this->redis_obj->incrby($key,$num);//默认增长
                }else{
                    $re = $this->redis_obj->decrBy($key,-$num);//默认增长
                }
                break;
        }
        if($re) return $re;
        return false;
    }


    /**
     * 清除缓存
     * @param int $type 默认为0，清除当前数据库；1表示清除所有缓存
     */
    function flush($type = 0){
        if($type) {
            $this->redis_obj->flushAll();//清除所有数据库
        }else{
            $this->redis_obj->flushdb();//清除当前数据库
        }
    }

    /**
     * 检验某个键值是否存在
     * @param $keys ，键值
     * @param string $type，类型，默认为常规
     * @param string $field。若为hash类型，输入$field
     * @return bool
     */
    public function exists($keys,$type = '',$field=''){
        switch(strtolower(trim($type))){
            case 'hash':
                $re = $this->redis_obj->hexists($keys,$field);//有返回1，无返回0
                break;
            default:
                $re = $this->redis_obj->exists($keys);
                break;
        }
        return $re;
    }

    /**
     * 删除缓存
     * @param string|array $key，键值
     * @param $type，类型，默认为常规，还有hash,zset
     * @param string $field,hash=>表示$field值，set=>表示value,zset=>表示value值，list类型特殊暂时不加
     * @return int | ，返回删除的个数
     */
    public function delete($key,$type = '',$field = ''){
        switch(strtolower(trim($type))){
            case 'hash':
                $re = $this->redis_obj->hDel($key,$field);//返回删除个数
                break;
            case 'set':
                $re = $this->redis_obj->sRem($key,$field);//返回删除个数
                break;
            case 'zset':
                $re = $this->redis_obj->zDelete($key,$field);//返回删除个数
                break;
            default:
                $re = $this->redis_obj->del($key);//返回删除个数
                break;
        }
        return $re;
    }

    //日志记录
    public function logger($log_content,$position = 'user')
    {
        $max_size = 1000000;   //声明日志的最大尺寸1000K

        $log_dir = './log';//日志存放根目录

        if(!file_exists($log_dir)) mkdir($log_dir,0777);//如果不存在该文件夹，创建

        if($position == 'user'){
            $log_filename = "{$log_dir}/User_redis_log.txt";  //日志名称
        }else{
            $log_filename = "{$log_dir}/Wap_redis_log.txt";  //日志名称
        }

        //如果文件存在并且大于了规定的最大尺寸就删除了
        if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)){
            unlink($log_filename);
        }

        //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
        file_put_contents($log_filename, date('Y-m-d_H:i:s')." ".$log_content."\n", FILE_APPEND);
    }


//    function __destruct()
//    {
//        $this->redis_obj->close();
//    }


}