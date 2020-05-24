<?php

class OpenAdvertiseService
{

    /**
     * 获取游戏的广告位信息
     * @param $uid
     * @param $gid
     * @param $aid
     * @return array
     */
    public function getAdvertiseInfo ($gid, $aid = 0)
    {
        $info = [];
        if ($gid) {
            $where = " `game_id`={$gid} AND `status`<>4 ORDER BY id DESC ";
            //如果gid有值，则获取单个游戏信息
            if ($aid) {
                $where = " `id`={$aid} AND " . $where . " LIMIT 1 ";
                $func = 'getRow';
            } else {
                $func = 'getAll';
            }
            $info = OpenAdvertiseModel::db()->$func($where);
        }

        return $info;
    }

    /**
     * 给游戏添加新的广告位
     * @param $uid
     * @param $gid
     * @param $addInfo
     * @return array
     */
    public function addAdvertise ($uid, $gid, $addInfo)
    {
        $info['result'] = false;
        if ($uid && $gid && $addInfo) {
            $time = time();
            $addOtherInfo = [
                'game_id' => $gid,
                'uid' => $uid,
                'status' => 1,
                'a_time' => $time,
                'u_time' => $time,
            ];
            $addInfo = array_merge($addOtherInfo, $addInfo);
            $aid = OpenAdvertiseModel::db()->add($addInfo);
            if ($aid) {
                $info['title'] = $addInfo['title'];
                $info['game_id'] = $gid;
                $info['aid'] = $aid;
                $info['result'] = true;
            }
        }
        return $info;
    }

    /**
     * 更新
     * @param $uid
     * @param $gid
     * @param $aid
     * @param $updateInfo
     * @return mixed
     */
    public function updateAdvertise ($uid, $gid, $aid, $updateInfo)
    {
        $info['result'] = false;
        $have = $this->getAdvertiseInfo($gid, $aid);
        if ($have) {
            $updateInfo['u_time'] = time();
            $r = OpenAdvertiseModel::db()->upById($aid, $updateInfo);
            if ($r) {
                $info['result'] = true;
            }
        }
        return $info;
    }

    /**
     * 软删除 将status置为4即可
     * @param $uid
     * @param $gid
     * @param $aid
     * @return mixed
     */
    public function deleteAdvertise ($uid, $gid, $aid)
    {
        $updateInfo['status'] = 4;
        return $this->updateAdvertise($uid, $gid, $aid, $updateInfo);
    }

    function getUVCntByDay ($gamesId)
    {
        $today = dayStartEndUnixtime();
        $cnt = PlayedGamesModel::db()->getCount(" game_id = $gamesId and a_time >= {$today['s_time']} and a_time <=  {$today['e_time']} group by uid");
        return $cnt;
    }

    //一个游戏  玩过的人数
    function getPlayedNum ($gamesId)
    {
        $uv = $this->getUVCntByDay($gamesId);

        $today = dayStartEndUnixtime();


        $dayBaseNumber = (int)($today['s_time'] - strtotime("2019-01-01")) / (24 * 60 * 60);
        $dayBaseNumber = $dayBaseNumber * 50;

        //随机数，每10分钟变一次，且随时间增长，数值定期增加。
        //实际上就是每个10分钟+6，再加上+gameID

        //(当前时间 - 当天开始时间 ) / 10分钟 = 当前时间为一天的，第几个，10分钟
        $period = (int)(time() - $today['s_time']) / (60 * 10);//最大1440
        //因为基数有点大，再除10
        $period = $period / 10;//最大144
        $periodNum = $period * 6;
        $rand = $periodNum + $gamesId + $dayBaseNumber;

        $base = $gamesId % 10;
        if (!$base) {
            $base = 1;
        }
        $base = $base * 1000;
        return $base + $rand + $uv;
    }

    //所有的游戏
    function getAllList ($page = 1)
    {
        $cnt = GamesModel::db()->getCount(" 1 " . $this->getNomalGameWhere());
        if (!$cnt) {
            return false;
        }
        $pageInfo = PageLib::getPageInfo($cnt, 20, $page);


        $list = GamesModel::db()->getAll(" 1 " . $this->getNomalGameWhere() . " order by sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
        if ($list) {
            $list = $this->formatListData($list);
        }


        $rs = array('pageInfo' => $pageInfo, 'list' => $list);

        return $rs;
    }

    //玩过的游戏列表
    function playedGameHistoryList ($uid)
    {
        $playedList = PlayedGamesModel::db()->getAll(" uid = " . $uid . " group by game_id ");
        if (!$playedList) {//为空时，为了好看，系统给自动加2条记录
            $playedList = $this->recommendIndex(3);
            foreach ($playedList as $k => $v) {
                $playedList[$k]['game_id'] = $v['id'];
            }
        }
        //至少要返回3条

        $cnt = count($playedList);
        if ($cnt == 1) {
            $recommend = $this->recommendIndex(2, $playedList[0]['game_id']);
            foreach ($recommend as $k => $v) {
                $recommend[$k]['game_id'] = $v['id'];
            }
            $playedList = array_merge($playedList, $recommend);
        } elseif ($cnt == 2) {
            $recommend = $this->recommendIndex(2, $playedList[0]['game_id'] . "," . $playedList[1]['game_id']);
            foreach ($recommend as $k => $v) {
                $recommend[$k]['game_id'] = $v['id'];
            }
            $playedList = array_merge($playedList, $recommend);
        }

        $list = [];
        foreach ($playedList as $k => $v) {
            $list[] = GamesModel::db()->getById($v['game_id']);
        }

        return $this->formatListData($list);
    }

    //推荐到首页的游戏
    function recommendIndex ($limit = 0, $exceptGameIds = "")
    {
        $limitStr = "";
        if ($limit) {
            $limitStr = " limit $limit ";
        }

        if ($exceptGameIds) {
            $exceptGameIds = " and  id not in ($exceptGameIds) ";
        }

        //        echo " recommend_index = ".GamesModel::$_recommend_index_true. $exceptGameIds . $this->getNomalGameWhere()  . $limitStr;


        $list = GamesModel::db()->getAll(" recommend_index = " . GamesModel::$_recommend_index_true . $exceptGameIds . $this->getNomalGameWhere() . $limitStr);
        if ($list) {
            $list = $this->formatListData($list);
        }
        return $list;
    }

    //最新上线的游戏
    function topOnline ($limit = 3)
    {
        $list = GamesModel::db()->getAll(" recommend_new = 1  " . $this->getNomalGameWhere() . "  limit $limit");
        if ($list) {
            $list = $this->formatListData($list);
        }
        return $list;
    }

    //除推荐之外的游戏
    function exceptRecommendIndex ($page)
    {
        $cnt = GamesModel::db()->getCount(" recommend_index = " . GamesModel::$_recommend_index_false . $this->getNomalGameWhere());
        if (!$cnt) {
            return false;
        }
        $pageInfo = PageLib::getPageInfo($cnt, 20, $page);


        $list = GamesModel::db()->getAll(" recommend_index = " . GamesModel::$_recommend_index_false . $this->getNomalGameWhere() . " order by sort desc limit " . $pageInfo['start'] . "," . $pageInfo['end']);
        if ($list) {
            $list = $this->formatListData($list);
        }
        $rs = array('pageInfo' => $pageInfo, 'list' => $list);
        return $rs;
    }

    function getRecommendImInvite ()
    {
        $list = GamesModel::db()->getAll(" recommend_im_invite = 1 order by id desc");
        if ($list) {
            $list = $this->formatListData($list);
        }
        return $list;
    }

    //获取IM内，发起邀请的游戏托盘
    function getImGameInvite ($uid)
    {
        $imInvite = $this->getRecommendImInvite($uid);
        $collect = $this->getCollect($uid);
        $list = array_merge($imInvite, $collect);

        return $list;
    }

    function getCollect ($uid)
    {
        $list = gamesCollectModel::db()->getAll(" uid = $uid order by id desc");
        if ($list) {
            if ($list) {
                foreach ($list as $k => $v) {
                    $list[$k] = GamesModel::db()->getById($v['game_id']);
                }
            }

            $list = $this->formatListData($list);
        }
        return $list;
    }

    function addCollect ($uid, $gameId)
    {
        if (!$gameId) {
            return out_pc(8027);
        }
        $row = GamesCollectModel::db()->getRow(" uid = $uid and game_id = $gameId");
        if ($row) {
            return out_pc(8246);
        }
        $data = array(
            'uid' => $uid,
            'a_time' => time(),
            'game_id' => $gameId,
        );

        $aid = GamesCollectModel::db()->add($data);

        return out_pc(200, $aid);
    }

    function cancelCollect ($uid, $gameId)
    {
        if (!$gameId) {
            return out_pc(8027);
        }

        $game = GamesModel::db()->getById($gameId);
        if (!$game) {
            return out_pc(1013);
        }

        $row = GamesCollectModel::db()->getRow(" uid = $uid and game_id = $gameId");
        if (!$row) {
            return out_pc(8261);
        }
        $aid = GamesCollectModel::db()->delById($row['id']);

        return out_pc(200, $aid);
    }


    function addPlayLog ($uid, $gameId)
    {
        if (!$gameId) {
            return out_pc(8027);
        }

        $game = GamesModel::db()->getById($gameId);
        if (!$game) {
            return out_pc(1013);
        }

        if (!$uid) {
            return out_pc(8002);
        }

        $data = array(
            'uid' => $uid,
            'game_id' => $gameId,
            'a_time' => time(),
        );

        $aid = PlayedGamesModel::db()->add($data);
        return out_pc(200, $aid);
    }

    private function getNomalGameWhere ()
    {
        return "  and is_online =   " . GamesModel::$_online_true;
    }

    function isCollect ($uid, $gameId)
    {
        $row = gamesCollectModel::db()->getRow(" uid = $uid and game_id = $gameId");
        if ($row) {
            return 1;
        } else {
            return 0;
        }
    }

    //游戏开始前置
    function playGamePrepare ($uid, $gameId)
    {
        if (!$gameId) {
            return out_pc(8027);
        }

        $game = GamesModel::db()->getById($gameId);
        if (!$game) {
            return out_pc(1013);
        }


        if (!$uid) {
            return out_pc(8002);
        }

        $lib = new UserService();
        $user = $lib->getUinfoById($uid);
        if (!$user) {
            return out_pc(1000);
        }


        $rs = $this->addPlayLog($uid, $gameId);
        $rs = array(
            'id' => $rs['msg'],
            'isCollect' => $this->isCollect($uid, $gameId)
        );

        return out_pc(200, $rs);
    }

    function gameStart ($uid, $gameId)
    {
        $rs = array(
            'countdown' => 30,
            'rewardPoint' => 10,
            'isCollect' => $this->isCollect($uid, $gameId)
        );

        return out_pc(200, $rs);
    }


    private function formatListData ($list)
    {
        foreach ($list as $k => $v) {
            $list[$k]['played_num'] = $this->getPlayedNum($v['id']);
            $list[$k]['small_img'] = get_img_url_by_app($v['small_img'], 'instantplayadmin', 'https');
            $list[$k]['list_img'] = get_img_url_by_app($v['list_img'], 'instantplayadmin', 'https');
            $list[$k]['index_reco_img'] = get_img_url_by_app($v['index_reco_img'], 'instantplayadmin', 'https');


            unset($list[$k]['category']);
            unset($list[$k]['recommend_im_invite']);
            unset($list[$k]['status']);
            unset($list[$k]['is_online']);
            unset($list[$k]['label']);
            unset($list[$k]['sort']);
            unset($list[$k]['recommend_index']);
            unset($list[$k]['recommend_im_invite']);
            unset($list[$k]['recommend_new']);
            unset($list[$k]['open_method']);


        }

        return $list;
    }

    function loginHook ($uid, $guestUid)
    {
        if ($uid == $guestUid) {
            return "用户一样，不需要操作";
        }
        //        var_dump($uid);var_dump($guestUid);
        $list = PlayedGamesModel::db()->getAll(" uid = " . $guestUid);
        //        var_dump($list);
        if (!$list) {
            return out_pc(200, 'no data');
        }

        foreach ($list as $k => $v) {
            PlayedGamesModel::db()->upById($v['id'], array('uid' => $uid));
        }

        return out_pc(200, 'ok');
    }

    function gameEnd ($id, $rewardData, $uid)
    {
        //        $arr = array(
        //            1=>10,
        //        );
        //        echo json_encode($arr);exit;

        if (!$id) {
            return out_pc(8043);
        }
        $gameRecord = PlayedGamesModel::db()->getById($id);
        if (!$gameRecord) {
            return out_pc(1021);
        }

        if ($gameRecord['uid'] != $uid) {
            return out_pc(8277);
        }
        //游戏已结束
        if ($gameRecord['e_time']) {
            return out_pc(8278);
        }

        if (!$rewardData) {
            return out_pc(8044);
        }

        $list = stripslashes($rewardData);
        $list = json_decode($list, true);
        if (!$list || !is_array($list)) {
            return out_pc(8267);
        }


        LogLib::appWriteFileHash($list);

        //玩游戏的总时间数
        $second = time() - $gameRecord['a_time'];

        if(PCK_AREA == 'cn'){
            $rule = $GLOBALS['main']['playGameRewardRule'];
        }else{
            $rule = $GLOBALS['main']['playGameRewardRuleEn'];
        }

        $reward[] = null;
        $rewardTotal = 0;
        foreach ($rule as $k => $v) {
            $rewardTotal += $this->calcReward($second, $v);
        }

        $realRewardTotal = 0;
        foreach ($list as $k => $v) {
            $realRewardTotal += $v;
        }

        if ($realRewardTotal > $rewardTotal + 100) {
            return out_pc(8290);
        }

        LogLib::appWriteFileHash([$realRewardTotal, $rewardTotal]);

        $bankService = new BankService();
        $goldInfo = $bankService->getGoldcoinInfo($uid);
        $goldInfoMsg = $goldInfo['msg'];

        // 增加国内or海外版本区分逻辑;
        if(PCK_AREA != 'en'){
            if ($goldInfoMsg['today'] >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimit']) {// 原代码;
                return out_pc(8279);
            }
        }else{
            if ($goldInfoMsg['today'] >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs']) {
                return out_pc(8279);
            }
        }

        // 增加国内or海外版本区分逻辑;
        if(PCK_AREA != 'en') {
            if ($realRewardTotal + $goldInfoMsg['today'] >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimit']) {// 原代码;
                $realRewardTotal = $realRewardTotal + $goldInfoMsg['today'] - $GLOBALS['main']['playGameRewardGoldcoinMaxLimit'];
            }
        }else{
            if ($realRewardTotal + $goldInfoMsg['today'] >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs']) {
                $realRewardTotal = $realRewardTotal + $goldInfoMsg['today'] - $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs'];
            }
        }


        $service = new UserService();
        $rs = $service->addGoldcoin($uid, $realRewardTotal, GoldcoinLogModel::$_type_play_games, $id);
        $invite = InviteModel::db()->getRow(" uid = $uid");
        if ($invite) {
            $realRewardTotal = round($realRewardTotal * 0.05);
            if ($realRewardTotal) {
                $rs = $service->addGoldcoin($invite['to_uid'], $realRewardTotal, GoldcoinLogModel::$_type_friend_play_game, $uid);
            }
        }

        $task = new TaskService();
        $rs = $task->trigger($uid, 1);
        //        var_dump($rs);
        $rs = $task->trigger($uid, 11);
        //        var_dump($rs);

        $upRs = PlayedGamesModel::db()->upById($id, array('e_time' => time()));

        return out_pc(200, $upRs);
    }

    function calcReward ($second, $rule)
    {
        $rewardTotal = 0;
        if ($rule['sec_end'] == -1) {
            //            $diff = $second - $rule['sec_start'];
            //            $mod = (int)($diff / $rule['second_period']);
            for ($i = $rule['sec_start']; $i <= $second;) {
                $i += $rule['second_period'];
                $rewardTotal += rand($rule['reward_goldcoin_rand_start'], $rule['reward_goldcoin_rand_end']);
            }
        } elseif ($second >= $rule['sec_end']) {//证明已经超了，那这个阶层就是满加分状态
            for ($i = $rule['sec_start']; $i <= $rule['sec_end'];) {
                $i += $rule['second_period'];
                $rewardTotal += rand($rule['reward_goldcoin_rand_start'], $rule['reward_goldcoin_rand_end']);
            }
        } elseif ($second >= $rule['sec_start']) {
            for ($i = $rule['sec_start']; $i <= $rule['sec_end'];) {
                $i += $rule['second_period'];
                $rewardTotal += rand($rule['reward_goldcoin_rand_start'], $rule['reward_goldcoin_rand_end']);
            }
        } else {
            return 0;
        }

        return $rewardTotal;

        //
        //
        //            $rewardTotal += $reward[$v['sec_end']];
    }
}