<?php
/**
 * Created by PhpStorm.
 * User: Kir
 * Date: 2019/6/25
 * Time: 17:22
 */

class RoomModel
{
    public static $_table = 'room';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

    static $_status_waiting = 1;
    static $_status_start = 2;
    static $_status_end = 3;

    static $_result_none = 0;
    static $_result_win = 1;
    static $_result_lose = 2;
    static $_result_draw = 3;

    static function getStatusDesc() {
        return [
            self::$_status_waiting => '等待',
            self::$_status_start => '已开始',
            self::$_status_end => '已结束',
        ];
    }

    static function getResultDesc() {
        return [
            self::$_result_none => '未处理',
            self::$_result_win => '赢',
            self::$_result_lose => '输',
            self::$_result_draw => '平',
        ];
    }

    public static function db (){
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    public static function __callStatic ($func, $arguments){
        return call_user_func_array(array(self::db(), $func), $arguments);
    }



}