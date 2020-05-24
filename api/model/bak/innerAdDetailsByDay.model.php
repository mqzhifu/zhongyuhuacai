<?php

/**
 * @Author: xuren
 * @Date:   2019-05-22 17:55:00
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-22 17:55:38
 */
class InnerAdDetailsByDayModel {
	static $_table = 'inner_ad_details_byday';
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
	

}