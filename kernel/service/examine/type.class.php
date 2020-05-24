<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-5-12
 * Time: 下午2:35
 */
class Table_Examine_Type extends Table
{
    public $_table = "examine_type";
    public $_primarykey = "id";

    public static $_static = false;

    public static $type_item_payment = 1;//供应商付款及其他付款
    public static $type_item_refund = 2;//供应商退款
    public static $type_finance_user_refund = 3;//退用户团款
    public static $type_finance_user_refund_channels = 4;//渠道退款
    public static $type_finance_collection = 5;//财务收款
    public static $type_item_allocate = 6;//调拨
    public static $type_item = 7;//项目结算

    public static function inst()
    {
        if (false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }

    public function getInfoByType($type)
    {
        if (is_string($type)) {
            $where = "type_key='$type'";
        } else {
            $where = "id=$type";
        }
        return self::inst()->autoClearCache()->where($where)->selectOne();
    }

} 