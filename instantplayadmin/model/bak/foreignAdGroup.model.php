<?php

/**
 * @Author: Kir
 * @Date:   2019-06-12 18:24:55
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-14 15:21:38
 */

/**
 * 
 */
class ForeignAdGroupModel
{
	
	static $_table = 'foreign_ad_group';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;

    static function get($value='')
    {
    	# code...
    }

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