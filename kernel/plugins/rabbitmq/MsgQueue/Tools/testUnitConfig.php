<?php
//单元测试 配置文件
$GLOBALS['mqConfig'] = array(
    1=>array("appId"=>1,'name'=>'测试<direct>类型exchange',
        "exchange"=>array(
            //测试<direct>类型exchange
            array("name"=>'test.direct','alternate-exchange'=>'','type'=>'direct','alternate-exchange'=>'test.direct.alternate_ex',
                'queue'=>array(
                    array(
                        'name'=>"test.direct.apple",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"apple",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-dead-letter-exchange'=>'test.direct.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.direct.banana",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"banana",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>'test.direct.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.direct.blank",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>'test.direct.dead_ex'
                        ),
                    ),
                ),
            ),

            array("name"=>'test.direct.dead_ex','type'=>'fanout',
                'queue'=>array(
                    array(
                        'name'=>"test.direct.dead_ex.queue",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),

            //alternate-exchange
            array("name"=>'test.direct.alternate_ex','type'=>'fanout',
                'queue'=>array(
                    array(
                        'name'=>"test.direct.alternate_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),
        ),
    ),
    2=>array("appId"=>2,'name'=>'测试<headers>类型exchange',
        "exchange"=>array(
            array("name"=>'test.header','type'=>'headers','alternate-exchange'=>'test.header.alternate_ex',
                'queue'=>array(
                    array(
                        'name'=>"test.header.sms",
                        'consumerType'=>'sms',
                        "bind_header_map"=>array("Jy\Common\MsgQueue\Test\Product\ProductSms"=>"Jy\Common\MsgQueue\Test\Product\ProductSms",'x-match'=>'any'),
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>'test.header.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.header.order",
                        'consumerType'=>'email',
                        "bind_header_map"=>array("type"=>"email",'x-match'=>'all'),
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>'test.header.dead_ex'
                        ),
                    ),
//                    array(
//                        'name'=>"test.header.blank",
//                        'consumerType'=>'email',
//                        "bind_header_map"=>null,
//                        'bind_routing_key'=>"",
//                        'arguments'=>array(
//                            'x-expires'=>0,//整个队列失效时间
//                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
//                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'test.header.dead_ex'
//                        ),
//                    ),
                ),
            ),

            array("name"=>'test.header.dead_ex','type'=>'fanout',
                'queue'=>array(
                    array(
                        'name'=>"test.header.dead_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),

            //alternate-exchange
            array("name"=>'test.header.alternate_ex','type'=>'direct',
                'queue'=>array(
                    array(
                        'name'=>"test.header.alternate_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),
        ),
    ),
    3=>array("appId"=>3,'name'=>'测试<延迟><headers>类型exchange',
        "exchange"=>array(
            array("name"=>'test.header.delay','type'=>'x-delayed-message', 'x-delayed-type'=>'headers',
                'queue'=>array(
                    array(
                        'name'=>"test.header.delay.sms",
//                        'consumerType'=>'sms',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\SmsBean"=>"php_base\MsgQueue\Test\Product\SmsBean",
                            "php_base\MsgQueue\Test\Product\UserBean"=>"php_base\MsgQueue\Test\Product\UserBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),
                    array(
                        'name'=>"test.header.delay.order",
//                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\OrderBean"=>"php_base\MsgQueue\Test\Product\OrderBean",
                            "php_base\MsgQueue\Test\Product\UserBean"=>"php_base\MsgQueue\Test\Product\UserBean",
                            'x-match'=>'any'),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),
                    array(
                        'name'=>"test.header.delay.user",
//                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\UserBean"=>"php_base\MsgQueue\Test\Product\UserBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),
                    array(
                        'name'=>"test.header.delay.payment",
//                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\PaymentBean"=>"php_base\MsgQueue\Test\Product\PaymentBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),

                    array(
                        'name'=>"test.header.delay.queueMinLengthSms",
//                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\SmsBean"=>"php_base\MsgQueue\Test\Product\SmsBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>1,
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),

                    array(
                        'name'=>"test.header.delay.queueMinLength",
//                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "php_base\MsgQueue\Test\Product\QueueMinLengthBean"=>"php_base\MsgQueue\Test\Product\QueueMinLengthBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>1,
                            'x-overflow'=>'reject-publish',
//                            'x-dead-letter-exchange'=>'test.header.delay.dead_ex',
                        ),
                    ),
                ),
            ),

//            array("name"=>'test.header.delay.dead_ex','type'=>'fanout',
//                'queue'=>array(
//                    array(
//                        'name'=>"test.header.delay.dead_ex.queue",
//                        'consumerType'=>'',
//                        "bind_header_map"=>null,
//                        'arguments'=>array(
//                            'x-expires'=>0,//整个队列失效时间
//                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
//                            'x-max-length'=>100000,
//                        ),
//                    ),
//                ),
//            ),
        ),
    ),
    4=>array("appId"=>4,'name'=>'测试<topic>类型exchange',
        "exchange"=>array(
            array("name"=>'test.topic','type'=>'topic',
                'queue'=>array(
                    array(
                        'name'=>"test.topic.alert",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"topic.*.alert",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                    array(
                        'name'=>"test.topic.error",
                        'consumerType'=>'email',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"#.topic.error",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                    array(
                        'name'=>"test.exception.alert",
                        'consumerType'=>'email',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"#.topic.error",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                ),
            ),


        ),
),
    5=>array("appId"=>5,'name'=>'测试扇形exchange',
        "exchange"=>array(
            array("name"=>'test.fanout','type'=>'fanout','alternate-exchange'=>'test.fanout.alternate_ex',
                'queue'=>array(
                    array(
                        'name'=>"test.fanout.red",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"fanout.red",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                    array(
                        'name'=>"test.fanout.yellow",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                    array(
                        'name'=>"test.fanout.blue",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                    array(
                        'name'=>"test.fanout.green",
                        'consumerType'=>'sms',
                        "bind_header_map"=>0,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                            'x-dead-letter-exchange'=>0,
                        ),
                    ),
                ),
            ),
            //
            array("name"=>'test.fanout.dead_ex','type'=>'topic',
                'queue'=>array(
                    array(
                        'name'=>"test.fanout.dead_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),
            //alternate-exchange
            array("name"=>'test.fanout.alternate_ex','type'=>'topic',
                'queue'=>array(
                    array(
                        'name'=>"test.fanout.alternate_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),
            //测试<fanout>类型exchange
        ),
    ),
    6=>array("appId"=>6,'name'=>'杂七杂八的消息参数测试',
        "exchange"=>array(
            array("name"=>'test.other','type'=>'direct',
                'queue'=>array(
                    array(
                        'name'=>"test.other.priority",
                        'bind_routing_key'=>"priority",
                        'arguments'=>array(
                            'x-dead-letter-exchange'=>'test.other.dead_ex',
                            'x-max-priority'=>10,
                        ),
                    ),
                    array(
                        'name'=>"test.other.max_length",
                        'bind_routing_key'=>"max_length",
                        'arguments'=>array(
                            'x-max-length'=>1,
                            'x-dead-letter-exchange'=>'test.other.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.other.expires",
                        'bind_routing_key'=>"expires",
                        'arguments'=>array(
                            'x-expires'=>100000,//整个队列失效时间,毫秒
                            'x-dead-letter-exchange'=>'test.other.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.other.message_tll",
                        'bind_routing_key'=>"message_tll",
                        'arguments'=>array(
                            'x-message-ttl'=>10000,//所有进入该队列消息的，TTL时效
                            'x-dead-letter-exchange'=>'test.other.dead_ex'
                        ),
                    ),
                ),
            ),

            array("name"=>'test.other.dead_ex','type'=>'fanout',
                'queue'=>array(
                    array(
                        'name'=>"test.other.dead_ex.queue",
                    ),
                ),
            ),
        ),
    ),

    7=>array("appId"=>7,'name'=>'测试<headers>多对多',
        "exchange"=>array(
            array("name"=>'test.header-many','type'=>'x-delayed-message', 'x-delayed-type'=>'headers',
//                'alternate-exchange'=>'test.header-many.alternate_ex',
                'queue'=>array(
                    array(
                        'name'=>"test.header-many.sms",
                        "bind_header_map"=>array("sms"=>"sms",'x-match'=>'any'),
                        'arguments'=>array(
                            'x-dead-letter-exchange'=>'test.header-many.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.header-many.email",
                        "bind_header_map"=>array("email"=>"email",'x-match'=>'any'),
                        'arguments'=>array(
                            'x-dead-letter-exchange'=>'test.header-many.dead_ex'
                        ),
                    ),
                    array(
                        'name'=>"test.header-many.order",
                        "bind_header_map"=>array("order"=>"order","sms"=>'sms','x-match'=>'any',),
                        'arguments'=>array(
                            'x-dead-letter-exchange'=>'test.header-many.dead_ex'
                        ),
                    ),
//                    array(
//                        'name'=>"test.header-many.blank",
//                        "bind_header_map"=>null,
//                        'arguments'=>array(
//                            'x-dead-letter-exchange'=>'test.header-many.dead_ex'
//                        ),
//                    ),
                ),
            ),

            array("name"=>'test.header-many.dead_ex','type'=>'headers',
                'queue'=>array(
                    array(
                        'name'=>"test.header-many.dead_ex.queue.sms",
                        "bind_header_map"=>array("sms"=>"sms",'x-match'=>'any'),
                    ),
                    array(
                        'name'=>"test.header-many.dead_ex.queue.email",
                        "bind_header_map"=>array("email"=>"email",'x-match'=>'any'),
                    ),
                ),
            ),

            //alternate-exchange
//            array("name"=>'test.header-many.alternate_ex','type'=>'direct',
//                'queue'=>array(
//                    array(
//                        'name'=>"test.header-many.alternate_ex.queue",
//                        'consumerType'=>'',
//                        "bind_header_map"=>null,
//                        'bind_routing_key'=>"",
//                        'arguments'=>array(
//                            'x-expires'=>0,//整个队列失效时间
//                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
//                            'x-max-length'=>100000,
//                        ),
//                    ),
//                ),
//            ),
        ),
    ),



    8=>array("appId"=>3,'name'=>'测试<延迟><headers>类型exchange_2',
        "exchange"=>array(
            array("name"=>'many.header.delay','type'=>'x-delayed-message', 'x-delayed-type'=>'headers',
                'queue'=>array(
                    array(
                        'name'=>"many.header.delay.sms",
                        "bind_header_map"=>array(
                            "Rouchi\Product\SmsBean"=>"Rouchi\Product\SmsBean",
                            "Rouchi\Product\UserBean"=>"Rouchi\Product\UserBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'many.header.delay.dead_ex',
                        ),
                    ),
                    array(
                        'name'=>"many.header.delay.order",
                        'consumerType'=>'email',
                        "bind_header_map"=>array(
                            "Rouchi\Product\OrderBean"=>"Rouchi\Product\OrderBean",
                            "Rouchi\Product\UserBean"=>"Rouchi\Product\UserBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'many.header.delay.dead_ex',
                        ),
                    ),
                    array(
                        'name'=>"many.header.delay.user",
                        "bind_header_map"=>array(
                            "Rouchi\Product\UserBean"=>"Rouchi\Product\UserBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'many.header.delay.dead_ex',
                        ),
                    ),

                    array(
                        'name'=>"many.header.delay.payment",
                        "bind_header_map"=>array(
                            "Rouchi\Product\PaymentBean"=>"Rouchi\Product\PaymentBean",
                            'x-match'=>'any'
                        ),
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
//                            'x-dead-letter-exchange'=>'many.header.delay.dead_ex',
                        ),
                    ),
                ),
            ),

            array("name"=>'many.header.delay.dead_ex','type'=>'fanout',
                'queue'=>array(
                    array(
                        'name'=>"many.header.delay.dead_ex.queue",
                        'consumerType'=>'',
                        "bind_header_map"=>null,
                        'bind_routing_key'=>"",
                        'arguments'=>array(
                            'x-expires'=>0,//整个队列失效时间
                            'x-message-ttl'=>0,//所有进入该队列消息的，TTL时效
                            'x-max-length'=>100000,
                        ),
                    ),
                ),
            ),
        ),
    ),
);
//'consumer'=>array(
//    array("name"=>'sms','type'=>'sms','exec_file'=>"sms.php"),
//    array("name"=>'email','type'=>'email','exec_file'=>"email.php"),
//),