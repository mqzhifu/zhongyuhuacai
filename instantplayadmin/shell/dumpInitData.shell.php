<?php
//表中的数据，也包括元数据，初始化的时候，得备份
class DumpInitData{

    private $_initTable = array(
        //基础表，算是死的数据，基本不变，备份一次即可
        'base'=>array(
            'area_city','area_county','area_province','area_town','black_word','id_num','ip','mobile','university'
        ),
        //动态表，这里的数据，可能会调整，每次都得备份一下
        'dynamic'=>array(
            'menu',//菜单
            'admin_user',//后台管理员
            'roles',//角色
            'sms_rule',//短信规则
            'agent',//代理
            'factory',//工厂
            'banner',//小程序 - 首页轮播图
            'product_category',//产品分类
            'product_category_attr',//产品分类属性
            'product_category_attr_para',
//            'user',//小程序用户，不用备份了，脚本会动态处理
        ),
    );

    private $database = "instantplay";
    private $host = "127.0.0.1";
    private $user = "root";
    private $ps = "root";
    private $targetDir = "D:/www/zhongyuhuacai_doc/init_data/";
//    public $tbCategoryAttrId = 37;
//    public $tbCategoryId = 8;//分类:1688抓取，空属性值ID
    public function __construct($c){
        $this->commands = $c;
    }

    function run(){
        $startTime = time();
//        $this->makeBaseTable();
        fopen("{$this->targetDir}init.sql","w+");
        $this->makeDynamicTable();

        $this->getMysqlDumpAppend("banner", " id = 1 ");

        $endTime = time();

        $total = $endTime - $startTime;
        out("total time:".$total);

    }

    function makeDynamicTable(){
        foreach ($this->_initTable['dynamic'] as $k=>$table){
            $sql = $this->getMysqlDump($table,1);
            echo "exec $sql \n";
            exec($sql);
        }
    }

    function makeBaseTable(){
        foreach ($this->_initTable['base'] as $k=>$table){
            $sql = $this->getMysqlDump($table);
            echo "exec $sql \n";
            exec($sql);
        }
    }

    function getMysqlDump($table){
        $baseSql = "mysqldump  --default-character-set=utf8 -t -h{$this->host} -u{$this->user} -p{$this->ps} {$this->database} $table >> {$this->targetDir}init.sql";
        return $baseSql;
    }

    function getMysqlDumpAppend($table,$where = ""){
        $commend = "mysqldump  --default-character-set=utf8 -t -h{$this->host} -u{$this->user} -p{$this->ps} {$this->database} $table";
        if($where){
            $commend .= " --where $where";
        }
        $baseSql = " $commend  >> {$this->targetDir}{$table}.sql";
        return $baseSql;
    }

}