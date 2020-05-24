<?php

/**
 * @Author: Kir
 * @Date:   2019-04-12 14:24:05
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-12 14:56:12
 */
class LinkGamePowerModel
{
    static $_table = 'link_game_power';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_power_link = 1;
    static $_power_wechat = 2;

    static function getRoleDesc($roles)
    {
    	$roles = explode(',',$roles);
    	$role_name = '';
    	if (in_array(self::$_power_link, $roles)) {
    		$role_name = '链接游戏';
    	}
    	if (in_array(self::$_power_wechat, $roles)) {
    		$role_name = '微信游戏';
    	}
    	if (in_array(self::$_power_link, $roles) && in_array(self::$_power_wechat, $roles)) {
    		$role_name = '全部';
    	}
    	return $role_name;
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