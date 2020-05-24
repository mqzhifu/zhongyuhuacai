<?php
class UserModel {
    static $_table = 'user';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_type_cellphone = 1;
    static $_type_email = 2;
    static $_type_name = 3;

    static $_type_wechat = 4;
    static $_type_weibo = 5;
    static $_type_facebook = 6;
    static $_type_google = 7;
    static $_type_twitter = 8;
    static $_type_qq = 9;
    static $_type_guest = 10;
    static $_type_cellphone_ps = 11;
    static $_type_pc_cellphone_ps = 12;
    static $_type_pc_cellphone_sms = 13;



    static $_online_true = 1;
    static $_online_false = 2;

    static $_push_open = 1;
    static $_push_off = 2;

    static $_gps_open = 1;
    static $_gps_off = 2;

    static $_robot_true = 1;
    static $_robot_false = 2;


    static $_sex_male = 1;//男
    static $_sex_female = 2;//女

    static function getTypeDesc(){
        return array(
            self::$_type_cellphone =>'手机号',
            self::$_type_email =>'邮箱',
            self::$_type_name =>'用户名',

            self::$_type_wechat =>'微信',
            self::$_type_weibo =>'微博',
            self::$_type_facebook =>'脸书',
            self::$_type_google =>'谷歌',
            self::$_type_twitter =>'推特',
            self::$_type_qq =>'QQ',
            self::$_type_guest =>'游客',
            self::$_type_cellphone_ps =>'手机密码登陆',
            self::$_type_pc_cellphone_ps =>'pc端手机密码登陆',
            self::$_type_pc_cellphone_sms =>'pc端手机验证码登陆',

        );
    }

    static function getOnlineDesc(){
        return array(self::$_online_true=>'在线',self::$_online_false=>'离线');
    }

    static function getSexDesc(){
        return array(self::$_sex_male=>'男',self::$_sex_female=>'女');
    }

    static function getPushDesc(){
        return array(self::$_push_open=>'打开',self::$_push_off=>'关闭');
    }

    static function getGpsDesc(){
        return array(self::$_gps_open=>'打开',self::$_gps_off=>'关闭');
    }


    static function keyInSex($key){
        return in_array($key,array_flip(self::getSexDesc()));
    }
    static function keyInRegType($key){
        return in_array($key,array_flip(self::getTypeDesc()));
    }

    static function keyInPush($key){
        return in_array($key,array_flip(self::getPushDesc()));
    }

    static function keyInGps($key){
        return in_array($key,array_flip(self::getGpsDesc()));
    }


    static function getTypeDescByKey($key){
        if(!self::keyInRegType($key)){
            return "未知";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
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

    static function login($uname,$ps){
        return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
    }

}