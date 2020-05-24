<?php

/**
 * @Author: Kir
 * @Date:   2019-05-14 11:39:32
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-14 11:40:55
 */


class PublishAdModel
{
    public static $_table = 'publish_ad';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

    //广告位状态
    public static $_status_wait = 1;
    public static $_status_cancel = 2;
    public static $_status_ok = 3;
    public static $_status_delete = 4;

    //广告类型
    public static $_advertise_type_1 = 1;
    public static $_advertise_type_2 = 2;
    public static $_advertise_type_3 = 3;

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
            self::$_advertise_type_1 => '全屏视频',
            self::$_advertise_type_2 => '激励视频',
            self::$_advertise_type_3 => 'Banner',
        );
    }

    public static function getAdDirectionDesc ()
    {
        return [1=>'横屏',2=>'竖屏'];
    }

    public static function keyInStatus ($key)
    {
        return in_array($key, array_flip(self::getStatusDesc()));
    }

}