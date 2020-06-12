<?php
namespace php_base\MsgQueue\Test\Product;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class UserBean extends MessageQueue{
    public $_id = 1;
    public $_nickName = "";
    public $_realName = "";
    public $_regTime = 0;
    public $_birthday = 0;

    function __construct($conf = null,$provinder = 'rabbitmq',$debugMode = 0){
        parent::__construct($provinder,$conf,$debugMode);
    }

//    function publishTx($bean){
//        $this->transactionStart();
//        try{
//            $this->send( $bean );
//            $this->transactionCommit();
//        }catch (\Exception $e){
//            $this->transactionCommit();
//        }
//    }

}