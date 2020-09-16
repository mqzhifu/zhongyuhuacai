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


    'getMoneyElement'=>array(
//        1=>1,
        2=>5,
        3=>10,
        4=>20,
        5=>50,
        6=>100,
        7=>0.5,
    ),


    //status:0置灰，1未打开，2已打开
    'signLotteryBox' => array(
        1=>array('cnt'=>2,'rewardGold'=>100,'status'=>0,'id'=>1),
        2=>array('cnt'=>4,'rewardGold'=>200,'status'=>0,'id'=>2),
        3=>array('cnt'=>6,'rewardGold'=>400,'status'=>0,'id'=>3),
        4=>array('cnt'=>8,'rewardGold'=>600,'status'=>0,'id'=>4),
    ),


    'getMoneyElementOs'=>array(
        1=>10,
        2=>20,
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

    // 海外版游戏结束金币奖励;
    'playGameRewardRuleEn'=>array(
        array('sec_start'=>0,'sec_end'=>120,'reward_goldcoin_rand_start'=>1,'reward_goldcoin_rand_end'=>21,'second_period'=>10,),
        array('sec_start'=>121,'sec_end'=>240,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>15,'second_period'=>10,),
        array('sec_start'=>241,'sec_end'=>360,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>13,'second_period'=>10,),
        array('sec_start'=>361,'sec_end'=>480,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>11,'second_period'=>10,),
        array('sec_start'=>481,'sec_end'=>600,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>9,'second_period'=>10,),
        array('sec_start'=>601,'sec_end'=>720,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>7,'second_period'=>10,),
        array('sec_start'=>720,'sec_end'=>-1,'reward_goldcoin_rand_start'=>0,'reward_goldcoin_rand_end'=>5,'second_period'=>10,),
    ),

    // 开心大轮盘 add by XiaHB time:2019/07/04;
    'happyBigWheelBox' => array(
        array('cnt'=>5, 'goldCoins'=>80, 'status'=>0, 'id'=>1),
        array('cnt'=>30, 'goldCoins'=>300, 'status'=>0, 'id'=>2),
        array('cnt'=>60, 'goldCoins'=>500, 'status'=>0, 'id'=>3),
        array('cnt'=>100, 'goldCoins'=>800, 'status'=>0, 'id'=>4),
    ),

);

$GLOBALS[APP_NAME]['main'] = $main;