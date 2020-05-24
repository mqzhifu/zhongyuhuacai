<?php

/**
 * @Author: xuren
 * @Date:   2019-02-14 15:34:22
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-04 10:54:02
 */
class OpenUserModel{
	static $_table = 'open_user';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;
    const TYPE_PERSON = 2;
    const TYPE_COMPANY = 1;

    static function getAccountDescs(){
        return array("1"=>"公司账号","2"=>"个人账号");
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

    public static function checkIdcardAndBusiness($uid){
        $where = "uid = $uid";
        $res = self::db()->getRow($where);
        if(empty($res['idcard_img']) || empty($res['idcard2_img'] || empty($res['business']))){
            return false;
        }
        return true;
    }

    public static function getType(){
        $where = "uid = $uid";
        $res = self::db()->getRow($where);
        
        return isset($res['type']) ? $res['type'] : null;
    }



}