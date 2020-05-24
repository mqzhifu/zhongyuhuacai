<?php

/**
 * @Author: Kir
 * @Date:   2019-04-15 17:44:08
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-16 15:29:11
 */
class UserBlockLogModel
{
    static $_table = 'user_block_log';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_block = 1;
    static $_unblock = 2;

    static function getOperateDesc()
    {
    	return [self::$_block=>"停封",self::$_unblock=>"解封"];
    }

    static function getDurationDesc()
    {
    	return [
    		86400*3650=>'永久',
    		86400=>'1天',
    		86400*2=>'2天',
    		86400*2=>'2天',
    		86400*3=>'3天',
    		86400*7=>'7天',
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

    static function addLog($uid,$type,$admin,$duration=null,$detail=null)
    {
    	$log = [
    		'uid'=>$uid,
    		'type'=>$type,
    		'duration'=>$duration,
    		'admin'=>$admin,
    		'detail'=>$detail,
    		'a_time'=>time(),
    	];

    	self::db()->add($log);
    }

}