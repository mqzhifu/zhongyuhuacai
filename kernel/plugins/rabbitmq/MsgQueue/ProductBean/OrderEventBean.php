<?php
/**
 * @user: ligongxiang (ligongxiang@rouchi.com)
 * @date : 2020/6/10
 * @version : 1.0
 * @file : OrderEventBean.php
 * @desc :
 */

namespace php_base\MsgQueue\ProductBean;

use php_base\MsgQueue\MsgQueue\MessageQueue;

class OrderEventBean extends MessageQueue
{
    public $event_type;     //事件类型:1.订单支付成功
    public $order_no;       //订单号
    public $source_id;      //订单来源
    public $type_id;        //订单类型
    public $paid_fee;       //实付金额，单位:分
    public $send_time;      //事件发送时间
}