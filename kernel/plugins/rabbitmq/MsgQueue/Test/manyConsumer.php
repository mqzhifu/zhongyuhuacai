<?php
namespace php_base\MsgQueue\Test;
include "../../loader.php";

use php_base\api\message\sms\v1\Sms;
use php_base\MsgQueue\MsgQueue\MessageQueue;

use php_base\MsgQueue\Test\Product\OrderBean;
use php_base\MsgQueue\Test\Product\UserBean;
use php_base\MsgQueue\Test\Product\SmsBean;
use php_base\MsgQueue\Test\Product\PaymentBean;

use php_base\MsgQueue\Exception\RejectMsgException;
use php_base\MsgQueue\Exception\RetryException;

class ConsumerOneBean extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);

        $SmsBean = new SmsBean(null,null,3);
        $this->regListenerBean(array($SmsBean));
    }

    function handleSmsBean($msg){
        echo "im handleSmsBean , ConsumerOneBean\n";
    }
}

class ConsumerOneBeanCancel extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);

        $SmsBean = new SmsBean(null,null,3);
        $this->regListenerBean(array($SmsBean));
    }

    function handleSmsBean($msg){
        echo "im handleSmsBean , ConsumerOneBean\n";
        $this->quitConsumerDemon();
    }
}


class ConsumerManyBean extends  MessageQueue{
    function __construct($conf = "")
    {
        parent::__construct("rabbitmq", $conf, 3);

        $PaymentBean = new PaymentBean(null,null,3);
        $OrderBean = new OrderBean(null,null,3);
        $UserBean = new UserBean(null,null,3);
        $SmsBean = new SmsBean(null,null,3);

        $this->regListenerBean(array($OrderBean,$PaymentBean,$UserBean,$SmsBean));

    }
    //拒绝一条消息
    function handlePaymentBean($msg){
        echo "im handlePaymentBean , ConsumerManyBean\n";
        throw new RejectMsgException();
    }
    //回滚，重试
    function handleOrderBean($msg){
        echo "im handleOrderBean , ConsumerManyBean \n";
        throw new RetryException();
    }
    //运行时异常
    function handleUserBean($msg){
        echo "im handleUserBean\n , ConsumerManyBean";
        throw new \Exception();
    }
    //
    function handleSmsBean($msg){
        echo "im handleSmsBean , ConsumerManyBean \n";
        $RetryException=new RetryException();
        $retry = array(2,6);
        $RetryException->setRetry($retry);
        throw $RetryException;
    }
}

//==========异常 测试==========================
class ConsumerOneBeanNoTopic extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);

        $SmsBean = new SmsBean();
        $this->regListenerBean(array($SmsBean));
        $this->setTopicName("none");
    }

    function handleSmsBean($msg){

    }
}

class ConsumerOneBeanTimeout extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);

        $SmsBean = new SmsBean();
        $this->regListenerBean(array($SmsBean));
    }
}


class ConsumerOneBeanNoHandle extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);

        $SmsBean = new SmsBean();
        $this->regListenerBean(array($SmsBean));
    }
}

class ConsumerOneBeanNoSetBean extends  MessageQueue{
    function __construct($conf = ""){
        parent::__construct("rabbitmq", $conf, 3);
    }
}
//===================================================

//$ConsumerOneBean = new ConsumerOneBean();
//$ConsumerOneBean->subscribe("normal");

//$ConsumerManyBean =  new ConsumerManyBean();
//$retry = array(2,4,6);
//$ConsumerManyBean->subscribe("many",$retry);

//$ConsumerOneBeanCancel = new ConsumerOneBeanCancel();
//subscribe($ConsumerOneBeanCancel,"cancel");
//异常处理

//$ConsumerOneBeanNoTopic = new ConsumerOneBeanNoTopic();
//subscribe($ConsumerOneBeanNoTopic,"NoTopic");

//$ConsumerOneBeanTimeout = new ConsumerOneBeanTimeout();
//subscribe($ConsumerOneBeanTimeout,"Timeout");

//$ConsumerOneBeanNoSetBean = new ConsumerOneBeanNoSetBean();
//subscribe($ConsumerOneBeanNoSetBean,"NoSetBean");

//$ConsumerOneBeanNoHandle = new ConsumerOneBeanNoHandle();
//subscribe($ConsumerOneBeanNoHandle,"NoHandle");

//signalStopProcess
//cancel


function subscribe($lib,$consumerTag ){
    $lib->subscribe($consumerTag);
}