<?php

/**
 * @Author: xuren
 * @Date:   2019-03-27 15:48:45
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-27 15:50:17
 */
class AppADMapModel{
	static $_table = 'app_ad_map';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;


	static function db(){
		if(self::$_db)
			return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
}