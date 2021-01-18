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

    "common"=>
    array(
        TANG_98=>
            array(
                "host"=>array(
                    "98skdjseq2wshop.xyz",
                    "98wsaweassd.xyz",
                    "98asedwwq.xyz"
                ),
                "forum"=>array(
                    OUMEI=>"forum-229-3.html",
                    JAPAN_ORI_UNCODE=>"forum-36-1.html",
                )
            ),
        SIS001=>
            array(
                "host"=>array(
                    "38.103.161.16/bbs",
                    "666.diyihuisuo.com/bbs",
                    "cn.1huisuo.com/forum",
                    "666.1huisuo.net/bbs"
                ),
                "forum"=>array(
                    OUMEI=>array(
                        "name"=>"欧美无码原创",
                        "link_id"=> 229,
                        "loop_start"=>2147,
                        "loop_end"=>2342,
                        "db_model_class"=>"Sis001OumeiModel"
                    ),
                    JAPAN_ORI_UNCODE=>array(
                        "name"=>"亚洲无码原创",
                        "link_id"=> 143,
                        "loop_start"=>2117,
                        "loop_end"=>2696,
                        "db_model_class"=>"Sis001JapanModel"
                    ),
                )
            )

    ),
);

//$GLOBALS[APP_NAME]['main'] = $main;
return $main;