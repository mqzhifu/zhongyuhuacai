<?php

class MoneyOrderModel {
    static $_table = 'money_order';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static $_state_unaudited = 0;
    static $_state_passed = 1;
    static $_state_rejected = 2;

    static function getStateDesc() {
        return array(
            self::$_state_unaudited =>'未审核',
            self::$_state_passed =>'通过',
            self::$_state_rejected =>'不通过',
        );
    }

    static function getTypeDesc() {
        return array('普通提现','金币+提现券');
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