<?php

/**
 * @Author: xuren
 * @Date:   2019-03-27 11:31:46
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-08 17:08:41
 */
class IncomeModel {
	static $_table = 'income';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;
	static $income_type_ad = 2;
	static $income_type_purchase = 1;
	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	

	public static function getDescByType($type){
		$arr = ['1'=>'内购收入','2'=>'广告收入'];
		return $arr[$type];
	}
}