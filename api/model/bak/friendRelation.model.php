<?php

class FriendRelationModel {
    static $_table = 'friend_relation';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static $_status_apply = 1;//好友申请
    static $_status_agree = 1;//同意申请
    static $_status_deny = 3;//拒绝申请
    static $_status_each_del = 4;//相互删除


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