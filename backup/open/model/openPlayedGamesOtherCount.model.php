<?php

class OpenPlayedGamesOtherCountModel
{
    static $_table = 'open_played_games_other_count';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;
    //type码对应
    public static $_type_0 = 0;
    public static $_type_1 = 1;
    public static $_type_2 = 2;
    public static $_type_3 = 3;
    public static $_type_4 = 4;
    public static $_type_5 = 5;
    public static $_type_6 = 6;
    public static $_type_7 = 7;
    public static $_type_8 = 8;

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

    public static function getTypeDesc ()
    {
        return array(
            self::$_type_0 => '每5分钟统计在线人数',
            self::$_type_1 => '暂定1',
            self::$_type_2 => '暂定2',
            self::$_type_3 => '暂定3',
            self::$_type_4 => '暂定4',
            self::$_type_5 => '暂定5',
            self::$_type_6 => '暂定6',
            self::$_type_7 => '暂定7',
            self::$_type_8 => '暂定8',
        );
    }
}