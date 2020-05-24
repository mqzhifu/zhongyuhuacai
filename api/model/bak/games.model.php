<?php
class GamesModel {
	static $_table = 'games';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
    static $_db = null;

    //推荐首页
    static $_recommend_index_true = 1;
    static $_recommend_index_false = 2;
    //处理状态
    static $_status_wait = 1;
    static $_status_ok = 2;
    static $_status_deny = 3;
    //在线状态
    static $_online_true = 1;
    static $_online_false = 2;
    //推荐：IM中，发起约战，弹出的 游戏托盘列表
    static $_recommend_im_invite_true = 1;
    static $_recommend_im_invite_false = 2;

    static $_screen_across = 1;
    static $_screen_vertical = 2;


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}


    static function getRecommendIndexDesc(){
        return array(self::$_recommend_index_true=>'是',self::$_recommend_index_false=>'否');
    }


    static function keyInRecommendIndex($key){
        return in_array($key,array_flip(self::getRecommendIndexDesc()));
    }

    static function getRecommendImInviteDesc(){
        return array(self::$_recommend_im_invite_true=>'是',self::$_recommend_im_invite_false=>'否');
    }

    static function keyInRecommendImInvite($key){
        return in_array($key,array_flip(self::getRecommendImInviteDesc()));
    }

    static function getStatusDesc(){
        return array(self::$_status_wait=>'待处理',self::$_status_ok=>'已处理发货',self::$_status_deny=>'拒绝');
    }

    static function keyInStatus($key){
        return in_array($key,array_flip(self::getStatusDesc()));
    }

    static function getOnlineDesc(){
        return array(self::$_online_true=>'是',self::$_online_false=>'否');
    }

    static function keyInOnline($key){
        return in_array($key,array_flip(self::getOnlineDesc()));
    }

    static function getScreenDesc(){
        return array(self::$_screen_vertical=>'竖',self::$_screen_across=>'横');
    }

    static function keyInScreen($key){
        return in_array($key,array_flip(self::getScreenDesc()));
    }


	

}