<?php
namespace Jy\Common\MsgQueue\Test;

include_once "../../loader.php";

use php_base\MsgQueue\MsgQueue\RabbitmqListenManyBean;

use php_base\MsgQueue\Exception\RejectMsgException;
use php_base\MsgQueue\Exception\RetryException;

//use Jy\Log\Facades\Log;
//Log::getInstance()->init("_path","Log");

$RabbitmqListenManyBean =  new RabbitmqListenManyBean(null,null,3);

$OrderBeanCallback = function(){
    echo "im RabbitmqListenManyBean callback OrderBean";
    throw new \Exception();
};
$UserBeanCallback = function(){
    echo "im RabbitmqListenManyBean callback UserBean";
    while (1){}
};
$PaymentBeanCallback = function(){
    echo "im RabbitmqListenManyBean callback PaymentBean";
    throw  new RetryException();
};
$SmsBeanCallback = function(){
    echo "im RabbitmqListenManyBean callback SmsBean";
    throw  new RetryException();
};

$NoRouteCallback = function() use($RabbitmqListenManyBean){
    echo "im RabbitmqListenManyBean callback SmsBean";
    $RabbitmqListenManyBean->quitConsumerDemon();
};

$classCallbackFunc = array(
    'php_base\MsgQueue\Test\Product\OrderBean' =>$OrderBeanCallback,
    'php_base\MsgQueue\Test\Product\UserBean' =>$UserBeanCallback,
    'php_base\MsgQueue\Test\Product\SmsBean' =>$PaymentBeanCallback,
    'php_base\MsgQueue\Test\Product\PaymentBean' =>$SmsBeanCallback,
    'php_base\MsgQueue\Test\Product\NoRouteBean' =>$NoRouteCallback,
);

$RabbitmqListenManyBean->regListenerBeanByClassName($classCallbackFunc);
$retry = array(3,9);
$RabbitmqListenManyBean->quickSubscribe("_byToolsClass",$retry);
