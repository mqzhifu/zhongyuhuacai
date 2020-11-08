<?php
//创建分表
class CreateSplitTable{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        if(!arrKeyIssetAndExist($attr,'ac')){
            exit("please ac=xxx ,wsLog  wsCnt wsCntByHour xyxCntByHour delWsLog delWsCnt upWslog  upWsCnt accessLogMore taskUserMore loginMore goldCoinLogMore playedGamesMore gamesScoreMore taskUserGroupMore delAccessLogMore sign. \n");
        }

        $ac = $attr['ac'];
        $this->$ac();
    }



    function upWslog(){
        $this->upField(WsLogModel::$_table);
    }
    function upWsCnt(){
        $this->upField(WsCntModel::$_table);
    }

    function delAccessLogMore(){
        $this->delTable(AccessLogMoreModel::$_table);
    }

    function delWsLog(){
        $this->delTable(WsLogModel::$_table);
    }

    function delWsCnt(){
        $this->delTable(WsCntModel::$_table);
    }

    function delTable($tablePre ){
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
                $sql = " drop table $tableName";
                $rs = mysqli_query($fd,$sql);
                var_dump($rs);
            }
        }


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
        if(PCK_AREA == 'cn'){
            if(ENV == 'release'){
                $fd = mysqli_connect('10.10.7.144','instantplay','2vLboyEVX9J','kxgame_log');
            }else{
                $fd = mysqli_connect('127.0.0.1','root','Dp74p966qPyTkBQ8','kxgame_log');
            }
        }else{
            if(ENV == 'release'){
//                $fd = mysqli_connect('10.10.7.144','instantplay','2vLboyEVX9J','kxgame_log');
            }else{
                $fd = mysqli_connect('127.0.0.1','games','pu6zMh2CQ55Q','kxgame_log');
            }
        }


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

    /**
     * time:2019/06/28;
     * 按月拆分成长任务表;
     */
    function taskUserMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL COMMENT '用户ID',
          `task_id` int(11) DEFAULT NULL COMMENT '主任务ID',
          `step` int(11) DEFAULT NULL COMMENT '当前完成了几步',
          `done_time` int(11) DEFAULT '0' COMMENT '完成时间',
          `goldcoin` int(11) DEFAULT NULL COMMENT '奖励金币',
          `point` int(11) DEFAULT NULL COMMENT '奖励积分',
          `reward_time` int(11) DEFAULT '0' COMMENT '领取时间',
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `s_time` int(11) DEFAULT NULL COMMENT '有效期开始',
          `e_time` int(11) DEFAULT NULL COMMENT '有效期结束',
          `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
          `task_config_type` tinyint(1) DEFAULT '0' COMMENT 'config主类型',
          `task_config_type_sub` tinyint(1) DEFAULT NULL COMMENT 'config子类型',
          `hook_info` varchar(255) DEFAULT NULL COMMENT '钩子的一些信息',
          `total_step` int(11) DEFAULT NULL COMMENT '任务总步数',
          `game_id` int(11) DEFAULT NULL COMMENT '游戏ID',
          `sort` int(11) DEFAULT NULL COMMENT '排序（数字越小越靠前；from：1）',
          PRIMARY KEY (`id`),
          KEY `uid_INDEX` (`uid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $this->createTable(1,TaskUserMoreModel::$_table,$baseSql,'2019');
    }

    /**
     * time:2019/06/29;
     * 按月拆分成长任务表;
     */
    function loginMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL,
          `ip` char(15) DEFAULT NULL,
          `lat` varchar(50) DEFAULT NULL,
          `lon` varchar(50) DEFAULT NULL,
          `addr` varchar(100) DEFAULT NULL COMMENT '详细地址',
          `cate` varchar(50) DEFAULT NULL COMMENT 'pc,h5,app',
          `os` varchar(50) DEFAULT NULL COMMENT '操作系统',
          `os_version` varchar(50) DEFAULT NULL COMMENT '操作系统-版本',
          `app_version` varchar(50) DEFAULT NULL COMMENT 'app版本',
          `device_model` varchar(50) DEFAULT NULL COMMENT 'iphone,ipad',
          `device_version` varchar(50) DEFAULT NULL COMMENT '设备版本',
          `browser_model` varchar(50) DEFAULT NULL COMMENT '浏览器类型',
          `browser_version` varchar(50) DEFAULT NULL COMMENT '浏览器版本',
          `ref` varchar(255) DEFAULT NULL COMMENT '来源',
          `user_agent` varchar(255) DEFAULT NULL COMMENT '用于查错',
          `sim_imsi` varchar(50) DEFAULT NULL COMMENT 'SIM卡的imsi号',
          `cellphone` varchar(15) DEFAULT NULL COMMENT '手机号',
          `dpi` varchar(50) DEFAULT NULL COMMENT '分辨率',
          `gps_geo_code` varchar(50) DEFAULT NULL COMMENT '经纬度转geo码',
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `type` tinyint(1) DEFAULT NULL COMMENT '1登陆2退出',
          `login_type` tinyint(2) DEFAULT NULL COMMENT '登陆方式',
          `sex` tinyint(1) DEFAULT NULL COMMENT '1男2女',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='登陆日志';";

        $this->createTable(1,loginMoreModel::$_table,$baseSql,'2019');
    }

    /**
     * time:2019/06/29;
     * 按月拆分金币日志表;
     */
    function goldCoinLogMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL,
          `num` decimal(10,2) DEFAULT NULL COMMENT '数量',
          `type` varchar(50) DEFAULT NULL COMMENT '调用者的KEY',
          `memo` varchar(255) DEFAULT NULL COMMENT '备注描述',
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `opt` tinyint(1) DEFAULT NULL COMMENT '1增加2减少',
          `title` varchar(100) DEFAULT NULL,
          `content` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `uid_INDEX` (`uid`),
          KEY `a_time_INDEX` (`a_time`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='金币日志';";

        $this->createTable(1,GoldCoinLogMoreModel::$_table,$baseSql,'2019');
    }

    /**
     * time:2019/06/29;
     * 按月拆分用户玩过的游戏表;
     */
    function playedGamesMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `game_id` int(11) DEFAULT NULL COMMENT '游戏ID',
          `uid` int(11) DEFAULT NULL COMMENT '用户ID',
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `e_time` int(11) DEFAULT NULL COMMENT '游戏结束时间',
          `src` tinyint(4) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `a_time` (`a_time`),
          KEY `index_uid` (`uid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户点击游戏日志';";

        $this->createTable(1,PlayedGamesMoreModel::$_table,$baseSql,'2019');
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

    /**
     * time:2019/07/01;
     * 按游戏ID取个位数字拆分用户成绩表;
     */
    function gamesScoreMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL,
          `score` int(11) DEFAULT NULL COMMENT '玩游戏的成绩',
          `game_id` int(11) DEFAULT NULL,
          `a_time` int(11) DEFAULT NULL,
          `u_time` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `game_id_INDEX` (`game_id`),
          KEY `index_score` (`score`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='开发者，持久化用户成绩';";
        $this->createTableById(GamesScoreMoreModel::$_table, $baseSql, 10);

    }

    /**
     * time:2019/07/01;
     * 按用户ID取个位数字拆分成长任务表;
     */
    function taskUserGroupMore(){
        $baseSql = "CREATE TABLE `#table#` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uid` int(11) DEFAULT NULL COMMENT '用户ID',
          `task_id` int(11) DEFAULT NULL COMMENT '主任务ID',
          `step` int(11) DEFAULT NULL COMMENT '当前完成了几步',
          `done_time` int(11) DEFAULT '0' COMMENT '完成时间',
          `goldcoin` int(11) DEFAULT NULL COMMENT '奖励金币',
          `point` int(11) DEFAULT NULL COMMENT '奖励积分',
          `reward_time` int(11) DEFAULT '0' COMMENT '领取时间',
          `a_time` int(11) DEFAULT NULL COMMENT '添加时间',
          `s_time` int(11) DEFAULT NULL COMMENT '有效期开始',
          `e_time` int(11) DEFAULT NULL COMMENT '有效期结束',
          `u_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
          `task_config_type` tinyint(1) DEFAULT '0' COMMENT 'config主类型',
          `task_config_type_sub` tinyint(1) DEFAULT NULL COMMENT 'config子类型',
          `hook_info` varchar(255) DEFAULT NULL COMMENT '钩子的一些信息',
          `total_step` int(11) DEFAULT NULL COMMENT '任务总步数',
          `game_id` int(11) DEFAULT NULL COMMENT '游戏ID',
          `sort` int(11) DEFAULT NULL COMMENT '排序（数字越小越靠前；from：1）',
          PRIMARY KEY (`id`),
          KEY `uid_INDEX` (`uid`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        $this->createTableById(TaskUserGroupMoreModel::$_table, $baseSql, 3);

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

    function createHashTable($hashMaxKey,$table_pre,$baseSql){
        $fd = $this->getMysql();

        for($i=0;$i<$hashMaxKey;$i++){
            $tableName = $table_pre . $i;
            $sql = str_replace("#table#", $tableName,$baseSql);
            mysqli_query($fd,$sql);
        }
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

    /**
     * 用户id/游戏id取末尾数字拆分;
     * @param $table_pre
     * @param $baseSql
     */
    public function createTableById($table_pre, $baseSql, $nums = 3){
        $fd = $this->getMysql();
        for($i=0;$i< $nums;$i++){
            echo ($i+1).": ";
            $tableName = $table_pre . $i;
            $sql = str_replace("#table#", $tableName, $baseSql);
            mysqli_query($fd,$sql);
            echo $tableName;
            echo "\n";
        }
    }

}



