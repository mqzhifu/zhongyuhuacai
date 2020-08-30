<?php
/**
 * Created by PhpStorm.
 * User: Kir
 * Date: 2019/6/21
 * Time: 15:08
 */

class IpBlockModel
{
    static $_table = 'ip_block';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_type_access_too_frequent = 1;

    static $_status_blocked = 1;
    static $_status_unblocked = 2;

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

    static function isBlocked($ip) {
        $rs = self::db()->getRow(" ip = '$ip' and status = " . self::$_status_blocked);
        if (!$rs) {
            return false;
        }
        if ($rs['expired_time'] && time() > $rs['expired_time']) {
            self::db()->upById($rs['id'], ['status'=>self::$_status_unblocked]);
            return false;
        }
        return true;
    }

    static function block($ip,$type,$mome='',$expiredTime='') {
        if (!$expiredTime) {
            $expiredTime = time() + 5 * 86400;  // 默认封五天
        }
        $data = [
            'ip'=>$ip,
            'mome'=>$mome,
            'status'=>self::$_type_access_too_frequent,
            'type'=>$type,
            'u_time'=>time(),
            'expired_time'=>$expiredTime,
        ];
        if ($record = self::db()->getRow("ip = '$ip'")) {
            $rs = self::db()->upById($record['id'], $data);
        } else {
            $data['a_time'] = time();
            $rs = self::db()->add($data);
        }
        return $rs;
    }

}