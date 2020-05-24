<?php

/**
 * @Author: Kir
 * @Date:   2019-06-19 18:32:07
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-20 14:55:26
 */


class CntActionLogModel 
{
	static $_table = 'cnt_action';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static $_category_h5 = 1;
	static $_category_pc = 2;
	static $_category_app = 3;

	static $_type_landing_page_access = 1;  // 落地页访问
	static $_type_landing_page_download = 2;  // 落地页下载
	static $_type_invite_friend_page_access = 3;  // 邀请好友页访问
	static $_type_box_tap = 4;  // 宝箱点击事件

	static function getCategories()
	{
		return [self::$_category_h5,self::$_category_pc,self::$_category_app];
	}

	static function getTypes()
	{
		return [
			self::$_type_landing_page_access,
			self::$_type_landing_page_download,
			self::$_type_invite_friend_page_access,
			self::$_type_box_tap,
		];
	}

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
}