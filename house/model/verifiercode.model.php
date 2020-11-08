<?php
class VerifiercodeModel {
	static $_table = 'verifier_code';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    const STATUS_NORMAL = 1;
    const STATUS_USED = 2;
    const STATUS_EXPIRE = 3;
    const STATUS_REPEAT_EXPIRE = 4;

    const STATUS_DESC = array(
        self::STATUS_NORMAL=>"正常",
        self::STATUS_USED=>"已使用",
        self::STATUS_EXPIRE=>"已失效",
        self::STATUS_REPEAT_EXPIRE =>"重复发送，触发失效",
    );

    const TYPE_SMS = 1;
    const TYPE_EMAIL = 2;

    const TYPE_DESC = array(
        self::TYPE_SMS=>"手机",
        self::TYPE_EMAIL=>"邮箱",
    );

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getStatusOption(){
        $html = "";
        foreach (self::STATUS_DESC as $k=>$v){
            $html .= "<option value='$k'>$v</option>";
        }
        return $html;
    }

    static function getTypeOption(){
        $html = "";
        foreach (self::TYPE_DESC as $k=>$v){
            $html .= "<option value='$k'>$v</option>";
        }
        return $html;
    }

}