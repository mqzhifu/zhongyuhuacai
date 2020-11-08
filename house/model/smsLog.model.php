<?php
class SmsLogModel {
    static $_table = 'sms_log';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    const STATUS_OK = 1;
    const STATUS_FAILED = 2;
    const STATUS_SENDING = 3;
    const STATUS_WAIT = 4;
    const STATUS_DESC = [
        self::STATUS_OK => "成功",
        self::STATUS_FAILED => "失败",
        self::STATUS_SENDING => "发送中",
        self::STATUS_WAIT => "等待发送",//后台如果指发送，会用到此状态
    ];

    const CHANNEL_ALI = 1;
//    const CHANNEL_TENCENT = 2;
    const CHANNEL_DESC = [
        self::CHANNEL_ALI => "阿里",
//        self::CHANNEL_TENCENT => "腾讯",
    ];

    const ALI_CALLBACK_MSG_STATUS_OK = 1;
    const ALI_CALLBACK_MSG_STATUS_FAIL = 2;
    const ALI_CALLBACK_MSG_STATUS_WAIT = 3;
    const ALI_CALLBACK_MSG_STATUS_DESC = [
        self::ALI_CALLBACK_MSG_STATUS_OK=>"成功",
        self::ALI_CALLBACK_MSG_STATUS_FAIL=>"失败",
        self::ALI_CALLBACK_MSG_STATUS_WAIT=>"未处理",
    ];


    static function getMsgStatusOptionHtml(){
        $html = "";
        foreach (self::ALI_CALLBACK_MSG_STATUS_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }


    static function getChannelOptionHtml(){
        $html = "";
        foreach (self::CHANNEL_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getStatusOptionHtml(){
        $html = "";
        foreach (self::STATUS_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }
	
    //过去一段时间内，发送的次数
    static function getPeriodTimes($cellphone,$ruleId,$period){
        $s_time = time() - $period;
        $rs =  self::db()->getCount("a_time >= $s_time and  a_time <= ".time()." and cellphone = '$cellphone' and rule_id = $ruleId");
        return $rs;
    }

    static function getDayMobileSendTimes($cellphone,$ruleId){
        $today  = dayStartEndUnixtime();
        $rs =  self::db()->getCount("a_time >= {$today['s_time']} and a_time <= {$today['e_time']} and cellphone = '$cellphone' and rule_id = $ruleId ");
        return $rs;
    }
	
}