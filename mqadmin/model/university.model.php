<?php
class UniversityModel {
	static $_table = 'university';
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

    static $_type_cellphone = 1;
    static $_type_wechat = 2;
    static $_type_qq = 3;
    static $_type_email = 4;
    static $_type_name = 5;

    static $_sex_male = 1;
    static $_sex_female = 2;

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

}