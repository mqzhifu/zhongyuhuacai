<?php
namespace php_base\MsgQueue\Test\Product;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class SmsBean extends MessageQueue{
    public $_id = 1;
    public $_type = "";
    public $_msg = "";

    function __construct($conf = null,$provinder = 'rabbitmq',$debug = 0){
        parent::__construct($provinder,$conf,$debug);
    }

}