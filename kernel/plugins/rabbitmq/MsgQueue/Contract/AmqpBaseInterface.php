<?php
namespace php_base\MsgQueue\Contract;

interface AmqpBaseInterface{
    function testENV();//检查当前环境
    function setExtType(int $type);//PHP以哪种方式运行，1扩展2composerLib
    function setTopicName(string $topName);//设置分类名
    function throwException(int $code,array $replace = []);//公共异常抛出
    function setDebug(int $flag);//调试模式
    function regSignals();//信号处理

    //product 相关
    function setMessageMaxLength(int $num);//发送一条消息，大小限制
    function publish($msgBody ,$exchangeName,$routingKey = '',$header = null,$arguments = null);//发送一条消息
    function confirmSelectMode();//切换确认模式
    function txSelect();//切换事务模式
    function txCommit();//事务提交
    function txRollback();//事务回滚

    //consumer相关
    function setReceivedServerMsgMaxNumByOneTime(int $num);//同一时间接收server返回消息数
    function setUserConsumerCallbackExecTimeout(int $second);//用户consumer 回调函数超时时间
    function quitConsumerDemon();//
    function baseSubscribe($exchangeName = "",$queueName,$consumerTag = "" ,$noAck = false);//开启一个consumer 订单一个队列
    function startListenerWait($queueName,$consumerTag,$baseCallback,$noAck);//consumer 进程循环 守护进程
    function setRetryTime(array $time);//重试机制

    //队列 路由器 基操作
    function setQueue($queueName, $arguments = null,$durable = true,$autoDelete = false);//创建队列/设置队列
    function deleteQueue($queueName);//删除队列
    function bindQueue($queueName, $exchangeName = null,$routingKey = '',$header = null);//交换器绑定队列
    function queueExist($queueName);//判断队列是否存在

    function setExchange($exchangeName,$type,$arguments = null);//创建交换器/设置交换器
    function deleteExchange($exchangeName);//删除交换器
    function unbindExchangeQueue($exchangeName,$queueName,$routingKey = "",$arguments = null);//队列解绑交换器






}
