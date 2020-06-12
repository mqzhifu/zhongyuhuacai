<?php
namespace php_base\MsgQueue\Test\Product;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class OrderBean extends MessageQueue{
    public $_id = 1;
    public $_channel = "tencent";//来源渠道
    public $_price = 0.00;//金额
    public $_num = 0;//购买数量
    public $_uid = 0;//用户ID

    function __construct($conf = null,$provinder = 'rabbitmq',$debugMode = 0){
        parent::__construct($provinder,$conf,$debugMode);
    }
}