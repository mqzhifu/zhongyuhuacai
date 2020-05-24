<?php
class MsgModel {
	static $_table = 'msg';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;


//    /*
//    1用户之间私信
//    2商家群发
//    3系统发送一个用户;
//    4系统群发 - 用户组 - 普通用户 高级用户
//    5系统群发 - 全部
//    6系统部分发 - 指定一些UID
//    7系统群发 - 指定标签发送.
//*/
//    public static $_TYPE_P2P = 1; // person to person
////    public static $_TYPE_S2S = 2; // system to sellers
//    public static $_TYPE_S2P = 3; // system to person
//    public static $_TYPE_S2G = 4; // system to group
//    public static $_TYPE_S2A = 5; // system to all
////    public static $_TYPE_S2X = 6; // system to x
////    public static $_TYPE_S2T = 7; // system to tag
////    public static $_TYPE_NOTIFY = 8; //system to person
//    public static $_TYPE_P2S = 9;//person to system

	static $_type_p2p = 1;
    static $_type_s2p = 3;

	static $_cate_follow_other = 1;
    static $_cate_followed_me = 2;
    static $_cate_share_games = 3;
    static $_cate_stranger_im_text = 41;
    static $_cate_stranger_im_img = 42;
    static $_cate_stranger_im_voice = 43;
    static $_cate_stranger_im_game = 44;

    static $_cate_follow_im_text = 51;
    static $_cate_follow_im_img = 52;
    static $_cate_follow_im_voice = 53;
    static $_cate_follow_im_game = 54;

    static $_cate_sys_push_games_state = 6;

	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}


    static function getCateStrangerDesc(){
        return array(
            self::$_cate_stranger_im_text=>'收到<陌生人>IM消息 文本',
            self::$_cate_stranger_im_img=>'收到<陌生人>IM消息 图片',
            self::$_cate_stranger_im_voice=>'收到<陌生人>IM消息 语音',
            self::$_cate_stranger_im_game=>'收到<陌生人>IM消息 游戏邀请'
        );
    }

    static function keyInCateStranger($key){
        return in_array($key,array_flip(self::getCateStrangerDesc()));
    }

    static function getCateDesc(){
        return array(
            self::$_cate_follow_other=>'我关注了谁',
            self::$_cate_followed_me=>'谁关注了我',
            self::$_cate_share_games=>'我分享了游戏连接给谁',

            self::$_cate_stranger_im_text=>'收到  陌生人  IM消息 文本',
            self::$_cate_stranger_im_img=>'收到   陌生人  IM消息 图片',
            self::$_cate_stranger_im_voice=>'收到 陌生人  IM消息 语音',
            self::$_cate_stranger_im_game=>'收到  陌生人  IM消息 游戏邀请',


             self::$_cate_follow_im_text=>'收到  已关注  IM消息 文本',
             self::$_cate_follow_im_img=>'收到   已关注  IM消息 图片',
             self::$_cate_follow_im_voice=>'收到 已关注  IM消息 语音',
             self::$_cate_follow_im_game=>'收到  已关注  IM消息 游戏邀请',
             self::$_cate_sys_push_games_state=>'系统  PUSH  游戏 动态',

        );
    }

    static function keyInCate($key){
        return in_array($key,array_flip(self::getCateDesc()));
    }

	

}