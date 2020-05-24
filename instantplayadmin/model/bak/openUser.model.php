<?php

/**
 * @Author: Kir
 * @Date:   2019-03-15 12:24:38
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-03-15 18:26:29
 */
class OpenUserModel
{
	static $_table = 'open_user';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;
    const TYPE_PERSON = 2;
    const TYPE_COMPANY = 1;

    static $_status_uncommitted = 0;    //未提交
    static $_status_auditing = 1;       //审核中
    static $_status_passed = 2;         //审核通过
    static $_status_rejected = 3;       //审核不通过

    static function getAccountDescs(){
        return array("1"=>"公司账号","2"=>"个人账号");
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
