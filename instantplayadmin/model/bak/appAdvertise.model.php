<?php

/**
 * @Author: xuren
 * @Date:   2019-03-27 15:48:27
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-13 21:02:32
 */
class AppAdvertiseModel {
	static $_table = 'app_advertise';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DEF_DB_CONN;
    static $status_audit = 1;
    static $status_pause = 2;
    static $status_ok = 3;
    static $status_del = 4;
	static function db(){
		if(self::$_db)
			return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}


	public static function getStateDesc(){
		return ['关','开'];
	}
	public static function getAdvertiserTypeDesc(){
		if (PCK_AREA == 'en') {
			return ['1'=>'Google','2'=>'Facebook'];
		} else {
			return ['1'=>'穿山甲','2'=>'腾讯','3'=>'百度'];
		}
	}

	public static function getChannelDesc(){
		$res = ChannelsModel::db()->getAllBySql("select distinct id, f_key, name from channels");
		$channels = [];
		foreach ($res as $key => $value) {
			$channels[$value['id']] = $value['name'];
		}
		return $channels;
	}
	
	public static function getOSTypeDesc(){
		return ['1'=>'IOS','2'=>'ANDROID'];
	}

	public static function getAdvertiseTypeDesc(){
		if (PCK_AREA == 'en') {
			return ['1'=>'插屏','2'=>'激励视频','3'=>'Banner','4'=>'开屏位广告'];
		} else {
			return ['1'=>'全屏视频','2'=>'激励视频','3'=>'Banner','4'=>'开屏位广告'];
		}
		
	}

	public static function getDescByAdType($type){
		if (PCK_AREA == 'en') {
			$arr = ['1'=>'插屏','2'=>'激励视频','3'=>'Banner','4'=>'开屏位广告'];
		} else {
			$arr = ['1'=>'全屏视频','2'=>'激励视频','3'=>'Banner','4'=>'开屏位广告'];
		}
		
		return $arr[$type];
	}

	public static function getAdDirectionDesc()
    {
        return [1=>'横屏',2=>'竖屏'];
    }

    public static function getSubStatusDesc(){
		return [ self::$status_pause=>'暂停', self::$status_ok=>'有效'];
	}
}