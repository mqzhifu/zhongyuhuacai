<?php

/**
 * Desc: open_advertise 表
 * User: zhangbingbing
 * Date: 2019/2/18 14:44
 */
class OpenAdvertiseModel
{
    public static $_table = 'open_advertise';
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

    //频次设定类型
    public static $_strategy_interval = 1;
    public static $_strategy_times = 2;
    public static $_strategy_unset = 3;

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
        if(PCK_AREA == 'en'){
            return array(
                self::$_advertise_type_1 => '插屏',
                self::$_advertise_type_2 => '激励视频',
                self::$_advertise_type_3 => 'Banner',
            );
        } else {
            return array(
                self::$_advertise_type_1 => '全屏视频',
                self::$_advertise_type_2 => '激励视频',
                self::$_advertise_type_3 => 'Banner',
            );
        }
        
    }

    public static function getAdDirectionDesc ()
    {
        return [1=>'横屏',2=>'竖屏'];
    }

    public static function keyInStatus ($key)
    {
        return in_array($key, array_flip(self::getStatusDesc()));
    }

    public static function countAdvertiseList($where, $field = '*') {
        $sql = 'select count(' . $field .') as total from open_advertise a 
              left join open_advertise_income ai on a.adtoutiao_id = ai.ad_slot_id
              where ' . $where;

        return self::db()->getOneBySQL($sql);

    }

    public static function getAdvertiseList($where, $order = '', $limit = '') {
        $field = 'a.title,ai.*';

        $sql = 'select ' . $field .' from open_advertise a 
              left join open_advertise_income ai on a.adtoutiao_id = ai.ad_slot_id
              where ' . $where . $order . $limit;

        return self::db()->getAllBySQL($sql);

    }
}