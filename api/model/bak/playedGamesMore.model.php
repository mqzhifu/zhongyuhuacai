<?php
class PlayedGamesMoreModel {
	static $_table = 'played_games_';
	static $_pk = 'id';
	static $_db_key = 'kxgame_log';
	static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}

    static function getTableByDay($day){
        $table  = self::$_table . $day;
        return $table;
    }

    static function getTable(){
        $table  = self::$_table . date("Ym");
        return $table;
    }

	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

    static function add($data){
        return self::db()->add($data,self::getTable());
    }

    static function upById($id,$data){
        return self::db()->upById($id,$data,self::getTable());
    }

    static function getRow($where, $table = '' , $field){
        return self::db()->getRow($where, self::getTable(), $field);
    }
	
}