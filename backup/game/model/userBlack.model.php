<?php
class UserBlackModel {
	static $_table = 'user_black';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static $_type = array(1=>'访问次数过于频繁');
	static $_status = array(1=>'禁止中',2=>'已解禁');

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
    static function isBlack($uid){
        return self::db()->getRow(" uid = $uid and status = 1");
    }

    static function add($uid,$type){
        $data = array('type'=>$type,'a_time'=>time(),'status'=>1,);
        return self::db()->add();
    }
	
}