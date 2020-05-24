<?php

/**
 * @Author: Kir
 * @Date:   2019-01-31 11:26:20
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-01-31 15:00:49
 */

class LotteryLogModel {
    static $_table = 'lottery';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static $_type_coin = 1;	//金币
    static $_type_ad = 2;	//广告  

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getTypeDesc() {
        return array(
            self::$_type_coin =>'金币',
            self::$_type_ad =>'广告',
        );
    }

    static function keyInSex($key) {
        return in_array($key,array_flip(self::getTypeDesc()));
    }

    static function getTypeDescByKey($key){
        if(!self::keyInSex($key)){
            return "未知";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
    }

}