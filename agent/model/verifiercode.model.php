<?php
class VerifiercodeModel {
	static $_table = 'verifier_code';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = "instantplay";


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }


    static function login($uname,$ps){
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }
	
}