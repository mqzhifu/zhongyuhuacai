<?php
class OfflineRewardModel {
	static $_table = 'offline_reward';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

    static function addReq($adminId){
        $data = array(
            'ctrl'=>CTRL,
            'AC'=>AC,
            'a_time'=>time(),
            'IP'=>get_client_ip(),
            'request'=>json_encode($_REQUEST),
            'admin_uid'=>$adminId
        );

        $id = self::db()->add($data);
        return $id;
    }
}