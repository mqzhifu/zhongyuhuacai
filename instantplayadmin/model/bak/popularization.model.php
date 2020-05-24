<?php

/**
 * @Author: xuren
 * @Date:   2019-03-25 14:36:24
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-25 15:38:21
 */
class PopularizationModel{
	static $_table = 'popularize';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;
	static $status_launching = 1;//投放中
	static $status_unlaunched = 2;//投放结束
	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	public static function getStatusDesc(){
		return [self::$status_launching=>'投放中',self::$status_unlaunched=>'投放结束'];
	}

	public static function getDescByStatus($status){
		$arr = [self::$status_launching=>'投放中',self::$status_unlaunched=>'投放结束'];
		return array_key_exists($status, $arr) ? $arr[$status] : '未知状态';
	}
}