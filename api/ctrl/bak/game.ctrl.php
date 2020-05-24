<?php
class GameCtrl extends BaseCtrl  {

    //推荐首页的游戏列表
//    function recommendIndex(){
//        $list = $this->gamesService->recommendIndex();
//        $this->out(200,$list);
//    }
    //最近上线的游戏
//    function topOnline(){
//        $list = $this->gamesService->topOnline();
//        $this->out(200,$list);
//    }
    //所有游戏的列表
    function getList($page=1, $f_key=0){
        $list = $this->gamesService->getAllList($page, $f_key);
//        LogLib::appWriteFileHash($list);
        $this->out(200,$list);
    }
    //除了推荐游戏之外的游戏
//    function exceptRecommendIndex($page = 1){
//        $list = $this->gamesService->exceptRecommendIndex($page);
//        $this->out(200,$list);
//    }

    function getRecommends($type)
    {
        $type = $type ? $type : 1;
         // 是否被推荐
        $recommeds = GameRecommendModel::db()->getAllBySQL("select game_id from game_recommend where type = $type order by sort desc");
        if ($recommeds) {
            $ids = array_column($recommeds, 'game_id');
            $idstr = implode(',', $ids);
            $games = GamesModel::db()->getAll("id in ($idstr)");
            if ($games) {
                $data = [];
                foreach ($ids as $id) {
                    foreach ($games as $game) {
                        if ($game['id'] == $id) {
                            $data[] = $game;
                            break;
                        }
                    }
                }
                $this->out(200,$this->gamesService->formatListData($data));
            }
        }
        return $this->out(8063,$GLOBALS['code'][8063]);
    }
    //玩过的游戏列表
    function playedGameHistoryList($toUid = 0 ){
        $uid = $this->uid;
        if($toUid){
            $uid = $toUid;
        }
        $list = $this->gamesService->playedGameHistoryList($uid);
        $this->out(200,$list);
    }

    //收获游戏
    function addCollect($gameId){
        $rs = $this->gamesService->addCollect($this->uid,$gameId);
        return $this->out($rs['code'],$rs['msg']);
    }

    //取消收藏
    function cancelCollect($gameId){
        $rs = $this->gamesService->cancelCollect($this->uid,$gameId);
        return $this->out($rs['code'],$rs['msg']);
    }
    //IM内游戏推荐 托盘
    function getImGameInvite(){
        $list = $this->gamesService->getImGameInvite($this->uid);
        $this->out(200,$list);
    }

//  游戏前，获取 基本参数，用于防刷
    function gameStart($gameId, $src = 0){
        $rs = $this->gamesService->playGamePrepare($this->uid,$gameId, $src);
//        LogLib::appWriteFileHash($rs);


        return $this->out($rs['code'],$rs['msg']);
    }
    //C端获取  玩游戏 加金币 的  规则，死数据
    function getPlayGameRewardRule(){
        if(PCK_AREA == 'cn'){
            return $this->out(200,$GLOBALS['main']['playGameRewardRule']);
        }else{
            return $this->out(200,$GLOBALS['main']['playGameRewardRuleEn']);
        }
    }

    function gameEnd($id,$rewardData){
        $rs = $this->gamesService->gameEnd($id,$rewardData,$this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }
    //0分钟1秒
    function todayPlayGameTimeTotal($timeFormatType = 0 ){
//        $today = dayStartEndUnixtime();
//        $list = PlayedGamesModel::db()->getAll(" uid = ".$this->uid . " and a_time >= ".$today['s_time']);
//        if(!$list){
//            return $this->out(200,0);
//        }
//
//        $total = 0;
//        foreach($list as $k=>$v){
//            if($v['a_time'] > 0 && $v['e_time'] > 0){
//                $total += $v['e_time'] - $v['a_time'];
//            }
//        }
        $total = $this->gamesService->getToadyUserPlayGameTime($this->uid);
        if($total){
            if(!$timeFormatType){
                $total = (int)($total/60);
            }
        }

        return $this->out(200,$total);
    }
    //开发者 - 获取 自己上传的游戏
    function getDevUploadGame(){
        //返回数据格式需要：游戏名、游戏图片(列表图片就行)、游戏ID、是否为测试版、背景色
        return $this->out(200, $this->gamesService->getOwnnerGames($this->uid));
    }
    //管理员 -  获取 待审核的游戏
    function getAdminGameCheck(){
        //返回数据格式需要：游戏名、多少人玩过此游戏、游戏图片(列表图片就行)、游戏ID、背景色
        return $this->out(200, $this->gamesService->getAuditingGames($this->uid));
    }


    //用户开始玩-游戏，记录
//    function addPlayLog($gameId){
//        $rs = $this->gamesService->addPlayLog($this->uid,$gameId);
//        return $this->out($rs['code'],$rs['msg']);
//    }
//    //游戏前，获取 基本参数，用于防刷
//    function playGamePrepare($gameId){
//        $rs = $this->gamesService->playGamePrepare($this->uid,$gameId);
//        return $this->out($rs['code'],$rs['msg']);
//    }
    function getGameInfo($gameid){
        // $gameid = _g('gameid');
        
        return $this->out(200, $this->gamesService->getOnlineGameInfo($gameid));
    }

    /**
     * Lucky金币兑换;
     * @param int $jetton
     * @param $convertType
     * @param $balance
     * @return array
     */
    public function exchangeLuckyGold($jetton = 0, $convertType, $balance){
        $res = $this->gamesService->luckyConvert($this->uid, $jetton, $convertType, $balance);
        return $this->out($res['code'], $res['msg']);
    }

    /**
     * 当前还能获取的金币额度;
     */
    public function residueLuckyCoin(){
        $dayTime = dayStartEndUnixtime();
        $selectSql = "SELECT sum(num) AS cnt FROM goldcoin_log WHERE uid = {$this->uid} AND type IN (92,93) AND opt = 1 AND a_time >= {$dayTime['s_time']} AND a_time <= {$dayTime['e_time']}";
        $info = GoldcoinLogModel::db()->getAllBySQL($selectSql);
        $numAll = array(
            'jettonNum' => 10000*100,
            'balanceNum' => 1.00,
        );
        if(isset($info[0]['cnt']) && !empty($info[0]['cnt'])){
            if($info[0]['cnt'] < 10000){
                $num = (int)(10000 - $info[0]['cnt']);
                $numAll = array(
                    'jettonNum' => $num*100,
                    'balanceNum' => round($num/10000, 2),
                );
            }else{
                $numAll = array(
                    'jettonNum' => 0,
                    'balanceNum' => 0,
                );
            }
        }
        return $this->out(200, $numAll);
    }

    /**
     * hide = 1;(0：显示；1：隐藏)
     * Lucky游戏信息获取;
     */
    public function getSingleGameInfo(){
        return $this->out(200, $this->gamesService->getLuckyGame());
    }

    /**
     * @param $gameId
     * @param int $src
     * @return array
     */
    public function gameLuckyBegin($gameId){
        $uid = $this->uid;
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        $data = array(
            'uid' => $uid,
            'game_id' => $gameId,
            'src' => 0,
            'a_time' => time(),
            'e_time' =>0,
        );
        $aid = PlayedGamesModel::db()->add($data);
        $rs["id"] = $aid;
        if($aid){
            return $this->out(200, $rs);
        }
        return $this->out(8044, $GLOBALS['code'][8044]);

    }

    /**
     * @param $id
     * @return array
     */
    public function gameLuckyOver($id){
        $uid = $this->uid;
        $gameRecord = PlayedGamesModel::db()->getById($id);
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        if (!$gameRecord) {
            return $this->out(1021, $GLOBALS['code'][1021]);
        }
        if ($gameRecord['uid'] != $uid) {
            return $this->out(8277, $GLOBALS['code'][8277]);
        }
        $upRs = PlayedGamesModel::db()->upById($id, array('e_time' => time()));
        if($upRs){
            return $this->out(200);
        }
        return $this->out(8044, $GLOBALS['code'][8044]);
    }

}