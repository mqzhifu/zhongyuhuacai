<?php

/**
 * @Author: Kir
 * @Date:   2019-04-04 16:25:51
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-08 15:12:10
 */


class OpenFinanceModel
{
    static $_table = 'open_finance';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static function getTaxType()
    {
    	return [1=>'增值税一般纳税人',2=>'增值税小规模纳税人'];
    }

    static function getInvoiceType()
    {
    	return [1=>'增值税专用发票',2=>'增值税普通发票'];
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

    static function db ()
    {
        if (self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key, self::$_table, self::$_pk);
        return self::$_db;
    }

    static function __callStatic ($func, $arguments)
    {
        return call_user_func_array(array(self::db(), $func), $arguments);
    }

}