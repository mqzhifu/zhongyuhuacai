<?php
$arr = array(
    'index'=>array(
        'title'=>'默认',

        'heartbeat'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'心跳-每5秒一次',
            'request'=>array(),
            'return'=>array(
            ),
        ),
        'getServer'=>array(
            'ws'=>array('request_code'=>1002,'response_code'=>1003),
            'title'=>'获取WS，IP跟端口号',
            'request'=>array(
                'channel'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'渠道'),
            ),
            'return'=>array(
                'ip'=>    array('type'=>'string','title'=>'IP','must'=>1),
                'port'=>  array('type'=>'int','title'=>'PORT','must'=>1),
            ),
        ),

       'cntLog'=>array(
           'ws'=>array('request_code'=>1001,'response_code'=>1002),
           'title'=>'一些统计日志',
           'request'=>array(
                'category'=>array('type'=>'int','title'=>'1：H5，2：pc，3：app','must'=>1,'default'=>1),
                'type'=>array('type'=>'int','title'=>'1：邀请落地页访问，2：邀请落地页下载，3：邀请好友页访问','must'=>1,'default'=>1),
                'memo'=>array('type'=>'string','title'=>'附加字段','must'=>0,'default'=>1),
           ),
           'return'=>array(
               'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
           ),
       ),

        'feidouBindUid'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'飞豆UID绑定平台UID',
            'request'=>array(
                'feidouUid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'飞豆UID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),


        'goldcoinExchangeRMB'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'金币兑换人民币',
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'汇率'),
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


        'getAppVersionInfo'=>array(
            'title'=>'获取APP版本信息',
            'ws'=>array('request_code'=>5009,'response_code'=>5010),
            'request'=>array(
                'versionCode'=>array('type'=>'int','must'=>0,'default'=>"1.0",'title'=>'当前APP版本号，如果不写，默认是返回的是最高版本'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                'summary'=>    array('type'=>'string','title'=>'当前APP版本 - 描述','must'=>1),
                'size'=>    array('type'=>'string','title'=>'大小','must'=>1),
                'version_name'=>    array('type'=>'string','title'=>'版本号','must'=>1),
                'version_code'=>    array('type'=>'int','title'=>'版本号 - 描述','must'=>1),
                'update_url'=>    array('type'=>'string','title'=>'更新下载地址','must'=>1),
//                'a_time'=>    array('type'=>'string','title'=>'大小','must'=>1),
                ),),
            ),
        ),

    ),
    'login'=>array(
        'title'=>'登陆',


        'webSocketLogin'=>array(
            'ws'=>array('request_code'=>2001,'response_code'=>2002),
            'title'=>'长连接 登陆验证',
            'request'=>array(
                'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短连接获取的登陆的凭证TOKEN'),
                'clientInfo'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'客户端信息，|分隔，设备号|APP版本'),
            ),
            'return'=>array(
            ),
        ),


        'guest'=>array(
            'ws'=>array('request_code'=>2001,'response_code'=>2002),
            'title'=>'游客-登陆并注册,会返回TOKEN<br/>拿TOKEN再换取用户信息',
            'request'=>array(),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
                ))
            ),
        ),

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



        'pcLoginCellphonePs'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'PC-手机-验证码-登陆',
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

        'pcLoginCellphoneSMS'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'PC-手机-密码-登陆',
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



//        'index'=>array(
//            'ws'=>array('request_code'=>2009,'response_code'=>2010),
//            'title'=>'用户名密码登陆',
//            'request'=>array(
//                'username'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//                'ps'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为分享，加倍奖励~'),
//            ),
//            'return'=>array(
//                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//            ),
//        ),

//        'loginRegister'=>array(
//            'ws'=>array('request_code'=>2011,'response_code'=>2012),
//            'title'=>'登陆(如果不存在，自动注册)',
//            'request'=>array(
//                'name'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户名'),
//                'ps'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'密码'),
//                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'类型'),
//                'clientInfo'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'客户端信息'),
//                'thirdInfo'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'3方返回的信息'),
//                'smsCode'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'短信登陆需要验证码'),
//            ),
//            'return'=>array(
//            ),
//        ),


//        'onClose'=>array(
//            'ws'=>array('request_code'=>2005,'response_code'=>2006),
//            'title'=>'断开WS连接/清TOKEN',
//            'request'=>array(
//            ),
//            'return'=>array(
//            ),
//        ),

    ),

    'user'=>array(
        'title'=>'用户',

        'getOne'=>array(
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

        'isBind'=>array(
            'title'=>'是否已绑定过',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>4,'title'=>'1手机4微信9QQ'),
                'unicode'=>array('type'=>'string','must'=>1,'default'=>100001,'title'=>'3方唯一值/手机号'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1已绑定2没有'),
            ),
        ),

        'reportUser'=>array(
            'title'=>'举报用户',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'被举报用户UID'),
                'content'=>array('type'=>'string','must'=>1,'default'=>"有3黄信息",'title'=>'内容'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),
        'addBlack'=>array(
            'title'=>'拉黑',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'对方UID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'cancelBlack'=>array(
            'title'=>'移除黑名单',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'对方UID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'addBother'=>array(
            'title'=>'添加-免打扰',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'对方UID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'cancelBother'=>array(
            'title'=>'取消-免打扰',
            'ws'=>array('request_code'=>4003,'response_code'=>4004),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'对方UID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),



        'setUserSystemContact'=>array(
            'title'=>'读取用户手机联系人，写入DB',
            'ws'=>array('request_code'=>4009,'response_code'=>4010),
            'request'=>array(
                'list'=>array('type'=>'string','must'=>1,'default'=>'[{"name":"\u5f20\u4e09","cellphone":"13522XX55XX"},{"name":"\u674e\u56db","cellphone":"13522XX55XX"}]','title'=>'联系人列表，JSON串~ 格式：[["cellphone":"手机号","name":"姓名"]]'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
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

        'setPush'=>array(
            'title'=>'更改消息推送状态',
            'ws'=>array('request_code'=>4013,'response_code'=>4014),
            'request'=>array(
                'status'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'PUSH状态，1打开2关闭'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'setHiddenGps'=>array(
            'title'=>'隐藏GPD信息',
            'ws'=>array('request_code'=>4015,'response_code'=>4016),
            'request'=>array(
                'status'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'PUSH状态，1打开2关闭'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'blackList'=>array(
            'title'=>'黑名单列表',
            'ws'=>array('request_code'=>4017,'response_code'=>4018),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1,'default'=>1),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'uid'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户UID'),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),),
            ),
        ),



        'getCouponList'=>array(
            'title'=>'获取提现卷列表',
            'ws'=>array('request_code'=>5013,'response_code'=>5014),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>  array('type'=>'int','title'=>'使用卷的时候，需要此ID','must'=>1),
                    'money'=>  array('type'=>'string','title'=>'人民币','must'=>1),
                    'a_time'=>  array('type'=>'int','title'=>'添加时间','must'=>1),
                    'valid_time'=>  array('type'=>'int','title'=>'有效起始时间','must'=>1),
                    'use_time'=>  array('type'=>'int','title'=>'使用时间','must'=>1),
                    'expire_time'=>  array('type'=>'int','title'=>'失效时间','must'=>1),
                )
                ),
            ),
        ),


//        'getNearUserList'=>array(
//            'title'=>'获取附近的人',
//            'ws'=>array('request_code'=>4005,'response_code'=>4006),
//            'request'=>array(
//            ),
//            'return'=>array(
//                'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'昵称'),
//                'nickname'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
//                'avatar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
//                'sex'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'性别1男2女'),
//                'distance'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'距离'),
//            ),
//        ),

        'getUserLuckyInfo'=>array(
            'title'=>'Lucky获取用户基本信息',
            'ws'=>array('request_code'=>4001,'response_code'=>4002),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'goldcoin'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'平台金币数'),
                    'balance'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'平台余额'),
                    'nickname'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                ),),
            ),
        ),

        'getluckyUserCoin'=>array(
            'title'=>'Lucky-获取用户当前时间点卡牌是否展示',
            'ws'=>array('request_code'=>4001,'response_code'=>4002),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'is_show'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'是否显示（1：显示；0：不显示）'),
                ),),
            ),
        ),

        'setluckyUserCoin'=>array(
            'title'=>'Lucky-当前时间点操作刮一刮后通知后台',
            'ws'=>array('request_code'=>4001,'response_code'=>4002),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'is_show'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'是否显示（1：显示；0：不显示）'),
                ),),
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

        'adStart'=>array(
            'title'=>'广告开始播放，获取一个广告Id',
            'ws'=>array('request_code'=>5001,'response_code'=>5002),
            'request'=>array(
                'type'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'20签到 21玩游戏中 22玩游戏结束 23开宝箱  24 签到-赚取更多金币 25 游戏中-赚取更多金币  26 游戏结束-赚取更多金币   27 幸运宝箱（海外） 100 每日福利宝箱 110 每日福利红包'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'广告ID'),
            ),
        ),

        'adEnd'=>array(
            'title'=>'广告结束，增加奖励',
            'ws'=>array('request_code'=>5001,'response_code'=>5002),
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'之前获取的Id'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'rewardType'=>      array('type'=>'int','title'=>'奖励类型，1金币3玩游戏','must'=>1),
                    'rewardGold'=>  array('type'=>'string','title'=>'奖励金币数','must'=>0),
                )),
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

        'banner'=>array(
            'title'=>'轮播图',
            'ws'=>array('request_code'=>5013,'response_code'=>5014),
            'request'=>array(
                'bannerType'=>array('type'=>'int','must'=>1,'default'=>'1','title'=>'Banner位置（1、游戏页面顶部轮播；2、任务页面顶部轮播；3、任务页面中部滑动；4、我的页面中部轮播）注：兼容老版本不传的时候默认展示bannerType=1的返回数据'),
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

        'dayGoldUserRankList'=>array(
            'title'=>'每日获取金币排行榜',
            'ws'=>array('request_code'=>5011,'response_code'=>5012),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>1,'list'=>array(
                    'goldNum' => array('type'=>'int','title'=>'金币数','must'=>1),
                    'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                    'rank'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'排名'),
                )
                ),
            ),
        ),


        'dayGoldUserRankFirst'=>array(
            'title'=>'每日获取金币排行榜-昨日冠军',
            'ws'=>array('request_code'=>5001,'response_code'=>5002),
            'request'=>array(
                'day'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'获取哪天的，格式为：20190607。可以为空，默认<昨天>'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'goldNum' => array('type'=>'int','title'=>'金币数','must'=>1),
                    'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                    'rewardGoldNum'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'奖励金币数'),
                )),
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

//        'getVerifierImg'=>array(
//            'title'=>'获取图片验证码',
//            'ws'=>array('request_code'=>5005,'response_code'=>5006),
//            'request'=>array(
//                'unicode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//        'authVerifierImgCode'=>array(
//            'title'=>'验证 - 图片验证码',
//            'ws'=>array('request_code'=>5007,'response_code'=>5008),
//            'request'=>array(
//                'unicode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//                'imgCode'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类ID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
    ),


    'invite'=>array(
        'title'=>'邀请',

        'setUserCode'=>array(
            'title'=>'设置邀请码',
            'ws'=>array('request_code'=>4017,'response_code'=>4018),
            'request'=>array(
                'code'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'好友邀请码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'incomeTotal'=>array(
            'title'=>'邀请获取的收益汇总',
            'ws'=>array('request_code'=>4017,'response_code'=>4018),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'people'=>      array('type'=>'int','title'=>'总人数','must'=>1),
                    'income'=>  array('type'=>'string','title'=>'收益','must'=>1),
                    'budget'=>   array('type'=>'string','title'=>'预计收益','must'=>1),
                    )
                ),
            ),
        ),

        'incomeLog'=>array(
            'title'=>'邀请获取的收益日志',
            'ws'=>array('request_code'=>4017,'response_code'=>4018),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                    'income'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                ),)
            ),
        ),
    ),


    'bank'=>array(
        'title'=>'金融',

        'getMoney'=>array(
            'title'=>'提现',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
                'elementId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'数量元素ID'),
                'shareId'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'分享ID，-1：代表第一次分享'),
                'adId'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'广告ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'getMoneyElement'=>array(
            'title'=>'提现商品列表',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'element_id'=>      array('type'=>'string','title'=>'该元素ID,调取提现接口时候用','must'=>1),
                    'num'=>  array('type'=>'string','title'=>'货币数量','must'=>1),
                    'allow'=>   array('type'=>'int','title'=>'0允许提现1不允许提','must'=>1),
                ),),
            ),
        ),

        'getMoneyLog'=>array(
            'title'=>'提现日志',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'a_time'=>      array('type'=>'string','title'=>'添加时间','must'=>1),
                    'num'=>  array('type'=>'string','title'=>'货币数量','must'=>1),
                    'status'=>   array('type'=>'int','title'=>'1未处理2成功3失败','must'=>1),
                ),),
            ),
        ),





        'getGoldcoinInfo'=>array(
            'title'=>'获取金币信息',
            'ws'=>array('request_code'=>6005,'response_code'=>6006),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'today'=>      array('type'=>'int','title'=>'今日获取金币总数','must'=>1),
                    'total'=>  array('type'=>'string','title'=>'当前金币','must'=>1),
                    'sum'=>   array('type'=>'int','title'=>'累计总数','must'=>1),
                )),
                ),
        ),

        'appAuthOrderState'=>array(
            'title'=>'APP支付完成，微信回调，要再次到S端验证结果',
            'ws'=>array('request_code'=>6005,'response_code'=>6006),
            'request'=>array(
                'oid'=>      array('type'=>'int','title'=>'订单id，生成预订单时候，S端返回的','must'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'status'=>      array('type'=>'int','title'=>'1未处理2成功3失败','must'=>1),
                    'signedRequest'=>  array('type'=>'string','title'=>'签名','must'=>1),
//                    'sum'=>   array('type'=>'int','title'=>'累计总数','must'=>1),
                )),
            ),
        ),


        'useCoupon'=>array(
            'title'=>'使用提现卷',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'提现卷ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),


        'getGoldLog'=>array(
            'title'=>'金币明细',
            'ws'=>array('request_code'=>6005,'response_code'=>6006),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'title'=>      array('type'=>'string','title'=>'标题','must'=>1),
                    'content'=>  array('type'=>'string','title'=>'详细描述','must'=>1),
                    'num'=>   array('type'=>'int','title'=>'数量','must'=>1),
                    'a_time'=>   array('type'=>'int','title'=>'添加时间','must'=>1),
                )
                ),),
        ),

        'payGameGoods'=>array(
            'title'=>'游戏内购买道具',
            'ws'=>array('request_code'=>6005,'response_code'=>6006),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'goodsId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
                'feidou_uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'飞豆UID'),
                'goldCoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数'),
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1微信2支付宝'),
                'developerPayload'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'开发者自定义的值，255长度'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'appid'=>   array('type'=>'string','title'=>'微信开放平台审核通过的应用APPID','must'=>1),
                    'mch_id'=>  array('type'=>'string','title'=>'微信支付分配的商户号','must'=>1),
                    'prepay_id'=>      array('type'=>'string','title'=>'微信的预订单标识','must'=>1),
                    'oid'=>      array('type'=>'string','title'=>'订单ID，用于回调','must'=>1),
//                    'package'=>   array('type'=>'string','title'=>'暂填写固定值Sign=WXPay','must'=>1),
//                    'nonce_str'=>   array('type'=>'string','title'=>'随机字符串，不长于32位。推荐随机数生成算法','must'=>1),
//                    'timestamp'=>   array('type'=>'string','title'=>'时间戳','must'=>1),
//                    'sign'=>   array('type'=>'string','title'=>'加密签到','must'=>1),
                )
            ),),
        ),

//        'pointExchangeGoods'=>array(
//            'title'=>'积分兑换商品 ',
//            'ws'=>array('request_code'=>6005,'response_code'=>6006),
//            'request'=>array(
//                'goodsId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
//                'email'=>array('type'=>'string','must'=>1,'default'=>'78878296@qq.com','title'=>'邮箱地址'),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//        'getGoodsList'=>array(
//            'title'=>'获取积分，可兑换的商品列表',
//            'ws'=>array('request_code'=>6005,'response_code'=>6006),
//            'request'=>array(
//            ),
//            'return'=>array(
//                'array_key_number_two'=>array("must"=>0,'list'=>array(
//                    'id'=>      array('type'=>'int','title'=>'唯一值','must'=>1),
//                    'dollar'=>  array('type'=>'string','title'=>'美元','must'=>1),
//                    'point'=>   array('type'=>'int','title'=>'积分/token','must'=>1),
//                    'summary'=> array('type'=>'string','title'=>'简介','must'=>1),
//                    'name'=>    array('type'=>'string','title'=>'名称','must'=>1),
//                    'img'=>     array('type'=>'string','title'=>'图片地址','must'=>1),
//                    'type'=>    array('type'=>'int','title'=>'类型1虚拟 2实物','must'=>1),
//                )
//            ),),
//        ),
//        'getTodayPointTotal'=>array(
//            'title'=>'获取用户今日赚取的积分/token ',
//            'ws'=>array('request_code'=>6007,'response_code'=>6008),
//            'request'=>array(
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),

        'withdrawOperation'=>array(
            'title'=>'提现操作（海外APP）',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
                'elementId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'数量元素ID（1：$10;2：$20）'),
                'payPalAddr'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'payPal地址'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'getOsWithdrawal'=>array(
            'title'=>'提现商品列表（海外APP）',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>      array('type'=>'int','title'=>'主键ID','must'=>1),
                    'gift_card_id'=>      array('type'=>'int','title'=>'礼品卡编号','must'=>1),
                    'gift_card_name'=>      array('type'=>'string','title'=>'礼品卡名称','must'=>1),
                    'gift_card_value'=>      array('type'=>'int','title'=>'礼品卡价值','must'=>1),
                    'gift_desc'=>      array('type'=>'string','title'=>'礼品卡描述','must'=>1),
                    'change_gold'=>      array('type'=>'int','title'=>'兑换等值礼品卡所需金币数','must'=>1),
                    'img_url'=>      array('type'=>'string','title'=>'礼品卡图片地址（绝对）','must'=>1),
                    'element_id'=>      array('type'=>'string','title'=>'该元素ID,调取提现接口时候用（1:10美元;2：20美元）','must'=>1),
                    'a_time'=>      array('type'=>'string','title'=>'创建时间（日期格式）','must'=>1),
                    'u_time'=>      array('type'=>'string','title'=>'最后更新时间（日期格式）','must'=>1),
                    'allow'=>   array('type'=>'int','title'=>'0允许提现1不允许提','must'=>1),
                ),),
            ),
        ),

        'getOsWithdrawalLog'=>array(
            'title'=>'提现日志（海外APP）',
            'ws'=>array('request_code'=>1501,'response_code'=>1502),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'a_time'=>      array('type'=>'string','title'=>'添加时间','must'=>1),
                    'num'=>  array('type'=>'string','title'=>'货币数量','must'=>1),
                    'status'=>   array('type'=>'int','title'=>'1未处理2成功3失败','must'=>1),
                    'payPal_title'=>   array('type'=>'string','title'=>'PayPal Payment','must'=>1),
                ),),
            ),
        ),

    ),

    'lottery'=>array(
        'title'=>'抽奖/活动',


        "pre"=>array(
            'title'=>'幸运大抽奖，初始化',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'剩余免费次数'),
            ),
        ),

        "getHappyLotteryPlayTime"=>array(
            'title'=>'开心大转盘，玩游戏总时间',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'秒数'),
            ),
        ),





        "doing"=>array(
            'title'=>'幸运大抽奖，执行抽奖',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'rewardType'=>      array('type'=>'int','title'=>'奖励类型，1金币2看广告','must'=>1),
                    'rewardGold'=>  array('type'=>'int','title'=>'奖励金币数','must'=>0),
                    'freeTime'=>  array('type'=>'int','title'=>'剩余免费次数','must'=>0),
                )),
            ),
        ),

        "getRandomLuckBox"=>array(
            'title'=>'随机宝箱，每4小时一个',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'rewardGold'=>  array('type'=>'int','title'=>'奖励金币数','must'=>0),
                    'rewardGoldByShareWX'=>      array('type'=>'int','title'=>'分享微信奖励金币数','must'=>1),
                    'rewardGoldByShareQQ'=>      array('type'=>'int','title'=>'分享QQ奖励金币数','must'=>1),
                    'countdown'=>array('type'=>'int','title'=>'倒计时,秒','must'=>1),
                )),
            ),
        ),

        "getRandomLuckBoxCountdown"=>array(
            'title'=>'获取随机宝箱倒计时（秒）',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0为可以领取新的~大于0为具体的秒数'),
            ),
        ),




        "goldcoinCarnivalBulletin"=>array(
            'title'=>'金币欢乐送 - 公告板',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'nickname'=>  array('type'=>'string','title'=>'昵称','must'=>0),
                    'reward'=>  array('type'=>'string','title'=>'奖励','must'=>0),
                )),
            ),
        ),






        "goldcoinCarnivalInfo"=>array(
            'title'=>'金币欢乐送 - 获取基本信息',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'isFree'=>  array('type'=>'int','title'=>'免费次数1有2没有','must'=>0),
                    'multipleTimes'=>      array('type'=>'int','title'=>'还有多少次可以翻倍','must'=>1),
                    'multiple'=>      array('type'=>'int','title'=>'翻几倍','must'=>1),
                    ))
//                'log'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
//                    'reward_goldcoin'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
//                )),
            ),
        ),


        'goldcoinCarnivalLog'=>array(
            'title'=>'金币欢乐送 - 日志记录',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'reward_goldcoin'=>    array('type'=>'int','title'=>'奖励金币数','must'=>1),
                )
                )
            ),
        ),


        "doGoldcoinCarnival"=>array(
            'title'=>'金币欢乐送 - 执行抽奖',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'奖励金币数'),
            ),
        ),



        "happyDoing"=>array(
            'title'=>'开心大抽奖-执行',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'id'=>          array('type'=>'int','title'=>'广告ID','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'type'=>    array('type'=>'int','title'=>'1金币2提现卷','must'=>1),
                    'num'=>    array('type'=>'string','title'=>'数量','must'=>1),
                )
                )

            ),
        ),

        "getDayLotteryBoxTimes"=>array(
            'title'=>'获取-每日福利开宝箱-剩余次数',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'剩余次数'),
            ),
        ),

        "doingDayLotteryBox"=>array(
            'title'=>'开启-每日福利开宝箱',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'ad_id'=>array('type'=>'int','title'=>'广告ID','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'goldcoin' => array('type'=>'int','title'=>'获得金币数','must'=>1),
                    'left' => array('type'=>'int','title'=>'剩余开启次数','must'=>1),
                ))
            ),
        ),

        "getDailyRedPacketInfo"=>array(
            'title'=>'获取-每日福利红包信息',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'packets'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'id'=>array('type'=>'int','title'=>'红包','must'=>1),
                    'num'=>array('type'=>'int','title'=>'红包金币数','must'=>1),
                    'seconds'=>array('type'=>'int','title'=>'开启需要时间（秒）','must'=>1),
                    'status'=>array('type'=>'int','title'=>'红包是否已开启（0未开启，1已开启','must'=>1),
                )),
            ),
        ),

        "openDailyRedPacket"=>array(
            'title'=>'开启-每日福利红包',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'adId'=>array('type'=>'int','title'=>'广告ID','must'=>1,'default'=>1),
                'isDownload'=>array('type'=>'int','title'=>'是否下载应用（1：是；0：否）','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'获得金币数'),
                ))
            ),
        ),

        "getTodayCardsTimes"=>array(
            'title'=>'获取单用户当日开心翻翻卡剩余游戏次数',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'剩余次数'),
                ))
            ),
        ),

        "doingFlipCards"=>array(
            'title'=>'执行开心翻翻卡',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'isDownload'=>array('type'=>'int','title'=>'是否下载（1：已下载；0：未下载）','must'=>0,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'goldCoins' => array('type'=>'int','title'=>'获得金币数','must'=>1),
                    'grade' => array('type'=>'int','title'=>'等级（1：幸运金卡；2：幸运银卡；3：幸运铜卡）','must'=>1),
                    'aid' => array('type'=>'int','title'=>'金币id','must'=>1),
                ))
            ),
        ),

        "doingFlipCardsTriple"=>array(
            'title'=>'执行开心翻翻卡看广告金币*3',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'adId'=>array('type'=>'int','title'=>'广告ID（未观看传0或者不传递此参数）','must'=>1,'default'=>1),
                'aid'=>array('type'=>'int','title'=>'金币id','must'=>1,'default'=>1),
                'goldCoins'=>array('type'=>'int','title'=>'金币数','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数（原始数据*3）'),
                ))
            ),
        ),

        "getHappyBigWheelBoxList"=>array(
            'title'=>'获取开心大轮盘宝箱列表+当前剩余游戏次数',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'box_datas'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'id'=>array('type'=>'int','title'=>'宝箱id','must'=>1),
                    'cnt'=>array('type'=>'int','title'=>'开启宝箱次数限制（5，30,60,100）','must'=>1),
                    'goldCoins'=>array('type'=>'int','title'=>'奖励金币数','must'=>1),
                    'status'=>array('type'=>'int','title'=>'宝箱状态（0：不可开，1：可以开，2：已经开）','must'=>1),
                )),
                'residue_degree'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'剩余游戏次数'),
            ),
        ),

        "dohappyBigWheel"=>array(
            'title'=>'执行开心大轮盘奖励',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                //'adId'=>array('type'=>'int','title'=>'广告ID（未观看传0或者不传递此参数）','must'=>0,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'type' => array('type'=>'int','title'=>'（1：广告；2：金币）','must'=>1),
                    'goldcoins' => array('type'=>'int','title'=>'奖励金币数','must'=>1),
                    'aid' => array('type'=>'int','title'=>'金币id','must'=>1),
                ))
            ),
        ),

        "dohappyBigWheelDouble"=>array(
            'title'=>'执行开心大轮盘奖励看广告金币*3',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'adId'=>array('type'=>'int','title'=>'广告ID（未观看传0或者不传递此参数）','must'=>1,'default'=>1),
                'aid'=>array('type'=>'int','title'=>'金币id','must'=>1,'default'=>1),
                'goldCoins'=>array('type'=>'int','title'=>'金币数','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数（原始数据*3）'),
                ))
            ),
        ),

        "dohappyBigWheelBox"=>array(
            'title'=>'执行开心大轮盘宝箱奖励',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                //'adId'=>array('type'=>'int','title'=>'广告ID（未观看传0或者不传递此参数）','must'=>0,'default'=>1),
                'boxId'=>array('type'=>'int','title'=>'宝箱id','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数'),
                ))
            ),
        ),





    ),

    'game'=>array(
        'title'=>'游戏',

        "todayPlayGameTimeTotal"=>array(
            'title'=>'今日游戏总时间',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'timeFormatType'=>          array('type'=>'int','title'=>'0分钟1秒','must'=>0,'default'=>"0"),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分钟数/秒数'),
            ),
        ),

        "gameEnd"=>array(
            'title'=>'游戏结束/关注游戏/游戏中后退',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                    'id'=>          array('type'=>'int','title'=>'游戏开始时，拿到的ID唯一值','must'=>1,'default'=>1),
                    'rewardData'=>  array('type'=>'string','title'=>'每奖励的金币,json格式{{秒:金币数},{秒:金币数}}  {"1":10}','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),

            ),
        ),


        'recommendIndex'=>array(
            'title'=>'首页推荐列表',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                        'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                        'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                        'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                        'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                        'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                        'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                        'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                        'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                        'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                        'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),

                    )
                )

            ),
        ),

        'topOnline'=>array(
            'title'=>'最新列表',
            'ws'=>array('request_code'=>1303,'response_code'=>1304),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                )
                )

            ),
        ),

        'getList'=>array(
            'title'=>'所有游戏列表',
            'ws'=>array('request_code'=>1305,'response_code'=>1306),
            'request'=>array(
                'page'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'当前页，此值可以用上一次返回的<nextPage>填写'),
                'f_key'=>array('type'=>'int','must'=>0,'default'=>0,'title'=>'渠道F值，默认返回所有游戏'),
            ),
            'return'=>array(
                'pageInfo'=>array("array_type"=>'array_key_number_one','must'=>0,'list'=>array(
                    'start'=>    array('type'=>'int','title'=>'分页起始值','must'=>1),
                    'end'=>    array('type'=>'int','title'=>'分页终止值','must'=>1),
                    'totalPage'=>    array('type'=>'int','title'=>'总页数','must'=>1),
                    'nextPage'=>    array('type'=>'int','title'=>'下一页','must'=>1),
                )),
                'list'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                )),
            ),
        ),

        'exceptRecommendIndex'=>array(
            'title'=>'首页去掉<推荐><最新>列表',
            'ws'=>array('request_code'=>1307,'response_code'=>1308),
            'request'=>array(
                'page'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'当前页，此值可以用上一次返回的<nextPage>填写'),
            ),
            'return'=>array(
                'pageInfo'=>array("array_type"=>'array_key_number_one','must'=>0,'list'=>array(
                    'start'=>    array('type'=>'int','title'=>'分页起始值','must'=>1),
                    'end'=>    array('type'=>'int','title'=>'分页终止值','must'=>1),
                    'totalPage'=>    array('type'=>'int','title'=>'总页数','must'=>1),
                    'nextPage'=>    array('type'=>'int','title'=>'下一页','must'=>1),
                )),
                'list'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                )),
            ),
        ),

        'playedGameHistoryList'=>array(
            'title'=>'玩过的游戏记录',
            'ws'=>array('request_code'=>1309,'response_code'=>1310),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'查看某人的记录'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                )
                )
            ),
        ),

        'addCollect'=>array(
            'title'=>'添加收藏',
            'ws'=>array('request_code'=>1311,'response_code'=>1312),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'cancelCollect'=>array(
            'title'=>'取消收藏',
            'ws'=>array('request_code'=>1311,'response_code'=>1312),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'getImGameInvite'=>array(
            'title'=>'获取IM内，发起邀请的托盘-游戏列表',
            'ws'=>array('request_code'=>1315,'response_code'=>1314),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                )
                )
            ),
        ),

        'gameStart'=> array(
            'title'=>'游戏开始前准备工作',
            'ws'=>array('request_code'=>1317,'response_code'=>1318),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'src'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'游戏开始源'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值，用于结束后，领取金币奖励','must'=>1),
                    'isCollect'=>    array('type'=>'int','title'=>'是否收藏1是0否','must'=>1),
                    'todaySinglePlayedGameTime'=>    array('type'=>'int','title'=>'今天玩该游戏的总时间','must'=>1),
                    'countdown'=>array('type'=>'int','title'=>'30','must'=>1),
                    )
                )
            ),
        ),

        'getPlayGameRewardRule'=> array(
            'title'=>'玩游戏,加金币的-规则',
            'ws'=>array('request_code'=>1317,'response_code'=>1318),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>1,'list'=>array(
                    'sec_start'=>    array('type'=>'int','title'=>'阶梯范围，秒数，起始值','must'=>1),
                    'sec_end'=>    array('type'=>'int','title'=>'阶梯范围，秒数，结束值','must'=>1),
                    'reward_goldcoin_rand_start'=>    array('type'=>'int','title'=>'周期内，随机奖励金币-起始值','must'=>1),
                    'reward_goldcoin_rand_end'=>    array('type'=>'int','title'=>'周期内，随机奖励金币-结束值','must'=>1),),
                    'second_period'=>    array('type'=>'int','title'=>'奖励，间隔周期，秒！','must'=>1),
                )
            ),
        ),


        'getDevUploadGame'=>array(
            'title'=>'开发者 - 获取自己上传的游戏',
            'ws'=>array('request_code'=>1315,'response_code'=>1314),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'is_test'=>array('type'=>'int','title'=>'是否为测试版1是2不是','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                )
                )
            ),
        ),


        'getAdminGameCheck'=>array(
            'title'=>'管理员 查看 待审核的游戏',
            'ws'=>array('request_code'=>1315,'response_code'=>1314),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                )
                )
            ),
        ),

        'getGameInfo'=>array(
            'title'=>'上线版游戏信息',
            'ws'=>array('request_code'=>1316,'response_code'=>1317),
            'request'=>array(
                'gameid'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'游戏id'),
            ),
            'return'=>array(
                'info'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                  )
                )
            ),
        ),
        'getRecommends'=>array(
            'title'=>'推荐游戏列表',
            'ws'=>array('request_code'=>1318,'response_code'=>1319),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'推荐位置（1：弹窗轮播，2：精品推荐，3：最新上架）'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                )),
            ),
        ),

        "exchangeLuckyGold"=>array(
            'title'=>'Lucky金币兑换（筹码，奖金两种方式）',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'convertType'=>  array('type'=>'string','title'=>'兑换方式（1：筹码;2:奖金）','must'=>1,'default'=>1),
                'jetton'=>array('type'=>'int','title'=>'筹码数量（convertType=1时必填）','must'=>1,'default'=>1),
                'balance'=>array('type'=>'string','title'=>'奖金数值（convertType=2时必填）','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),

            ),
        ),

        'getSingleGameInfo'=>array(
            'title'=>'获取单一游戏详情（Lucky）',
            'ws'=>array('request_code'=>1305,'response_code'=>1306),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值','must'=>1),
                    'name'=>    array('type'=>'string','title'=>'游戏名','must'=>1),
                    'small_img'=>    array('type'=>'string','title'=>'小图','must'=>1),
                    'list_img'=>    array('type'=>'string','title'=>'列表图','must'=>1),
                    'index_reco_img'=>    array('type'=>'string','title'=>'首页推荐图','must'=>1),
                    'summary'=>    array('type'=>'string','title'=>'简介','must'=>1),
                    'played_num'=>    array('type'=>'int','title'=>'玩过该游戏的总人数','must'=>1),
                    'screen'=>    array('type'=>'int','title'=>'1横2竖','must'=>1),
                    'background_color'=>    array('type'=>'string','title'=>'背景色','must'=>1),
                    'play_url'=>    array('type'=>'string','title'=>'游戏地址','must'=>1),
                    'link_url'=>    array('type'=>'string','title'=>'游戏外链地址','must'=>1),
                    'url_type'=>    array('type'=>'int','title'=>'地址类型','must'=>1),
                    'wx_userName'=>    array('type'=>'string','title'=>'微信小程序原始id','must'=>1),
                    'wx_path'=>    array('type'=>'string','title'=>'小程序路径','must'=>1),
                    'wx_miniprogramType'=>    array('type'=>'int','title'=>'版本类型','must'=>1),
                )
                )

            ),
        ),

        'residueLuckyCoin'=>array(
            'title'=>'Lucky-用户当天还可兑换数',
            'ws'=>array('request_code'=>1305,'response_code'=>1306),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'jettonNum'=>    array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前还可兑换筹码书'),
                    'balanceNum'=>    array('type'=>'float','must'=>1,'default'=>1,'title'=>'当前还可兑换现金数'),
                ),),
            ),
        ),

        'gameLuckyBegin'=> array(
            'title'=>'游戏开始前准备工作',
            'ws'=>array('request_code'=>1317,'response_code'=>1318),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'id'=>    array('type'=>'int','title'=>'唯一值，用于结束后，领取金币奖励','must'=>1),
                )
                )
            ),
        ),

        "gameLuckyOver"=>array(
            'title'=>'趣味刮一刮游戏结束',
            'ws'=>array('request_code'=>1301,'response_code'=>1302),
            'request'=>array(
                'id'=>          array('type'=>'int','title'=>'游戏开始时，拿到的ID唯一值','must'=>1,'default'=>1),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'addPlayLog'=>array(
//            'title'=>'添加游戏记录',
//            'ws'=>array('request_code'=>1313,'response_code'=>1314),
//            'request'=>array(
//                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//        'playGamePrepare'=> array(
//            'title'=>'游戏开始前准备工作',
//            'ws'=>array('request_code'=>1317,'response_code'=>1318),
//            'request'=>array(
//                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
//            ),
//            'return'=>array(
//                'array_key_number_one'=>array("must"=>0,'list'=>array(
//                'countdown'=>    array('type'=>'int','title'=>'倒计时','must'=>1),
//                'rewardPoint'=>    array('type'=>'int','title'=>'奖励TOKEN数','must'=>1),
//                'isCollect'=>    array('type'=>'int','title'=>'是否收藏1是0否','must'=>1),))
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

        'addReadIdAuth'=>array(
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'title'=>'添加实名验证',
            'request'=>array(
                'idNo'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'身份证号'),
                'realName'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'真实姓名'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'isReadIdAuth'=>array(
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'title'=>'是否添加过实名验证',
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1是2否'),
            ),
        ),

        'setXinGeThirdPushToken'=>array(
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'title'=>'设置3方推送的TOKEN值-信鸽',
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token值'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

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

        'bindThird'=>array(
            'title'=>'绑定3方平台',
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'request'=>array(
                'type'=>    array('type'=>'int','title'=>'类型，6:facebook(详细看getUserTypeDesc接口)','must'=>1,'default'=>6),
                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'bindMoneyToken'=>array(
            'title'=>'绑定提现口令',
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'request'=>array(
                'moneyToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'提现口令'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),
    ),

    'fans'=>array(
        'title'=>'粉丝/关注',
        'getFollowList'=>array(
            'title'=>'获取我关注别人的列表 ',
            'ws'=>array('request_code'=>7001,'response_code'=>7002),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                        'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                        'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                        'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                        'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),

            ),),
        ),
        'getFansList'=>array(
            'title'=>'获取关注我的列表',
            'ws'=>array('request_code'=>7003,'response_code'=>7004),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                ),),
            ),
        ),

        'recommendList'=>array(
            'title'=>'推荐列表',
            'ws'=>array('request_code'=>7005,'response_code'=>7006),
            'request'=>array(
                'page'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'当前页，此值可以用上一次返回的<nextPage>填写'),
            ),
            'return'=>array(
                'pageInfo'=>array("array_type"=>'array_key_number_one','must'=>0,'list'=>array(
                    'start'=>    array('type'=>'int','title'=>'分页起始值','must'=>1),
                    'end'=>    array('type'=>'int','title'=>'分页终止值','must'=>1),
                    'totalPage'=>    array('type'=>'int','title'=>'总页数','must'=>1),
                    'nextPage'=>    array('type'=>'int','title'=>'下一页','must'=>1),
                )),
                'list'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'关注者UID','must'=>1),
                    'nickname'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                )),
            ),
        ),

        'add'=>array(
            'title'=>'添加',
            'ws'=>array('request_code'=>7007,'response_code'=>7008),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'要关注人的ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'cancel'=>array(
            'title'=>'取消',
            'ws'=>array('request_code'=>7007,'response_code'=>7008),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'取消注人的ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),


//        'guestBindThirdPlatform'=>array(
//            'title'=>'游客账号绑定3方平台',
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'request'=>array(
//                'type'=>    array('type'=>'int','title'=>'类型，4:微信.6:facebook.9:qq','must'=>1,'default'=>4),
//                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
//                'nickname'=>    array('type'=>'string','title'=>'昵称','default'=>"imZ",'must'=>1),
//                'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1,'default'=>"https://b-ssl.duitang.com/uploads/people/201805/21/20180521200051_imuch.thumb.36_36_c.jpeg"),
//                'sex'=>    array('type'=>'int','title'=>'性别，1男2女','must'=>0,'default'=>2),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//        'setMemoName'=>array(
//            'title'=>'设置备注',
//            'ws'=>array('request_code'=>8007,'response_code'=>8008),
//            'request'=>array(
//                'toUid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'对方UID'),
//                'name'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'备注名'),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),


//      'getFollowLog'=>array(
//            'title'=>'获取我关注的人-动态 ',
//            'ws'=>array('request_code'=>5006,'response_code'=>5007),
//            'request'=>array(
//            ),
//            'return'=>array(
//            ),
//        ),

    ),

    'imMsg'=>array(
        'title'=>'聊天室',

        'receive'=>array(
            'title'=>'接收消息',
            'ws'=>array('request_code'=>1101,'response_code'=>1102),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'发送给谁/目标用户'),
                'type'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'类型，1文字2图片3约战/发起游戏邀请4礼物'),
                'title'=>array('type'=>'int','must'=>1,'default'=>"你好呀，需不需要特殊服务？",'title'=>'标题。注：此值不可以为空'),
                'content'=>array('type'=>'int','must'=>1,'default'=>"你好呀，需不需要特殊服务？",'title'=>'内容。注：此值不可以为空'),
            ),
            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),


        'userIMSessionStatus'=>array(
            'title'=>'用户会话更新，当用户进入/离开IM，发送数据',
            'ws'=>array('request_code'=>1101,'response_code'=>1102),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'会话状态,1进入2离开'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),



        'getSessionList'=>array(
            'title'=>'获取用户的session列表',
            'ws'=>array('request_code'=>1103,'response_code'=>1104),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'类型,1陌生人'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'被关注者UID','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'被关注者 昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'被关注者 头像','must'=>1),
                )),

            ),
        ),

        'getSessionMsgList'=>array(
            'title'=>'获取一个SESSION的所有消息记录',
            'ws'=>array('request_code'=>1105,'response_code'=>1106),
            'request'=>array(
                'sessionId'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'会话ID'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'to_uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'a_time'=>    array('type'=>'int','title'=>'添加时间','must'=>1),
                    'from_uid'=>    array('type'=>'int','title'=>'发送者','must'=>1),
                    'content'=>    array('type'=>'string','title'=>'内容','must'=>1),
                )),
            ),
        ),

        'provideList'=>array(
            'title'=>'发起会话/分享-指定列表',
            'ws'=>array('request_code'=>8009,'response_code'=>8010),
            'request'=>array(
            ),
            'return'=>array(
                'nearTalkUser'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'last_time'=>    array('type'=>'int','title'=>'最后更新时间','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1),
                )),
                'fans'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'a_time'=>    array('type'=>'int','title'=>'添加时间','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1),
                )),
            ),
        ),

        'contactList'=>array(
            'title'=>'联系人列表',
            'ws'=>array('request_code'=>8011,'response_code'=>8012),
            'request'=>array(
            ),
            'return'=>array(
                'fans'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                )),
                'follow'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                )),
                'friend'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'uid'=>    array('type'=>'int','title'=>'接收者','must'=>1),
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1),
                    'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1),
                    'sex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0未知1男2女'),
                )),

            ),
        ),

    ),


    'msg'=>array(
        'title'=>'push动态',

        'getList'=>array(
            'title'=>'列表',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'getUnread'=>array(
            'title'=>'未读数',
            'ws'=>array('request_code'=>1403,'response_code'=>1404),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

    ),


    'sdk'=>array(
        'title'=>'SDK接口',

        'getAccessToken'=>array(
            'title'=>'如下接口，都需要先调用此接口，获取accessToken',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
            ),
        ),

        'getRankCount'=>array(
            'title'=>'获取排行榜总人数',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'数量'),
            ),
        ),

        'getRankList'=>array(
            'title'=>'获取排行榜列表数据',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'everyPageNum'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页显示多少条'),
                'offset'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'偏移量'),
                'isDesc'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'0降序1升序'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'oid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'score'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成绩'),
                    'a_time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'添加时间'),
                    'name'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'photo'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'u_time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'最后更新时间'),
                ),),
            ),
        ),

        'getUserRank'=>array(
            'title'=>'获取 用户 排行榜 信息',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'isDesc'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'0降序1升序'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'score'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'成绩'),
                    'rank'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'排名'),
                ))
            ),
        ),

        'setRankListScore'=>array(
            'title'=>'设置 - 分数',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'score'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分数'),
//                'extra'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'扩展字段'),

            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'setCacheData'=>array(
            'title'=>'设置持久化数据',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'dataKey'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'持久化的数据-键值'),
                'dataValue'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'持久化的数据-内容'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'getCacheData'=>array(
            'title'=>'获取持久化数据',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'dataKey'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'持久化的数据-键值'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'value值'),
            ),
        ),


        'share'=>array(
            'title'=>'分享',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类，61:SDK内分享'),
                'platform'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'4:微信，9:QQ，14:APP内分享'),
                'toUid'=>array('type'=>'int','must'=>0,'default'=>10000000,'title'=>'分享给指定的好友,如果没有可以为空'),
                'platformMethod'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'1指定人2平台'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),



        'getGoodsInfo'=>array(
            'title'=>'获取商品列表',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'id'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID,唯一值，与APP通信用'),
                    'name'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'名称'),
                    'money'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'人民币'),
                    'os_type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1安卓2IOS'),
                ),),
            ),
        ),

        'getUnwasteGoods'=>array(
            'title'=>'获取 未消耗 商品订单 列表',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'in_trade_no'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'订单号'),
                    'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'人民币'),
                    'a_time'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'添加时间'),
                    'goldcoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币 '),
                    'money'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'人民币'),
                    'pay_type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1微信2支付宝'),
                    'pay_category'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1微信全额2支付宝全额3 现金+金币'),
                    'done_time'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'订单完成时间'),
                    'goods_id'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
                    'signedRequest'=>  array('type'=>'string','title'=>'签名','must'=>1),
                ),),
            ),
        ),

        'wasteGoods'=>array(
            'title'=>'消耗商品',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'oid'=>array('type'=>'int','must'=>0,'default'=>10000000,'title'=>'订单ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
            ),
        ),

        'appAuthOrderStatus'=>array(
            'title'=>'',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                'request_payload'=>array('type'=>'string','title'=>'payload','must'=>1),
                'player_id'=>array('type'=>'string','title'=>'','must'=>1),
                'issued_at'=>array('type'=>'number','title'=>'','must'=>1),
                'game_id'=>array('type'=>'number','title'=>'游戏ID','must'=>1),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'code'=>      array('type'=>'int','title'=>'成功or失败状态码（200：成功）','must'=>1),
                    'signedRequest'=>  array('type'=>'string','title'=>'加密后的字符串','must'=>1),
                )),
            ),
        ),

        'cntShareCount'=>array(
                    'title'=>'H5分享数据记录',
                    'ws'=>array('request_code'=>4007,'response_code'=>4008),
                    'request'=>array(
                        'accessToken'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'SDK-访问TOKEN'),
                        'gameId'=>array('type'=>'int','title'=>'游戏id','must'=>1),
                        'sharePath'=>array('type'=>'int','title'=>'分享路径（分享路径74：微信好友，75：微信朋友圈，76：QQ好友，77：QQ空间）','must'=>1),
                        'type'=>array('type'=>'int','title'=>'分享方式（1：登陆量；2：点击量；3：下载量；）','must'=>1),
                    ),
                    'return'=>array(
                        'array_key_number_one'=>array("must"=>1,'list'=>array(
                            'code'=>      array('type'=>'int','title'=>'成功or失败状态码（200：成功；-1101：参数缺失；-1102：请求参数非法；-1103：数据更新失败；）','must'=>1),
                        )),
                    ),
                ),








    ),



    'sign'=>array(
        'title'=>'签到',
        'getListAndReward'=>array(
            'title'=>'获取签到列表并领取奖励',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'historyList'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'rewardGold'=>    array('type'=>'int','title'=>'奖励金币数','must'=>1),
                    'addition'=>    array('type'=>'int','title'=>'附加金币数','must'=>1),
                    'isSign'=>    array('type'=>'int','title'=>'0未签到1已签到','must'=>1),
                )),
                'rewardGold'=>    array('type'=>'string','title'=>'本次签到获取的金币数','must'=>1),
            ),
        ),

        'getListLog'=>array(
            'title'=>'获取签到列表',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>1,'list'=>array(
                    'isSign'=>    array('type'=>'int','title'=>'0未签到1已签到','must'=>1),
                    'dayStartTime'=>    array('type'=>'int','title'=>'时间','must'=>1),
                ))
            ),
        ),

        'getStatus'=>array(
            'title'=>'获取用户今天签到状态及奖励金币数',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>1,'list'=>array(
                    'todayIsSign'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'今天是否已签到 ，1是2否'),
                    'todayRewardGoldcoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'今日奖励金币数'),
                    'tomorrowRewardGoldcoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'明白奖励金币数'),
                ))
            ),
        ),


        'doing24'=>array(
            'title'=>'执行签到(24小时)',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'get24List'=>array(
            'title'=>'24小时签到列表',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'hour'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'小时 '),
                    'isSign'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'1已签2未签 '),
                    'current_hour'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'当前需要操作的小时 '),
                ))
            ),
        ),

        'getLotteryBoxList'=>array(
            'title'=>'24小时签到-宝箱列表',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
            ),
            'return'=>array(
                'array_key_number_two'=>array("must"=>0,'list'=>array(
                    'cnt'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'需要签到几次才能领取宝箱 '),
                    'rewardGold'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'奖励多少金币'),
                    'status'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'0置灰1可领导2已领取 '),
                    'id'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'开宝箱时用 '),
                ))
            ),
        ),

        'getLotteryBoxReward'=>array(
            'title'=>'24小时签到-开宝箱',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'具体哪个宝箱的ID'),
                'adId'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'广告ID '),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'add24Test'=>array(
            'title'=>'24小时签到-开宝箱-测试，增加签到数',
            'ws'=>array('request_code'=>1201,'response_code'=>1202),
            'request'=>array(
                'times'=>array('type'=>'int','must'=>1,'default'=>0,'title'=>'次数'),
                'uid'=>array('type'=>'int','must'=>0,'default'=>0,'title'=>'UID '),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'addAndGetReward'=>array(
//            'title'=>'签到并获取奖励',
//            'ws'=>array('request_code'=>1203,'response_code'=>1204),
//            'request'=>array(
//            ),
//            'return'=>array(
//            ),
//        ),
//
//        'userHistory'=>array(
//            'title'=>'签到记录',
//            'ws'=>array('request_code'=>1205,'response_code'=>1206),
//            'request'=>array(
//                'fix'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'补签'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),

    ),

    'task'=>array(
        'title'=>'任务',
        'getUserList'=>array(
            'ws'=>array('request_code'=>3003,'response_code'=>3004),
            'title'=>'获取用户-任务列表',
            'request'=>array(),
            'return'=>array(
//                'array_key_number_one'=>array("must"=>0,'list'=>array(
//                    'bntStatus'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'状态按钮1未完成2已完成3已领取'),
//                    'rewardGoldcoin'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'奖励金币数'),
//                    'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//                    'totalStep'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'完成任务总步骤'),
//                    'step'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前任务完成到第几步'),
//                    'title'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'标题'),
//                    'desc'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'描述'),
////                    'doneReward'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'已完成奖励的金币数'),
//                    'countdown'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'taskConfigId=7时，开宝箱倒计时unixstamp，0：距离上一次抽已超过了5分钟，不需要倒计时'),
//                ))


                'daily'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'bntStatus'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'状态按钮1未完成2已完成3已领取'),
                    'rewardGoldcoin'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'奖励金币数'),
                    'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
                    'totalStep'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'完成任务总步骤'),
                    'step'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前任务完成到第几步'),
                    'title'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'标题'),
                    'desc'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'描述'),
                    'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏id'),
//                    'doneReward'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'已完成奖励的金币数'),
                    'countdown'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'taskConfigId=7时，开宝箱倒计时unixstamp，0：距离上一次抽已超过了5分钟，不需要倒计时'),
                )),

                'growup'=>array("array_type"=>'array_key_number_two','must'=>0,'list'=>array(
                    'bntStatus'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'状态按钮1未完成2已完成3已领取'),
                    'rewardGoldcoin'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'奖励金币数'),
                    'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
                    'totalStep'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'完成任务总步骤'),
                    'step'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前任务完成到第几步'),
                    'title'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'标题'),
                    'desc'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'描述'),
//                    'doneReward'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'已完成奖励的金币数'),
                    'countdown'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'taskConfigId=7时，开宝箱倒计时unixstamp，0：距离上一次抽已超过了5分钟，不需要倒计时'),
                )),





            ),
        ),
        'init'=>array(
            'ws'=>array('request_code'=>3001,'response_code'=>3002),
            'title'=>'日常任务初始化，每次APP启动后，拿到TOKEN后，都要请求这个方法',
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),


        'trigger'=>array(
            'ws'=>array('request_code'=>3001,'response_code'=>3002),
            'title'=>'登陆接口，返回reg=1，证明是新用户，需要调用此接口，触发任务钩子',
            'request'=>array(
                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID，5：注册成功'),

            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),





//        'getUserReward'=>array(
//            'ws'=>array('request_code'=>3001,'response_code'=>3002),
//            'title'=>'用户领取奖励',
//            'request'=>array(
//                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//            ),
//            'return'=>array(
//                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//            ),
//        ),


    ),




//    'friend'=>array(
//        'title'=>'好友',
//
//        'addBlack'=>array(
//            'title'=>'好友拉黑',
//            'ws'=>array('request_code'=>8001,'response_code'=>8002),
//            'request'=>array(
//                'toUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'对方UID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//        'delBlack'=>array(
//            'title'=>'移除黑名单',
//            'ws'=>array('request_code'=>8003,'response_code'=>8004),
//            'request'=>array(
//                'toUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'对方UID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//        'getList'=>array(
//            'title'=>'好友列表',
//            'ws'=>array('request_code'=>8005,'response_code'=>8006),
//            'request'=>array(
//            ),
//            'return'=>array(
//            ),
//        ),
//
//
//
//
//        'apply'=>array(
//            'title'=>'添加好友',
//            'ws'=>array('request_code'=>8013,'response_code'=>8014),
//            'request'=>array(
//                'toUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'对方UID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//        'del'=>array(
//            'title'=>'单方面删除一个好友',
//            'ws'=>array('request_code'=>8015,'response_code'=>8016),
//            'request'=>array(
//                'toUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'对方UID'),
//            ),
//            'return'=>array(
//            ),
//        ),
//
//        'agree'=>array(
//            'title'=>'同意添加',
//            'ws'=>array('request_code'=>8017,'response_code'=>8018),
//            'request'=>array(
//                'fromUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'请求方UID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//        'deny'=>array(
//            'title'=>'拒绝添加',
//            'ws'=>array('request_code'=>8019,'response_code'=>8020),
//            'request'=>array(
//                'fromUid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'请求方UID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),
//
//    ),

   'advertise'=>array(
        'title'=>'广告位展示',
        'getGameAd'=>array(
            'title'=>'获取游戏内广告',
            'ws'=>array('request_code'=>9001,'response_code'=>9002),
            'request'=>array(
               'innerId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'内部广告位ID'),
           ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                   'advertiser'=>array('type'=>'int','must'=>1,'title'=>'广告商（1穿山甲）'),
                    'channel'=>array('type'=>'int','must'=>1,'title'=>'渠道'),
                    'outerId'=>array('type'=>'int','title'=>'第三方广告ID','must'=>1),
               ))
                
            ),
        ),

        'getAppAd'=>array(
            'title'=>'获取APP广告',
            'ws'=>array('request_code'=>9003,'response_code'=>9004),
            'request'=>array(
               'innerId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'内部广告位ID'),
           ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'advertiser'=>array('type'=>'int','must'=>1,'title'=>'广告商（1穿山甲）'),
                    'channel'=>array('type'=>'int','must'=>1,'title'=>'渠道'),
                    'outerId'=>array('type'=>'int','title'=>'第三方广告ID','must'=>1),
                    'state'=>array('type'=>'int','title'=>'显示开关（0关1开）','must'=>1),
               ))
            ),
        ),

    ),




);
$GLOBALS['api'] = $arr;