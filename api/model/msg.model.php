<?php
class MsgModel {
	static $_table = 'msg';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";


    const DEL_FROM_TRUE = 1;
    const DEL_FROM_FALSE = 2;
    const DEL_FROM = [
        self::DEL_FROM_TRUE =>'已删除',
        self::DEL_FROM_FALSE =>'未删除',
    ];

    const DEL_TO_TRUE = 1;
    const DEL_TO_FALSE = 2;
    const DEL_TO = [
        self::DEL_TO_TRUE =>'已删除',
        self::DEL_TO_FALSE =>'未删除',
    ];

    const TYPE_PERSON_TO_PERSON = 1;
    const TYPE_SYSTEM_TO_ALL_PERSON  =2;
    const TYPE_SYSTEM_TO_PERSON = 3;

    const TYPE = [
        self::TYPE_PERSON_TO_PERSON =>'个人对个人',
        self::TYPE_SYSTEM_TO_ALL_PERSON =>'系统群发',
        self::TYPE_SYSTEM_TO_PERSON =>'系统单发',
    ];

    const FROM_READ_TRUE = 1;
    const FROM_READ_FALSE = 2;
    const FROM_READ = [
        self::FROM_READ_TRUE =>'接收方已读',
        self::FROM_READ_FALSE =>'接收方未读',
    ];

    const CATE_ORDER_WAIT_PAY = 1;
    const CATE_SHIPPING = 2;
    const CATE_SYSTEM_ALL = 3;

    const CATE = [
        self::CATE_ORDER_WAIT_PAY =>'等待支付',
        self::CATE_SHIPPING =>'已发付',
        self::CATE_SYSTEM_ALL =>'系统群发通知',
    ];

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }


    static function getTypeSelectOptionHtml(){
        $html = "";
        foreach (self::TYPE as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getCategorySelectOptionHtml(){
        $html = "";
        foreach (self::CATE as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

}