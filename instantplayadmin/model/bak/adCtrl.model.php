<?php

/**
 * @Author: Kir
 * @Date:   2019-06-05 11:33:22
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-05 17:18:37
 */


class AdCtrlModel {
	static $_table = 'ad_ctrl';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;

    static $_ctrl_type_frequency = 1;
    static $_ctrl_type_state = 2;

    static $_time_ctrl_type_forever = 1;
    static $_time_ctrl_type_set = 2;

    static $_user_type_all = 0;
    static $_user_type_new = 1;
    static $_user_type_old = 2;

    static $_frequency_type_interval = 1;
    static $_frequency_type_times = 2;

    static function getCtrlTypeDesc()
    {
    	return [
    		self::$_ctrl_type_frequency => '频次控制',
    		self::$_ctrl_type_state => '开关控制'
    	];
    }

    static function getTimeCtrlTypeDesc()
    {
    	return [
    		self::$_time_ctrl_type_forever => '永久',
    		self::$_time_ctrl_type_set => '时间选择'
    	];
    }

    static function getUserTypeDesc()
    {
    	return [
    		self::$_user_type_all => '全部',
    		self::$_user_type_new => '新用户',
    		self::$_user_type_old => '老用户'
    	];
    }


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

    
}