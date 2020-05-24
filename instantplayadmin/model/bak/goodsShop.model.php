<?php

class goodsShopModel
{
    public static $_table = 'goods';
    public static $_pk = 'id';
    public static $_db_key = 'quiz';
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

}