<?php
namespace php_base\MsgQueue\ProductBean;

use php_base\MsgQueue\MsgQueue\MessageQueue;

class EventBean extends MessageQueue {

    public $event_type; // 事件类型
    public $event_fields; // 事件内容
    public $send_time; // 事件发送时间

}