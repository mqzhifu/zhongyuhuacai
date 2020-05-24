<?php
class GamesGoodsOrderModel {
    static $_table = 'games_goods_order';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_type_wechat = 1;
    static $_type_alipay = 2;

    static $_category_goldcoin = 1;
    static $_category_cash = 2;
    static $_category_goldcoin_cash = 3;


    static $_trade_type_app = 1;
    static $_trade_type_h5 = 2;


    static $_status_wait = 1;
    static $_status_ok = 2;
    static $_status_fail = 3;


    static function getTypeDesc(){
        return array(
            self::$_type_wechat =>'微信',
            self::$_type_alipay =>'支付宝',
        );
    }

    static function keyInSex($key){
        return in_array($key,array_flip(self::getTypeDesc()));
    }




    static function getTypeDescByKey($key){
        if(!self::keyInRegType($key)){
            return "未知";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
    }

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function login($uname,$ps){
        return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
    }

}