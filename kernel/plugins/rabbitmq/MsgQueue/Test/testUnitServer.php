<?php
//testCancelConsumer();
//testDirectExchangeByMultiConsumer($lib);
//测试性能，最简单的模式
//function testCapabilitySimple(Lib $lib){
//
//}

function testOneConsumer($lib){
    $lib->setBasicQos(1);

    $callback = function ($recall) use ($lib){
        $info = AmqpConsumer::debugMergeInfo($recall['attr']);
        out(" callback attr info: $info");

        $sms = new \Jy\Common\Rabbitmq\Test\Consumer\Sms();
        $sms->process();

        $lib->ack($recall['AMQPMessage']);

    };

    $queue_name = "test.direct.apple";
    $autoAck = false;
    $consumerTag = $queue_name ." tag";
    out("queue:$queue_name , autoAck: $autoAck , consumerTag : $consumerTag. ");
    $lib->basicConsume($queue_name, $consumerTag, $autoAck,  $callback);
    $lib->startListenerWait();
}

function testOneConsumerAckMode(){
    $lib = new AmqpConsumer();
    $lib->setBasicQos(1);

    $callback = function ($recall) use ($lib){
        $info = AmqpConsumer::debugMergeInfo($recall['attr']);
        out(" callback attr info: $info");

        $sendMsgStatus = AmqpConsumer::redisGetMsgStatusAck($recall['attr']['message_id']);
        if($sendMsgStatus['status'] == 'sendWait' || $sendMsgStatus['status'] == 'consumerFinish' ){
            $lib->reject($recall['AMQPMessage'],false);
            return true;
        }

        if($sendMsgStatus['status'] == 'consumerProcessing'){
            if(time() - $sendMsgStatus['time'] < 10){
                $lib->basic_reject($recall['AMQPMessage'],true);
                return true;
            }
        }

        AmqpConsumer::redisSetMsgStatusAck("consumerProcessing");
        try{
            $sms = new Sms();
            $sms->process();
        }catch (Exception $e){
            AmqpConsumer::redisSetMsgStatusAck("failed-".$e->getMessage());
            $lib->reject($recall['AMQPMessage'],false);
            return true;
        }


        AmqpConsumer::redisSetMsgStatusAck("consumerFinish");
        $lib->ack($recall['AMQPMessage']);
    };

    $queue_name = "test.header.delay.email";
    $autoAck = false;
    $consumerTag = "test.header.delay.email.consumer";
    out("queue:$queue_name , autoAck: $autoAck , consumerTag : $consumerTag. ");
    $lib->basicConsume($queue_name, $consumerTag, $autoAck,  $callback);

    $lib->consumerWait();
}

function testCancelConsumer(){
    $lib = new AmqpConsumer();
    $lib->setBasicQos(3);

    $queue_name = "test.direct.apple";
    $autoAck = false;
    $consumerTag = $queue_name ." tag";
    out("queue:$queue_name , autoAck: $autoAck , consumerTag : $consumerTag. ");


    $cnt = 1;
    $callback = function ($recall) use ($lib,$cnt,$consumerTag){
        global $cnt;
        $info = AmqpConsumer::debugMergeInfo($recall['attr']);
        out(" callback attr info: $info");

        $sms = new Sms();
        $sms->process();

        $lib->ack($recall['AMQPMessage']);

        if($cnt >= 5){
            $lib->listenerCancel($consumerTag);
            $lib->setStopListenerWait(1);
        }
        $cnt++;
    };

    $lib->basicConsume($queue_name, $consumerTag, $autoAck,  $callback);
    $lib->consumerWait();
}
//测试direct exchange ，开启多个consumer 监听
function testDirectExchangeByMultiConsumer($lib){
    $lib->setBasicQos(1);

    $callback = function ($recall) use ($lib){
//        $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'],true);

        $info = AmqpConsumer::debugMergeInfo($recall['attr']);
        out(" have a new msg: attr info: $info");

        if(!isset($recall['attr']['message_id']) || !$recall['attr']['message_id']){
            $lib->ack($recall['AMQPMessage']);
            return false;
        }

        $sendMsgStatus = RedisMsgStatusAck::redisGetMsgStatusAck($recall['attr']['message_id']);
        if($sendMsgStatus['status'] == 'sendWait' || $sendMsgStatus['status'] == 'consumerFinish' ){
            $lib->reject($recall['AMQPMessage'],false);
            return true;
        }

        if($sendMsgStatus['status'] == 'consumerProcessing'){
            if(time() - $sendMsgStatus['time'] < 10){
                $lib->reject($recall['AMQPMessage'],true);
                return true;
            }
        }

        RedisMsgStatusAck::redisSetMsgStatusAck($recall['attr']['message_id'],"consumerProcessing");
        try{
            //这里可以根据自己的需求，实现类
//            $sms = new Sms();
//            $sms->process();
            RedisMsgStatusAck::redisSetMsgStatusAck($recall['attr']['message_id'],"consumerFinish");
            $lib->ack($recall['AMQPMessage']);
        }catch (Exception $e){
            AmqpConsumer::redisSetMsgStatusAck("failed-".$e->getMessage());
            $lib->reject($recall['AMQPMessage'],false);
        }
    };
    //根据工具类，直接获取特定的一批队列名称
    $Tools = new Tools($lib);
    $Tools->setProjectId(1);
    $queues = $Tools->getQueues();
    //循环开启多个consumer ，走channel模式，复用一个TCP连接
    //这里，我为了省事儿只定义了一个CALLBACK function，如果想根据TYPE定制消费。多定义几个callback function 注册一下就行了
    foreach ($queues as $k=>$queue_name) {
        $autoAck = false;
        $consumerTag = $queue_name.".consumer";
        $lib->basicConsume($queue_name, $consumerTag, $autoAck,  $callback);
    }

    $lib->startListenerWait();
}