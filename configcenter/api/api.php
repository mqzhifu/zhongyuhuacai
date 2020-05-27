<?php
$arr = array(
    'index'=>array(
        'title'=>'默认/首页',

        'index'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'测试',
            'request'=>array(),
            'return'=>array(

            ),
        ),

        'getBannerList'=>array(
            'title'=>'轮播图',
            'ws'=>array('request_code'=>5013,'response_code'=>5014),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>1,'list'=>array(
                    'banner_location' => array('type'=>'int','title'=>'Banner位置（1、游戏页面顶部轮播；2、任务页面顶部轮播；3、任务页面中部滑动；4、我的页面中部轮播）','must'=>1),
                    'relative_path' => array('type'=>'int','title'=>'跳转类型（1、站内游戏；2、邀请好友；3、任务页面；4、金币欢乐送；5、幸运大抽奖；6、开心刮一刮；7、好友加成；8、签到有奖；9、奖金排行榜；10、开心抽一抽；11、铲铲队；12、福利红包；13：开心大轮盘；14：开心翻翻卡）','must'=>1),
                    'game_id'=>  array('type'=>'int','title'=>'游戏ID（非游戏类别时，展示0）','must'=>1),
                    'img'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                    'img_link'=>  array('type'=>'string','title'=>'图片链接','must'=>1),
                    'is_relative'=>  array('type'=>'int','title'=>'链接地址1站内2非站内（目前只有站内,默认返回1）','must'=>1),
                    //'relative_path'=>  array('type'=>'string','title'=>'APP内跳转地址（1：游戏，2：邀请，3：任务，4：金币欢乐送）','must'=>1),
//                    'weight'=>  array('type'=>'int','title'=>'排序（数字越小，展示位置越靠前）','must'=>1),
                    'banner_name'=>  array('type'=>'string','title'=>'跳转类型中文名称','must'=>1),
                )
                ),
            ),
        ),

    ),

    'product'=>array(
        'title'=>'产品',

        'getRecommendList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'后台推荐的产品列表',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'getListByCategory'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'获取一个分类下的所有产品',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'getOneDetail'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'产品详情页',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'getCommentList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'获取评论列表',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'up'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'点赞',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'collect'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'收藏',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'comment'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'评论',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'search'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'关键词搜索，目前仅支持:UID',
            'request'=>array(
                'keyword'=>array('type'=>'string','must'=>1,'default'=>100001,'title'=>'关键词'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'isFollow'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否已关注你，1是2否'),
                    'nickname'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),),
            ),
        ),
    ),

    'pay'=>array(
        'title'=>'支付',

        'index'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'test',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'recommendList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'推荐列表',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'getOne'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'商品详情页',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'search'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'关键词搜索，目前仅支持:UID',
            'request'=>array(
                'keyword'=>array('type'=>'string','must'=>1,'default'=>100001,'title'=>'关键词'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'isFollow'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否已关注你，1是2否'),
                    'nickname'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),),
            ),
        ),
    ),

    'order'=>array(
        'title'=>'订单',

        'getListByUser'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'用户订单列表',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'getOneDetail'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'订单详情',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'refund'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'申请退款',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),



        'doing'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'下单',
            'request'=>array(
                'gid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'商品ID'),
                'nyum'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'购买数量'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'isFollow'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否已关注你，1是2否'),
                    'nickname'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),),
            ),
        ),
    ),

    'login'=>array(
        'title'=>'登陆',


        'logout'=>array(
            'ws'=>array('request_code'=>2007,'response_code'=>2008),
            'title'=>'登出',
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'third'=>array(
            'ws'=>array('request_code'=>2007,'response_code'=>2008),
            'title'=>'3方平台-登陆/3方平台注册',
            'request'=>array(
                'type'=>    array('type'=>'int','title'=>'类型，类型，4:微信.6:facebook.9:qq','must'=>1,'default'=>6),
                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
                'nickname'=>    array('type'=>'string','title'=>'昵称','default'=>"imZ",'must'=>1),
                'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1,'default'=>"https://b-ssl.duitang.com/uploads/people/201805/21/20180521200051_imuch.thumb.36_36_c.jpeg"),
                'sex'=>    array('type'=>'int','title'=>'性别，1男2女','must'=>0,'default'=>2),
                'unionId'=>    array('type'=>'string','title'=>'3方联合ID，跨应用的','must'=>0,'default'=>2),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
                ))
            ),
        ),
//


        'wxLittleLoginByCode'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'小程序登陆并注册',
            'request'=>array(
                'code'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'微信给的CODE'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
                ))
            ),
        ),


        'cellphoneSMS'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'手机-验证码-登陆',
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
                ))
            ),
        ),

        'cellphonePS'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'手机-密码-登陆',
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'ps'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'密码'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                ))
            ),
        ),

        'index'=>array(
            'ws'=>array('request_code'=>2009,'response_code'=>2010),
            'title'=>'用户名密码登陆',
            'request'=>array(
                'username'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
                'ps'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为分享，加倍奖励~'),
            ),
            'return'=>array(
                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
            ),
        ),

    ),

    'user'=>array(
        'title'=>'用户',

        'getOneDetail'=>array(
            'title'=>'获取/查看其它，用户基础信息',
            'ws'=>array('request_code'=>4001,'response_code'=>4002),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>0,'default'=>100001,'title'=>'要查看的用户UID,为空:代表是查看自己'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(

                    'uid'=>          array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'name'=>        array('type'=>'string','must'=>1,'default'=>1,'title'=>'用户名'),
                    'nickname'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>         array('type'=>'int','must'=>1,'default'=>1,'title'=>'1男2女'),
                    'a_time'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'注册时间'),
                    'push_status'=> array('type'=>'int','must'=>1,'default'=>1,'title'=>'关闭PUSH,1是2否'),
                    'robot'=>       array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否机器人，1是2否'),
                    'hidden_gps'=>  array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否隐藏GPD，1是2否'),
                    'sign'=>        array('type'=>'string','must'=>1,'default'=>1,'title'=>'个性签名'),
                    'summary'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'简介'),
                    'im_tencent_sign'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'IM用户签名验证'),
                    'invite_code'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'邀请码'),
                    'qq_uid'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'绑定QQ的ID'),
                    'wechat_uid'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'绑定微信的ID'),

                    'cellphone'=>   array('type'=>'string','must'=>0,'default'=>1,'title'=>'手机号,如果是查看别人的信息，此值为没有'),
                    'type'=>        array('type'=>'int','must'=>0,'default'=>1,'title'=>'类型,,如果是查看别人的信息，此值为没有'),
                    'isFollow'=>    array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否已关注,1是2否,查看别人信息才有此字段'),
                    'isBlack'=>     array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否被对方拉黑,1是2否,查看别人信息才有此字段'),
                    'selfBlack'=>     array('type'=>'int','must'=>0,'default'=>1,'title'=>'我把对方拉黑,1是2否,查看别人信息才有此字段'),
                    'isBother'=>    array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否被对方免打扰,1是2否,查看别人信息才有此字段'),

                    'invite_uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'邀请人的UID'),

                    'developer'=>array('type'=>'int','must'=>1,'default'=>2,'title'=>'1是2否'),


//                    'point'=>       array('type'=>'int','must'=>0,'default'=>1,'title'=>'积分数/token,如果是查看别人的信息，此值为没有'),
//                    'goldcoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数'),
//                    'diamond'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'钻石数'),
//                    'email'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'邮箱'),
//                    'vip_endtime'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'VIP到期时间'),
//                    'avatars'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像集'),
//                    'language'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'语言'),
//                    'isFriend'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为好友'),
                ),),

            ),
        ),

        'upInfo'=>array(
            'title'=>'更改基础信息',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'nickname'=>array('type'=>'string','must'=>1,'default'=>"张3必疯",'title'=>'昵称'),
//                'avatar'=>array('type'=>'string','must'=>1,'default'=>'https://images.liqucn.com/img/h22/h41/img_localize_e30c5fce43d7465ff694ae224e621f29_200x200.png','title'=>'头像地址'),
                'sex'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'性别1男2女'),
                'sign'=>array('type'=>'string','must'=>1,'default'=>"每天必疯3次，疯呀疯",'title'=>'个性签名'),
                'summary'=>array('type'=>'string','must'=>1,'default'=>'啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊','title'=>'简介'),
                'ps'=>array('type'=>'string','must'=>1,'default'=>"e10adc3949ba59abbe56e057f20f883e",'title'=>'密码，MD5格式'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'upAvatar'=>array(
            'title'=>'修改头像',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'avatar'=>array('type'=>'string','must'=>1,'default'=>'二进制','title'=>'头像二进制流'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

        'feedback'=>array(
            'title'=>'反馈',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'avatar'=>array('type'=>'string','must'=>1,'default'=>'二进制','title'=>'头像二进制流'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

        'getCollectList'=>array(
            'title'=>'反馈',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'avatar'=>array('type'=>'string','must'=>1,'default'=>'二进制','title'=>'头像二进制流'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

    ),
    'system'=>array(
        'title'=>'系统',
        'sendSMS'=>array(
            'title'=>'发送短信',
            'ws'=>array('request_code'=>5001,'response_code'=>5002),
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'ruleId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类ID,1:登陆/注册'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token值'),
            ),
        ),

        'share'=>array(
            'title'=>'分享',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                //'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'类型，5分享好友，6分享收益'),
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类：5：分享给好友奖励，6：晒收入奖励，61：SDK内分享，62：提现分享，72：wechat幸运宝箱，73:qq幸运宝箱，78添加好友，79：sdk调用app分享，80：app直接调用分享，83：分享给站内联系人，95：游戏分享【海外】;96：分享给好友【海外】；97：提现分享【海外】；98：开宝箱【海外】'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'platform'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'4微信，9QQ, 6:facebook【海外】;15:messenger【海外】;16:系统内应用分享【海外】'),
                'toUid'=>array('type'=>'int','must'=>0,'default'=>10000000,'title'=>'分享给指定的好友,如果没有可以为空'),
                'platformMethod'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'1指定人2平台'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),



        'feedback'=>array(
            'title'=>'用户反馈',
            'ws'=>array('request_code'=>5011,'response_code'=>5012),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>'1','title'=>'分类 ID'),
                'contact'=>array('type'=>'string','must'=>1,'default'=>18812366547,'title'=>'联系方式'),
                'content'=>array('type'=>'string','must'=>1,'default'=>'什么破玩艺，不好用。。。。','title'=>'内容'),
                'pics'=>array('type'=>'string','must'=>1,'default'=>'什么破玩艺，不好用。。。。','title'=>'图片'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'sendEmail'=>array(
//            'title'=>'发送邮件',
//            'ws'=>array('request_code'=>5003,'response_code'=>5004),
//            'request'=>array(
//                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//                'ruleId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类ID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),

    ),


    'userSafe'=>array(
        'title'=>'用户安全',
        'upPs'=>array(
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'title'=>'手机-验证码-修改密码',
            'request'=>array(
                'ps'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'密码'),
                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'addReadIdAuth'=>array(
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'title'=>'添加实名验证',
//            'request'=>array(
//                'idNo'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'身份证号'),
//                'realName'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'真实姓名'),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//
//        'isReadIdAuth'=>array(
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'title'=>'是否添加过实名验证',
//            'request'=>array(
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1是2否'),
//            ),
//        ),

        'bindCellphone'=>array(
            'title'=>'绑定手机',
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'bindThird'=>array(
//            'title'=>'绑定3方平台',
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'request'=>array(
//                'type'=>    array('type'=>'int','title'=>'类型，6:facebook(详细看getUserTypeDesc接口)','must'=>1,'default'=>6),
//                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),

    ),

    'msg'=>array(
        'title'=>'站内信',

        'getListByUser'=>array(
            'title'=>'获取用户列表',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'detail'=>array(
            'title'=>'详情',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'unreadList'=>array(
            'title'=>'未读数',
            'ws'=>array('request_code'=>1403,'response_code'=>1404),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

    ),


);
//$GLOBALS[APP_NAME]['api'] = $arr;
return $arr;