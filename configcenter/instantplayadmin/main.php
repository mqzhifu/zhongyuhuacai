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
    'ali_sms'=>array(
        'domain'=>"dysmsapi.aliyuncs.com",
        'AccessKeyID'=>'LTAI4GEaDscAQUgNhjq6JE55',//https://ak-console.aliyun.com/
        'AccessKeySecret'=>"MkOX5wrhSKIG4noOPBnPkIWzys7vPx",
        "SignName"=>"新零售云分享",//https://dysms..consolealiyun.com/dysms.htm#/develop/sign
        'RegionId'=>'cn-hangzhou',
    ),
);

//$GLOBALS[APP_NAME]['main'] = $main;
return $main;