<?php

/**
 * @Author: Kir
 * @Date:   2019-04-01 11:30:27
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-01 11:33:18
 */

class OpenADMapModel
{
	static $_table = 'ad_map';
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