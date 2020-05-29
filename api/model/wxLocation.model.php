<?php
/**
 * Created by PhpStorm.
 * User: XiaHb.
 * Date: 2019/3/29
 * Time: 14:36
 */
class wxLocationModel {
    static $_table = 'wx_location';
    static $_pk = 'id';
    static $_db_key = "instantplay";
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }
}