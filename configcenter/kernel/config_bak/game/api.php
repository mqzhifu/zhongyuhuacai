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
            'ws'=>array('request_code'=>1003,'response_code'=>1004),
            'title'=>'获取WS，IP跟端口号',
            'request'=>array(
                'channel'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'渠道'),
            ),
            'return'=>array(
                'ip'=>    array('type'=>'string','title'=>'IP','must'=>1),
                'domain'=>    array('type'=>'string','title'=>'域名','must'=>1),
                'port'=>  array('type'=>'int','title'=>'PORT','must'=>1),
            ),
        ),
        'getUinfo'=>array(
            'ws'=>array('request_code'=>1005,'response_code'=>1006),
            'title'=>'获取用户信息',
            'request'=>array(
            ),
            'return'=>array(
                'status'=>    array('type'=>'int','title'=>'状态1正常2匹配中3游戏中','must'=>1),
                'fd'=>    array('type'=>'int','title'=>'fd','must'=>1),
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1),
                'loginTime'=>    array('type'=>'int','title'=>'登陆时间','must'=>1),
            ),
        ),

        'getRoomInfo'=>array(
            'ws'=>array('request_code'=>1007,'response_code'=>1008),
            'title'=>'获取房间信息',
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1),
            ),
            'return'=>array(
                'fromUser'=>    array('type'=>'string','title'=>'用户1信息','must'=>1),
                'toUser'=>    array('type'=>'string','title'=>'用户2信息','must'=>1),
                'appTypeId'=>    array('type'=>'int','title'=>'appTypeId','must'=>1),
                'appId'=>    array('type'=>'int','title'=>'appId','must'=>1),
                'status'=>    array('type'=>'int','title'=>'1未开始2已开始3已结束','must'=>1),
                'result'=>    array('type'=>'int','title'=>'游戏结束1赢2输3平','must'=>1),
                'gameStartTime'=>    array('type'=>'int','title'=>'游戏开始时间','must'=>1),
                'gameEndTime'=>    array('type'=>'int','title'=>'游戏结束时间','must'=>1),
            ),
        ),

        'getRoomRsyncMsg'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'获取房间消息同步信息',
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1),
            ),
            'return'=>array(
                'uid'=>    array('type'=>'int','title'=>'状态1正常2匹配中3游戏中','must'=>1),
                'score'=>    array('type'=>'int','title'=>'fd','must'=>1),
                'aTime'=>    array('type'=>'int','title'=>'登陆时间','must'=>1),
            ),
        ),

        'clearUserInfoRoomInfo'=>array(
            'ws'=>array('request_code'=>1011,'response_code'=>1012),
            'title'=>'清除用户状态、报名信息、匹配信息、房间信息',
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

    ),

    'asynTask'=>array(
        'title'=>'异步任务',
        'rsyncMsgWriteDB'=>array(
            'ws'=>array('request_code'=>7001,'response_code'=>70002),
            'title'=>'游戏消息同步持久化',
            'request'=>array(
                'data'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'数据'),
            ),
            'return'=>array(
            ),
        ),
    ),


    'login'=>array(
        'title'=>'登陆',

        'getToken'=>array(
            'ws'=>array('request_code'=>2001,'response_code'=>2002),
            'title'=>'SDK登陆，短连接获取TOKEN，用来长连接',
            'request'=>array(
                'appId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'appid'),
                'uid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'用户ID'),
                'ps'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'密码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
            ),
        ),


        'webSocketLogin'=>array(
            'ws'=>array('request_code'=>2003,'response_code'=>2004),
            'title'=>'用短连接获取的token,长连接登陆验证.(uid绑定FD)',
            'request'=>array(
                'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短连接获取的登陆的凭证TOKEN'),
            ),
            'return'=>array(
            ),
        ),

        'onClose'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'断开WS连接/清FD、容错比赛',
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),
    ),

    'game'=>array(
        'title'=>'游戏信息',

//        'getGameEndTotalInfo'=>array(
//            'title'=>'获取用户结算信息',
//            'ws'=>array('request_code'=>6009,'response_code'=>6010),
//            'request'=>array(
//                'roomId'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'房间ID，二次请求时，服务端返回的ID值'),
//            ),
//            'return'=>array(
//                'bestScore'=>    array('type'=>'string','title'=>'最佳战绩','must'=>1,'default'=>1),
//                'vsHistory'=>    array('type'=>'string','title'=>'对战历史成绩','must'=>1,'default'=>1),
//            ),
//        ),



        'getGameRank'=>array(
            'title'=>'统计',
            'ws'=>array('request_code'=>3001,'response_code'=>3002),
            'request'=>array(
            ),
            'return'=>array(
                'todayRank'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'今日自己的排名'),
                'todayWinCnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'今日自己的获胜场次'),
                'totalRank'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'自己的总排名'),
                'totalWinCnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'自己的总获胜场次'),


                'todayList'=>array("is_array"=>1,"must"=>0,'list'=>array(
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1,'default'=>1),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'uid'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户UID'),
                    'rank'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'排名'),
                    'winCnt'=> array('type'=>'int','must'=>1,'default'=>1,'title'=>'胜利场数'),
                ),),

                'totalList'=>array("is_array"=>1,"must"=>0,'list'=>array(
                    'nickname'=>    array('type'=>'string','title'=>'昵称','must'=>1,'default'=>1),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'uid'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户UID'),
                    'rank'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'排名'),
                    'winCnt'=> array('type'=>'int','must'=>1,'default'=>1,'title'=>'胜利场数'),
                ),),

            ),
        ),


        'getUserGameTotalInfo'=>array(
            'title'=>'用户每个用户的，历史汇总信息:玩过的次数、排名',
            'ws'=>array('request_code'=>3003,'response_code'=>3004),
            'request'=>array(
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID，目前传入1就行'),
            ),
            'return'=>array(
                'winCnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'总获胜次数'),
                'todayRank'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当日排名'),
            ),
        ),
    ),


    'gameMatch'=>array(
        'title'=>'游戏/匹配',

        'userMatchSign'=>array(
            'title'=>'报名/用户进入匹配',
            'ws'=>array('request_code'=>6001,'response_code'=>6002),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类，实际就是游戏ID，后台设置'),
                'userSex'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户性别1男2女'),
            ),
            'return'=>array(
            ),
        ),

        'cancelMatch'=>array(
            'title'=>'取消匹配',
            'ws'=>array('request_code'=>6003,'response_code'=>6004),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类，实际就是游戏ID，后台设置'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID，目前传入1就行'),
            ),
            'return'=>array(
            ),
        ),

        'realtimeRsyncMsg'=>array(
            'title'=>'实时同步消息',
            'ws'=>array('request_code'=>6005,'response_code'=>6006),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
                'score'=>    array('type'=>'string','title'=>'成绩','must'=>1,'default'=>1),
            ),
            'return'=>array(
            ),
        ),

        'userLeaveGameRoom'=>array(
            'title'=>'用户离开房间，需要通知另外一个玩家，用户已离开',
            'ws'=>array('request_code'=>6007,'response_code'=>6008),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
            ),
            'return'=>array(
            ),
        ),

        'gameEndTotal'=>array(
            'title'=>'C端主动发起，游戏结算请求',
            'ws'=>array('request_code'=>6011,'response_code'=>6012),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
                'rs'=>    array('type'=>'string','title'=>'比赛结束1赢2输3平','must'=>1,'default'=>1),
            ),
            'return'=>array(
            ),
        ),

        'againGameApply'=>array(
            'title'=>'申请再来一局',
            'ws'=>array('request_code'=>6013,'response_code'=>6014),
            'request'=>array(
                'roomId'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'房间ID，匹配成功后，S端PUSH的'),
            ),
            'return'=>array(
            ),
        ),

        'agreeAgainGameApply'=>array(
            'title'=>'同意再来一局',
            'ws'=>array('request_code'=>6015,'response_code'=>6016),
            'request'=>array(
                'roomId'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'房间ID，匹配成功后，S端PUSH的'),
            ),
            'return'=>array(
            ),
        ),

        'startGame'=>array(
            'title'=>'开始游戏',
            'ws'=>array('request_code'=>6017,'response_code'=>6018),
            'request'=>array(
                'roomId'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'房间ID，匹配成功后，S端PUSH的'),
            ),
            'return'=>array(
            ),
        ),

        'reConnect'=>array(
            'title'=>'断线重连',
            'ws'=>array('request_code'=>6019,'response_code'=>6020),
            'request'=>array(
                'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短连接获取的登陆的凭证TOKEN'),
            ),
            'return'=>array(
            ),
        ),

    ),

    'push'=>array(
        'title'=>'S端主动PUSH-C端-WS消息',

        'matchTimeout'=>array(
            'title'=>' 匹配超时(失败) ',
            'ws'=>array('request_code'=>7001,'response_code'=>7002),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'matchedUser'=>array(
            'title'=>'人已OK，房已开好,等待确认，开始游戏',
            'ws'=>array('request_code'=>7003,'response_code'=>7004),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
                'fromUid'=>    array('type'=>'int','title'=>'用户1','must'=>1,'default'=>1),
                'toUid'=>    array('type'=>'int','title'=>'用户2','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'gameStart'=>array(
            'title'=>'人到了房也开了，赶紧开始游戏',
            'ws'=>array('request_code'=>7005,'response_code'=>7006),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
                'fromUid'=>    array('type'=>'int','title'=>'用户1','must'=>1,'default'=>1),
                'toUid'=>    array('type'=>'int','title'=>'用户2','must'=>1,'default'=>1),
                'toAvatar'=>    array('type'=>'string','title'=>'对方头像','must'=>1,'default'=>1),
                'toNickName'=>    array('type'=>'string','title'=>'对方昵称','must'=>1,'default'=>1),
                'toThirdUid'=>array('type'=>'string','title'=>'对方-3方(facebook)平台UID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'gameEndTotal'=>array(
            'title'=>'比赛结算',
            'ws'=>array('request_code'=>7007,'response_code'=>7008),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'againGameApply'=>array(
            'title'=>'对手申请再来局，更新button',
            'ws'=>array('request_code'=>7009,'response_code'=>7010),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'realtimeRsyncMsg'=>array(
            'title'=>'同步比分',
            'ws'=>array('request_code'=>7011,'response_code'=>7012),
            'request'=>array(
                'roomId'=>    array('type'=>'string','title'=>'房间ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'userLeaveGameRoom'=>array(
            'title'=>'游戏结束后，某用户离开',
            'ws'=>array('request_code'=>7013,'response_code'=>7014),
            'request'=>array(
                'toUid'=>    array('type'=>'int','title'=>'离开的用户ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'userOffLine'=>array(
            'title'=>'某用户已断线',
            'ws'=>array('request_code'=>7015,'response_code'=>7016),
            'request'=>array(
                'toUid'=>    array('type'=>'int','title'=>'离开的用户ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

        'userReconnect'=>array(
            'title'=>'某用户已重连',
            'ws'=>array('request_code'=>7017,'response_code'=>7018),
            'request'=>array(
                'toUid'=>    array('type'=>'int','title'=>'离开的用户ID','must'=>1,'default'=>1),
            ),
            'return'=>array(

            ),
        ),

    ),

);
$GLOBALS['api'] = $arr;