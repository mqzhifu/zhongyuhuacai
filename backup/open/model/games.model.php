<?php

/**
 * Desc: games 表
 * User: zhangbingbing
 * Date: 2019/2/14 17:51
 */
class GamesModel
{
    public static $_table = 'games';
    public static $_pk = 'id';
    public static $_db_key = DEF_DB_CONN;
    public static $_db = null;

    //推荐首页
    public static $_recommend_index_true = 1;
    public static $_recommend_index_false = 2;
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
    //在线状态
    public static $_online_true = 1;
    public static $_online_false = 0;
    //推荐：IM中，发起约战，弹出的 游戏托盘列表
    public static $_recommend_im_invite_true = 1;
    public static $_recommend_im_invite_false = 2;

    public static $_screen_across = 1;
    public static $_screen_vertical = 2;

    // 内部游戏
    public static $url_type_inner = 1;
    // 外链
    public static $url_type_link = 2;
    // 微信小程序
    public static $url_type_wx = 3;

    public static function db ()
    {
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    public static function __callStatic ($func, $arguments)
    {
        return call_user_func_array(array(self::db(), $func), $arguments);
    }


    public static function getRecommendIndexDesc ()
    {
        return array(self::$_recommend_index_true => '是', self::$_recommend_index_false => '否');
    }


    public static function keyInRecommendIndex ($key)
    {
        return in_array($key, array_flip(self::getRecommendIndexDesc()));
    }

    public static function getRecommendImInviteDesc ()
    {
        return array(self::$_recommend_im_invite_true => '是', self::$_recommend_im_invite_false => '否');
    }

    public static function keyInRecommendImInvite ($key)
    {
        return in_array($key, array_flip(self::getRecommendImInviteDesc()));
    }

    public static function getStatusDesc ()
    {
        return array(
            self::$_status_0 => '不显示',//#91AAE0
            self::$_status_1 => '开发中',//#91AAE0
            self::$_status_2 => '测试版',//#FFA73B
            self::$_status_3 => '审核中',//#33CD9C
            self::$_status_4 => '删除',
            self::$_status_5 => '审核已通过',//#7B6CD5
            self::$_status_6 => '审核不通过',//EF4D57
            self::$_status_7 => '已下线',//434343
            self::$_status_8 => '已上线',//#33CD9C
        );
    }

    public static function getStatusColor ()
    {
        return array(
            self::$_status_0 => '#91AAE0',//#91AAE0
            self::$_status_1 => '#91AAE0',//#91AAE0
            self::$_status_2 => '#FFA73B',//#FFA73B
            self::$_status_3 => '#33CD9C',//#33CD9C
            self::$_status_4 => '#91AAE0',
            self::$_status_5 => '#7B6CD5',//#7B6CD5
            self::$_status_6 => '#EF4D57',//EF4D57
            self::$_status_7 => '#434343',//434343
            self::$_status_8 => '#33CD9C',//33CD9C
        );
    }

    /**
     * 三期项目新增,add by XiaHB
     * @return array
     */
    public static function getStatusAdminDesc ()
    {
        return array(
            self::$_status_5 => '审核已通过',//#7B6CD5
            self::$_status_6 => '审核不通过',//EF4D57
            self::$_status_7 => '已下线',//434343
            self::$_status_8 => '已上线',//#33CD9C
        );
    }

    public static function keyInStatus ($key)
    {
        return in_array($key, array_flip(self::getStatusDesc()));
    }

    public static function getOnlineDesc ()
    {
        return array(self::$_online_true => '是', self::$_online_false => '否');
    }

    public static function keyInOnline ($key)
    {
        return in_array($key, array_flip(self::getOnlineDesc()));
    }

    public static function getScreenDesc ()
    {
        return array(self::$_screen_vertical => '竖', self::$_screen_across => '横');
    }

    public static function keyInScreen ($key)
    {
        return in_array($key, array_flip(self::getScreenDesc()));
    }

    public static function checkCopyright($id){
        $where = "id = $id";
        $res = self::$_db->getRow($where);
        if(!$res){
            return false;
        }
        if(empty($res['soft_copyright'])){
            return false;
        }

        return true;
    }

    public static function getUrlTypeDesc(){
        return [self::$url_type_inner=>"上传包", self::$url_type_link=>"link-game", self::$url_type_wx=>"wx-game"];
    }

    public static function checkURLType($type){
        $arr = [self::$url_type_inner, self::$url_type_link, self::$url_type_wx];
        return in_array($type, $arr);
    }

    public static function getWechatProgramDesc(){
        return ['0'=>"发布版",'1'=>"测试版",'2'=>"体验版"];
    }


}