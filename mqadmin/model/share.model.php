<?php

class ShareModel {
    static $_table = 'share';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    // 分享平台
    static $_platform_wx = 4;
    static $_platform_qq = 9;

    // 分享给个人或朋友圈（空间）
    static $_to_friend = 1;
    static $_to_platform = 2;

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