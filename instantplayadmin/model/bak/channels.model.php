<?php

/**
 * @Author: Kir
 * @Date:   2019-04-28 18:22:16
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-30 09:39:54
 */


class ChannelsModel {
    static $_table = 'channels';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static function getOsDesc()
    {
        return [1=>'安卓',2=>'苹果'];
    }

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

}