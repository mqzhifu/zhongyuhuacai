<?php

class GamesService
{

    function getUVCntByDay ($gamesId)
    {

        $mod = date("i") / 10 + 1;
        $date = date("YmdH"). (int)$mod;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['game_uv']['key'],$gamesId."_".$date);
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['game_uv']['key'],$gamesId."_");
        $cnt = RedisPHPLib::getServerConnFD()->get($key);
        if($cnt){
            return $cnt;
        }elseif($cnt === false){
            $today = dayStartEndUnixtime();
            // $cnt = PlayedGamesModel::db()->getCount(" game_id = $gamesId and a_time >= {$today['s_time']} and a_time <=  {$today['e_time']} group by uid");
            // bug分析：$gamesId为空可能由于当前游戏已经下线造成，现在做兼容处理 Modify By XiaHB time:2018/05/21 Begin;
            if(isset($gamesId) && !empty($gamesId)){
//                $cnt = PlayedGamesModel::db()->getCount(" game_id = $gamesId and a_time >= {$today['s_time']} and a_time <=  {$today['e_time']} group by uid");
                $cnt = rand(1000,9999);
                RedisPHPLib::getServerConnFD()->set($key,$cnt,$GLOBALS['rediskey']['game_uv']['expire']);
                return $cnt;
            }else{
                return 0;
            }
            // bug分析：$gamesId为空可能由于当前游戏已经下线造成，现在做兼容处理 Modify By XiaHB time:2018/05/21   End;
        }else{
            return 0;
        }


    }

    //一个游戏  玩过的人数
    function getPlayedNum ($gamesId, $base_playd_num = 0)
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
        return (int)($base_playd_num + $base + $rand + $uv);
    }

    //所有的游戏
    function getAllList ($page=1, $f_key=0)
    {
        // wzgame需要过滤一层
        if(APP_NAME == 'instantplay_new'){
            $sql = "select count(*) as cnt from (select g.id,if(wge.sort is null,g.sort,wge.sort) as sort,g.name,g.category,if(wge.u_time is null,g.u_time,wge.u_time) as u_time,if(wge.is_online is null,g.is_online,wge.is_online) as is_online,g.status,g.small_img,g.list_img,g.index_reco_img,g.play_url,g.screen,g.summary,if(wge.background_color is null,g.background_color,wge.background_color) as background_color,g.a_time,g.link_url,g.url_type,g.wx_userName,g.wx_path,g.wx_miniprogramType,g.hide from games g left join wz_games_extend wge on g.id=wge.game_id ) g where g.is_online = 1 and g.status !=4 and g.hide=0 ". $this->getChannelGamesWhere($f_key);
            $cntSql = GamesModel::db()->getRowBySQL($sql);
            $cnt = (0 == $cntSql['cnt'])?0:$cntSql['cnt'];
            if (!$cnt) {
                $rs = array('pageInfo' => [], 'list' => []);
                return $rs;
            }
            $pageInfo = PageLib::getPageInfo($cnt, 42, $page);
            $clientInfo = get_client_info();
            if(  !arrKeyIssetAndExist($clientInfo, 'app_version') ||$clientInfo['app_version'] < '1.0.9' ){
                // $list = GamesModel::db()->getAll(" 1 " . $this->getNomalGameWhere() . $this->getChannelGamesWhere($f_key) . $this->getAppShowWhere() . " and url_type = 1 order by sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
                $list = GamesModel::db()->getAllBySQL("select *  from (select g.id,if(wge.sort is null,g.sort,wge.sort) as sort,g.name,g.category,if(wge.u_time is null,g.u_time,wge.u_time) as u_time,if(wge.is_online is null,g.is_online,wge.is_online) as is_online,g.status,g.small_img,g.list_img,g.index_reco_img,g.play_url,g.screen,g.summary,if(wge.background_color is null,g.background_color,wge.background_color) as background_color,g.a_time,g.link_url,g.url_type,g.wx_userName,g.wx_path,g.wx_miniprogramType,g.hide from games g left join wz_games_extend wge on g.id=wge.game_id ) g where g.is_online = 1 and g.status !=4 and g.hide=0 ". $this->getChannelGamesWhere($f_key) . " and g.url_type = 1 order by g.sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
            }else{
                $list = GamesModel::db()->getAllBySQL("select * from (select g.id,if(wge.sort is null,g.sort,wge.sort) as sort,g.name,g.category,if(wge.u_time is null,g.u_time,wge.u_time) as u_time,if(wge.is_online is null,g.is_online,wge.is_online) as is_online,g.status,g.small_img,g.list_img,g.index_reco_img,g.play_url,g.screen,g.summary,if(wge.background_color is null,g.background_color,wge.background_color) as background_color,g.a_time,g.link_url,g.url_type,g.wx_userName,g.wx_path,g.wx_miniprogramType,g.hide from games g left join wz_games_extend wge on g.id=wge.game_id ) g where g.is_online = 1 and g.status !=4 and g.hide=0 ". $this->getChannelGamesWhere($f_key) . $this->getExceptRecList() . " order by sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
            }
        }else{
            $cnt = GamesModel::db()->getCount(" 1 " . $this->getNomalGameWhere() . $this->getChannelGamesWhere($f_key) . $this->getExceptRecList());
            if (!$cnt) {
                $rs = array('pageInfo' => [], 'list' => []);
                return $rs;
            }
            $pageInfo = PageLib::getPageInfo($cnt, 42, $page);
            $clientInfo = get_client_info();
            if(  !arrKeyIssetAndExist($clientInfo, 'app_version') ||$clientInfo['app_version'] < '1.0.9' ){
                $list = GamesModel::db()->getAll(" 1 " . $this->getNomalGameWhere() . $this->getChannelGamesWhere($f_key) . $this->getAppShowWhere() . " and url_type = 1 order by sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
            }else{
                $list = GamesModel::db()->getAll(" 1 " . $this->getNomalGameWhere() . $this->getChannelGamesWhere($f_key) . $this->getAppShowWhere() . $this->getExceptRecList() . " order by sort desc  limit {$pageInfo['start']}, {$pageInfo['end']}");
            }
        }
        
        

        // 后台三期项目新增嵌入推广逻辑 add by XiaHB Begin ;
        $now_time = time();
        $popular_info = popularizeModel::db()->getAll(" 1 = 1 AND status = 1 AND start_launch_time <= '{$now_time}' AND '{$now_time}' <= end_launch_time ",'','p_id,game_id,status,start_launch_time,end_launch_time');
        if($popular_info){
            $arr_new = [];
            // 将满足审核中和时间区间段的数据取出添加到新数组;
            foreach ($list as $k => $value){
                foreach ($popular_info as $v){
                    if($value['id'] == $v['game_id']){
                          array_push($arr_new, $list[$k]);
                          unset($list[$k]);
                    }
                }
            }
            // 将新数组依据p_id进行排序，并处理掉冗余字段;
            foreach ($arr_new as &$vv){
                $res = popularizeModel::db()->getRow("1 = 1 AND game_id = {$vv['id']}", '', 'p_id');
                $vv['p_id'] = $res['p_id'];
                $last_names = array_column($arr_new,'p_id');
                array_multisort($last_names,SORT_ASC, $arr_new);
                unset($vv['p_id']);
            }
            $list = array_merge($arr_new, $list);
        }
        // 后台三期项目新增嵌入推广逻辑 add by XiaHB   End ;
        if ($list) {
            $list = $this->formatListData($list);
        }
       

        $rs = array('pageInfo' => $pageInfo, 'list' => $list);

        return $rs;
    }

    //玩过的游戏列表
    function playedGameHistoryList ($uid)
    {

        $playedList = $this->getUserPlayedGameList($uid);
//        $playedList = PlayedGamesModel::db()->getAll(" uid = " . $uid . " group by game_id ");
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
            // WARNING 系统级错误信息处理,因为会存在游戏下线也就是is_online=0或者游戏直接删了的情况，故当前数组中存在取不到游戏id的情况，如下：
            // [2] = string(5) 10129;
            // [3] = string(5) 10174;
            // 原代码：$list[] = GamesModel::db()->getById($v['game_id']);
            // 现做兼容处理;
            if(isset($v['game_id']) && !empty($v['game_id'])){
                $list[] = GamesModel::db()->getById($v['game_id']);
            }
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
//    function exceptRecommendIndex ($page)
//    {
//        $cnt = GamesModel::db()->getCount(" recommend_index = " . GamesModel::$_recommend_index_false . $this->getNomalGameWhere());
//        if (!$cnt) {
//            return false;
//        }
//        $pageInfo = PageLib::getPageInfo($cnt, 20, $page);
//
//
//        $list = GamesModel::db()->getAll(" recommend_index = " . GamesModel::$_recommend_index_false . $this->getNomalGameWhere() . " order by sort desc limit " . $pageInfo['start'] . "," . $pageInfo['end']);
//        if ($list) {
//            $list = $this->formatListData($list);
//        }
//        $rs = array('pageInfo' => $pageInfo, 'list' => $list);
//        return $rs;
//    }

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


    function addPlayLog ($uid, $gameId, $src)
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
            'src' => $src,
            'a_time' => time(),
            'e_time' =>0,
        );

        $this->setUserPlayedGameList($uid,$gameId);

        $aid = PlayedGamesModel::db()->add($data);

        // 新增游戏日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29 Begin;
        PlayedGamesMoreModel::add($data);
        // 新增游戏日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29   End;

        return out_pc(200, $aid);
    }

    private function getNomalGameWhere () 
    {
        return "  and is_online =   " . GamesModel::$_online_true . " and status != 4";
    }
    // 渠道游戏配置
    private function getChannelGamesWhere($f_key=0)
    {
        if ($f_key == 0) {
            return '';
        }

        $channel = ChannelsModel::db()->getRow("f_key=$f_key");
        if (!$channel) {
            return '';
        }

        $gameIds = $channel['games'];

        if ($gameIds == 'all') {
            return '';
        }
        // 兼容wzgame
        $column = 'id';
        if(APP_NAME == 'instantplay_new'){
            $column = 'g.id';
        }
        return " and $column in ($gameIds) ";

    }
    
    // 排除被推荐游戏
    private function getExceptRecList() {
        $clientInfo = get_client_info();
        if (!arrKeyIssetAndExist($clientInfo, 'app_version') || $clientInfo['app_version'] < '1.1.7') {
            return '';
        }
        $recTypes = GameRecommendModel::getTypeDesc();
        unset($recTypes[GameRecommendModel::$_type_more]);
        $recTypes = array_keys($recTypes);
        $recTypes = implode(',', $recTypes);
        if (!$recTypes) {
            return '';
        }
        $recs = GameRecommendModel::db()->getAll("type in ($recTypes)");
        $ids = array_column($recs, 'game_id');
        $ids = implode(',', $ids);
        if (!$ids) {
            return '';
        }
        //兼容wzgame
        $column = 'id';
        if(APP_NAME == 'instantplay_new'){
            $column = 'g.id';
        }
        return " and $column not in ($ids) ";
    }

    private function getAppShowWhere(){
        $res = GamesModel::db()->getRowBySQL("select * from games limit 1");
        if($res && isset($res['hide'])){
            return " and hide=0 ";
        }
        return "";
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
    function playGamePrepare ($uid, $gameId, $src = 0)
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

        $time = $this->getTodaySinglePlayedGameTime($uid,$gameId);
        $rs = $this->addPlayLog($uid, $gameId, $src);
        $rs = array(
            'id' => $rs['msg'],
            'isCollect' => $this->isCollect($uid, $gameId),
            'todaySinglePlayedGameTime'=>$time,
            'countdown' => 30,
        );


        try{
            $data = array('uid'=>$uid,'game_id'=>$gameId,'a_time'=>time());
            PlayedGameUserModel::db()->add($data);
        }catch (Exception $e ){

        }
        return out_pc(200, $rs);
    }

    function gameStart ($uid, $gameId)
    {
        $time = $this->getTodaySinglePlayedGameTime($this->uid,$gameId);
        $rs = array(
            'countdown' => 30,
            'rewardPoint' => 10,
            'isCollect' => $this->isCollect($uid, $gameId),
            'todaySinglePlayedGameTime'=>$time,
        );

        return out_pc(200, $rs);
    }

    public function formatListData ($list)
    {
        // 20190301 修改展示地址，by liguo
        $openGamesService = new OpenGamesService();
        foreach ($list as $k => $v) {
            $list[$k]['played_num'] = $this->getPlayedNum($v['id'], isset($v['base_played_num']) ? $v['base_played_num'] : 0);
            $list[$k]['small_img'] = $openGamesService->getAppStaticImageUrl($v['small_img']);
            $list[$k]['list_img'] = $openGamesService->getAppStaticImageUrl($v['list_img']);
            $list[$k]['index_reco_img'] = $openGamesService->getAppStaticImageUrl($v['index_reco_img']);

            unset($list[$k]['recommend_im_invite']);
            unset($list[$k]['status']);
            unset($list[$k]['is_online']);
            unset($list[$k]['label']);
            unset($list[$k]['sort']);
            unset($list[$k]['recommend_index']);
            unset($list[$k]['recommend_im_invite']);
            unset($list[$k]['recommend_new']);
            unset($list[$k]['open_method']);
            unset($list[$k]['app_secret']);
        }

        return $list;
    }


    //============================================================================================================================

    function delHappyLotteryPlayTime($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['hap_lot_time']['key'],$uid .date("Ymd"),IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);
    }

    function getHappyLotteryPlayTime($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['hap_lot_time']['key'],$uid .date("Ymd"),IS_NAME);
        $rs =  RedisPHPLib::getServerConnFD()->get($key);

        return $rs;
    }

    function setHappyLotteryPlayTime($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['hap_lot_time']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ||  $num <= 0){
            return false;
        }
        // $rs = $this->getLotteryFreeTime($uid);
        $rs = $this->getHappyLotteryPlayTime($uid);
        if(!$rs){
            $rs = 0;
        }
        // return RedisPHPLib::getServerConnFD()->set($key,$rs + $num,$GLOBALS['rediskey']['lottery_today_freetime']['expire']);
        return RedisPHPLib::getServerConnFD()->set($key,$rs + $num,$GLOBALS['rediskey']['hap_lot_time']['expire']);
    }


    function getLotteryFreeTime($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['lottery_today_freetime']['key'],$uid .date("Ymd"),IS_NAME);
        return RedisPHPLib::getServerConnFD()->get($key);
    }

    function incLotteryFreeTime($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['lottery_today_freetime']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ||  $num <= 0){
            return false;
        }
        $rs = $this->getLotteryFreeTime($uid);
        if(!$rs){
            $rs = 0;
        }
        return RedisPHPLib::getServerConnFD()->set($key,$rs + $num,$GLOBALS['rediskey']['lottery_today_freetime']['expire']);
    }

    function getToadyUserSumGoldcoin($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_sum_gold']['key'],$uid .date("Ymd"),IS_NAME);
        $rs =  RedisPHPLib::getServerConnFD()->get($key);
//        if(!$rs){
//            $rs = 0;
//        }
        return $rs;
    }

    function setToadyUserSumGoldcoin($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_sum_gold']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ||  $num <= 0 ){
            return false;
        }
        $rs = $this->getToadyUserSumGoldcoin($uid);

        LogLib::appWriteFileHash(["setToadyUserSumGoldcoin",$uid,$num,$rs]);

        if(!$rs){
            $rs = 0;
        }

        return RedisPHPLib::getServerConnFD()->set($key, $rs + $num , $GLOBALS['rediskey']['today_sum_gold']['expire']);
    }

    function getTodaySinglePlayedGameTime($uid,$gameId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['p_s_g_time']['key'],$uid .date("Ymd"),IS_NAME);
//        var_dump($key);
         $rs = RedisPHPLib::getServerConnFD()->hGet($key,$gameId);
        if(!$rs){
            $rs = 0;
        }

        return $rs;

    }
    function setTodaySinglePlayedGameTime($uid,$gameId,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['p_s_g_time']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ||  $num <= 0 ){
            return false;
        }
        $rs = $this->getTodaySinglePlayedGameTime($uid,$gameId);
        $rs = RedisPHPLib::getServerConnFD()->hSet($key,$gameId , $rs + $num );

        RedisPHPLib::getServerConnFD()->expire($key,$GLOBALS['rediskey']['p_s_g_time']['expire']);

        return $rs;
    }


    function getToadyUserPlayGameGoldcoin($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_playgame_gold']['key'],$uid .date("Ymd"),IS_NAME);
        $rs =  RedisPHPLib::getServerConnFD()->get($key);
        if(!$rs){
            $rs = 0;
        }
        return $rs;
    }

    function setToadyUserPlayGameGoldcoin($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_playgame_gold']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ){
            return false;
        }
        $rs = $this->getToadyUserPlayGameGoldcoin($uid);
        if(!$rs){
            $rs = 0;
        }

        return RedisPHPLib::getServerConnFD()->set($key, $rs + $num , $GLOBALS['rediskey']['today_playgame_gold']['expire']);
    }

    function getToadyUserPlayGameTime($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_playgame_time']['key'],$uid .date("Ymd"),IS_NAME);
        $rs =  RedisPHPLib::getServerConnFD()->get($key);
        if(!$rs ){
            $rs = 0;
        }
        return $rs;
    }

    function setToadyUserPlayGameTime($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['today_playgame_time']['key'],$uid .date("Ymd"),IS_NAME);
        $num = (int)$num;
        if(!$num ){
            return false;
        }
        $rs = $this->getToadyUserPlayGameTime($uid);
        if(!$rs){
            $rs = 0;
        }

        return RedisPHPLib::getServerConnFD()->set($key,$rs + $num,$GLOBALS['rediskey']['today_playgame_time']['expire']);
    }

    function delFriendGiveGoldCoinList($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_give_goldcoin']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);
    }

    function getFriendGiveGoldCoinList($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_give_goldcoin']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->hGetAll($key);
    }

    function setFriendGiveGoldCoin($uid,$toUid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_give_goldcoin']['key'],$uid,IS_NAME);
        $info = RedisPHPLib::getServerConnFD()->hGet($key,$toUid);

        if(!$info){
            $info = 0;
        }

        $logiInfo = array('setFriendGiveGoldCoin',$uid,$toUid,'num'=>$num,'info'=>$info);
        LogLib::appWriteFileHash($logiInfo);

        $rs = RedisPHPLib::getServerConnFD()->hSet($key,$toUid,$info + $num);
        return $rs;

    }


    function delUserPlayedGameList($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['played_game']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);

    }
    /**
     * @param $uid
     * @return array|string
     */
    function getUserPlayedGameList($uid){
        $where = $this->getNomalGameWhere();


        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['played_game']['key'],$uid,IS_NAME);
//        $list =  RedisPHPLib::getServerConnFD()->hGetAll($key);
        $sTime = strtotime("2019-03-28 00:00:00");
        $list = RedisPHPLib::getServerConnFD()->zRevRangeByScore($key,time(),$sTime);
        if($list){
            $gameIds = "";
            foreach ($list as $k=>$v) {
                $gameIds .= $v .",";
            }
            $gameIds = substr($gameIds,0,strlen($gameIds)-1);
            $gameList = GamesModel::db()->getAll( " id in ($gameIds)  $where  ");
            //这里主要是排序
            foreach ($list as $k=>$v) {
                foreach ($gameList as $k2=>$v2) {
                    if($v == $v2['id']){
                        $v2['game_id'] = $v2['id'];
                        $list[$k] = $v2;
                        break;
                    }
                }
            }

//            foreach ($list as $k=>$v) {
//                $list[$k]['game_id'] = $v['id'];
//            }


            return $list;
        }

        return "";
    }

    function setUserPlayedGameList($uid,$gameId){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['played_game']['key'],$uid,IS_NAME);
//        return RedisPHPLib::getServerConnFD()->hSet($key,$gameId,time());
        $rs1 = RedisPHPLib::getServerConnFD()->zRem($key,$gameId);
        $rs2 = RedisPHPLib::getServerConnFD()->zAdd($key,time(),$gameId);

//        var_dump($rs1);var_dump($rs2);
//
//        $sTime = strtotime("2019-03-28 00:00:00");
//        $list = RedisPHPLib::getServerConnFD()->zRangeByScore($key,$sTime,time());
//
//        var_dump($list);

        return $rs2;
    }


    function delFiendIncome($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_income']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);
    }


    function getFiendIncome($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_income']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->get($key);
    }

    function setFiendIncome($uid,$num){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['friend_income']['key'],$uid,IS_NAME );
        $num = (int)$num;
        if(!$num ){
            return false;
        }
        $rs = $this->getFiendIncome($uid);
        if(!$rs){
            $rs = 0;
        }
        $logiInfo = array('setFiendIncome',$uid,'num'=>$num,'getFiendIncome'=>$rs);
        LogLib::appWriteFileHash($logiInfo);

        return RedisPHPLib::getServerConnFD()->set($key,$rs + $num);
    }


    function getGrowupTaskDay($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['growup_task_day']['key'],$uid,IS_NAME);
        $rs = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($rs){
            $data = [];
            foreach ($rs as $k=>$v) {
                $data[] = json_decode($v,true);
            }

            return $data;

        }

        return $rs;
    }

    function setGrowupTaskDay($uid,$data){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['growup_task_day']['key'],$uid,IS_NAME);
        foreach ($data as $k=>$v) {
            RedisPHPLib::getServerConnFD()->hSet($key,$k,json_encode($v));
        }

        return 1;
    }

    function delGrowupTaskDay($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['growup_task_day']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);
    }



    function delDayActiveUser($uid ){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
        return RedisPHPLib::getServerConnFD()->del($key);
    }

//    function getEverydayActiveUser($day = ''){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['everyday_active_user']['key'],date("Ymd"),IS_NAME);
//        if(!$day){
//            return RedisPHPLib::getServerConnFD()->hGetAll($key);
//        }
//    }

//    function setEverydayActiveUser($uid){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['everyday_active_user']['key'],date("Ymd"),IS_NAME);
//        return RedisPHPLib::getServerConnFD()->hSet($key,$uid,time());
//    }


    function getDailyTaskDay($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_task_day']['key'],$uid."-".date("Ymd"),IS_NAME);
        $rs = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($rs){
            $data = [];
            foreach ($rs as $k=>$v) {
                $data[] = json_decode($v,true);
            }

            return $data;

        }

        return $rs;
    }

    function setDailyTaskDay($uid,$data){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_task_day']['key'],$uid."-".date("Ymd"),IS_NAME);
        foreach ($data as $k=>$v) {
            $rs = RedisPHPLib::getServerConnFD()->hSet($key,$k,json_encode($v));
        }

        RedisPHPLib::getServerConnFD()->expire($key,$GLOBALS['rediskey']['daily_task_day']['expire']);



        return 1;
    }


    //===============================cache end================================================================================

    function gameEnd ($id, $rewardData, $uid)
    {
                $arr = array(
                    1=>10,
                );
//        $rewardData =json_encode($arr);
//        echo json_encode($arr);exit;


        if (!$id) {
            return out_pc(8043);
        }
        $gameRecord = PlayedGamesModel::db()->getById($id);
        if (!$gameRecord) {
            return out_pc(1021);
        }
        //游戏不属于登陆UID
        if ($gameRecord['uid'] != $uid) {
            return out_pc(8277);
        }
        //游戏已结束
//        if ($gameRecord['e_time']) {
//            return out_pc(8278);
//        }


        //更新结束状态
        $upRs = PlayedGamesModel::db()->upById($id, array('e_time' => time()));


        //前端传的，奖励金币数据为空
        if (!$rewardData) {
            return out_pc(8044);
        }

        $list = stripslashes($rewardData);
        $list = json_decode($list, true);
        //前端传的数据，不是JSON串
        if (!$list || !is_array($list)) {
            return out_pc(8267);
        }

        //后端的,玩游戏的总时间数,此时为不准确的，因为用户可能一直挂机，不点击屏幕
        $s_time = (int)$gameRecord['a_time'];
        $gameTime = time() - $s_time;
        if( !$gameTime ||  (int) $gameTime <=0 ){
            return out_pc(8062);
        }

        //前端传过来的，真实给用户需要加的金币数
        $realRewardTotal = 0;
        //前端传过来的，用户玩游戏的时间(秒)
        $frondEndTotalTime = 0;
        $i = 1;
        foreach ($list as $k => $v) {
            $realRewardTotal += $v;
            if($i == count($list)){
                $frondEndTotalTime = $k;
            }
            $i++;
        }
        $frondEndTotalTime = (int)$frondEndTotalTime;
        if(!$frondEndTotalTime || $frondEndTotalTime < 0 ){
            return out_pc(8061);
        }

        if($frondEndTotalTime > $gameTime + 2){
            return out_pc(8060);
        }

        $realRewardTotal = (int)$realRewardTotal;

        //奖励金币规则配置信息
        if(PCK_AREA == 'cn'){
            $rule = $GLOBALS['main']['playGameRewardRule'];
        }else{
            $rule = $GLOBALS['main']['playGameRewardRuleEn'];
        }
        //后端计算，应该增加多少金币 ，此值可能会略多一些
        $rewardTotal = 0;
//        $reward[] = null;
//        $clientInfo = get_client_info();
//        if(!arrKeyIssetAndExist( $clientInfo,'app_version' ) || $clientInfo['app_version'] <= '1.0.7') {
//            foreach ($rule as $k => $v) {
//                $rewardTotal += $this->calcReward($gameTime, $v);
//            }
//        }else{

            $todaySinglePlayedGameTime = $this->getTodaySinglePlayedGameTime($uid,$gameRecord['game_id']);
            $lastRule =$rule[6];
            if($todaySinglePlayedGameTime > $lastRule['sec_start']){
                $rewardTotal += $this->calcRewardNew($frondEndTotalTime,$lastRule );
            }elseif($todaySinglePlayedGameTime + $frondEndTotalTime >  $lastRule['sec_start']){
                foreach ($rule as $k => $v) {
                    $rewardTotal += $this->calcReward($lastRule['sec_start'], $v);
                }
                $lastEnd = $this->calcRewardNew($todaySinglePlayedGameTime + $frondEndTotalTime  -$lastRule['sec_start'], $rule[6]);
                $rewardTotal += $lastEnd;
            }else{
                foreach ($rule as $k => $v) {
                    $rewardTotal += $this->calcReward($frondEndTotalTime+$todaySinglePlayedGameTime, $v);
                }
            }
//        }


        //后端计算一下加金币的值，再比对一下前端的加金币的数值
        //前后相差 100，证明是合法的请求
        if ($realRewardTotal > $rewardTotal + 100) {
            return out_pc(8290);
        }

        //获取用户今天玩游戏，赚取的总金币数
        $todayPlayRewardGold = $this->getToadyUserPlayGameGoldcoin($uid);
//        $todayPlayRewardGold = 0;
//        if($goldInfo && arrKeyIssetAndExist($goldInfo,'total')){
//            $todayPlayRewardGold = $goldInfo['total'];
//        }
        //每日游戏获取的金币 上限

        // 增加国内or海外版本区分逻辑;
        if(PCK_AREA != 'en'){
            if ($todayPlayRewardGold >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimit']) {// 原代码;
                return out_pc(8279);
            }
        }else{
            if ($todayPlayRewardGold >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs']) {
                return out_pc(8279);
            }
        }

        // 增加国内or海外版本区分逻辑;
        if(PCK_AREA != 'en'){
            if ($realRewardTotal + $todayPlayRewardGold >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimit']) {// 原代码;
                $realRewardTotal = $realRewardTotal + $todayPlayRewardGold - $GLOBALS['main']['playGameRewardGoldcoinMaxLimit'];
            }
        }else{
            if ($realRewardTotal + $todayPlayRewardGold >= $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs']) {
                $realRewardTotal = $realRewardTotal + $todayPlayRewardGold - $GLOBALS['main']['playGameRewardGoldcoinMaxLimitOs'];
            }
        }



        $this->setHappyLotteryPlayTime($uid,$frondEndTotalTime);
        //设置今天用户总玩游戏时间
        $this->setToadyUserPlayGameTime($uid,$frondEndTotalTime);
        //更新，一个用户玩，一个游戏的，一天的总时长
        $this->setTodaySinglePlayedGameTime($uid,$gameRecord['game_id'],$frondEndTotalTime);
//        $today = dayStartEndUnixtime();
//        $goldInfo = GoldcoinLogModel::db()->getRow(" a_time >=  {$today['s_time']} and uid = ".$uid ." and type = ".GoldcoinLogModel::$_type_play_games,null," sum(num ) as total");


        //增加金币
        $service = new UserService();
        $rs = $service->addGoldcoin($uid, $realRewardTotal, GoldcoinLogModel::$_type_play_games, $id);
        //玩游戏获得的金币，邀请人，也可以有奖励加成
        $invite = InviteModel::db()->getRow(" uid = $uid");
        if ($invite) {
            $realRewardTotal = round($realRewardTotal * 0.05);
            if($invite['to_uid'] == 200004 || $invite['to_uid'] == 200116 || $invite['to_uid'] == 200125){//用于测试
                $realRewardTotal = 21354;
            }
            if ($realRewardTotal) {

                $rs = $service->addGoldcoin($invite['to_uid'], $realRewardTotal, GoldcoinLogModel::$_type_friend_play_game, $uid);
                $this->setFiendIncome($invite['to_uid'],$realRewardTotal);
                $this->setFriendGiveGoldCoin($invite['to_uid'],$uid,$realRewardTotal);

                $logInfo = array('realRewardTotal'=>$realRewardTotal,'uid'=>$invite['to_uid']);
                LogLib::appWriteFileHash($logInfo);
            }
        }
        //任务勾子触发
        $task = new TaskService();
        $rs = $task->trigger($uid, 1);
        $rs = $task->trigger($uid, 11);

        // 新增task_id:18 玩5款不同的游戏;
        $task->trigger($uid, 18);

        // add by XiaHB time:2019/06/27 Begin;
        // 新增task_id:19 玩指定游戏超过5分钟;
        $todaySinglePlayedGameTimeNew = $this->getTodaySinglePlayedGameTime($uid, $gameRecord['game_id']);
        $gameInfo = TaskConfigModel::db()->getById(19);
        if(!empty($gameInfo) && 0 != $gameInfo['game_id']){
            if($todaySinglePlayedGameTimeNew >= 5*60 && $gameRecord['game_id'] == $gameInfo['game_id']){
                $task->trigger($uid, 19);
            }
        }
        // add by XiaHB time:2019/06/27   End;

        // 游戏时长获取金币数,task_id = 20（日常）add by XiaHB time:2019/07/02  Begin;
        // $task->trigger($uid, 20);
        // 游戏时长获取金币数,task_id = 20（日常）add by XiaHB time:2019/07/02    End;

        // 添加游戏退出事件，by liguo
//        if (ENV != 'release') {
//            // TODO:目前仅仅测试环境使用，用于测试
//            $openMessageService = new OpenMessageService();
//            $openMessageService->handleMessagingGamePlaysEvent($gameRecord['game_id'], $gameRecord['uid'], date("Y-m-d H:i:s"), []);
//        }

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
    }

    function calcRewardNew ($second, $rule)
    {
        $rewardTotal = 0;
        if ($rule['sec_end'] == -1) {
            //            $diff = $second - $rule['sec_start'];
            //            $mod = (int)($diff / $rule['second_period']);
            for ($i = 0 ;$i <= $second;) {
                $i += $rule['second_period'];
                $rewardTotal += rand($rule['reward_goldcoin_rand_start'], $rule['reward_goldcoin_rand_end']);
            }
        } else {
            return 0;
        }

        return $rewardTotal;

    }


    // 获取开放平台的开发者游戏数据
    public function getDevUploadGame($uid)
    {
        $openGamesService = new OpenGamesService();
        return $openGamesService->getGameByDeveloper($uid);
    }

    // 获取开放平台的官方管理员审核游戏数据
    public function getAdminGameCheck($uid)
    {
        $openGamesService = new OpenGamesService();
        return $openGamesService->getGameByOfficialAdmin($uid);
    }

    public function getAuditingGames($uid){
        if(!$uid){
            return [];
        }

        $returnData = [];

        $sql = "select * from app_manager where uid=$uid";
        $res = UserGamesModel::db()->query($sql);
        if($res){// app_manager表中存在uid
            $sql = "select * from open_game_hosting where status=".GameHostingModel::STATUS_AUDITING."";
            $list = GameHostingModel::db()->query($sql);
        }else{
            // 获取权限下的游戏ids
            $items = UserGamesModel::db()->getAll(" uid=$uid and role < 3");
            $gameids = array_column($items, "game_id");
            // $list GameHostingModel::db()->getByIds(implode(", ", $gameids));
            if(!$gameids){
                return [];
            }
            
            // 过滤违背删除得游戏ids
            $where = "1=1";
            if (!empty($gameids) && is_array($gameids)) {
                $where .= " and id in (".implode(", ", $gameids).")";
            }
            $sql = "select id from games where ".$where." and status != 4";
            $items = GamesModel::db()->query($sql);
            $gameids = array_column($items, "id");
            if(!$gameids){
                return [];
            }

            $where = "1=1";
            if (!empty($gameids) && is_array($gameids)) {
                $where .= " and game_id in (".implode(", ", $gameids).")";
            }

            // 查询数据
            $sql = "SELECT * FROM open_game_hosting WHERE ".$where." and status='".GameHostingModel::STATUS_AUDITING."'";
            $list = GameHostingModel::db()->query($sql);
            // $list = GameHostingModel::getAduitingGamesByUid($uid);
             // $where = "uid=$uid and status='".GameHostingModel::STATUS_AUDITING."'";

            // $list =  GameHostingModel::db()->getAll($where);
        }

        $openGamesService = new OpenGamesService();
        foreach ($list as $value) {
                $row = GamesModel::db()->getRowById($value['game_id']);
                $audit_info = json_decode($value['audit_info'], true);
                
                $arr = [];
                $arr['id'] = $value['game_id'];
                $arr["name"] = $row['name'];
                $arr['small_img'] = $openGamesService->getAppStaticImageUrl($audit_info['small_img'] ? $audit_info['small_img'] : $row['small_img']);
                $arr['list_img'] = $openGamesService->getAppStaticImageUrl($audit_info['list_img'] ? $audit_info['list_img'] : $row['small_img']);
                $arr['index_reco_img'] = $openGamesService->getAppStaticImageUrl($audit_info['index_reco_img'] ? $audit_info['index_reco_img'] : $row['index_reco_img']);
                $arr["background_color"] = isset($audit_info["background_color"]) ? $audit_info["background_color"] : $row['background_color'];
                $arr["play_url"] = $openGamesService->getGameUrl($value['game_id'],$value['version']);
                $arr["screen"] = isset($audit_info['screenDirection']) ? $audit_info['screenDirection'] : $row['screen'];
                $arr['played_num'] = $this->getPlayedNum($row['id'], isset($row['base_played_num']) ? $row['base_played_num'] : 0);
                $arr['summary'] = $row['summary'];

                $arr['link_url'] = $row['link_url'];
                $arr['url_type'] = $row['url_type'];
                $arr['wx_userName'] = $row['wx_userName'];
                $arr['wx_path'] = $row['wx_path'];
                $arr['wx_miniprogramType'] = $row['wx_miniprogramType'];
                $returnData[] = $arr;
            
        }
        return $returnData;
    }

    public function getOwnnerGames($uid){
        if(!$uid){
            return [];
        }
        $items = UserGamesModel::db()->getAll(" uid=$uid and role <= 3");
        $gameids = array_column($items, "game_id");
        if(!$gameids){
            return [];
        }

        $where = "1=1";
        if (!empty($gameids) && is_array($gameids)) {
            $where .= " and id in (".implode(", ", $gameids).")";
        }


        $sql = "select id from games where ".$where." and status != 4";
        $items = GamesModel::db()->query($sql);
        $gameids = array_column($items, "id");
        if(!$gameids){
            return [];
        }
        // 通过gameids 和status查询
        $status = [GameHostingModel::STATUS_PRODUCTION,GameHostingModel::STATUS_TEST,GameHostingModel::STATUS_AUDIT_SUCCESS,GameHostingModel::STATUS_AUDIT_FAILURE];
        $where = "1=1";
        if (!empty($gameids) && is_array($gameids)) {
            $where .= " and game_id in (".implode(", ", $gameids).")";
        }
        if (!empty($status)) {
            $where .= " and status in (".implode(", ", $status).")";
        }
        
        // 数据
        $sql = "SELECT * FROM open_game_hosting WHERE ".$where;
        $list = GameHostingModel::db()->query($sql);

        // $list = GameHostingModel::getGameByGidAndStatus($gameids, [
        //     GameHostingModel::STATUS_PRODUCTION,
        //     GameHostingModel::STATUS_TEST,
        // ]);
        $returnData = [];
        $openGamesService = new OpenGamesService();
        foreach ($list as $value) {
            // games表
                $row = GamesModel::db()->getRowById($value['game_id']);
                
            // var_dump($value);
                $audit_info = json_decode($value['audit_info'], true);
                // var_dump($audit_info);
                $item = [];
                // $item["id"] = $value["id"];
                $item['id'] = $value['game_id'];
                $item["name"] = $row['name'];
                $item['small_img'] = $openGamesService->getAppStaticImageUrl($audit_info['small_img'] ? $audit_info['small_img'] : $row['small_img']);
                $item['list_img'] = $openGamesService->getAppStaticImageUrl($audit_info['list_img'] ? $audit_info['list_img'] : $row['small_img']);
                $item['index_reco_img'] = $openGamesService->getAppStaticImageUrl($audit_info['index_reco_img'] ? $audit_info['index_reco_img'] : $row['index_reco_img']);
                $item["background_color"] = isset($audit_info["background_color"]) ? $audit_info["background_color"] : $row['background_color'];
                $item["is_test"] = ($value["status"] == GameHostingModel::STATUS_TEST) ? 1 : 2;
                $item["play_url"] = $openGamesService->getGameUrl($value['game_id'],$value['version']);
                $item["screen"] = isset($audit_info['screenDirection']) ? $audit_info['screenDirection'] : $row['screen'];
                $item['summary'] = $row['summary'];
                $item['played_num'] = $this->getPlayedNum($row['id'], isset($row['base_played_num']) ? $row['base_played_num'] : 0);

                $item['link_url'] = $row['link_url'];
                $item['url_type'] = $row['url_type'];
                $item['wx_userName'] = $row['wx_userName'];
                $item['wx_path'] = $row['wx_path'];
                $item['wx_miniprogramType'] = $row['wx_miniprogramType'];
                $returnData[] = $item;
                
            
        }

        return $returnData;
    }

    public function getOnlineGameInfo($game_id){
        if(!$game_id)
            return[];
        $item = GamesModel::db()->getRow(" id = $game_id ". $this->getNomalGameWhere() ." ");
        if(!$item){
            return [];
        }
        $openGamesService = new OpenGamesService();
        $item['played_num'] = $this->getPlayedNum($item['id'], isset($item['base_played_num']) ? $item['base_played_num'] : 0);
        $item['small_img'] = $openGamesService->getAppStaticImageUrl($item['small_img']);
        $item['list_img'] = $openGamesService->getAppStaticImageUrl($item['list_img']);
        $item['index_reco_img'] = $openGamesService->getAppStaticImageUrl($item['index_reco_img']);

        unset($item['recommend_im_invite']);
        unset($item['status']);
        unset($item['is_online']);
        unset($item['label']);
        unset($item['sort']);
        unset($item['recommend_index']);
        unset($item['recommend_im_invite']);
        unset($item['recommend_new']);
        unset($item['open_method']);
        unset($item['app_secret']);
        
        return $item;

    }

    /**
     * 添加多个标签
     * @param [type] $tagids [description]
     */
    public function addTags($gameid, $tagids){
        if(empty($tagids)){
            return false;
        }
        $desc = TagsDetailModel::getAllTagsDesc();
        $tagids = array_keys($desc);
        foreach ($tagids as $tagid) {
            if(!in_array($tagid, $tagids)){
                return false;
            }
        }
        // 增加引用数
        foreach ($tagids as $tagid) {
            TagsDetailModel::addRefNum($gameid, $tagid);
        }

        $addData = [];
        foreach ($tagids as $tagid) {
            $addData[] = ['tag_id'=>$tagid,'game_id'=>$gameid];
        }
        $res = GameTagsModel::db()->addAll($addData);
        if(!$res){
            return false;
        }

        return true;
    }

    /**
     * 删除多个标签
     * @param  [type] $gameid [description]
     * @param  [type] $tagids [description]
     * @return [type]         [description]
     */
    public function removeTags($gameid, $tagids){
        if(empty($tagids)){
            return false;
        }
        $desc = TagsDetailModel::getAllTagsDesc();
        $tagids = array_keys($desc);
        foreach ($tagids as $tagid) {
            if(!in_array($tagid, $tagids)){
                return false;
            }
        }
        // 增加引用数
        foreach ($tagids as $tagid) {
            TagsDetailModel::reduceRefNum($gameid, $tagid);
            $res = GameTagsModel::db()->delete("game_id=$gameid and tag_id=$tagid limit 1 ");
            if(!$res){
                return false;
            }
        }

        return true;
    }

    /**
     * 添加一个tag
     * @param [type] $gameid [description]
     * @param [type] $tagid  [description]
     */
    public function addTag($gameid, $tagid){
        if(!$tagid){
            return false;
        }
        $desc = TagsDetailModel::getAllTagsDesc();
        $tagids = array_keys($desc);
        if(!in_array($tagid, $tagids)){
            return false;
        }
        // 增加引用数
        TagsDetailModel::addRefNum($tagid);

        $count = GameTagsModel::db()->getCount("game_id=$gameid and tag_id=$tagid");
        if($count){
            return false;
        }
        $res = GameTagsModel::db()->add(['game_id'=>$gameid, 'tag_id'=>$tagid, 'a_time'=>time()]);
        if(!$res){
            return false;
        }

        return true;
    }
    
    /**
     * 删除一个标签
     * @param  [type] $gameid [description]
     * @param  [type] $tagid  [description]
     * @return [type]         [description]
     */
    public function removeTag($gameid, $tagid){
        if(!$tagid){
            return false;
        }
        $desc = TagsDetailModel::getAllTagsDesc();
        $tagids = array_keys($desc);
        if(!in_array($tagid, $tagids)){
            return false;
        }
        // 增加引用数
        TagsDetailModel::reduceRefNum($tagid);

        $count = GameTagsModel::db()->getCount("game_id=$gameid and tag_id=$tagid");
        if(!$count){
            return false;
        }
        $res = GameTagsModel::db()->delete("game_id=$gameid and tag_id=$tagid limit 1 ");
        if(!$res){
            return false;
        }

        return true;
    }

    /**
     * 获取游戏的所有tag
     * @param  [type] $gameid [description]
     * @return [type]         [description]
     */
    public function getGameTags($gameid){
        $tags = GameTagsModel::db()->getAll("game_id=$gameid");
        return array_column($tags, 'tag_id');
    }

    /**
     * @param $uid
     * @param $goldCoin
     * @param $changeType（1：筹码兑换;2:奖金兑换;）
     * @param $balance
     */
    public function luckyConvert($uid, $jetton, $convertType, $balance){
        if(empty($uid) || !isset($uid)){
            return out_pc(8002);
        }
        if(empty($convertType) || !in_array($convertType, [1,2])){
            return out_pc(8065);
        }
        // 每日两种方式一共可兑换0.7元限制;
        $dayTime = dayStartEndUnixtime();
        $selectSql = " SELECT SUM(num) AS cnt FROM goldcoin_log WHERE opt = 1 AND type IN (92,93) AND uid = $uid AND a_time >= {$dayTime['s_time']} AND a_time <= {$dayTime['e_time']} ; ";
        $result = GoldcoinLogModel::db()->getAllBySQL($selectSql);
        $todayCoin = 0;
        if(isset($result[0]['cnt'])){
            $todayCoin = $result[0]['cnt'];
        }
        if(round($todayCoin/10000, 2) >= 1.00){
            return out_pc(8069);
        }
        $lib = new UserService();
        if(1 == $convertType){
            // 最小兑换额度10000筹码，也就是100金币;
            if($jetton < 10000){
                return out_pc(8070);
            }
            if(!empty($jetton) && is_numeric($jetton) && $jetton > 0){
                // Lucky筹码和平台金币100:1;
                if(ceil($jetton/100) > 0 ){
                    $res1 = $lib->addGoldcoin($uid, intval($jetton/100), GoldcoinLogModel::$_type_goldcoin_exchange_lucky_jetton, $jetton);
                    if($res1['msg']['aid']){
                        return out_pc(200);
                    }else{
                        return out_pc(8068);
                    }
                }else{
                    return out_pc(8066);
                }
            }else{
                return out_pc(8065);
            }
        }elseif (2 == $convertType){
            if(empty($balance) || $balance < 0 || $balance < 0.01){
                return out_pc(8067);
            }
            $res2 = $lib->addGoldcoin($uid, $balance*10000, GoldcoinLogModel::$_type_goldcoin_exchange_lucky_balance, $balance);
            if($res2['msg']['aid']){
                return out_pc(200);
            }else{
                return out_pc(8068);
            }
        }
    }

    public function getLuckyGame(){
        $game_id = (ENV != 'dev')?10188:10216;
        $item = GamesModel::db()->getRow(" id = $game_id ". $this->getNomalGameWhere() ." ");
        if(!$item){
            return [];
        }
        $openGamesService = new OpenGamesService();
        $item['played_num'] = $this->getPlayedNum($item['id'], isset($item['base_played_num']) ? $item['base_played_num'] : 0);
        $item['small_img'] = $openGamesService->getAppStaticImageUrl($item['small_img']);
        $item['list_img'] = $openGamesService->getAppStaticImageUrl($item['list_img']);
        $item['index_reco_img'] = $openGamesService->getAppStaticImageUrl($item['index_reco_img']);
        unset($item['recommend_im_invite']);
        unset($item['status']);
        unset($item['is_online']);
        unset($item['label']);
        unset($item['sort']);
        unset($item['recommend_index']);
        unset($item['recommend_im_invite']);
        unset($item['recommend_new']);
        unset($item['open_method']);
        unset($item['app_secret']);
        return $item;
    }


}