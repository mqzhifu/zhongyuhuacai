<?php
namespace Jy\Common\MsgQueue\Test;
//消息一致性相关
class RedisMsgStatusAck{
    static $_inc = null;
    static $_statusProductWait = 1;
    static $_statusProductOk = 2;
    static $_statusConsumerProcessing = 3;
    static $_statusConsumerFinish = 4;
    static $_statusConsumerException = 5;

    static function getRedis(){
        $redis = new \Redis();
        $redis->connect("127.0.0.1");
        return $redis;
    }

    static function getStatusDesc(){
        $_statusDesc = array(
            self::$_statusProductWait=>'生产者-发送等待',
            self::$_statusProductOk=>'生产者-发送已确认',
            self::$_statusConsumerProcessing=>'消费者-处理中',
            self::$_statusConsumerFinish=>'消费者处理完成',
            self::$_statusConsumerException=>'消费者-处理异常');

        return $_statusDesc;
    }

    static function getInstant(){
        if(self::$_inc){
            return self::$_inc;
        }

        self::$_inc = new self();
        return self::$_inc;
    }

    static function getUniqueMsgId(){
        return uniqid(time());
    }

    static function redisSetMsgStatusAck($msgId,$status,$info = ""){
        $msg = "$status|".time();
        if($info){
            $msg .= "|".$info;
        }
        return self::getRedis()->set($msgId,$msg);
    }

    static function redisGetMsgStatusAck($msgId){
        $redisMsg = self::getRedis()->get($msgId);
        $msg = null;
        if($redisMsg){
            $redisMsg = explode("|",$redisMsg);
            $msg['status'] = $redisMsg[0];
            $msg['time'] = $redisMsg[1];
            if(isset($redisMsg[2]) && $redisMsg[2]){
                $msg['info'] = $redisMsg[2];
            }
        }
        return $msg;
    }
    //
    static function setMsgRetryIncr($msgId){
        return self::getRedis()->incr($msgId);
    }

    static function getMsgRetryIncr($msgId){
        return self::getRedis()->get($msgId);
    }
}