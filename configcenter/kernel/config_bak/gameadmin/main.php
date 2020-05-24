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
        'name' => '白鹭游戏中心',
        'username' => "open@egret.com",
        "password" => "1@system",
        'fromEmail'=>'open@egret.com',
    ),

    'loginAPIExcept'=>        $arr = array(
        array("login","login",),
        array(   'system','sendSMS',),
        array(   'system','sendEmail',),
        array(   'system','getVerifierImg',),
        array(  'system','authVerifierImgCode',),
        array(   'user','register',),
        array(   'usersafe','findPS',),
        array(   'usersafe','resetPS',),
        array(   'user','isCellphoneUnique',),
        array(   'index','apilist',),
        array(   'index','apitest',),
        array(   'index','getCodeDesc',),



    ),



    'tokenKey'=>'e65178de9e5543a1f3cffd00345da58f',
    'tokenSecret'=>'e65178de9e5543a1f3cffd00345da58f',
);

$GLOBALS['main'] = $main;