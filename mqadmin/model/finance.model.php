<?php

/**
 * @Author: Kir
 * @Date:   2019-03-21 11:36:23
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-30 10:09:43
 */

class FinanceModel {
    static $_table = 'game_finance';
    static $_pk = 'game_id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;
    static $type_ad = 2;
    static $type_purchase = 1;
    // 签约主体
    static $_contract_body_desc = [
    	1 => '北京开心人信息技术有限公司',
    	// 2 => '主体B',
    ];

    // 账期
    static $_account_period_desc = [
    	1 => 'N+1',
    	2 => 'N+2',
    	3 => 'N+3',
    ];

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