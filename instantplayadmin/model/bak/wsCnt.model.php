<?php
class WsCntModel {
	static $_table = 'ws_cnt_';
	static $_pk = 'id';
	static $_db_key = "kxgame_log";
	static $_db = null;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	static function getTableByDay($y_m){
        $table  = self::$_table . $y_m;
        return $table;
    }

    static function getTable(){
	    $table  = self::$_table . date("Ym");
	    return $table;
    }

    static function add($data){
	    return self::db()->add($data,self::getTable());
    }

    static function upById($id,$data){
        return self::db()->upById($id,$data,self::getTable());
    }

    static function getLatestItem($day = ''){
    	if(!$day){
            $table = self::getTable();
        }else{
            $table = self::getTableByDay($day);
        }

    	$sql = "select * from ".$table." order by a_time desc limit 1";
    	return self::db()->getRowBySQL($sql);
    }
}