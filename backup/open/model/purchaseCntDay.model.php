<?php

/**
 * @Author: xuren
 * @Date:   2019-05-07 10:14:30
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-16 11:52:22
 */
class PurchaseCntDayModel {
    static $_table = 'purchase_cnt_day';
    static $_pk = 'id';
    static $_db_key ="kxgame_log";
    static $_db = null;

    static $type_single_day_income = 1;
    static $type_accumulative_income = 2;
    static $type_pay_user_num = 3;
    static $type_first_pay_user_num = 4;
    static $type_permeate_rate = 5;
    static $type_avg_pay_user_income = 6;
    static $type_avg_user_income = 7;
    static $type_first_pay_percent = 8;

    // 近30日访问趋势select
    static $type_active_user_num = 9;
    static $type_new_reg_user_num = 10;
    // 近30日收入趋势select
    static $type_single_day_ios_income = 11;
    static $type_single_day_android_income = 12;
    static $type_first_pay_income = 13;
    static $type_visit_times = 14;
    static $type_avg_play_time = 15;
    static $type_share_times = 16;
    static $type_share_user_num = 17;

    static $type_accumulative_reg_num = 18;

    static $type_retention1 = 19;
    static $type_retention3 = 20;
    static $type_retention7 = 21;
    // static $os_type_all = 0;
    // static $os_type_android = 1;
    // static $os_type_ios = 2;

    static $os_type_all = "all";
    static $os_type_android = "android";
    static $os_type_ios = "ios";
    static $os_type_pc = "windows";

    static $date_type_30 = 1;
    static $date_type_7 = 2;
    static $date_type_custom = 3;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getSingleDayIncome($gameid, $from, $to, $osType){
        $sql = "select a_time,money from ".self::$_table." where (a_time between $from and $to ) and os_type=$osType and game_id=$gameid ";
        return self::db()->getAllBySQL($sql);
    }

    static function getTypeDesc(){
        return [
            self::$type_single_day_income=>"单日总收入",
            self::$type_accumulative_income=>"累计总收入",
            self::$type_pay_user_num=>"付费用户数",
            self::$type_first_pay_user_num=>"首次付费用户数",
            self::$type_permeate_rate=>"渗透率",
            self::$type_avg_pay_user_income=>"平均付费用户收入",
            self::$type_avg_user_income=>"平均用户收入",
            self::$type_first_pay_percent=>"平均首付占比"
        ];
    }

    static function getOSTypeDesc(){
        return [
            self::$os_type_all=>"全部",
            self::$os_type_android=>"安卓",
            self::$os_type_ios="IOS"
        ];
    }

    static function getDateTypeDesc(){
        return [
            self::$date_type_30=>"最近30天",
            self::$date_type_7=>"最近7天",
            self::$date_type_custom=>"自定义"
        ];
    }

    static function get30VisitSelection(){
        return [
            self::$type_active_user_num=>"活跃用户数",
            self::$type_new_reg_user_num=>"新增注册用户数",
            self::$type_pay_user_num=>"付费用户数",
            self::$type_first_pay_user_num=>"首次付费用户数"
        ];
    }

    static function get30IncomeSelection(){
        return [
            self::$type_single_day_income=>"单日总收入",
            self::$type_single_day_android_income=>"单日安卓总收入",
            self::$type_single_day_ios_income=>"单日IOS总收入",
            self::$type_first_pay_user_num=>"单日新增付费用户收入"
        ];
    }

    static function getVisitLineDesc(){
        return [
            self::$type_accumulative_reg_num=>"累计注册用户数",
            self::$type_active_user_num=>"活跃用户数",
            self::$type_visit_times=>"访问次数",
            self::$type_new_reg_user_num=>"新增注册用户数",
            self::$type_avg_play_time=>"人均停留时长",
            self::$type_share_times=>"分享次数",
            self::$type_share_user_num=>"分享用户数"
        ];
    }

}