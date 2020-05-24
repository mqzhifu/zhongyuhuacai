<?php
class OrderGoodsModel {
    static $_table = 'order_goods';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_status_wait = 1;
    static $_status_deny = 2;
    static $_status_finish = 4;
    static $_status_wait_send = 3;

    static function getStatusDesc(){
        return array(
            self::$_status_wait =>'等待审核',
            self::$_status_deny =>'拒绝',
            self::$_status_finish =>'已发货',
            self::$_status_wait_send =>'待发货',

        );
    }

    static function keyInStatus($key){
        return in_array($key,array_flip(self::getStatusDesc()));
    }

    static function getTypeDescByKey($key){
        if(!self::keyInStatus($key)){
            return "未知";
        }
        $arr = self::getStatusDesc();
        return $arr[$key];
    }

    static function getStatusDescByKey($key){
        if(!self::keyInStatus($key)){
            return "未知";
        }
        $arr = self::getStatusDesc();
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

}