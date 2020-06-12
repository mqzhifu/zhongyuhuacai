<?php
namespace php_base\MsgQueue\ProductBean;

use php_base\MsgQueue\MsgQueue\MessageQueue;

class HomeworkBean extends MessageQueue {

    public $primary_key; //主键字段
    public $primary_value; //主键值
    public $table_name; //表名
    public $record_type;//表操作
    public $fields; //变化字段集合
    public $current_time; //当前时间

}