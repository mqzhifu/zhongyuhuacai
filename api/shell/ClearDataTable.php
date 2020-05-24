<?php
class ClearDataTable{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

//        if(!arrKeyIssetAndExist($attr,'ac')){
//            exit("please ac=xxx ,wsLog  wsCnt wsCntByHour xyxCntByHour delWsLog delWsCnt upWslog  upWsCnt accessLogMore delAccessLogMore sign. \n");
//        }
//
//        $ac = $attr['ac'];
//        $this->$ac();
        $this->clearTable();
    }



    function clearTable( ){
        $sql = " show tables";
        $fd = $this->getMysql();
        $rs = mysqli_query($fd,$sql);
        if(!$rs){
            var_dump($rs);exit;
        }

        $result = array();
        if($rs) {
            while($row = mysqli_fetch_assoc($rs)){
                $result[]   =   $row;
            }
        }
        $except_table = array('user','user_detail','menu','admin_user','roles','task_config','games_category','sms_rule','email_rule');

        echo "total table cnt:".count($result)."\n";

        foreach ($result as $k=>$v) {
            $tableName = $v['Tables_in_kxgame'];
            echo $tableName;
            if( !in_array($tableName,$except_table) ){
                $sql = " truncate table $tableName";
//                $rs = mysqli_query($fd,$sql);
//                var_dump($rs);
                echo " ".$sql . "\n";
            }else{
                echo "no process.\n";
            }
        }

        echo "done.\n";
        exit;


    }

    function upField($tablePre){
        $sql = " show tables";
        $fd = $this->getMysql();
        $rs = mysqli_query($fd,$sql);
        if(!$rs){
            var_dump($rs);exit;
        }

        $result = array();
        if($rs) {
            while($row = mysqli_fetch_assoc($rs)){
                $result[]   =   $row;
            }
        }

        foreach ($result as $k=>$v) {
            $tableName = $v['Tables_in_kxgame_log'];
            if(strpos($tableName,$tablePre) !== false){
                $sql = " alter table $tableName add column `goldcoin` varchar (50) null DEFAULT 0 ";
                $rs = mysqli_query($fd,$sql);
                var_dump($rs,mysqli_error($fd));
            }
        }
    }

    function getMysql(){
//        if(ENV == 'release'){
//            $fd = mysqli_connect('10.10.7.144','instantplay','2vLboyEVX9J','kxgame_log');
//        }else{
//            $fd = mysqli_connect('10.10.7.60','games','pu6zMh2CQ55Q','kxgame_log');
//        }
        $fd = mysqli_connect('10.13.4.15','root','qEJy7ApUY@9n','kxgame');
        return $fd;
    }




    function sign(){
        $baseSql = "
        CREATE TABLE `#table#` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `uid` int(11) DEFAULT '0',
            `a_time` int(11) DEFAULT '0' COMMENT '签到时间',
            PRIMARY KEY (`id`),
            KEY `uid_INDEX` (`uid`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户签到记录'";

        $this->createTable(1,SignModel::$_table,$baseSql,'2019');
    }

    function wsLog(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT '0' COMMENT '用户ID',
          `a_time` int(11) DEFAULT '0' COMMENT '开始时间',
          `e_time` int(11) DEFAULT '0' COMMENT '结束时间',
          `fd` int(11) DEFAULT '0' COMMENT '连接ID',
          `ip` char(15) DEFAULT NULL COMMENT '总时长',
          `device_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '设置ID',
          `app_version` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'APP版本',
          `reg_time` int(11) DEFAULT '0' COMMENT '注册时间',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";


        $this->createTable(2,WsLogModel::$_table,$baseSql,'2019');
    }

    function accessLogMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL,
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `IP` char(15) DEFAULT NULL COMMENT '登陆IP地址',
          `long` varchar(50) DEFAULT NULL COMMENT '经度',
          `lat` varchar(50) DEFAULT NULL COMMENT '纬度',
          `area` varchar(255) DEFAULT NULL COMMENT '地区信息',
          `province` varchar(45) DEFAULT NULL COMMENT '省份名称',
          `city` varchar(45) DEFAULT NULL COMMENT '城市名称',
          `sex` tinyint(1) DEFAULT NULL COMMENT '性别0未知1男2女',
          `ctrl` varchar(50) DEFAULT NULL,
          `ac` varchar(50) DEFAULT NULL,
          `request` text COMMENT '请求参数',
          `code` int COMMENT 'api返回状态码',
          `return_info` text COMMENT '返回信息',
          `exec_time` varchar(20) DEFAULT NULL COMMENT '执行时间',
          `client_data` text COMMENT '客户端信息',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='记录用户登陆信息' ";

        $this->createTable(2,AccessLogMoreModel::$_table,$baseSql,'2019');

    }

    function wsCnt(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL COMMENT '用户ID',
          `ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'IP地址',
          `device_id` varchar(255) DEFAULT NULL COMMENT '设备ID',
          `login_times` int(11) DEFAULT NULL COMMENT '登陆次数',
          `total_time` int(11) DEFAULT NULL COMMENT '总时长',
          `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
          `end_time` int(11) DEFAULT NULL COMMENT '结束时间',
          `app_verstion` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'APP版本',
          `reg_time` int(11) DEFAULT NULL COMMENT '注册时间',
          `a_time` int(11) DEFAULT '0',
          `goldcoin` varchar(50) DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";


        $this->createTable(1,WsCntModel::$_table,$baseSql,"2019");
    }

    function wsCntByHour(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `active_user` int(11) NOT NULL DEFAULT 0,
          `total_time` int(11) DEFAULT NULL COMMENT '总时长',
          `new_reg_user` int(11) NOT NULL DEFAULT 0 COMMENT '新增用户数',
          `etime_active_user` text NOT NULL DEFAULT '' COMMENT '多个uid组合的串,可能会重复uid,用于按照天来统计活跃用户数',
          `a_time` int(11) DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";


        $this->createTable(1,WsCntByHourModel::$_table,$baseSql,"2019");
    }

    function xyxCntByHour(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `game_id` int(11) NOT NULL DEFAULT 0 COMMENT '游戏id',
          `total_time` int(11) DEFAULT NULL COMMENT '总时长',
          `active_user_num` int(11) NOT NULL DEFAULT 0 COMMENT '活跃用户数',
          `active_uids` text NOT NULL DEFAULT '' COMMENT '活跃用户的uid集合',
          `etime_active_user` text NOT NULL DEFAULT '' COMMENT '多个uid组合的串,可能会重复uid,用于按照天来统计活跃用户数',
          `new_reg_user` int(11) NOT NULL DEFAULT 0 COMMENT '新增用户数',
          `a_time` int(11) DEFAULT '0' COMMENT '添加时间段 例如2019-04-29 02:00的时间戳',
          PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8";


        $this->createTable(1,XYXCntByHourModel::$_table,$baseSql,"2019");
    }
    //1按照月划分2按照天划分
    function createTable($type = 1,$table_pre,$baseSql,$year){
        if(!$year){
            exit('year is null');
        }

        $start_time = strtotime($year."0101 00:00:00");
        if($type == 1){
            $e = 12;

        }elseif($type == 2){
            $e = 365;
        }else{
            exit("type is err");
        }


        $fd = $this->getMysql();

        $start = $start_time;
        if($type == 1){
            $now = strtotime(date("Ym") . "01 00:00:00");
        }else{
            $now = strtotime(date("Ymd") . " 00:00:00");
        }


        echo date("Y-m-d",$start)."\n";

        for($i=0;$i< $e;$i++){
            $j = $i+1;
            if( $now > $start){
                if($type == 1){
                    $start = strtotime("+{$j} month",$start_time );
                }else{
                    $start = strtotime("+{$j} day",$start_time );
                }
                continue;
            }

            echo date("Y-m-d",$start)."\n";

            if($type == 1){
                $ym = date("Ym",$start);
                $tableName = $table_pre . $ym;
                $start = strtotime("+{$j} month",$start_time );
            }else{
                $ymd = date("Ymd",$start);
                $tableName = $table_pre . $ymd;
                $start = strtotime("+{$j} day",$start_time );
            }

            $sql = str_replace("#table#", $tableName,$baseSql);
//            var_dump($sql);
//            exit;
            mysqli_query($fd,$sql);
        }
    }

}



