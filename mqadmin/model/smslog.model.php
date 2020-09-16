<?php
class SmsLogModel {
	static $_table = 'sms_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
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