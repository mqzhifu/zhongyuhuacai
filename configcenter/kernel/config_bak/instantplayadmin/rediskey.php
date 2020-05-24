<?php
$key = array(
//    'sms'=>array('key'=>'sendsms','expire'=>0),
//    'heartbeat'=>array('key'=>'heartbeat','expire'=>0),
//    'eventMsg'=>array('key'=>'eventMsg','expire'=>0),
//    'gameActionCnt'=>array('key'=>'game_cnt','expire'=>0),


    //用户个人信息
    'userinfo'=>array('key'=>'uinfo','expire'=>0 ),
    //登陆token
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
    //API 接口统计次数 黑名单
    'cntUserReq'=>array('key'=>'cntUserReq','expire'=>0),
    //API 接口统计次数 黑名单
    'blackip'=>array('key'=>'blackip','expire'=>0),
    //消息未读数
    'msgUnread'=>array('key'=>'msgUnread','expire'=>0),
    //提现时候的  锁
    'getMoneyLock'=>array('key'=>'moneyLock','expire'=>10),
    //签到时候的 锁
    'signLock'=>array('key'=>'signLock','expire'=>5),
    //sdk 给开发者 的数据
    'sdkUserData'=>array('key'=>'sdkUserData','expire'=>0),
    //用户最近3天 金币日志
    'goldcoin_3_log'=>array('key'=>'goldcoin_3_log','expire'=>0),
    //当天玩游戏获取的金币总数
    'today_playgame_gold'=>array('key'=>'today_playgame_gold','expire'=> 24 * 60 * 60),
    //当天玩游戏的总时长
    'today_playgame_time'=>array('key'=>'today_playgame_time','expire'=> 24 * 60 * 60),
    //幸运大抽奖 每天 免费次数 是否已经使用过
    'lottery_today_freetime'=>array('key'=>'lottery_today_freetime','expire'=> 24 * 60 * 60),
    //好友 贡献的 总收益
    'friend_income'=>array('key'=>'friend_income','expire'=> 0),
    //每个好友 贡献的 收益汇总
    'friend_give_goldcoin'=>array('key'=>'friend_give_goldcoin','expire'=> 0),
    //游戏每天玩的人数的UV
    'game_uv'=>array('key'=>'game_uv','expire'=> 10 * 60),
    //用户玩过的游戏
    'played_game'=>array('key'=>'played_game','expire'=> 0),
    //用户日常任务列表
    'daily_task_day'=>array('key'=>'daily_task_day','expire'=> 24 * 60 * 60),
    //用户成长任务列表
    'growup_task_day'=>array('key'=>'growup_task_day','expire'=> 0),

);
$GLOBALS['rediskey'] = $key;

