<?php

/**
 * Desc: open_bank 表
 * User: haopeng
 * Date: 2019/3/7 14:44
 */
class BankModel
{
    public static $_table = 'open_bank';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

    static $_status_uncommitted = 0;    //未提交
    static $_status_auditing = 1;       //审核中
    static $_status_passed = 2;         //审核通过
    static $_status_rejected = 3;       //审核不通过

    public static function db ()
    {
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    public static function __callStatic ($func, $arguments)
    {
        return call_user_func_array(array(self::db(), $func), $arguments);
    }

}