<?php

/**
 * @Author: Kir
 * @Date:   2019-02-28 11:46:52
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-03-13 18:21:27
 */

class ActivityLogModel {
    static $_table = 'open_activity_log';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    // 事件源
    static $_event_add_dev_user = 101;
    static $_event_add_test_user = 102;
    static $_event_del_dev_user = 103;
    static $_event_del_test_user = 104;
    static $_event_add_game = 201;
    static $_event_up_game_name = 301;
    static $_event_up_game_desc = 302;
    static $_event_up_game_pic = 303;
    static $_event_up_game_cate = 304;
    static $_event_upload_res = 401;
    static $_event_set_test_ver = 501;
    static $_event_set_dev_ver = 502;
    static $_event_set_pro_ver = 503;
    static $_event_submit_audit = 601;
    static $_event_set_mode_common = 701;
    static $_event_set_mode_dev = 702;
    static $_event_up_msg_rule = 801;

    static function getLogDesc() {
    	return array(
            self::$_event_add_dev_user => '添加了一位开发者 ',
            self::$_event_add_test_user => '添加了一位测试者 ',
            self::$_event_del_dev_user => '删除了一位开发者 ',
            self::$_event_del_test_user => '删除了一位测试者 ',
            self::$_event_add_game => '创建了游戏 ',
            self::$_event_up_game_name => '把游戏名称更改为： ',
            self::$_event_up_game_desc => '把游戏简介更改为： ',
            self::$_event_up_game_pic => '更新了图片素材',
            self::$_event_up_game_cate => '更改了游戏类别，',
            self::$_event_upload_res => '上传了新的托管资产',
            self::$_event_set_test_ver => '设置为测试版本',
            self::$_event_set_dev_ver => '设置为开发版本',
            self::$_event_set_pro_ver => '设置为上线版本',
            self::$_event_submit_audit => '提交了应用审核',
            self::$_event_set_mode_common => '更新了应用状态：已发布并对外公开',
            self::$_event_set_mode_dev => '更新了应用状态：开发模式，应用不再公开',
            self::$_event_up_msg_rule => '更新了消息规则',
    	);
    }

    static function db() {
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments) {
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function addLog($uid, $source, $gameId, $gameVer=null, $extra=null) {
    	$log = [
    		'uid'=>$uid,
    		'event_source'=>$source,
    		'game_id'=>$gameId,
    		'game_version'=>$gameVer,
    		'extra'=>$extra,
    		'a_time'=>time()
    	];
    	return self::db()->add($log);
    }

    static function logAnalysis($log)
    {
    	$source = $log['event_source'];
    	$logDesc = self::getLogDesc()[$source];

    	switch ($source) {
            case self::$_event_add_dev_user:
            case self::$_event_add_test_user:
            case self::$_event_del_dev_user:
            case self::$_event_del_test_user:
                $opUid = $log['extra']->operateUid;
                $opName = UserModel::db()->getRowById($opUid)['nickname'];
                return $logDesc.$opName;

    		case self::$_event_add_game:
    			$game_name = GamesModel::db()->getRowById($log['game_id'])['name'];
    			return $logDesc.$game_name;

            case self::$_event_up_game_desc:
                return $logDesc.$log['extra']->gameDesc;

            case self::$_event_up_game_cate:
                if($log['extra']->oldCategory){
                    $oldCateName = GameCategoryModel::db()->getRowById($log['extra']->oldCategory)['name_cn'];
                }
                if($log['extra']->newCategory){
                    $oldCateName = GameCategoryModel::db()->getRowById($log['extra']->newCategory)['name_cn'];
                }

                $newCateName = GameCategoryModel::db()->getRowById($log['extra']->newCategory)['name_cn'];
                return $logDesc.'从 '.$oldCateName.' 更改为 '.$newCateName;

    		case self::$_event_set_test_ver:
    		case self::$_event_set_dev_ver:
    		case self::$_event_set_pro_ver:
            case self::$_event_submit_audit:
    			return '把 版本'.$log['game_version'].' '.$logDesc;
    		
    		default:
    			return $logDesc;
    	}
    }

}