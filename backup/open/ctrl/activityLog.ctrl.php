<?php

/**
 * @Author: Kir
 * @Date:   2019-03-13 10:48:14
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-30 21:39:33
 */


/**
 * 
 */
class ActivityLogCtrl extends BaseCtrl
{
	
	/**
     * 活动日志页面
     */
    public function index ()
    {
        $this->checkGame();
        $this->addCss("assets/open/css/activityLog.css");

        $this->display("activitylog.html", "new", "isLogin");
    }

    /**
     * 获取日志，按游戏ID
     */
    public function getLogs ()
    {
        $length = 15;
        $gameid = _g("gameid");
        $page = _g("page");
        $start = ($page-1) * $length;

        $count = ActivityLogModel::db()->getCount("game_id = $gameid");
        $totalPage = ceil($count/$length);
        if ($page > $totalPage) {
            $logs = [];
        } else {
            $logs = ActivityLogModel::db()->getAllBySql(" select user.nickname, l.* from (select * from open_activity_log where game_id = $gameid order by id desc limit $start,$length) as l left join user on l.uid=user.id ");
        }

        foreach ($logs as &$log) {
            $log['a_time'] = date('Y-m-d H:i:s', $log['a_time']);
            $log['extra'] = json_decode($log['extra']);
            $log['desc'] = ActivityLogModel::logAnalysis($log);
            $user = UserModel::db()->getRowById($log['uid']);
            if (is_null($log['nickname'])) {
                $log['nickname'] = "";
            }
        }

        $this->outputJson(200, "succ", ["totalPage"=>$totalPage, "logs"=>$logs]);
    }
}