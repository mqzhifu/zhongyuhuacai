<?php
class ImSessionModel {
	static $_table = 'im_session';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
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
	
	static function filter($str,$replaceStr = "***"){
        $data  = self::db()->getAll(" 1 ",null,"name");
        if(!$data){
            return $str;
        }

        foreach($data as $k=>$v){
            $str =  str_replace($v['name'],$replaceStr,$str) ;
        }

        return $str;
    }
	
}