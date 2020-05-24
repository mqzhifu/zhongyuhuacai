<?php
class SmsRuleModel {
	static $_table = 'sms_rule';
	static $_pk = 'id';
	static $_db_key =DEF_DB_CONN;
	static $_db = null;

    static $_type_login = 1;//登陆
    static $_type_bind = 2;//绑定
    static $_type_reg = 3;//注册
    static $_type_findPs = 4;//找回密码
    static $_type_notice = 5;//报警
    static $_type_upPs = 6;//修改密码
    static $_type_pc_login = 7;//密码端手机验证码登陆
    static $_type_realname_verify = 8;//实名认证

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