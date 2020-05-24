<?php
class PointLogModel {
	static $_table = 'point_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static $_type_play_games = 1;


    static function getTypeDesc(){
        return array(self::$_type_play_games=>'玩游戏XX秒');
    }

    static function keyInSex($key){
        return in_array($key,array_flip(self::getTypeDesc()));
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