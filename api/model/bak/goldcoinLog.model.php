<?php
class GoldcoinLogModel {
	static $_table = 'goldcoin_log';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
	static $_db = null;

	static $_type_play_games = 1;
    static $_type_pay_game_goods = 11;
    static $_type_game_share_messenger = 15;// messenger;

    static $_type_play_games_20 = 2;

    static $_type_ad_sign = 20;
    static $_type_ad_play_game = 21;
    static $_type_ad_play_game_end = 22;
    static $_type_ad_open_box = 23;
    static $_type_ad_addition_sign = 24;
    static $_type_ad_addition_playing_game = 25;
    static $_type_ad_addition_game_end = 26;
    static $_type_ad_24_sign = 27;
    static $_type_ad_happy_lottery = 28;
    

    static $_type_ad_get_money = 200;
    static $_type_ad_sign_box = 201;

    static $_type_sign = 3;
    static $_type_30day_nologin_clear = 33;

    static $_type_friend_play_game = 4;


    static $_type_share_friend = 5;
    static $_type_task_first_play_game = 51;
    static $_type_task_first_open_wallet = 52;
    static $_type_task_first_follow= 53;
    static $_type_task_first_get_money = 54;
    static $_type_task_first_reg = 55;

    static $_type_share_income = 6;
    static $_type_share_sdk = 61;
    static $_type_share_get_money = 62;

    static $_type_luck_box = 7;
    static $_type_rand_luck_box_step1 = 71;
    static $_type_rand_luck_box_share_wx = 72;
    static $_type_rand_luck_box_share_qq = 73;
    // H5游戏新增分享统计数功能用于管理后台显示 Begin;
    static $_type_h5_game_share_wechat_single = 74;
    static $_type_h5_game_share_wechat_platform = 75;
    static $_type_h5_game_share_qq_single = 76;
    static $_type_h5_game_share_qq_platform = 77;
    static $_type_game_share_add_friends = 78;
    static $_type_game_share_sdk_byapp = 79;


    static $_type_luck_lottery = 8;
    static $_type_game_share_sdk_direct = 80;
    static $_type_goldcoin_carnival = 81;
    static $_type_goldcoin_carnival_less = 82;
    static $_type_game_share_toUid = 83;
    static $_type_gold_user_rank_first = 84;


    // H5游戏新增分享统计数功能用于管理后台显示   End;
    static $_type_gold_exchange_money = 9;
    static $_type_use_coupon_gold_exchange_money = 91;
    static $_type_get_money_back = 91;
    static $_type_goldcoin_exchange_lucky_jetton = 92;
    static $_type_goldcoin_exchange_lucky_balance = 93;

    // 海外版APP增加四个分享动作（暂时不用）；
    static $_type_game_share_games = 95;
    static $_type_game_share_friends = 96;
    static $_type_game_share_deposit = 97;
    static $_type_game_share_luck_box = 98;

    // 新增任务项目 time:2019/06/26;
    static  $_type_share_game_get_goldcoin = 40;// 新手
    static  $_type_share_game_3_day_get_goldcoin = 41;// 新手
    static  $_type_perfect_information = 42;// 新手
    static  $_type_play_5_diff_games = 43;// 日常
    static  $_type_play_appoint_game = 44;// 日常
    static  $_type_play_flip_cards = 45;// 日常
    static  $_type_play_big_wheel = 46;// 日常
    static  $_type_play_big_wheel_box = 47;// 开心大轮盘开宝箱奖励

    // 每日福利宝箱
    static $_type_daily_lottery_box = 100;
    // 每日福利红包
    static $_type_daily_red_packet = 110;

    static function getTypeDesc(){
        return array(

//            self::$_type_ad_sign=>'看视频的奖励-签到',
//            self::$_type_ad_play_game=>'看视频的奖励-玩游戏中',
//            self::$_type_ad_play_game_end=>'看视频的奖励-玩游戏结束',



            self::$_type_pay_game_goods=>'购买游戏内道具',
            self::$_type_ad_open_box=>'看视频的奖励-开宝箱',

            self::$_type_ad_addition_sign=>'看视频得奖励',
            self::$_type_ad_addition_playing_game=>'看视频得奖励',
            self::$_type_ad_addition_game_end=>'看视频得奖励',


            self::$_type_sign=>'每日签到连续获得的奖励',
            self::$_type_friend_play_game=>'好友做任务贡献的奖励',
            self::$_type_gold_exchange_money=>'请到提现记录中查看详情',

            self::$_type_play_games_20=>'每日游戏达20分钟奖励',
            self::$_type_play_games=>'每日玩游戏奖励',
            self::$_type_share_friend=>'每日分享给好友奖励',
            self::$_type_share_income=>'每日晒收入奖励',
            self::$_type_share_sdk=>'SDK内分享',

            self::$_type_luck_box=>'每日开宝箱奖励',

            self::$_type_rand_luck_box_step1=>'开宝箱',
            self::$_type_rand_luck_box_share_wx=>'开宝箱-分享到微信',
            self::$_type_rand_luck_box_share_qq=>'开宝箱-分享到QQ',



            self::$_type_luck_lottery=>'每日抽奖获得的奖励',
            self::$_type_goldcoin_carnival=>'金币欢乐送活动获得奖励',
            self::$_type_goldcoin_carnival_less=>'金币欢乐送活动抽奖消耗',

            self::$_type_task_first_play_game=>'首次玩游戏超过2分钟的奖励',
            self::$_type_task_first_open_wallet=>'查看钱包的奖励',
            self::$_type_task_first_follow=>'关注第一个小伙伴奖励',
            self::$_type_task_first_get_money=>'首次提现成功奖励',
            self::$_type_task_first_reg=>'新用户首次注册成功奖励',


            self::$_type_30day_nologin_clear=>'30天未登录扣金币',

            self::$_type_get_money_back=>'提现失败原路返还金币',
            self::$_type_share_get_money => '提现分享',


            self::$_type_ad_24_sign => '签到成功奖励',
            self::$_type_ad_happy_lottery =>'开心抽一抽抽中金币',

            self::$_type_use_coupon_gold_exchange_money => '请到提现记录中查看详情',


            self::$_type_game_share_add_friends => '添加好友',
            self::$_type_game_share_sdk_byapp => 'sdk调用app分享',
            self::$_type_game_share_sdk_direct => 'app直接分享',
            self::$_type_game_share_toUid => '站内好友',

            self::$_type_goldcoin_exchange_lucky_jetton => '幸运趣刮刮筹码转换为金币',
            self::$_type_goldcoin_exchange_lucky_balance => '幸运趣刮刮中奖获得的奖励',

            // 新增任务项目; time:2019/06/26;
            self::$_type_share_game_get_goldcoin => '分享游戏给好友奖励',
            self::$_type_share_game_3_day_get_goldcoin => '连续3天分享游戏奖励',
            self::$_type_perfect_information => '完善资料的奖励',
            self::$_type_play_5_diff_games => '每天玩5款不同游戏',
            self::$_type_play_appoint_game => '玩指定游戏5分钟',

            self::$_type_gold_user_rank_first=>'#date#获得金币最多的用户',

            self::$_type_daily_lottery_box=>'每日福利宝箱奖励',
            self::$_type_daily_red_packet=>'每日福利红包',

            self::$_type_ad_sign_box => '开宝箱奖励',


            self::$_type_ad_get_money=>'提现看广告',

            // 新增任务项目task_id = 24 by XiaHB time:2019/07/04;
            self::$_type_play_flip_cards=>'翻翻卡获得奖励',
            self::$_type_play_big_wheel=>'开心大轮盘奖励',
            self::$_type_play_big_wheel_box=>'开心大轮盘开宝箱奖励',


            );
    }

    static function getTypeTitle(){
        return array(
            self::$_type_pay_game_goods=>'购买游戏内道具',


            self::$_type_ad_sign=>'额外奖励',
            self::$_type_ad_play_game=>'额外奖励',
            self::$_type_ad_play_game_end=>'额外奖励',
            self::$_type_ad_open_box=>'额外奖励',


            self::$_type_ad_addition_sign=>'额外收益',
            self::$_type_ad_addition_playing_game=>'额外收益',
            self::$_type_ad_addition_game_end=>'额外收益',

            self::$_type_sign=>'每日签到',
            self::$_type_friend_play_game=>'好友贡献收益',
            self::$_type_gold_exchange_money=>'提现兑换',

            self::$_type_play_games_20=>'游戏收益',
            self::$_type_play_games=>'游戏收益',
            self::$_type_share_friend=>'任务收益',
            self::$_type_share_income=>'任务收益',
            self::$_type_share_sdk=>'SDK内分享',
            self::$_type_luck_box=>'开宝箱',

            self::$_type_rand_luck_box_step1=>'任务收益',
            self::$_type_rand_luck_box_share_wx=>'任务收益',
            self::$_type_rand_luck_box_share_qq=>'任务收益',


            self::$_type_luck_lottery=>'幸运大抽奖',
            self::$_type_goldcoin_carnival=>'金币欢乐送',
            self::$_type_goldcoin_carnival_less=>'金币欢乐送',

            self::$_type_task_first_play_game=>'任务收益',
            self::$_type_task_first_open_wallet=>'任务收益',
            self::$_type_task_first_follow=>'任务收益',
            self::$_type_task_first_get_money=>'任务收益',
            self::$_type_task_first_reg=>'任务收益',

            self::$_type_30day_nologin_clear=>'金币回收',

            self::$_type_get_money_back=>'金币返还',

            self::$_type_share_get_money => '提现分享',

            self::$_type_ad_24_sign => '签到奖励',
            self::$_type_ad_happy_lottery =>'金币奖励',

            self::$_type_use_coupon_gold_exchange_money => '提现兑换',


            self::$_type_game_share_add_friends => '添加好友',
            self::$_type_game_share_sdk_byapp = 'sdk调用app分享',
            self::$_type_game_share_sdk_direct = 'app直接分享',
            self::$_type_game_share_toUid = '站内好友',

            
            self::$_type_goldcoin_exchange_lucky_jetton => '幸运趣刮刮-筹码兑换',
            self::$_type_goldcoin_exchange_lucky_balance => '幸运趣刮刮-福利奖励',

            // 新增任务项目; time:2019/06/26;
            self::$_type_share_game_get_goldcoin => '任务收益',
            self::$_type_share_game_3_day_get_goldcoin => '任务收益',
            self::$_type_perfect_information => '任务收益',
            self::$_type_play_5_diff_games => '任务收益',
            self::$_type_play_appoint_game => '任务收益',


            self::$_type_gold_user_rank_first=>'奖金排行榜-每日冠军奖励',

            self::$_type_ad_sign_box => '签到奖励',

            self::$_type_daily_lottery_box => '每日福利宝箱',
            self::$_type_daily_red_packet => '每日福利红包',

            // 新增任务项目task_id = 24 by XiaHB time:2019/07/04;
            self::$_type_play_flip_cards=>'任务收益',
            self::$_type_play_big_wheel=>'任务收益',
            self::$_type_play_big_wheel_box=>'额外奖励',
        );
    }

    // 海外版APP类型描述;
    static function getForeignTypeDesc(){
        return array(

//            self::$_type_ad_sign=>'看视频的奖励-签到',
//            self::$_type_ad_play_game=>'看视频的奖励-玩游戏中',
//            self::$_type_ad_play_game_end=>'看视频的奖励-玩游戏结束',



            self::$_type_pay_game_goods=>'Buy in game items',
            self::$_type_ad_open_box=>'Daily treasure chest reward',

            self::$_type_ad_addition_sign=>'The reward of watching video',
            self::$_type_ad_addition_playing_game=>'The reward of watching video',
            self::$_type_ad_addition_game_end=>'The reward of watching video',


            self::$_type_sign=>'Rewards for opening the chest after check-in',
            self::$_type_friend_play_game=>'A reward a friend gets for doing a task',
            self::$_type_gold_exchange_money=>'Check the exchange record for details',

            self::$_type_play_games_20=>'Reward for playing the game for more than 20 minutes',
            self::$_type_play_games=>'Rewards for daily game play',
            self::$_type_share_friend=>'Rewards Shared daily with friends',
            self::$_type_share_income=>'Open the chest-Share on facebook',
            self::$_type_share_sdk=>'Within the SDK to share',

            self::$_type_luck_box=>'Daily treasure chest reward',

            self::$_type_rand_luck_box_step1=>'Open the chest',
            self::$_type_rand_luck_box_share_wx=>'Open the chest-Share on Facebook',
            self::$_type_rand_luck_box_share_qq=>'Open the chest-Share on Messenger',



            self::$_type_luck_lottery=>'The award of the daily draw',
            self::$_type_goldcoin_carnival=>'Gold coin happy send activity gets award',
            self::$_type_goldcoin_carnival_less=>'Gold coin happy send activity lucky draw is used up',

            self::$_type_task_first_play_game=>'Reward for playing the game for more than 2 minutes for the first time',
            self::$_type_task_first_open_wallet=>'View Wallet Rewards',
            self::$_type_task_first_follow=>'Follow the first friend award',
            self::$_type_task_first_get_money=>'Reward for first withdrawal success',
            self::$_type_task_first_reg=>'New user first sign up for successful Rewards',


            self::$_type_30day_nologin_clear=>'30 days without login to deduct tokens',

            self::$_type_get_money_back=>'The exchange failed and the tokens was returned',
            self::$_type_share_get_money => 'Withdrawal share',


            self::$_type_ad_24_sign => 'Rewards for opening the chest after check-in',
            self::$_type_ad_happy_lottery =>'Happy big turn dish draws medium gold coin',

            self::$_type_use_coupon_gold_exchange_money => 'Please go to the withdrawal record for details',


            self::$_type_game_share_add_friends => 'Add friends',
            self::$_type_game_share_sdk_byapp => 'SDK calls app share',
            self::$_type_game_share_sdk_direct => 'App direct sharing',
            self::$_type_game_share_toUid => 'On site friends',

            // 新加分享类型（暂时不用）; add by XiaHB 2019/06/03 Begin;
            self:: $_type_game_share_games => 'Share games',
            self:: $_type_game_share_friends => 'Share Friends',
            self:: $_type_game_share_deposit => 'Share Deposit',
            self:: $_type_game_share_luck_box => 'Share Open luck_box',
            // 新加分享类型（暂时不用）; add by XiaHB 2019/06/03   End;
            self:: $_type_game_share_messenger => 'Open the chest-Share on Messenger'


        );
    }

    // 海外版APP标题描述;
    static function getForeignTypeTitle(){
        return array(
            self::$_type_pay_game_goods=>'Buy in-game items',


            self::$_type_ad_sign=>'Added bonus',
            self::$_type_ad_play_game=>'Added bonus',
            self::$_type_ad_play_game_end=>'Added bonus',
            self::$_type_ad_open_box=>'open the chest',


            self::$_type_ad_addition_sign=>'Extra reward',
            self::$_type_ad_addition_playing_game=>'Extra reward',
            self::$_type_ad_addition_game_end=>'Extra reward',

            self::$_type_sign=>'Chest reward',
            self::$_type_friend_play_game=>'Friend contribution benefit',
            self::$_type_gold_exchange_money=>'Exchange',

            self::$_type_play_games_20=>'Game reward',
            self::$_type_play_games=>'Game reward',
            self::$_type_share_friend=>'Mission reward',
            self::$_type_share_income=>'Mission reward',
            self::$_type_share_sdk=>'SDK to share',
            self::$_type_luck_box=>'Open the chest',

            self::$_type_rand_luck_box_step1=>'Open the chest',
            self::$_type_rand_luck_box_share_wx=>'Mission reward',
            self::$_type_rand_luck_box_share_qq=>'Mission reward',


            self::$_type_luck_lottery=>'Free wheel',
            self::$_type_goldcoin_carnival=>'Gold coin happy send',
            self::$_type_goldcoin_carnival_less=>'Gold coin happy send',

            self::$_type_task_first_play_game=>'Mission reward',
            self::$_type_task_first_open_wallet=>'Mission reward',
            self::$_type_task_first_follow=>'Mission reward',
            self::$_type_task_first_get_money=>'Task earnings',
            self::$_type_task_first_reg=>'Mission reward',

            self::$_type_30day_nologin_clear=>'Tokens recovery',

            self::$_type_get_money_back=>'Return of tokens',

            self::$_type_share_get_money => 'Withdrawal share',

            self::$_type_ad_24_sign => 'Chest reward',
            self::$_type_ad_happy_lottery =>'Gold award',

            self::$_type_use_coupon_gold_exchange_money => 'Cash conversion',


            self::$_type_game_share_add_friends => 'Add friends',
            self::$_type_game_share_sdk_byapp = 'SDK calls app share',
            self::$_type_game_share_sdk_direct = 'App direct sharing',
            self::$_type_game_share_toUid = 'On site friends',

            // 新加分享类型（暂时不用）; add by XiaHB 2019/06/03 Begin;
            self::$_type_game_share_games => 'Share games',
            self::$_type_game_share_friends => 'Share Friends',
            self::$_type_game_share_deposit => 'Share Deposit',
            self::$_type_game_share_luck_box => 'Share Open luck_box',
            // 新加分享类型（暂时不用）; add by XiaHB 2019/06/03   End;
            self:: $_type_game_share_messenger => 'Mission reward'

        );
    }

    static function getTypeDescByKey($key){
        if(!self::getTypeDesc($key)){
            return "未知";
        }
        $arr = self::getTypeDesc();
        return $arr[$key];
    }

    static function getTypeTitleByKey($key){
        if(!self::getTypeTitle($key)){
            return "未知";
        }
        $arr = self::getTypeTitle();
        return $arr[$key];
    }



    static function keyInType($key){
        $arr = self::getTypeDesc();
        foreach ($arr as $k=>$v) {
            if($key == $k){
                return 1;
            }
        }

        return 0;
    }


	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
}