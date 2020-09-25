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

    'website_title'=>"新零售 - 云分享",
);

//$GLOBALS[APP_NAME]['main'] = $main;
return $main;