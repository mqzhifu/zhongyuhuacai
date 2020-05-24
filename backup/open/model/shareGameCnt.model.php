<?php
class ShareGameCntModel {
	static $_table = 'share_game_cnt';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

    static $_type_cellphone = 1;
    static $_type_wechat = 2;
    static $_type_qq = 3;
    static $_type_email = 4;
    static $_type_name = 5;

    static $_sex_male = 1;
    static $_sex_female = 2;

    // sdk->cntShareGame insert.share_path
    static $_type_h5_game_share_wechat_single = 74;
    static $_type_h5_game_share_wechat_platform = 75;
    static $_type_h5_game_share_qq_single = 76;
    static $_type_h5_game_share_qq_platform = 77;

    static function getTypeDesc(){
        return array(self::$_type_cellphone =>'手机',self::$_type_wechat =>'微信',self::$_type_qq =>'QQ',self::$_type_email =>'邮箱',self::$_type_name =>'用户名',);
    }

    static function getSexDesc(){
        return array(self::$_sex_male=>'男',self::$_sex_female=>'女');
    }

    static function keyInSex($key){
        return in_array($key,array_flip(self::getSexDesc()));
    }
    static function keyInRegType($key){
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