<?php
namespace php_base\MsgQueue\Contract;

interface AmqpConsumerInterface{
    function setBasicQos(int $num);//单consumer同一时间内，最多可处理消息总数
    function ack($msg);//返回server确认
    function nack($msg,$requeue = false);//返回server确认，但出错
    function reject($msg,$requeue = false);//返回server确认，但出错
    function setStopListenerWait($flag);//设置取消死循环标识
    function listenerCancel($consumerTag);//取消死循环
    function startListenerWait();////进入死循环，等待server push 消息
}