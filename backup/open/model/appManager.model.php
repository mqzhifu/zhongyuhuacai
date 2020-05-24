<?php

/**
 * @Author: xuren
 * @Date:   2019-03-28 16:33:00
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-28 16:33:49
 */
class AppManagerModel{
	static $_table = 'app_manager';
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