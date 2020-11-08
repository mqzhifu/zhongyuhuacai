<?php

class BannerModel{
	static $_table = 'banner';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = "instantplay";

    const STATUS_TRUE = 1;
    const STATUS_FALSE = 2;

    const STATUS_DESC = [
        self::STATUS_TRUE => "上架",
        self::STATUS_FALSE => "下架",
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

    static function getStatusOptionHtml(){
        $html = "";
        foreach (self::STATUS_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }
}