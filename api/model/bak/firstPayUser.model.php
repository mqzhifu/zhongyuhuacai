<?php

/**
 * @Author: xuren
 * @Date:   2019-05-07 10:16:19
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-30 15:50:59
 */
class FirstPayUserModel {
    static $_table = 'first_pay_user';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function addIfNotExists($uid, $gameid, $atime){
        $res = self::db()->getCount("uid=$uid and game_id=$gameid");
        if(!$res){
            $res2 = self::db()->add(["uid"=>$uid,"game_id"=>$gameid, "a_time"=>$atime]);
            if(!$res2){
                return false;
            }
        }
        return true;
    }
}