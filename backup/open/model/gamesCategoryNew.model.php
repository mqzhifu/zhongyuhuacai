<?php

/**
 * @Author: xuren
 * @Date:   2019-06-04 18:02:34
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-04 18:03:39
 */
class GamesCategoryNewModel
{
    public static $_table = 'games_category_new';
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

    function getCategory(){
    	return self::db()->getAll();
    }
}