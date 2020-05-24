<?php
class AdminUserModel {
	static $_table = 'admin_user';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;


	static function db(){
		if(self::$_db)
			return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function login($uname,$ps){
//	    var_dump(" uname = '$uname' and ps = '$ps'");
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getOption(){
        $list = self::db()->getAll(1);
        $str = "";
        foreach($list as $k=>$v){
            $str.= "<option value='{$v['id']}'>{$v['nickname']}</option>";
        }

        return $str;
    }

    static function getFieldById($adminUid,$field){
	    if(!$adminUid){
            return "--";
        }
        $user = self::db()->getById($adminUid);
        if($user[$field]){
            return $user[$field];
        }

        return "默认昵称";

    }
	
}