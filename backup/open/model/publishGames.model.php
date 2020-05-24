<?php

/**
 * @Author: Kir
 * @Date:   2019-05-07 18:00:11
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-08 11:22:39
 */

class PublishGamesModel
{
    static $_table = 'publish_games';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $typeDesc = [1=>'安卓',2=>'IOS'];

    static function db ()
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