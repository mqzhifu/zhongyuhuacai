<?php

/**
 * Desc: open_advertise_income 表
 * User: haopeng
 * Date: 2019/3/7 14:44
 */
class advertiseIncomeModel
{
    public static $_table = 'open_advertise_income';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

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


    public static function getStatusDesc ()
    {
        return array(
            self::$_status_wait => '审核',
            self::$_status_cancel => '暂停',
            self::$_status_ok => '有效',
            self::$_status_delete => '删除'
        );
    }

    public static function getAdvertiseTypeDesc ()
    {
        return array(
            self::$_advertise_type_1 => '插屏',
            self::$_advertise_type_2 => '横幅',
            self::$_advertise_type_3 => '插页',
        );
    }

    public static function keyInStatus ($key)
    {
        return in_array($key, array_flip(self::getStatusDesc()));
    }
}