<?php

/**
 * @Author: xuren
 * @Date:   2019-02-14 15:34:22
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-08 13:46:39
 */
class InformationModel{
	static $_table = 'open_user';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;
    const TYPE_PERSON = 2;
    const TYPE_COMPANY = 1;

    static function getAccountDescs(){
        return array("1"=>"公司账号","2"=>"个人账号");
    }

    static $_status_uncommitted = 0;    //未提交
    static $_status_auditing = 1;       //审核中
    static $_status_passed = 2;         //审核通过
    static $_status_rejected = 3;       //审核不通过

    static function getStatusDescs(){
        return array(
            self::$_status_uncommitted=>"未提交",
            self::$_status_auditing=>"审核中",
            self::$_status_passed=>"审核通过",
            self::$_status_rejected=>"审核不通过",
        );
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