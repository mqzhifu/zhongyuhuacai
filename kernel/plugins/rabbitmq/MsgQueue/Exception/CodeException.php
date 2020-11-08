<?php
namespace php_base\MsgQueue\Exception;
class CodeException {
    static $_code = array(
        400=>'code is null',
        401=>'code not is key',
        500=>"msgBody is null",
        501=>"msgBody is bool",
        502=>"<message_id> key value: must null",
        503=>"<type> key value: must null",

        504=>" set mode value : is error.",
        505=>"confirm mode or  tx mode just have use one,is mutex -1",
        506=>"N-Ack {0}",
        507=>"send msg is not route Queue, server_err_return_listener {0}",
        508=>"consumerTag is null",
        509=>"conn failed",
        510=>"queue name is null",
        511=>"exchange name is null",
        512=>"<timestamp> key value: must null",
        513=>"user diy bean not match rabbitmq server back header.",
        514=>"beanName is not object",
        515=>"config get :rabbitmq key  is null",
        516=>"config key err.",
        517=>"delayTime must int.",
        518=>"delayTime must > 1000",
        519=>"delayTime must <= 7 days.",
        520=>"rabbitmq return ack not include header",
        521=>'set bean must obj',
        522=>'please setSubscribeBean',
        523=>'setSubscribeBean: please set {0} method',
        524=>"retry time <= 1 days.",
        525=>"retry time must is int.",
        526=>"timeout is null.",
        527=>"timeout min 10",
        528=>"timeout max 600",
        529=>"exec user program timeout ",
        530=>"message length > {0}",
        531=>"userBeanClassCollection is null",
        532=>"qos <= 0",
        533=>'no reg serverBackConsumerRetry callback',
        534=>'_extType value is err',
        535=>"consumerSubscribeType value is err",
        536=>"class {0} not exist",
        537=>"customBindBean is exist,dont't repeat define",
        538=>'func is not callable',
        539=>"debug value is err.",
        540=>"exchange no exist.",
        541=>"basic.publish err: {0}",
        //类库直接抛出异常，程序停止
        600=>"NOT_FOUND - no exchange",
        601=>"PRECONDITION_FAILED - cannot switch from confirm to tx mode",
        602=>"AMQP-rabbit doesn't define data of type []",
        604=>"PRECONDITION_FAILED - inequivalent arg 'x-dead-letter-exchange' for queue",

        603=>"NOT_FOUND - no queue",
        //运行时异常
        312=>"NO_ROUTE - exchange test.header.delay , routingKey",


        800=>'user callback function runtime exception ,alarm!',

    );

    static function getCode(){
        return self::$_code;
    }
}