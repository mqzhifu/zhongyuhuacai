<?php

/**
 * @Author: Kir
 * @Date:   2019-06-13 14:58:03
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-13 15:07:17
 */



class AppUiConfigModel {
	static $_table = 'app_ui_config';
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

    
}