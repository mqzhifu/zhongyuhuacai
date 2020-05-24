<?php

/**
 * @Author: Kir
 * @Date:   2019-04-17 11:21:17
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-28 14:39:45
 */

class GameRecommendModel
{
    static $_table = 'game_recommend';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_type_more = 1;
    static $_type_quality = 2;
    static $_type_new = 3;

    static function getTypeDesc() {
        return [
            self::$_type_more => '弹窗轮播',
            self::$_type_quality => '精品推荐',
            self::$_type_new => '最新上架',
        ];
    }

    static function db ()
    {
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    static function __callStatic ($func, $arguments)
    {
        return call_user_func_array(array(self::db(), $func), $arguments);
    }

}