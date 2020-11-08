<?php

/**
 * @Author: Kir
 * @Date:   2019-04-16 17:19:39
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-16 17:44:27
 */

class IpBlockLogModel
{
    static $_table = 'ip_block_log';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_block = 1;
    static $_unblock = 2;

    static function getOperateDesc()
    {
    	return [self::$_block=>"停封",self::$_unblock=>"解封"];
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

    static function addLog($ip,$type,$admin,$detail=null)
    {
    	$log = [
    		'ip'=>$ip,
    		'type'=>$type,
    		'admin'=>$admin,
    		'detail'=>$detail,
    		'a_time'=>time(),
    	];

    	self::db()->add($log);
    }

}