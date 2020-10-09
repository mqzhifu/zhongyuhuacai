<?php
$main = array(
    'tokenKey'=>'e65178de9e5543a1f3cffd00345da58f',
    'tokenSecret'=>'e65178de9e5543a1f3cffd00345da58f',


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

    'loginAPIExcept'=> array(
//        array("login","webSocketLogin",),

        array("login",'index'),
        array("login",'ps'),
        array("login",'sms'),
        array("login",'psSms'),
        array("login",'findPsBySms'),
        array("system",'sendLoginSms'),


//        array(   'index','index',),

//        array(   'index','testProtoBuf',),
//        array(   'index','getUiShowConfig',),
//        array(   'system','getAppUpgradeInfo',),

//        array("system","sendSMS",),

//        array("wxopen","push",),
//        array("qqLogin","login"),
//        array("qqLogin","getUserinfo"),
    ),


    'dayGoldUserRankNum'=>100,//日金币总排行榜，取前X人

    'dayGoldUserRanRewardFirstGold'=>100000,//日金币总排行榜，冠军奖励金币数

    'playGameRewardGoldcoinMaxLimit'=>9900,
    'todayTotalGoldcoinMaxLimit'=>21100,//每日用户总金额的收益上限
    'friendIncomeMaxLimit'=>30,
    'goldcoinExchangeRMB'=>0.0001,

    // 海外版本APP常量配置 add by XiaHB;
    'playGameRewardGoldcoinMaxLimitOs'=>12000,
    'friendIncomeMaxLimitOs'=>5,// 美元;
    'goldcoinExchangeUSD'=>0.00001,// 1比10w;

    'signReward'=>array(
        array("rewardGold"=>106,'addition'=>0),
        array("rewardGold"=>128,'addition'=>0),
        array("rewardGold"=>186,'addition'=>300),
        array("rewardGold"=>278,'addition'=>0),
        array("rewardGold"=>310,'addition'=>500),
        array("rewardGold"=>356,'addition'=>0),
        array("rewardGold"=>388,'addition'=>1000),
    ),

);

//$GLOBALS[APP_NAME]['main'] = $main;
return $main;