<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/4/29
 * Time: 11:31
 */

/**
 * Class shareGameCntModel
 */
class shareGameCntModel {
    static $_table = 'share_game_cnt';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    // sdk->cntShareGame insert.share_path
    static $_type_h5_game_share_wechat_single = 74;
    static $_type_h5_game_share_wechat_platform = 75;
    static $_type_h5_game_share_qq_single = 76;
    static $_type_h5_game_share_qq_platform = 77;
    // system/sdk->share update type
    static $_type_game_share_add_friends = 80;

    const SHARE_ALL = [
        '74' => array('platform'=>4,'platform_method'=>1),
        '75' => array('platform'=>4,'platform_method'=>2),
        '76' => array('platform'=>9,'platform_method'=>1),
        '77' => array('platform'=>9,'platform_method'=>2),
    ];

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    /**
     * @param $func
     * @param $arguments
     * @return mixed
     */
    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    /**
     * @param $sql
     * @return array
     */
    public static function getAll($sql){
        $result = self::db()->query($sql);
        return $result;
    }

    /**
     * @return array
     */
    static function getTypeTitle(){
        return array(
            self::$_type_h5_game_share_wechat_single => '微信好友',
            self::$_type_h5_game_share_wechat_platform => '微信朋友圈',
            self::$_type_h5_game_share_qq_single => 'QQ好友',
            self::$_type_h5_game_share_qq_platform => 'QQ朋友圈'
        );
    }

    /**
     * @param $key
     * @return mixed|string
     */
    static function getTypeTitleByKey($key){
        $arr = self::getTypeTitle();
        return $arr[$key];
    }
}