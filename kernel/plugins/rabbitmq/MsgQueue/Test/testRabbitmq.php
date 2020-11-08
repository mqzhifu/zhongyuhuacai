<?php
//注意 SERVER返回ACK确认 回调函数
//$lib->regAckCallback($clientAck);

//testDirectExchange($lib);
//testHeaderExchange($lib);
//testTopic($lib);
//testFanout($lib);
//testAck($lib);
//testDelayExchange($lib);
//testOther($lib);
//testManyToMany($lib);
//testCapabilitySimple($lib);
//testCapabilityConfirmMode($lib);
//testCapabilityTxMode($lib);

//$lib->wait();

//testUnit(6);





//$rabbit = new RabbitmqBean();
//$rabbit->setMode(1);
//$arg = array("delivery_mode"=>2);
//$rabbit->publishToBase("aaaaaa","test.other","sdff","",$arg);
//$rabbit->waitReturnListener();

//sleep(1);

//exit;

//echo "end";

//这个是最最简单的方式  非<确认|事务> 模式，(exchange queue 都已经建立好)
function testSimple($lib){
//    testUnit(2);
//    $info = "im testSimple";
    $info = new ProductSmsBean();
    $lib->publish( $info );
}

//确认模式下，SERVER返回ACK确认
$clientAck = function ($AMQPMessage) {
    out("client receive callback ack info:");

    $body = AmqpClient::getBody($AMQPMessage);
    var_dump($body);
    //发送消息的属性值
    $attr = AmqpClient::getReceiveAttr($AMQPMessage);
    //格式化，方便输出
    $info = AmqpClient::debugMergeInfo($attr);
    out("body:".json_encode($body) ." " .$info );
    if(isset($attr['message_id'])){
        $msgStatus = RedisMsgStatusAck::redisGetMsgStatusAck($attr['message_id']);
        RedisMsgStatusAck::redisSetMsgStatusAck($attr['message_id'],"sendOk");
        $info = " sendStatus:".$msgStatus['status'] . " sendTime:".date("Y-m-d H:i:s",$msgStatus['time']);
        out($info);
    }

    return true;
};

function testDirectExchange($lib){
//    testUnit($lib);

    $lib->confirmSelectMode();

    $exchangeName = "test.direct";

    $pre = "publish msg <direct> ";
    $info  = $pre ." ,test blank ";
    $lib->publish($info,$exchangeName);

    $info  =  $pre .",test no route";
    $lib->publish($info,$exchangeName,"aaaxxxxx");

    $routingKey = "apple";
    $info  =$pre . ",test routingKey : $routingKey  , queue ";
    $lib->publish($info,$exchangeName,$routingKey);

    $routingKey = "banana";
    $info  =$pre . ",test routingKey : $routingKey  , queue ";
    $lib->publish($info,$exchangeName,$routingKey);

    $routingKey = "banana";
    $info  =$pre ." ,test routingKey : $routingKey  ,complex msg";
    $arguments = [
//        'correlation_id'
//        'priority'=>1e

        'content_type' => 'text/plain',// application/json
        'delivery_mode' => 1 ,//1非持久化 2持久化
//        'content_encoding'=>"gzip",//gzip deflate ,传输格式
        "expiration"=>5000,
        'timestamp'=>time(),

        //以下均为扩展字段
        'type'=>'ext_direct',
        'user_id'=>'root',
        'app_id'=>1,
//        'cluster_id'=>-1,
        "message_id"=>RedisMsgStatusAck::getUniqueMsgId(),
    ];
    $lib->publish($info,$exchangeName,'banana',null,$arguments);

}

function testHeaderExchange($lib){
//    testUnit($lib,2);
    $lib->confirmSelectMode();

    $exchangeName = "test.header";
    $info  ="im header ,test blank publish";
    $lib->publish($info,$exchangeName);

    $info  ="im direct ,test no route publish";
    $lib->publish($info,$exchangeName,"aaaxxxxx");

    $arr = array("aaaa"=>"cccc");
    $info  ="im direct ,test err header publish";
    $lib->publish($info,$exchangeName,"",$arr);

    $info = "im header ,test x-match-all email";
    $arr = array("x-match"=>'all','type'=>'email');
    $lib->publish($info,$exchangeName,"",$arr);

    $info = "im header ,test x-match-all sms";
    $arr = array("x-match"=>'all','type'=>'sms');
    $lib->publish($info,$exchangeName,"",$arr);

    $arr = array("cate"=>"sms",'x-match'=>'all');
    $lib->unbindExchangeQueue($exchangeName,"test.header.sms",null,$arr);

    $arr = array('type'=>'email','x-match'=>'all');
    $lib->bindQueue("test.header.sms",$exchangeName,"",$arr);

    $info = "im header ,test x-match-all email";
    $arr = array("x-match"=>'all','type'=>'email');
    $lib->publish($info,$exchangeName,"",$arr);

}

function testTopic(Lib $lib){
    $lib->confirmSelectMode();

    $exchangeName = "test.topic";

    $info = "im Topic ,test key: #.a.b";
    $lib->publish($info,$exchangeName,"test.topic.error");
    $lib->publish($info,$exchangeName,"test.aaaa.topic.error");


    $info = "im Topic ,test key: A.*.B";
    $lib->publish($info,$exchangeName,"topic.emailsdfsdfd sdf.alert");
    $lib->publish($info,$exchangeName,"topic.emailsdfsdfd.alert");
    $lib->publish($info,$exchangeName,"topic.emailsdfsdfd ,.alert");
}

function testFanout(Lib $lib){
    $lib->confirmSelectMode();

    $exchangeName = "test.fanout";
    $info = "im Topic fanout";
    $info = json_encode($info);
    $lib->publish($info,$exchangeName);
    $lib->publish($info,$exchangeName,"xxxxxx");

}

function testAck(Lib $lib){
    $lib->confirmSelectMode();

    $exchangeName = "test.fanout";
    $info = "im testAck by fanout EX  ,use json header. ";
    $msgId = RedisMsgStatusAck::getUniqueMsgId();
    $arguments = array(
        'content_type'=>'application/json',
        "message_id"=>$msgId,
    );
    //增加批量确认 模式
    RedisMsgStatusAck::redisSetMsgStatusAck($msgId,"sendWait");
    $lib->publish($info,$exchangeName,"fanout.red",null,$arguments);


}

function testDelayExchange($lib){
    $lib->confirmSelectMode();

    $exchangeName = "test.header.delay";
    class Demo1{
        private $_id = 1;
        private $_name = "xiaoz";
    }
    $msgId = RedisMsgStatusAck::getUniqueMsgId();

    $infoClass = new Demo1();
    $info = serialize($infoClass);
    $arguments = array(
//        "expire"=>5000,
        "message_id"=>$msgId,
        'content_type'=>'application/serialize',
    );
    RedisMsgStatusAck::redisSetMsgStatusAck($msgId,"sendWait");
    $header = array("type"=>"email","x-match"=>'any','x-delay'=>10000);
    $lib->publish($info,$exchangeName,"",$header,$arguments);

}

function testOther($lib){
    $lib->confirmSelectMode();

    $exchangeName = "test.other";

    //测试优先集队列
    $info =  "test priority 1";
    $arguments = array(
        'content_type'=>'text/plain',
        'priority'=>1,
    );

    $lib->publish($info,$exchangeName,"priority",null,$arguments);


    $info =  "test priority 10";
    $arguments = array(
        'content_type'=>'text/plain',
        'priority'=>10,
    );

    $lib->publish($info,$exchangeName,"priority",null,$arguments);

    //测试 当队列大于设置的最大值后，消息处理. 去 dead exchange 查找
    $info =  "test max length 1";
    $arguments = array(
        'content_type'=>'text/plain',
    );

    $lib->publish($info,$exchangeName,"max_length",null,$arguments);


    $info =  "testmessage_tll";
    $arguments = array(
        'content_type'=>'text/plain',
    );

    $lib->publish($info,$exchangeName,"message_tll",null,$arguments);

}

function testManyToMany(Lib $lib){
//    testUnit($lib,7);
    $lib->txSelect();

    try{
        $exchangeName = "test.header-many";
        $arr = array("sms"=>"sms");
        $info  ="im header ,testManyToMany publish";
        $lib->publish($info,$exchangeName,"",$arr);


//        $lib->publish($info,"bbbbbb","",$arr);

        $lib->txCommit();
    }catch (Exception $e){
        $lib->txRollback();
    }
}

//测试性能，最简单的模式
function testCapabilitySimple(Lib $lib){
//    testUnit($lib,1);
    $exchangeName = "test.direct";
    $lib->_debug = 0;

    $info = "aaaaaa";
    TestConfig::Capability($lib,$exchangeName,'apple',10000,$info);
//    max length 没有满的情况下，最大0.4 最小0.32

    Capability($lib,$exchangeName,'apple',100000,$info);
    //最多4.1 最少3.59
}
//测试性能，<确认模式>的模式
function testCapabilityConfirmMode(Lib $lib){
//    testUnit($lib,1);
    $lib->confirmSelectMode();
    $exchangeName = "test.direct";
    $lib->_debug = 0;

    $info = "aaaaaa";
    TestConfig::Capability($lib,$exchangeName,'apple',10000,$info);
    //最小0.42  最大0.50
    TestConfig::Capability($lib,$exchangeName,'apple',100000,$info);
    //均5秒
}

function testCapabilityTxMode(Lib $lib){
//    testUnit($lib,1);
    $exchangeName = "test.direct";
    $lib->_debug = 0;
    $lib->txSelect();
    $info = "aaaaaa";

    TestConfig::CapabilityTx($lib,$exchangeName,'apple',10000,$info);
    //2.7-2.4秒
    TestConfig::CapabilityTx($lib,$exchangeName,'apple',100000,$info);
    //不忍直视
}

function getQueueInfo($conf,$queueName){
    return ToolsUnit::apiCurlQueueInfo($conf['user'],$conf['pwd'],$conf['host'].":15672",'%2f',$queueName);
}
