<?php
class PlayedGamesModel {
	static $_table = 'played_games';
	static $_pk = 'id';
	static $_db_key = DEF_DB_CONN;
    static $_db = null;
    // 开始游戏来源
    static $_src_im = 1;
    static $_src_home_page = 2;
    static $_src_person_page_self = 3;
    static $_src_person_page_other = 4;
    static $_src_watch_ad_win_game = 5;

    static $_src_window_sign_in = 6;
    static $_src_window_kxdzp_win_gold = 7;
    static $_src_window_look_earnings_in_game = 8;
    static $_src_window_look_total_earnings_out_game = 9;
    static $_src_window_get_extra_earnings_in_game = 10;
    static $_src_window_get_extra_earnings_out_game = 11;

    static $_src_window_watch_ad_open_box = 12;
    static $_src_window_lucky_draw_lottery = 13;
    static $_src_window_share_to_friend = 14;
    static $_src_window_show_income = 15;
    static $_src_window_play_game_twenty_min = 16;
    static $_src_window_play_game_two_min = 17;
    static $_src_window_check_wallet = 18;
    static $_src_window_first_withdraw_deposit = 19;
    static $_src_window_follow_first_partner = 20;
    static $_src_banner = 21;
	static function db(){
		if(self::$_db)
			return self::$_db;
		
		self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}
	
	static function login($uname,$ps){
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getById($uid){
        $user = self::db()->getById($uid);
        if($user){

        }
    }

    static function getSrcDesc(){
    	return array(
    		self::$_src_home_page=>"首页 游戏列表",
    		self::$_src_im=>"im会话",
    		self::$_src_person_page_other=>"个人主页别人",
    		self::$_src_person_page_self=>"个人主页自己",
    		self::$_src_watch_ad_win_game=>"看视频开宝箱随机抽中一个游戏",
    		self::$_src_window_check_wallet=>"查看钱包",
    		self::$_src_window_first_withdraw_deposit=>"首次体现成功",
    		self::$_src_window_follow_first_partner=>"关注第一个小伙伴",
    		self::$_src_window_get_extra_earnings_in_game=>"游戏中获得游戏收益",
    		self::$_src_window_get_extra_earnings_out_game=>"游戏外获得额外收益",
    		self::$_src_window_kxdzp_win_gold=>"开心大转盘抽中金币弹窗",
    		self::$_src_window_look_earnings_in_game=>"游戏中查看游戏收益",
    		self::$_src_window_look_total_earnings_out_game=>"退出游戏后查看游戏总收益",
    		self::$_src_window_lucky_draw_lottery=>"幸运大抽奖",
    		self::$_src_window_play_game_twenty_min=>"游戏达到20分钟",
    		self::$_src_window_play_game_two_min=>"玩游戏2分钟",
    		self::$_src_window_share_to_friend=>"分享给好友，提升收益",
    		self::$_src_window_show_income=>"晒收入",
    		self::$_src_window_sign_in=>"签到后开启宝箱弹窗",
    		self::$_src_window_watch_ad_open_box=>"看视频开宝箱",
    		self::$_src_banner=>"banner",
    	);
    }

    static function getDataByHourTime($hourTime){
        
        $startTime = $hourTime;
        $endTime = $startTime+3599;

        $sql = "select game_id, count(distinct uid) as active_num, sum(e_time-a_time) as total from ".self::$_table." where (a_time between $startTime and $endTime) and e_time!=0 group by game_id";
        return self::db()->getAllBySQL($sql);
    }

    static function gerETimeDataByHourTime($hourTime){
        $startTime = $hourTime;
        $endTime = $startTime+3599;

        $sql = "select game_id,e_time,a_time,uid from ".self::$_table." where (e_time between $startTime and $endTime) and e_time!=0";
        return self::db()->getAllBySQL($sql);
    }
	
    static function gerATimeDataByHourTime($hourTime){
        $startTime = $hourTime;
        $endTime = $startTime+3599;

        $sql = "select game_id,e_time,a_time,uid from ".self::$_table." where (a_time between $startTime and $endTime) and e_time!=0";
        return self::db()->getAllBySQL($sql);
    }

    static function getDataByHourTime2($hourTime){
        $startTime = $hourTime;
        $endTime = $startTime+3599;

        $sql = "select game_id,e_time,a_time,uid,case when (a_time>=$startTime&&a_time<$endTime&&e_time>=$startTime&&e_time<$endTime) then 1 when (((e_time>=$startTime && e_time<$endTime) && a_time<$startTime)) then 2 when ( ((a_time >= $startTime && a_time<$endTime) && e_time>$endTime)) then 3 when (a_time<$startTime && e_time>$endTime) then 4 end as type from ".self::$_table." where ((a_time between $startTime and $endTime) and (e_time between $startTime and $endTime)) or ((e_time between $startTime and $endTime) and a_time<$startTime) or ((a_time between $startTime and $endTime) and e_time>$endTime) or (a_time<$startTime and e_time>$endTime)";
        return self::db()->getAllBySQL($sql);
    }

    static function getDataByDayTime($dayTime){
        $startTime = $dayTime;
        $endTime = $startTime+86399;
        $sql = "select b.os,a.game_id,a.e_time,a.a_time,a.uid,case when (a.a_time>=$startTime&&a.a_time<$endTime&&a.e_time>=$startTime&&a.e_time<$endTime) then 1 when (((a.e_time>=$startTime && a.e_time<$endTime) && a.a_time<$startTime)) then 2 when ( ((a.a_time >= $startTime && a.a_time<$endTime) && a.e_time>$endTime)) then 3 when (a.a_time<$startTime && a.e_time>$endTime) then 4 end as type from ".self::$_table." as a left join (select  uid,os from login group by uid) b on a.uid=b.uid where ((a.a_time between $startTime and $endTime) and (a.e_time between $startTime and $endTime)) or ((a.e_time between $startTime and $endTime) and a.a_time<$startTime) or ((a.a_time between $startTime and $endTime) and a.e_time>$endTime) or (a.a_time<$startTime and a.e_time>$endTime)";
        return self::db()->getAllBySQL($sql);
    }

    static function getDataByDayTimeAndLimit($dayTime,$limitStart,$limitEnd){
        $startTime = $dayTime;
        $endTime = $startTime+86399;
        $sql = "select b.os,a.game_id,a.e_time,a.a_time,a.uid,case when (a.a_time>=$startTime&&a.a_time<$endTime&&a.e_time>=$startTime&&a.e_time<$endTime) then 1 when (((a.e_time>=$startTime && a.e_time<$endTime) && a.a_time<$startTime)) then 2 when ( ((a.a_time >= $startTime && a.a_time<$endTime) && a.e_time>$endTime)) then 3 when (a.a_time<$startTime && a.e_time>$endTime) then 4 end as type from ".self::$_table." as a left join (select  uid,os from login group by uid) b on a.uid=b.uid where ((a.a_time between $startTime and $endTime) and (a.e_time between $startTime and $endTime)) or ((a.e_time between $startTime and $endTime) and a.a_time<$startTime) or ((a.a_time between $startTime and $endTime) and a.e_time>$endTime) or (a.a_time<$startTime and a.e_time>$endTime) limit $limitStart,$limitEnd ";
        return self::db()->getAllBySQL($sql);
    }

    static function getCount($dayTime){
        $startTime = $dayTime;
        $endTime = $startTime+86399;
        $sql = "select count(*) as num from ".self::$_table." as a left join (select  uid,os from login group by uid) b on a.uid=b.uid where ((a.a_time between $startTime and $endTime) and (a.e_time between $startTime and $endTime)) or ((a.e_time between $startTime and $endTime) and a.a_time<$startTime) or ((a.a_time between $startTime and $endTime) and a.e_time>$endTime) or (a.a_time<$startTime and a.e_time>$endTime)";
        $arr = self::db()->getRowBySQL($sql);
        return $arr['num'];
    }
}