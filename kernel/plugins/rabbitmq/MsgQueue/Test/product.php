<?php
//以下是用来兼容
if (! function_exists('env')) {
    function env($key, $default = null)
    {
        return false;
    }
}

if(!function_exists("out")){
    function out($msg ,$br = 1){
        if(is_object($msg) || is_array($msg)){
            $msg = json_encode($msg);
        }
        if($br){
            if (preg_match("/cli/i", php_sapi_name())){
                echo $msg . "\n";
            }else{
                echo $msg . "<br/>";
            }
        }else{
            echo $msg;
        }
    }
}

include_once "../../loader.php";

use php_base\MsgQueue\Test\Product\OrderBean;
use php_base\MsgQueue\Test\Product\UserBean;
use php_base\MsgQueue\Test\Product\SmsBean;
use php_base\MsgQueue\Test\Product\PaymentBean;

use php_base\MsgQueue\Test\Product\LargerBean;
use php_base\MsgQueue\Test\Product\NoRouteBean;
use php_base\MsgQueue\Test\Product\QueueMinLengthBean;

use php_base\MsgQueue\Model\MsgRecordModel;
//======================================================

use php_base\MsgQueue\Tools\RabbitmqApi;
use php_base\MsgQueue\Tools\AutoTestUnitEnv;
//use Jy\Log\Facades\Log;
//Log::getInstance()->init("_path","Log");

//测试工具类
$ToolsUnit = new  AutoTestUnitEnv('rabbitmq',null,3,2,0);
//$ToolsUnit->clearAll();exit;
//$ToolsUnit->clearRabbitmqAll();exit;
//$ToolsUnit->createCaseAndDelOldCase(3);

$SmsBean = new SmsBean(null,null,3);
$OrderBean = new OrderBean(null,null,3);
$UserBean = new UserBean(null,null,3);
$LargerBean =  new LargerBean(null,null,3);
$QueueMinLengthBean = new QueueMinLengthBean(null,null,3);
$NoRouteBean =  new NoRouteBean(null,null,3);


//===================================如下是 测试用例======================================================

//simple($SmsBean);
setIgnoreSendNoQueueReceive($SmsBean);
//simpleDelay($UserBean);
//ackModeCallback($OrderBean);
//transaction();
//manyBeanSynchExec($SmsBean,$UserBean,$OrderBean);
//异常
//noTopic($SmsBean);exit;
//noRouting($NoRouteBean);
//modeMutex($SmsBean);
//ignoreSendNoQueueReceive();
//queueMinLengthNack($QueueMinLengthBean);
//LargerBeanException($LargerBean);
//manyBeanSynchExecMutex($SmsBean,$UserBean,$OrderBean,$PaymentBean);

//sleep(1);

function manyBeanSynchExec($SmsBean,$UserBean,$OrderBean){
    simple($SmsBean);
    simpleDelay($UserBean);
    ackModeCallback($OrderBean);
}

function manyBeanSynchExecMutex($SmsBean,$UserBean,$OrderBean,$PaymentBean){
    simple($SmsBean);
    simgpleDelay($UserBean);
    ackModeCallback($OrderBean);
    transaction($PaymentBean);
}


function modeMutex(SmsBean $SmsBean){
    try{
        $SmsBean->setMode(1);
        $SmsBean->setMode(2);
        out(" test modeMutex no good! ");
    }catch (\Exception $e){
        out(" test modeMutex ok! ");
        var_dump($e->getMessage());
    }
}

function noRouting(NoRouteBean $NoRouteBean){
    $NoRouteBean->send();
}

function noTopic(SmsBean $SmsBean){
    $SmsBean->setTopicName("none");
    try{
        $SmsBean->send();
        out(" test noTopic no good! ");
    }catch (\Exception $e){
        out(" test noTopic ok! ");
        var_dump($e->getMessage());
    }
}

function queueMinLengthNack(QueueMinLengthBean $QueueMinLengthBean){
    $QueueMinLengthBean->_id = 2;
    try{
        $QueueMinLengthBean->send();
        out(" test queueMinLengthNack no good! ");
    }catch (\Exception $e){
        out(" test queueMinLengthNack ok! ");
        var_dump($e->getMessage());
    }
}


function setIgnoreSendNoQueueReceive(SmsBean $SmsBean){
    $SmsBean->_type = "register";
    $SmsBean->_id = 1;
    $SmsBean->_msg = "注册成功";

    $SmsBean->setIgnoreSendNoQueueReceive(false);

    $callback = function($msg){
        out ("im simple ack callback");
        if($msg->_id == 1){
            out ("ack ok!");
        }else{
            out ("ng :ack failed!");
        }
    };
    $SmsBean->regUserCallbackAck($callback);
    $msgId = $SmsBean->send();
    out("new msg id :".$msgId);
}

function simple(SmsBean $SmsBean){
//    checkNowQueueInfo("test.header.delay.sms");

    $SmsBean->_type = "register";
    $SmsBean->_id = 1;
    $SmsBean->_msg = "注册成功";

    $callback = function($msg){
        out ("im simple ack callback");
        if($msg->_id == 1){
            out ("ack ok!");
        }else{
            out ("ng :ack failed!");
        }
    };
    $SmsBean->regUserCallbackAck($callback);
    $msgId = $SmsBean->send();
    out("new msg id :".$msgId);
//    checkMysqlMsgRecord($msgId);
}

function simpleDelay(UserBean $user){
    $apiQueueInfo = checkNowQueueInfo("test.header.delay.user");
    $firstMsgCnt = $apiQueueInfo['messages_ready'];

    $user->_id = "123456";
    $user->_regTime = time();
    $user->_birthday = "20200101";
    $user->_realName = "zhangsan";
    $user->_nickName = "carssbor";

    $msgId = $user->sendDelay(3000);
    checkMysqlMsgRecord($msgId);
    //发送成功的话，5秒后，test.header.delay.sms 队列会多一条消息

    sleep(5);
    $apiQueueInfo = checkNowQueueInfo("test.header.delay.user");
    if($apiQueueInfo['messages_ready'] - $firstMsgCnt == 1){
        out(" test simgpleDelay ok!");
    }else{
        out(" test simgpleDelay no good!");
    }
}
class TestBack{
    function ackBack($msg){
        echo "im ack mode Class callback ,orderBean<br/>";
        if($msg->_price == 100){
            echo (" test ackModeCallback ok! <br/>");
        }else{
            echo(" test ackModeCallback no good! <br/>");
        }
    }
}
function ackModeCallback(OrderBean $OrderBean){
    $OrderBean->_id = 1;
    $OrderBean->_price = 100;
    $OrderBean->setMode(1);
    //这里也可以用 类 ，不一定是匿名函数
    $callback3 = array(new TestBack(),'ackBack');
    $OrderBean->regUserCallbackAck($callback3);
    $OrderBean->send();
}

function LargerBeanException(LargerBean $largerBean){
    $largerBean->setMessageMaxLength(10);
    try{
        $largerBean->send();
        out(" test ackModeCallback no good! ");
    }catch (\Exception $e){
        out(" test ackModeCallback ok! ");
        var_dump($e->getMessage());
    }
}

function transaction(){
    $PaymentBean = new PaymentBean(null,null,3);
    $PaymentBean->_type = "3yuan_small_class";
    $PaymentBean->_orderId = "abcdefg";
    $PaymentBean->_id = 456;
    try{
        $PaymentBean->transactionStart();
        $PaymentBean->send();
        $PaymentBean->transactionCommit();
    }catch (Exception $e){
        var_dump($e->getMessage());exit;
        $msg = $e->getMessage();
        $PaymentBean->transactionRollback();
    }
}


function ignoreSendNoQueueReceive(){
    $NoRouteBean = new NoRouteBean();
    $NoRouteBean->setIgnoreSendNoQueueReceive(1);
    noRouting($NoRouteBean);
}

function checkNowQueueInfo($queueName){
    $apiQueueInfo = RabbitmqApi::getInstant()->getQueueByName($queueName);
    $firstMsgCnt = $apiQueueInfo['messages_ready'];
    out("queue  ($queueName) first get has msg :".$firstMsgCnt);
    return $apiQueueInfo;
//    sleep(2);
//    $apiQueueInfo = RabbitmqApi::getInstant()->getQueueByName("test.header.delay.sms");
//    $secondMsgCnt = $apiQueueInfo['messages_ready'];
//    out("queue test.header.delay.sms second get has msg :".$secondMsgCnt);
//    if($secondMsgCnt - $firstMsgCnt != 1){
//        out("ng: {$secondMsgCnt} - $firstMsgCnt != 1");
//    }
}

function checkMysqlMsgRecord($msgId){
    $msgMysqlRecord = MsgRecordModel::getListByMsgId($msgId);
    if(!$msgMysqlRecord){
        out("ng : msgMysqlRecord is null");
    }

    foreach ($msgMysqlRecord as $k=>$v) {
        if($v['status'] != MsgRecordModel::STATUS_SENT){
            out("ng: msgMysqlRecord status != ".MsgRecordModel::STATUS_SENT);
        }
    }

    out("checkMysqlMsgRecord ok~");
}