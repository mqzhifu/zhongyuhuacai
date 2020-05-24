<?php

/**
 * @Author: Kir
 * @Date:   2019-03-04 14:30:33
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-03-04 17:38:59
 */

/**
 * 游戏成员管理类
 */
class UserGamesModel {
    static $_table = 'open_user_games';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    ## 用户权限
    # 管理员
    static $_user_admin = 1;
    # 开发者
    static $_user_dev = 2;
    # 测试者
    static $_user_test = 3;

    # 管理员权限列表
    static $_user_admin_auths = [
    	'open_admin_login',		# 登陆管理后台
    	'game_manage',			# 游戏管理、设置
    	'game_host',			# 素材托管
    	'data_analysis',		# 数据分析
    	'ad',					# 广告
    	'revenue',				# 订单、收入查询
    	'app_test',				# App中使用测试版
    ];

    # 开发者权限列表
    static $_user_dev_auths = [
    	'open_admin_login',
    	'game_manage',
    	'game_host',
    	'data_analysis',
    	'ad',
    	'app_test',
    ];

    # 测试者权限列表
    static $_user_test_auths = [
    	'app_test',
    ];

    static function getAuths() {
    	return array(
    		self::$_user_admin => self::$_user_admin_auths,
	    	self::$_user_dev => self::$_user_dev_auths,
	    	self::$_user_test => self::$_user_test_auths
    	);
    }


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

}