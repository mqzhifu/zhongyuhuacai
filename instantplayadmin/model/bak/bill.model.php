<?php

/**
 * @Author: Kir
 * @Date:   2019-03-21 20:46:37
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-18 15:29:53
 */

/**
 * 
 */
class BillModel
{
	static $_table = 'game_bills';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    // 未对账
    static $_status_no_checked = 0;
    // 已对账
    static $_status_checked = 1;
    // 发送对账单
    static $_status_sent = 2;
    // 确认收票
    static $_status_received = 3;
    // 申请付款
    static $_status_pay_applied = 4;
    // 财务收票
    static $_status_finance_received = 5;
    // 已付款
    static $_status_paid = 6;

    // 账单类型
    // 内购
    static $_type_purchase = 1;
    // 广告
    static $_type_ad = 2;


    static function getBillStatusDesc() {
        return [
            self::$_status_no_checked => '未对账',
            self::$_status_checked => '已对账',
            self::$_status_sent => '发送对账单',
            self::$_status_received => '确认收票',
            self::$_status_pay_applied => '申请付款',
            self::$_status_finance_received => '财务收票',
            self::$_status_paid => '已付款',
        ];
    }

    static function getSettlementStatusDesc() {
        return [
            self::$_status_no_checked => '未对账',
            self::$_status_checked => '已对账',
            self::$_status_received => '确认收票',
        ];
    }

    // 生成账单周期
    static function getBillPeriod() {
        $billPeriod = [];
        $now = strtotime('this month');
        $bill = self::db()->getRow();
        if ($bill && $bill['bill_period']) {
            for ($date=$bill['bill_period']; $date <= $now; $date=strtotime('+1 month',$date)) { 
                $billPeriod[] = date('Y-m',$date);
            }
        } else {
            $billPeriod[] = date('Y-m',$now);
        }
        return $billPeriod;
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