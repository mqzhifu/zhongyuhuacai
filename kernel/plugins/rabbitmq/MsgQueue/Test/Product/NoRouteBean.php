<?php
namespace php_base\MsgQueue\Test\Product;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class NoRouteBean extends MessageQueue{
    public $_id = 1;
    public $_orderId = 0;

    function __construct($conf = null,$provinder = 'rabbitmq',$debugMode = 0){
        parent::__construct($provinder,$conf,$debugMode);
    }
}