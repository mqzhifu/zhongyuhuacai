<?php
$key = array(
    //消息未读数  暂时不用
    'msgUnread'=>array('key'=>'msgUnread','expire'=>0),
    //好友 贡献的 总收益
    'friend_income'=>array('key'=>'friend_income','expire'=> 0),
    //每个好友 贡献的 收益汇总
    'friend_give_goldcoin'=>array('key'=>'friend_give_goldcoin','expire'=> 0),
    //sdk 给开发者 的数据
    'sdkUserData'=>array('key'=>'sdkUserData','expire'=>0),
    //用户最近3天 金币日志 , 过期，脚本清除
    'goldcoin_3_log'=>array('key'=>'goldcoin_3_log','expire'=>0),
    //用户玩过的游戏
    'played_game'=>array('key'=>'played_game','expire'=> 0),
    //单个用户，每天，活跃 列表
    'day_active_user'=>array('key'=>'day_active_user','expire'=>  0),
    //当前在线总人数
    'online_user_total'=>array('key'=>'online_user_total','expire'=>  0),
    //用户成长任务列表
    'growup_task_day'=>array('key'=>'growup_task_day','expire'=> 0),
    //=======  以上待处理============


    //提现口令
    'money_token_day'=>array('key'=>'money_token_day','expire'=> 24*60*60),
    //wechat
    'wx_access_token'=>array('key'=>'wx_access_token','expire'=> 7000),
    'wx_js_api_ticket'=>array('key'=>'wx_js_api_ticket','expire'=> 7000),
    //lucky0点，11点，18点展示卡牌刷新
    'lucky_day_three_times'=>array('key'=>'lucky_day_three_times','expire'=> 24 * 60 * 60),
    //ip访问记录(有序集合)
    'ip_access_set'=>array('key'=>'ip_access_set','expire'=> 24*60*60),
    //每日宝箱次数
    'daily_box_times'=>array('key'=>'daily_box_times','expire'=> 24*60*60),
    // 开心翻翻卡单人单日次数限制（50'）;
    'flip_Cards_times' => array('key'=>'daily_box_times', 'expire'=> 24 * 60 * 60),
    // 开心翻翻卡单人单日次数限制（100'）;
    'happy_big_wheel_times' => array('key'=>'happy_big_wheel_times', 'expire'=> 24 * 60 * 60),
    //
    'happy_big_wheel_box' => array('key'=>'happy_big_wheel_box', 'expire'=> 24 * 60 * 60),

    //============================以上待确认===============


    //用户个人信息
    'userinfo'=>array('key'=>'uinfo','expire'=> 3 * 24 * 60 * 60),
    //登陆token
    'token'=>array('key'=>'token','expire'=> 30 * 24 * 60 * 60),
    //提现时候的  锁
    'getMoneyLock'=>array('key'=>'moneyLock','expire'=>10),
    //签到时候的 锁
    'signLock'=>array('key'=>'signLock','expire'=>5),
    //当天玩游戏获取的金币总数
    'today_playgame_gold'=>array('key'=>'today_playgame_gold','expire'=> 25 * 60 * 60),
    //当天玩游戏的总时长
    'today_playgame_time'=>array('key'=>'today_playgame_time','expire'=> 25 * 60 * 60),
    //当天获取总金币数
    'today_sum_gold'=>array('key'=>'today_sum_gold','expire'=> 25 * 60 * 60),
    //幸运大抽奖 每天 免费次数 是否已经使用过
    'lottery_today_freetime'=>array('key'=>'lottery_today_freetime','expire'=> 24 * 60 * 60),
    //游戏每天玩的人数的UV
    'game_uv'=>array('key'=>'game_uv','expire'=> 10 * 60),
    //用户日常任务列表
    'daily_task_day'=>array('key'=>'daily_task_day','expire'=> 24 * 60 * 60),
    //用户推荐，每10分钟，更新一次排序值
    'recomm_rand_order'=>array('key'=>'recomm_rand_order','expire'=> 10*60 ),
    //玩 单个 游戏 时长
    'p_s_g_time'=>array('key'=>'p_s_g_time','expire'=>  25*60*60 ),
    //大转盘，玩游戏5分钟抽一次，5分钟游戏时间池
    'hap_lot_time'=>array('key'=>'hap_lot_time','expire'=>  24*60*60),
    //每日签到获取的宝箱
    'day_sign_box'=>array('key'=>'day_sign_box','expire'=> 24*60*60),
    //每日金币排行榜-每10分钟刷新一次
    'day_gold_rank'=>array('key'=>'day_gold_rank','expire'=> 24*60*60),
    //每日福利红包
    'daily_red_packets'=>array('key'=>'daily_red_packets','expire'=> 24*60*60),
    // 用户累计获得金币数;（add by XiaHB time:2019/07/01）
    'additive_sum_gold'=>array('key'=>'additive_sum_gold','expire'=> 24 * 60 * 60),
    //每天活跃总用户列表.因为有websocket 长连接，按天统计，此功能无用了
//    'everyday_active_user'=>array('key'=>'everyday_active_user','expire'=>  0),

    //API 接口统计次数 黑名单，已不用
    //    'cntUserReq'=>array('key'=>'cntUserReq','expire'=>0),
    //API IP 黑名单 已不用
    //    'blackip'=>array('key'=>'blackip','expire'=>0),
    //API 接口统计次数 黑名单 已不用
    //    'cntIPReq'=>array('key'=>'cntIPReq','expire'=>0),

);
$GLOBALS['rediskey'] = $key;

