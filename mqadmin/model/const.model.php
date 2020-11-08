<?php
class ConstModel {
	static $_table = 'const';
	static $_pk = 'id';
	static $_db = null;

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