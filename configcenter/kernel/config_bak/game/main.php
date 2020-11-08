<?php
$main = array(
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
        array("login","getToken",),

        array(   'index','getServer',),
        array(   'index','testProtoBuf',),
        array(   'system','getAppUpgradeInfo',),

    ),


    'getMoneyElement'=>array(
//        1=>1,
        2=>5,
        3=>10,
        4=>20,
        5=>50,
        6=>100,
        7=>0.5,
    ),

    'getMoneyElementOs'=>array(
        1=>10,
        2=>20,
    ),

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

    'money_coupon'=>array(
        1=>array('id'=>1, 'money'=>0.3, 'title'=>'0.3元提现卷', 'valid_time'=>0,'expire'=>30 *86400 ,'equalGoldcoin'=>1500),
        2=>array('id'=>2, 'money'=>0.5, 'title'=>'0.5元提现卷', 'valid_time'=>0,'expire'=>30 *86400 ,'equalGoldcoin'=>2500 ),
        3=>array('id'=>3, 'money'=>1,   'title'=>'1元提现卷', 'valid_time'=>0,'expire'=>30 *86400 ,'equalGoldcoin'=>5000 ),
    ),

    'playGameRewardRule'=>array(
        array('sec_start'=>0,'sec_end'=>120,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>12,'second_period'=>10,),
        array('sec_start'=>121,'sec_end'=>240,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>10,'second_period'=>10,),
        array('sec_start'=>241,'sec_end'=>360,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>8,'second_period'=>10,),
        array('sec_start'=>361,'sec_end'=>480,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>7,'second_period'=>10,),
        array('sec_start'=>481,'sec_end'=>600,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>6,'second_period'=>10,),
        array('sec_start'=>601,'sec_end'=>720,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>4,'second_period'=>10,),
        array('sec_start'=>720,'sec_end'=>-1,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>3,'second_period'=>10,),

//        array('time_level'=>75,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>1,),
//        array('time_level'=>150,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>2,),
//        array('time_level'=>225,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>5,),
//        array('time_level'=>300,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>6,),
//        array('time_level'=>375,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>10,),
//        array('time_level'=>450,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>12,),
//        array('time_level'=>-1,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>2,'second_period'=>12,),


    ),

    'tokenKey'=>'e65178de9e5543a1f3cffd00345da58f',
    'tokenSecret'=>'e65178de9e5543a1f3cffd00345da58f',
);

$GLOBALS['main'] = $main;