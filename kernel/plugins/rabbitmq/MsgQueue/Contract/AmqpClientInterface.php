<?php
namespace php_base\MsgQueue\Contract;

interface AmqpClientInterface{
    function publish($msgBody ,$exchangeName,$routingKey = '',$header = null,$arguments = null);//发送一条消息给路由器
    function waitAck();//确认模式下，等待SERVER返回ACK确认
    function waitReturnListener();
    function wait();
    function regDefaultAllCallback();
    function regReturnListenerCallback($clientReturnListener);
    function regAckCallback($clientAck);
    function regNAckCallback($clientNAck);
}