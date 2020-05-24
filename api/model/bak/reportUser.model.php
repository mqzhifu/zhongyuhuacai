<?php

class ReportUserModel {
    static $_table = 'report_user';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;


    static $_status_wait = 1;
    static $_status_finish = 2;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getStatusDesc(){
        return array(self::$_status_wait=>'待处理', self::$_status_finish=>'已结束');
    }

    static function keyInStatus($key){
        return in_array($key,array_flip(self::getStatusDesc()));
    }
}