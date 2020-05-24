<?php

/**
 * @Author: Kir
 * @Date:   2019-06-19 10:46:04
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-20 15:07:37
 */

/**
 * 
 */
class ShareCtrl extends BaseCtrl
{

	function index()
	{
		$category = [
			'box' => '宝箱',
			'addFriend' => '添加好友',
			'income' => '晒收入奖励',
			'money' => '提现分享',
			'h5Friend' => '邀请好友页面',
			'h5Invite' => '邀请落地页',
		];

		$this->assign("firstday", date('Y-m-d',strtotime("-6 day")));
		$this->assign("today", date('Y-m-d',time()));
		$this->assign("category", $category);
		$this->display("app_func_data/share.html");
	}


	function box()
	{
		$head = [
			'日期',
			'点击次数+20',
			'点击人数+20',
			'分享微信次数+40',
			'分享微信人数+40',
			'分享QQ次数+40',
			'分享QQ人数+40',
		];

	    $where = $this->getWhere();

	    $type_box_tap = CntActionLogModel::$_type_box_tap;
	    $step_share_wx = GoldcoinLogModel::$_type_rand_luck_box_share_wx;
	    $step_share_qq = GoldcoinLogModel::$_type_rand_luck_box_share_qq;

	    $sql = "
			SELECT
				g.*,
				s.wx_cnt,
				s.wx_users,
				s.qq_cnt,
				s.qq_users 
			FROM
				(
				SELECT
					date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
					count( id ) AS cnt,
					count( DISTINCT uid ) AS users 
				FROM
					cnt_action 
				WHERE
					$where and type = $type_box_tap 
				GROUP BY
					a_date 
					) AS g
				LEFT JOIN (
				SELECT
					date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
					count( IF ( type = $step_share_wx, id, NULL ) ) AS wx_cnt,
					count( DISTINCT IF ( type = $step_share_wx, uid, NULL ) ) AS wx_users,
					count( IF ( type = $step_share_qq, id, NULL ) ) AS qq_cnt,
					count( DISTINCT IF ( type = $step_share_qq, uid, NULL ) ) AS qq_users 
				FROM
					share 
				WHERE
					$where 
				GROUP BY
					a_date 
					) AS s 
				ON g.a_date = s.a_date
		";


		$data = ShareModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);
	}


	function addFriend()
	{
		$head = [
			'日期',
			'总分享次数',
			'总分享人数',
			'微信好友次数',
			'微信好友人数',
			'QQ好友次数',
			'QQ好友人数',
		];

		$_platform_wx = ShareModel::$_platform_wx;
	    $_platform_qq = ShareModel::$_platform_qq;

	    $_to_friend = ShareModel::$_to_friend;

	    $where = $this->getWhere();

	    $type = GoldcoinLogModel::$_type_game_share_add_friends;

		$sql = "
			SELECT
				date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
				count( id ) AS total_cnt,
				count( DISTINCT uid ) AS total_users,
				count( IF ( platform = $_platform_wx AND platform_method = $_to_friend, id, NULL ) ) AS wx_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_friend, uid, NULL ) ) AS wx_friend_users,
				count( IF ( platform = $_platform_qq AND platform_method = $_to_friend, id, NULL ) ) AS qq_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_friend, uid, NULL ) ) AS qq_friend_users
			FROM
				share
			WHERE
				$where AND type = $type
			GROUP BY
				a_date
		";

		$data = ShareModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);
	}

	function income()
	{
		$head = [
			'日期',
			'总分享次数',
			'总分享人数',
			'微信好友次数',
			'微信好友人数',
			'朋友圈次数',
			'朋友圈人数',
			'QQ好友次数',
			'QQ好友人数',
			'QQ空间次数',
			'QQ空间人数',
		];

		$_platform_wx = ShareModel::$_platform_wx;
	    $_platform_qq = ShareModel::$_platform_qq;

	    $_to_friend = ShareModel::$_to_friend;
	    $_to_platform = ShareModel::$_to_platform;

	    $where = $this->getWhere();

	    $type = GoldcoinLogModel::$_type_share_income;

		$sql = "
			SELECT
				date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
				count( id ) AS total_cnt,
				count( DISTINCT uid ) AS total_users,
				count( IF ( platform = $_platform_wx AND platform_method = $_to_friend, id, NULL ) ) AS wx_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_friend, uid, NULL ) ) AS wx_friend_users,
				count( IF ( platform = $_platform_wx AND platform_method = $_to_platform, id, NULL ) ) AS wx_platform_cnt,
				count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_platform, uid, NULL ) ) AS wx_platform_users,
				count( IF ( platform = $_platform_qq AND platform_method = $_to_friend, id, NULL ) ) AS qq_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_friend, uid, NULL ) ) AS qq_friend_users,
				count( IF ( platform = $_platform_qq AND platform_method = $_to_platform, id, NULL ) ) AS qq_platform_cnt,
				count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_platform, uid, NULL ) ) AS qq_platform_users
			FROM
				share
			WHERE
				$where AND type = $type
			GROUP BY
				a_date
		";

		$data = ShareModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);

	}


	function money()
	{
		$head = [
			'日期',
			'总分享次数',
			'总分享人数',
			'微信好友次数',
			'微信好友人数',
			'朋友圈次数',
			'朋友圈人数',
			'QQ好友次数',
			'QQ好友人数',
			'QQ空间次数',
			'QQ空间人数',
		];

		$_platform_wx = ShareModel::$_platform_wx;
	    $_platform_qq = ShareModel::$_platform_qq;

	    $_to_friend = ShareModel::$_to_friend;
	    $_to_platform = ShareModel::$_to_platform;

	    $where = $this->getWhere();

	    $type = GoldcoinLogModel::$_type_share_get_money;

		$sql = "
			SELECT
				date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
				count( id ) AS total_cnt,
				count( DISTINCT uid ) AS total_users,
				count( IF ( platform = $_platform_wx AND platform_method = $_to_friend, id, NULL ) ) AS wx_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_friend, uid, NULL ) ) AS wx_friend_users,
				count( IF ( platform = $_platform_wx AND platform_method = $_to_platform, id, NULL ) ) AS wx_platform_cnt,
				count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_platform, uid, NULL ) ) AS wx_platform_users,
				count( IF ( platform = $_platform_qq AND platform_method = $_to_friend, id, NULL ) ) AS qq_friend_cnt,
				count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_friend, uid, NULL ) ) AS qq_friend_users,
				count( IF ( platform = $_platform_qq AND platform_method = $_to_platform, id, NULL ) ) AS qq_platform_cnt,
				count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_platform, uid, NULL ) ) AS qq_platform_users
			FROM
				share
			WHERE
				$where AND type = $type
			GROUP BY
				a_date
		";

		$data = ShareModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);
	}


	function h5Friend()
	{
		$head = [
            '日期',
			'打开次数',
			'打开人数',
			'总分享次数',
			'总分享人数',
			'微信好友次数',
			'微信好友人数',
			'朋友圈次数',
			'朋友圈人数',
			'QQ好友次数',
			'QQ好友人数',
			'QQ空间次数',
			'QQ空间人数',
		];

	    $where = $this->getWhere();

	    $ac_type = CntActionLogModel::$_type_invite_friend_page_access;
	    $share_type = GoldcoinLogModel::$_type_share_friend;

	    $_platform_wx = ShareModel::$_platform_wx;
	    $_platform_qq = ShareModel::$_platform_qq;

	    $_to_friend = ShareModel::$_to_friend;
	    $_to_platform = ShareModel::$_to_platform;

	    $sql = "
			SELECT
				c.*,
				s.total_cnt,
				s.total_users,
				s.wx_friend_cnt,
				s.wx_friend_users, 
				s.wx_platform_cnt,
				s.wx_platform_users, 
				s.qq_friend_cnt,
				s.qq_friend_users, 
				s.qq_platform_cnt,
				s.qq_platform_users
			FROM
				(
				SELECT
					date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
					count( id ) AS cnt,
					count( DISTINCT uid ) AS users 
				FROM
					cnt_action 
				WHERE
					$where and type = $ac_type 
				GROUP BY
					a_date 
					) AS c
				LEFT JOIN (
				SELECT
					date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
					count( id ) AS total_cnt,
					count( DISTINCT uid ) AS total_users,
					count( IF ( platform = $_platform_wx AND platform_method = $_to_friend, id, NULL ) ) AS wx_friend_cnt,
					count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_friend, uid, NULL ) ) AS wx_friend_users,
					count( IF ( platform = $_platform_wx AND platform_method = $_to_platform, id, NULL ) ) AS wx_platform_cnt,
					count( DISTINCT IF ( platform = $_platform_wx AND platform_method = $_to_platform, uid, NULL ) ) AS wx_platform_users,
					count( IF ( platform = $_platform_qq AND platform_method = $_to_friend, id, NULL ) ) AS qq_friend_cnt,
					count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_friend, uid, NULL ) ) AS qq_friend_users,
					count( IF ( platform = $_platform_qq AND platform_method = $_to_platform, id, NULL ) ) AS qq_platform_cnt,
					count( DISTINCT IF ( platform = $_platform_qq AND platform_method = $_to_platform, uid, NULL ) ) AS qq_platform_users
				FROM
					share 
				WHERE
					$where and type = $share_type
				GROUP BY
					a_date 
					) AS s 
				ON c.a_date = s.a_date
		";

		$data = ShareModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);
	}


	function h5Invite()
	{
		$head = [
			'日期',
			'打开次数',
			'打开人数',
			'立即下载次数',
			'立即下载人数',
		];

	    $where = $this->getWhere();

	    $type_access = CntActionLogModel::$_type_landing_page_access;
	    $type_download = CntActionLogModel::$_type_landing_page_download;

		$sql = "
			SELECT
				date_format( from_unixtime( a_time ), '%Y-%m-%d' ) AS a_date,
				count( IF ( type = $type_access, id, NULL ) ) AS access_cnt,
				count( DISTINCT IF ( type = $type_access, uid, NULL ) ) AS access_users,
				count( IF ( type = $type_download, id, NULL ) ) AS download_cnt,
				count( DISTINCT IF ( type = $type_download, uid, NULL ) ) AS download_users 
			FROM
				cnt_action
			WHERE
				$where
			GROUP BY
				a_date
		";

		$data = CntActionLogModel::db()->getAllBySql($sql);

		$this->outputJson(200, "succ", ['head'=>$head, 'body'=>$data]);
	}


	function getWhere()
	{
		$where = " 1 ";
        $from = _g("from");
        $to = _g("to");

        if (!is_null($from) && $from!='') {
        	$from = strtotime($from);
        	$where.= " and a_time >= $from";
        }

        if (!is_null($to) && $to!='') {
        	$to = strtotime("+1day $to");
        	$where.= " and a_time <= $to";
        }

        return $where;
	}
	
}

