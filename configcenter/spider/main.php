<?php
$main = array(
    "common"=>
    array(
        TANG_98=>
            array(
                "name"=>"98堂",
                "provider_class"=>"Tang98Shell",
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
                "name"=>"第一会所",
                "provider_class"=>"Sis001Shell",
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
                        "loop_start"=>1,
                        "loop_end"=>2342,
                        "db_model_class"=>"Sis001OumeiModel"
                    ),
                    JAPAN_ORI_UNCODE=>array(
                        "name"=>"亚洲无码原创",
                        "link_id"=> 143,
                        "loop_start"=>1,
                        "loop_end"=>2696,
                        "db_model_class"=>"Sis001JapanModel"
                    ),
                )
            ),
        TAOHUA=>
            array(
                "name"=>"桃花",
                "provider_class"=>"TaohuaShell",
                "host"=>array(
                    "taohuazu4.com",
                ),
                "forum"=>array(
                    OUMEI=>array(
                        "name"=>"欧美无码原创",
                        "link_id"=> 182,
                        "loop_start"=>132,
                        "loop_end"=>263,
                        "db_model_class"=>"TaohuaOumeiModel"
                    ),
                    JAPAN_ORI_UNCODE=>array(
                        "name"=>"亚洲无码原创",
                        "link_id"=> 181,
                        "loop_start"=>1,
                        "loop_end"=>490,
                        "db_model_class"=>"TaohuaJapanModel"
                    ),
                )
            ),
    ),
    'mysql'=>array(
        "create database spider charset=utf8mb4;",
        "
CREATE TABLE `sis001_oumei` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '分类',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '标题',
  `author` varchar(100) DEFAULT NULL COMMENT '作者',
  `comment` varchar(20) DEFAULT NULL COMMENT '评论数',
  `view` varchar(20) DEFAULT NULL COMMENT '阅读数',
  `a_time` int DEFAULT NULL COMMENT '记录添加时间 ',
  `ori_time` varchar(20) DEFAULT NULL COMMENT '网站原添加时间',
  `video_size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '视频大小',
  `video_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '视频类型',
  `up` varchar(20) DEFAULT NULL COMMENT '点赞数',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT '跳转连接',
  `vedio_ duration` varchar(50) DEFAULT NULL COMMENT '视频时长',
  `actor` varchar(255) DEFAULT NULL COMMENT '演员',
  `img` mediumtext COMMENT '图片地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
"
    ),

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