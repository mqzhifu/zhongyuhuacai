<?php
namespace php_base\MsgQueue\MsgQueue;

use php_base\MsgQueue\Model\ErrorRecordModel;

class RabbitmqPhpExt{
    private $_conn = null;
    private $_channel = null;
//    private $_exchange = null;

    private $_confirmSelectMode = 0;

    private $_deliveryPool = array();
    private $_deliveryTagSequence = 0;
    function connect($conf){
        $arr = array(
            'host' =>$conf['host'],
            'vhost' =>$conf['vhost'],
            'port' => $conf['port'],
            'login' => $conf['user'],
            'password' =>$conf['pwd'],
            'heartbeat'=>60,
        );
        try{
            $conn = new \AMQPConnection($arr);
            $conn->connect();
        }catch (\Exception $e){
            $data = array(
                'code'=>$e->getCode(),
                'content'=>$e->getMessage(),
                'type'=>ErrorRecordModel::TYPE_EXT,
            );
            ErrorRecordModel::create($data);
            throw $e;
        }
        $this->_conn = $conn;
        return $conn;
    }

    function getHeartbeatInterval(){
        return $this->_conn->getHeartbeatInterval();
    }

    function getReadTimeOut(){
        return $this->_conn->getReadTimeout();
    }

    function getWriteTimeOut(){
        return $this->_conn->getWriteTimeout();
    }

    function isConnected(){
        return $this->_conn->connect();
    }

    function getChannel(){
        if($this->_channel){
            return $this->_channel;
        }

        $channel = new \AMQPChannel($this->_conn);
        $this->_channel = $channel;
        return $channel;
    }

    function getExchange($exchangeName = ""){
        $exchange = new \AMQPExchange($this->getChannel());
        if($exchangeName){
            $exchange->setName($exchangeName);
        }
        return $exchange;
    }

    function getQueue($queueName = 0){
        $queue = new \AMQPQueue($this->getChannel());
        if($queueName){
            $queue->setName($queueName);
        }

        return $queue;
    }

    function setBasicQos($num){
        return $this->getChannel()->qos(null,$num);
    }

    function queueDeclare($queueName,$passive = false ,$durable,$exclusive = false ,$autoDelete,$nowait = false,$arguments){
        $queue = $this->getQueue($queueName);
        if($arguments)
            $queue->setArguments($arguments);

        if($durable)
            $queue->setFlags(AMQP_DURABLE);


        if($passive)
            $queue->setFlags(AMQP_PASSIVE);


        if($autoDelete)
            $queue->setFlags(AMQP_AUTODELETE);


        if($exclusive)
            $queue->setFlags(AMQP_EXCLUSIVE);


        $queue->declareQueue();
    }

    //等待rabbitmq 返回内容
    function baseWait(){
        try{
            $this->getChannel()->waitForConfirm();
        }catch (\Exception $e){
            $data = array(
                'code'=>$e->getCode(),
                'content'=>$e->getMessage(),
                'type'=>ErrorRecordModel::TYPE_EXT,
            );
            ErrorRecordModel::create($data);
            throw $e;
        }

    }

    function consumerWait(){
        return $this->getChannel()->waitForBasicReturn();
    }

    function queueBind($queueName,$exchangeName,$routingKey,$nowait = false,$header){
        try{
            $queue = $this->getQueue($queueName);
            if($header){
                return $queue->bind($exchangeName,$routingKey,$header);
            }else{
                return $queue->bind($exchangeName,$routingKey);
            }
        }catch (\Exception $e){
            $data = array(
                'code'=>$e->getCode(),
                'content'=>$e->getMessage(),
                'type'=>ErrorRecordModel::TYPE_EXT,
            );
            ErrorRecordModel::create($data);
            throw $e;
        }


    }
    function deleteQueue($queueName){
        $queue = $this->getQueue($queueName);
        $queue->delete();
    }

    function exchangeDeclare($exchangeName,$type,$arguments = null){
        $exchange = $this->getExchange($exchangeName);
        $exchange->setType($type);

        if($arguments){
            $exchange->setArguments($arguments);
        }

        $exchange->declareExchange();
    }

    function unbindExchangeQueue($exchangeName,$queueName,$routingKey = "",$arguments = null){
        $queue = $this->getQueue($queueName);
        $queue->unbind($exchangeName,$routingKey,$arguments);
    }

    function deleteExchange($exchangeName){
        $exchange = $this->getExchange();
        $exchange->setName($exchangeName);
        $exchange->delete();
    }

    function confirmSelectMode(){
        $this->_confirmSelectMode = 1;
        $this->getChannel()->confirmSelect();
    }
    //开启一个事务
    function txSelect(){
        $this->getChannel()->startTransaction();
    }

    function txCommit(){
        $this->getChannel()->commitTransaction();
    }

    function txRollback(){
        $this->getChannel()->rollbackTransaction();
    }
    //false,$noAck,false,false,
    function basicConsume($queueName,$consumerTag = "" ,$callback){
        $queue = $this->getQueue($queueName);
        $queue->consume($callback,null,$consumerTag);
//        $this->getChannel()->basic_consume($queueName,$consumerTag,false,$noAck,false,false,$callback);
    }

    function basicConsumeNew($queueObj,$consumerTag = "" ,$callback){
        $queueObj->consume($callback,null,$consumerTag);
    }

    function listenerCancel($consumerTag){
       $queue = $this->getQueue();
       $queue->cancel($consumerTag);
    }

    function basicPublish($exchangeName,$routingKey,$msg,$arguments){
//        if($arguments && isset($arguments['application_headers'] ) && $arguments['application_headers'] ){
//            $arguments['application_headers'] = new AMQPTable(arguments['application_headers']);
//        }
//        $AMQPMessage = new AMQPMessage($msg,$arguments);

        if(!isset($arguments['headers']) || !$arguments['headers'] ){
            $this->_deliveryTagSequence++;
        }else{
            if(!isset($arguments['headers']['x-delay']) || !$arguments['headers']['x-delay'] ){
                $this->_deliveryTagSequence++;
            }
        }




//        if(isset($arguments['application_headers'])){
//            $arguments['headers'] = $arguments['application_headers'];
//            unset($arguments['application_headers']);
//        }

        try{
            $this->getExchange($exchangeName)->publish($msg,$routingKey,AMQP_MANDATORY,$arguments);
        }catch (\Exception $e){
            $data = array(
                'code'=>$e->getCode(),
                'content'=>$e->getMessage(),
                'type'=>ErrorRecordModel::TYPE_EXT,
            );
            ErrorRecordModel::create($data);
            throw $e;
        }

        $arguments['body'] = $msg;
        $this->setDeliveryPool($this->_deliveryTagSequence,$arguments);
    }

    function setDeliveryPool($deliveryTag,$msg){
        if($this->_confirmSelectMode){
            $this->_deliveryPool[$deliveryTag] = $msg;
        }
    }

    function destroyDeliveryPool($deliveryTag){
        if($this->_confirmSelectMode){
            unset($this->_deliveryPool[$deliveryTag]);
        }
    }

    function getDeliveryPoolByDeliveryTag($deliveryTag){
        if($this->_confirmSelectMode){
            if(isset($this->_deliveryPool[$deliveryTag])){
                return $this->_deliveryPool[$deliveryTag];
            }
        }

        return false;
    }

    function setRabbitmqAckCallback($ackFunc,$nackFuc){
        $this->getChannel()->setConfirmCallback($ackFunc,$nackFuc);
    }

    function setRabbitmqErrCallback($clientReturnListener){
        $this->getChannel()->setReturnCallback($clientReturnListener);
    }

    //将rabbitmq push的消息属性值，解析成数组，删掉Header
    function getMsgAttr( $deliveryTag){
        $info = $this->getDeliveryPoolByDeliveryTag($deliveryTag);
        if($info){
            return $info['arguments'];
        }
        return false;
    }

    function ack(\AMQPEnvelope $obj,\AMQPQueue $queue = null){
        $tag = $obj->getDeliveryTag();
        $queue->ack($tag);
    }

    function disconnect(){
        $this->_conn->disconnect();
    }

    function nack(\AMQPEnvelope $obj,$queue = null){
        $tag = $obj->getDeliveryTag();
        $queue->nack($tag);
    }

    function reject(\AMQPEnvelope $obj,$queue  = null ,$requeue = false){
        $tag = $obj->getDeliveryTag();
        $queue->reject($tag,$requeue);
    }

    function parseBackDataToUniteArr(\AMQPEnvelope $obj){
        $arr = array(
            'content_type'=>$obj->getContentType(),
            'content_encoding'=>$obj->getContentEncoding(),
            'delivery_mode'=>$obj->getDeliveryMode(),
            'priority'=>$obj->getPriority(),
            'correlation_id'=>$obj->getCorrelationId(),
            'reply_to'=>$obj->getReplyTo(),
            'expiration'=>$obj->getExpiration(),
            'message_id'=>$obj->getMessageId(),
            'timestamp'=>$obj->getTimestamp(),
            'type'=>$obj->getType(),
            'user_id'=>$obj->getUserId(),
            'app_id'=>$obj->getAppId(),
            'cluster_id'=>$obj->getClusterId(),
            //兼容字段
//            'application_headers'=>$obj->getHeaders(),
            'headers'=>$obj->getHeaders(),

            //以上都是父类 方法
            'body'=>$obj->getBody(),

            'routing_key'=>$obj->getRoutingKey(),
            'consumer_tag'=>$obj->getConsumerTag(),
            'delivery_tag'=>$obj->getDeliveryTag(),
            'exchange_name'=>$obj->getExchangeName(),
            'redelivery'=>$obj->isRedelivery(),
        );
        return $arr;
    }

    function parseBackDataAttrToUniteArr(\AMQPBasicProperties $obj){
        $arr = array(
            'content_type'=>$obj->getContentType(),
            'content_encoding'=>$obj->getContentEncoding(),
            'delivery_mode'=>$obj->getDeliveryMode(),
            'priority'=>$obj->getPriority(),
            'correlation_id'=>$obj->getCorrelationId(),
            'reply_to'=>$obj->getReplyTo(),
            'expiration'=>$obj->getExpiration(),
            'message_id'=>$obj->getMessageId(),
            'timestamp'=>$obj->getTimestamp(),
            'type'=>$obj->getType(),
            'user_id'=>$obj->getUserId(),
            'app_id'=>$obj->getAppId(),
            'cluster_id'=>$obj->getClusterId(),
            //兼容字段
//            'application_headers'=>$obj->getHeaders(),
            'headers'=>$obj->getHeaders(),

        );
        return $arr;
    }

    function debugMergeInfo( \AMQPEnvelope $backData , $includeBody = 0){
        $arr = $this->parseBackDataToUniteArr($backData);
        $info = "";
        foreach ($arr as $k=>$v) {
            if($k == 'headers'){
//                if($k == 'application_headers'){
                $list = null;
                foreach ($v as $k2=>$v2) {
                    $list = $k2 . " " . $v2;
                }
//                $info .= " application_headers :" .$list . " ";
                $info .= " headers :" .$list . " ";
            }else{
                if($k == "body"){
                    if(!$includeBody){
                        continue;
                    }
                }
                $info .= $k . ":" .$v . " ";
            }
        }
        return $info;
    }

    //=============================以下3个方法  都是给product=====================================
    function getMsgByDeliveryTag($deliveryTag){
        $info = $this->getDeliveryPoolByDeliveryTag($deliveryTag);
        return $info;
    }
//    function getMsgBody($deliveryTag){
//
//        $attr = $this->getMsgAttr($deliveryTag);
//        $body = $info['msg'];
////    var_dump($body);
//
//        $body = $this->getMsgBodyByHeader($body,$attr);
//        return $body;
//    }
//    //将rabbitmq push的消息体，根据不同头类型，解析成不同类型。
//    function getMsgBodyByHeader($body,$attr){
//
//        //实际大部分是 将序列化的<字符串>转成obj
//        if(isset($attr['content_type']) &&  $attr['content_type']){
////        out("content_type:".$attr['content_type']);
//            switch ($attr['content_type']){
//                case "application/json":
//                    $body = json_decode($body,true);
//                    break;
//                case "application/serialize":
//                    $body = unserialize($body);
//                    break;
//                default:
//                    break;
//            }
//        }
//
//        return $body;
//    }

//    //将rabbitmq push的消息属性值，解析成数组，只取Header
//    function getMsgHeader($deliveryTag){
//        $info = $this->getDeliveryPoolByDeliveryTag($deliveryTag);
//        if($info){
//            return $info['arguments']['application_headers'];
//        }
//        return false;
//    }
    //=============================以上3个方法  都是给product=====================================

}