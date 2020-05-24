<?php
class GamesModel {
	static $_table = 'games';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
    static $_db = null;

    //推荐最新
    static $_recommend_new_true = 1;
    static $_recommend_new_false = 2;

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

    //处理状态
    public static $_status_0 = 0;
    public static $_status_1 = 1;
    public static $_status_2 = 2;
    public static $_status_3 = 3;
    public static $_status_4 = 4;
    public static $_status_5 = 5;
    public static $_status_6 = 6;
    public static $_status_7 = 7;
    public static $_status_8 = 8;

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

    //====================
    static function getRecommendNewDesc(){
        return array(self::$_recommend_new_true=>'是',self::$_recommend_new_false=>'否');
    }

    static function keyInRecommendNewDesc($key){
        return in_array($key,array_flip(self::getRecommendNewDesc()));
    }

    static function getRecommendNewDescByKey($key){
        if(!self::getRecommendNewDesc($key)){
            return "未知";
        }
        $arr = self::getRecommendNewDesc();
        return $arr[$key];
    }

    static function getRecommendNewOption($status = ""){
        $arr = self::getRecommendNewDesc();
        $str = "";
        foreach($arr as $k=>$v){
            $selected = "";
            if($status && $status == $k){
                $selected ="selected='selected'";
            }
            $str .="<option $selected value='{$k}'>{$v}</option>";
        }
        return $str;
    }
    //==================

    /**
     * @return array
     */
	public static function getGamesNameList(){
        $sql = "SELECT id,name FROM ".self::$_table.";";
        $result = self::db()->query($sql);
        if(!empty($result) && is_array($result)){
            return $result;
        }else{
            return [];
        }
    }

    public static function getOnlineGamesNameList(){
        $sql = "SELECT id,name FROM ".self::$_table." where is_online=1 and status != 4;";
        $result = self::db()->query($sql);
        if(!empty($result) && is_array($result)){
            return $result;
        }else{
            return [];
        }
    }

}