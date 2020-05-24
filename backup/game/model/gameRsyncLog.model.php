<?php
class GameRsyncLogModel {
	static $_table = 'game_rsync_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
    static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	

}