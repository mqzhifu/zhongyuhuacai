<?php
if (! function_exists('env')) {
    function env($key, $default = null)
    {
        return false;
    }
}

include_once "../../loader.php";

use php_base\MsgQueue\Test\Product\OrderBean;
use php_base\MsgQueue\Test\Product\UserBean;
use php_base\MsgQueue\Test\Product\SmsBean;
use php_base\MsgQueue\Test\Product\PaymentBean;
use php_base\MsgQueue\Test\Product\QueueMinLengthBean;

use php_base\MsgQueue\Exception\RejectMsgException;
use php_base\MsgQueue\Exception\RetryException;

//use Jy\Log\Facades\Log;
//Log::getInstance()->init("_path","Log");

//开始  consumer监听单bean


//simple();exit;
autoDeleteQueue();exit;
//retry();exit;
//retryByMsg();exit;
//reject();exit;
//runtimeException();exit;
//runtimeUserCancel();exit;
//runtimeDeadLoopUserCancel();exit;
//synchReceiveServerManyMsg();exit;

//异常
//noTopic();
//noAck();
//execTimeout();
//signalStopProcess();

function autoDeleteQueue(){
    $SmsBean = new SmsBean(null,null,3);
    $callback = function($msg){
        echo "im autoDeleteQueue UnitTest  callback ,bean:SmsBean! \n";
        echo "msg info:  id ".$msg->_id . " type ".$msg->_type ." msg ".$msg->_msg . " \n";
        echo "test groupSimple ok! \n";
    };

    $SmsBean->groupSubscribe($callback,"autoDeleteQueue",true);
}

function runtimeDeadLoopUserCancel(){
    $SmsBean = new SmsBean(null,null,3);


    $shutdownFunc = function(){
        echo "im runtimeDeadLoopUserCancel callback func\n";
    };
    $SmsBean->regConsumerShutdownCallback($shutdownFunc);

    $callback = function() use ($SmsBean){
        echo "im runtimeDeadLoopUserCancel callback func, id is :{$SmsBean->_id} \n";
        $SmsBean->quitConsumerDemon();
    };

    $loop = 0;
    while ($loop++ <= 10){
        $SmsBean->groupSubscribe($callback,"runtimeDeadLoopUserCancel");
        echo "cancel after....loop \n";
    }

    echo "loop end \n";
}


function runtimeUserCancel(){
    $SmsBean = new SmsBean(null,null,3);


    $shutdownFunc = function(){
        echo "im shutdown callback func\n";
    };
    $SmsBean->regConsumerShutdownCallback($shutdownFunc);

    $callback = function() use ($SmsBean){
        echo "im runtimeUserCancel\n";
        $SmsBean->quitConsumerDemon();
    };
    $SmsBean->groupSubscribe($callback,"groupSimple");


    echo "cancel after....";
}

function execTimeout(){
    //最简单的情况
    $SmsBean = new SmsBean(null,null,3);
    $callback = function($msg){
        while (1){}
    };
    $SmsBean->groupSubscribe($callback,"groupSimple");
}



function noTopic(){
    $SmsBean = new SmsBean(null,null,3);
    $SmsBean->setTopicName("none");
    $callback = function(){
        echo "im noTopic";
    };
    $SmsBean->groupSubscribe($callback,"groupSimple");
}

function simple(){
    //最简单的情况
    $SmsBean = new SmsBean(null,null,3);
    $callback = function($msg){
        echo "im groupSimple UnitTest  callback ,bean:SmsBean! \n";
        echo "msg info:  id ".$msg->_id . " type ".$msg->_type ." msg ".$msg->_msg . " \n";
        echo "test groupSimple ok! \n";
    };
    $SmsBean->groupSubscribe($callback,"groupSimple");
}
//用户，暂时不想处理，走retry机制
function retry(){
    $UserBean = new UserBean(null,null,3);
    $UserBean->setRetryTime(array(5, 10));
    $callback = function ($msg) {
        echo "im groupRetry  UnitTest,bean: UserBean!! \n";
        throw new RetryException();
    };

    $UserBean->groupSubscribe($callback,'groupRetry');
}
function retryByMsg(){
    $UserBean = new UserBean(null,null,3);
    $UserBean->setRetryTime(array(5, 10));
    $callback = function ($msg) {
        echo "im group retryByMsg ,bean: UserBean!! \n";
        $RetryException = new RetryException();
        $RetryException->setRetry(array(4, 7));
        throw $RetryException;
    };

    $UserBean->groupSubscribe($callback,'groupRetry');
}
//用户，觉得该消息有问题，直接丢弃掉
function reject(){
    $OrderBean = new OrderBean(null,null,3);
    $callback = function($msg){
        echo "im group reject,bean: OrderBean! \n";
//        throw new \Exception("tmp",900);
        throw new RejectMsgException();
    };
    $OrderBean->groupSubscribe($callback,'groupReject');
}
//运行时异常
function runtimeException(){
    $SmsBean = new SmsBean(null,null,3);
    $SmsBean->setRetryTime(array(5,10));
    $callback = function($msg){
        throw new \Exception("runtime err",9999);
    };

    $SmsBean->groupSubscribe($callback,'runtimeException');
}

////QueueMinLengthBean();
//function QueueMinLengthBean(){
//    $QueueMinLengthBean = new QueueMinLengthBean(null,null,3);
//    $func = function ($backData){
//        var_dump($backData->_id);
//    };
//    $QueueMinLengthBean->groupSubscribe($func,"QueueMinLengthBean");
//}