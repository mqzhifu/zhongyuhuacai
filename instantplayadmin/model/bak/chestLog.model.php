<?php

/**
 * @Author: Kir
 * @Date:   2019-01-31 15:11:36
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-01-31 15:15:51
 */
class ChestLogModel {
    static $_table = 'luck_box';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static $_type_coin = 1;	//金币
    static $_type_game = 2;	//小游戏  

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
            self::$_type_game =>'小游戏',
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