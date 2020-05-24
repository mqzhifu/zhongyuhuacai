<?php
class RoomModel {
	static $_table = 'room';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
    static $_db = null;

    //处理状态
    static $_rs_win = 1;
    static $_rs_lose = 2;
    static $_rs_draw  = 3;
    //在线状态
    static $_status_wait = 1;
    static $_status_start = 2;
    static $_status_end = 3;

    static $_type_normal = 1;
    static $_type_pk = 1;


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}


    static function getStatusDesc(){
        return array(self::$_status_wait=>'等待',self::$_status_start=>'开始',self::$_status_end=>'结束');
    }

    static function getStatusDescByKey($key){
        if(!self::getStatusDesc($key)){
            return "";
        }
        $arr = self::getStatusDesc();
        return $arr[$key];
    }


    static function keyInStatus($key){
        return in_array($key,array_flip(self::getStatusDesc()));
    }

    static function getRsDesc(){
        return array(self::$_rs_win=>'赢',self::$_rs_lose=>'输',self::$_rs_draw=>'平');
    }

    static function getRsDescByKey($key){
        if(!self::getRsDesc($key)){
            return "";
        }
        $arr = self::getRsDesc();
        return $arr[$key];
    }


    static function keyInRs($key){
        return in_array($key,array_flip(self::getRsDesc()));
    }


    static function getTypeDesc(){
        return array(self::$_type_normal=>'指定游戏1V1',self::$_type_pk=>'约战');
    }

    static function getTypeDescByKey($key){
        if(!self::getTypeDesc($key)){
            return "";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
    }


    static function keyInType($key){
        return in_array($key,array_flip(self::getTypeDesc()));
    }




}