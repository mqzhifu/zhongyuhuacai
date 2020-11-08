<?php
$main = array(
    //5分钟内，请求多少次。0为关闭
    'ipCntLimit'=>100,

    'sms'=>array(
        'url' => 'http://task.egret.com/task/push',
        'appid' => 'fdf18ca',
        'sign' => '5f33cfdf18caf644dff6d7c75a14d07a'
    ),
    'email'=>array(
//        'smtpHost' => "smtp.qq.com",
        'smtpHost' => 'smtp.exmail.qq.com',
        'name' => '开心网',
        'username' => "wangdongyan@kaixin-inc.com",
        "password" => "Kaixin@001",
        'fromEmail'=>'wangdongyan@kaixin-inc.com',
    ),

    'loginAPIExcept'=>        $arr = array(
        array("login","webSocketLogin",),
//        array("login","cellphoneSMS",),
        array("login","guest",),

        array("system","sendSMS",),


        array(   'index','getServer',),
        array(   'index','testProtoBuf',),
        array(   'index','getUiShowConfig',),
        array(   'system','getAppUpgradeInfo',),


        array("game","recommendIndex",),
        array("game","getRecommends",),
        array("game","topOnline",),
        array("game","exceptRecommendIndex",),
        array("game","getList",),
        array("bank","WXPayCallback",),
        array("bank","aliPayCallback",),
        array("wxopen","push",),
        array("qqLogin","login"),
        array("qqLogin","getUserinfo"),

        array("system","banner",),
//        array("bank","getGoodsList",),

    ),

    'tokenKey'=>'e65178de9e5543a1f3cffd00345da58f',
    'tokenSecret'=>'e65178de9e5543a1f3cffd00345da58f',
);

$GLOBALS[KERNEL_NAME]['main'] = $main;