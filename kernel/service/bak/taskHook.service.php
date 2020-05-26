<?php

class TaskHookService {
    public $_config = null;
    public $_uid = 0;
    private $_data = null;
    public $_gold_type = 0;






    function trigger($uid, $config,$data) {
        $this->_config = $config;
        $this->_uid = $uid;
        $this->_data = $data;
        switch ($this->_config['id']){
            //日常任务
            case 1://玩游戏2分钟
                $this->_gold_type = GoldcoinLogModel::$_type_task_first_play_game;
                $rs = $this->playGameTotalTime(2,1);
                break;
            case 2://查看我的钱包
                $this->_gold_type = GoldcoinLogModel::$_type_task_first_open_wallet;
                $rs = $this->lookWallet();
                break;
            case 3://首次提现成功
                $this->_gold_type = GoldcoinLogModel::$_type_task_first_get_money;
                $rs = $this->getMoney();
                break;
            case 4://关注第一个小伙伴
                $this->_gold_type = GoldcoinLogModel::$_type_task_first_follow;
                $rs = $this->follow();
                break;
            case 5://首次注册成功
                $this->_gold_type = GoldcoinLogModel::$_type_task_first_reg;
                $rs = $this->reg();
                break;
            case 6://幸运大抽奖
                $this->_gold_type = GoldcoinLogModel::$_type_luck_lottery;
                $rs = $this->lottery();
                break;
            case 7://看视频，开宝箱
                $this->_gold_type = GoldcoinLogModel::$_type_ad_open_box;
                $rs = $this->luckBox();

                break;
            case 8://晒晒收入
                $this->_gold_type = GoldcoinLogModel::$_type_share_income;
                $rs = $this->shareMoney();
                break;
            case 9://分享给好友，提升收益
                $this->_gold_type = GoldcoinLogModel::$_type_share_friend;
                $rs = $this->shareUser();
                break;
            case 10://玩游戏，领金币
                $rs = $this->playGameSomeTime();
                break;
            case 11://游戏达到20分钟
                $this->_gold_type = GoldcoinLogModel::$_type_play_games_20;
                $rs = $this->playGameTotalTime(20,1);
                break;
            case 12://金币欢乐送
                return out_pc(200);
            case 13://随机宝箱
                return out_pc(200);
            case 14:// 趣味刮一刮;
                return out_pc(200);
            case 15:// 分享游戏拿金币（新手）;
                $this->_gold_type = GoldcoinLogModel::$_type_share_game_get_goldcoin;
                $rs = $this->shareGame();
                break;
            case 16:// 连续三天分享游戏（新手）;
                $this->_gold_type = GoldcoinLogModel::$_type_share_game_3_day_get_goldcoin;
                $rs = $this->shareGameSerial($uid);
                break;
            case 17:// 完善资料（新手）;
                $this->_gold_type = GoldcoinLogModel::$_type_perfect_information;
                $rs = $this->perfectInformation($uid);
                break;
            case 18:// 玩5款不同的游戏（日常）;
                $this->_gold_type = GoldcoinLogModel::$_type_play_5_diff_games;
                $rs = $this->playFiveDiffGames($uid);
                break;
            case 19:// 玩指定游戏5分钟（日常）;
                $this->_gold_type = GoldcoinLogModel::$_type_play_appoint_game;
                $rs = $this->playAppointGame();
                break;
            case 20:// 根据游戏时间计算任务完成度（日常）;
                return out_pc(200);
                // 游戏时长任务进程展示逻辑暂时保留，说不定啥时候就又要做了;
                // $rs = $this->playGameTodayTime($uid);
                // break;
            case 21:// 开心抽一抽（日常）;
                return out_pc(200);
            case 22:// 签到赚金币（日常）;
                return out_pc(200);
            case 23:// 邀请好友，拿金币（日常）;
                return out_pc(200);
            case 24:// 翻翻卡任务完成（日常）;
                $rs = $this->flipCards();
                break;
            case 25:// 开心大轮盘任务完成（日常）;
                $rs = $this->setHappySwheel();
                break;
            default:
        }

        return $rs;
    }

    //======================== 成长任务，也就是一次完成，再也不会有了===============================================================

    function playGameTotalTime($minute,$isToday = null){
        //必须是正常，有 结束 时间的 游戏记录，才算用户真正玩游戏的时间
//        $sql = "select * from played_games where uid = ".$this->_uid  . " and e_time > 0 and e_time is not null ";
        $sql = "select a_time,e_time from played_games where uid = ".$this->_uid  ;
        if($isToday){
            $today = dayStartEndUnixtime();
            $sql .= " and a_time >= ".$today['s_time'];
        }
        $list = PlayedGamesModel::db()->getAllBySQL($sql);
        if($list){
            $b = 0;
            foreach($list as $k=>$v){
                if(!arrKeyIssetAndExist($v,'e_time')){
                    continue;
                }
                $b += $v['e_time'] - $v['a_time'];
            }

            if(!$b || $b < 1){
                return false;
            }

            $cnt = $b / 60;
            return $this->upStepRs((int)$cnt);
        }
    }



    function lookWallet(){
        //只要进来，就证明已经完成了
        return $this->upStepRs(1);
    }

    function getMoney(){
        return $this->upStepRs(1);
    }

    function follow(){
        return $this->upStepRs(1);
    }

    function reg(){
        return $this->upStepRs(1);
    }

    //======================== 日常任务 每天刷新一次 =================================
    function luckBox(){

    }

    function lottery(){

    }


    function shareMoney(){
        $this->upStepRs(1);
    }

    function shareUser(){
        $this->upStepRs(1);
    }

    function playGameSomeTime(){

    }

    // 15：分享游戏，拿金币;
    public function shareGame(){
        return $this->upStepRs(1);
    }

    /**
     * 16：连续三天分享游戏;
     * @param $uid
     * @return array
     */
    public function shareGameSerial($uid){
        // 需满足连续三天，否则还要将已完成的step数值置为0;
        $today = dayStartEndUnixtime();
        $status = TaskUserModel::db()->getRow(" uid = $uid AND task_id = 16 AND task_config_type = 2 ");
        $select = "SELECT id FROM share WHERE uid = $uid AND game_id != 0 AND a_time >= {$today['s_time']} AND a_time <= {$today['e_time']}";
        $info_today = ShareModel::db()->getAllBySQL($select);
        // 判断昨天是否分享;
        $s_time_yesd = ($today['s_time'] - 24 * 60 * 60);
        $e_time_yesd = ($today['e_time'] - 24 * 60 * 60);
        $selectSql = "SELECT id FROM share WHERE uid = $uid AND game_id != 0 AND a_time >= {$s_time_yesd} AND a_time <= {$e_time_yesd}";
        $info_yesd = ShareModel::db()->getAllBySQL($selectSql);
        if(empty($info_today) && empty($info_yesd) && !empty($status) && isset($status['step']) && 3 != $status['step']){
            return $this->upStepRs(0);
        }
        // 判断前天是否分享;
        $s_time_byesd = ($s_time_yesd - 24 * 60 * 60);
        $e_time_byesd = ($e_time_yesd - 24 * 60 * 60);
        $selectSqlNew = "SELECT id FROM share WHERE uid = $uid AND game_id != 0 AND a_time >= {$s_time_byesd} AND a_time <= {$e_time_byesd}";
        $info_byesd = ShareModel::db()->getAllBySQL($selectSqlNew);
        if(!empty($info_today) && !empty($info_yesd[0]['id']) && isset($info_yesd[0]['id']) && empty($info_byesd[0]['id']) && !isset($info_byesd[0]['id'])){
            return $this->upStepRs(2);
        }
        if(!empty($info_today) && !empty($info_yesd[0]['id']) && isset($info_yesd[0]['id']) && !empty($info_byesd[0]['id']) && isset($info_byesd[0]['id'])){
            return $this->upStepRs(3);
        }
        if(!empty($info_today) && empty($info_yesd[0]['id']) && empty($info_byesd[0]['id'])){
            return $this->upStepRs(1);
        }
        return $this->upStepRs(0);
    }

    /**
     * 17：完善资料;
     * @param $uid
     * @return array
     */
    public function perfectInformation($uid){
        // 三个步骤（1头像，2昵称，3性别）
        $ounts = perfectPresonalMessageModel::db()->getCount("uid = $uid");
        switch ($ounts) {
            case 1 :
                $cnt = 1;
                break;
            case 2 :
                $cnt = 2;
                break;
            case 3 :
                $cnt = 3;
                break;
            default:
                $cnt = 0;
        }
        return $this->upStepRs($cnt);
    }

    /**
     * 18：每天玩5款不同的游戏;
     * @param $uid
     * @return array
     */
    public function playFiveDiffGames($uid){
        $today = dayStartEndUnixtime();
        $cntResult = PlayedGamesMoreModel::getRow("uid = $uid AND a_time >= {$today['s_time']} AND a_time <= {$today['e_time']}", '', "COUNT(DISTINCT game_id) AS diff_cnt");
        if($cntResult){
            $cnt = $cntResult['diff_cnt'];
        }else{
            $cnt = 0;
        }
        return $this->upStepRs($cnt);
    }

    /**
     * 19：玩特定游戏超过5分钟;
     * @param $uid
     */
    public function playAppointGame(){
        return $this->upStepRs(1);
    }

    /**
     * 24:翻翻卡;
     * @return array
     */
    public function flipCards(){
        return $this->upStepRs(1);
    }

    /**
     * 25:开心大轮盘;
     * @return array
     */
    public function setHappySwheel(){
        return $this->upStepRs(1);
    }

    /**
     * 暂时不需要下面这段代码了，钩子也不会触发;
     * task_id：20根据游戏时间计算任务完成度;
     * @param $uid
     * @return array
     */
    /*public function playGameTodayTime($uid){
        $gamesService = new GamesService();
        $totalToday = $gamesService->getToadyUserPlayGameTime($uid);
        if(!$totalToday){
            $totalToday = (int)($totalToday/60);
        }
        $totalToday = ($totalToday)?(int)$totalToday:0;
        if($totalToday >= 2 && $totalToday < 4){
            return $this->upStepRs(1);
        }elseif ($totalToday >= 4 && $totalToday < 8){
            return $this->upStepRs(2);
        }elseif ($totalToday >= 8 && $totalToday < 12){
            return $this->upStepRs(3);
        }elseif ($totalToday >= 12 && $totalToday < 16){
            return $this->upStepRs(4);
        }elseif ($totalToday >= 16 && $totalToday < 20){
            return $this->upStepRs(5);
        }elseif ($totalToday >= 20){
            return $this->upStepRs(6);
        }
    }*/


    function upStepRs($cnt){
        if($cnt > $this->_config['step_num']) {//证明数据有问题,但是也得冗错一下吧~
            $cnt = $this->_config['step_num'];
        }
        if($cnt == $this->_config['step_num']){//已完成
            $rs = $this-> upTaskRecord(time(),$cnt, $this->_data );
            return out_pc(200,array('doneStep'=>$cnt,'isDone'=>1));
        }else{
            $rs = $this-> upTaskRecord(0,$cnt, $this->_data );
            return out_pc(200,array('doneStep'=>$cnt,'isDone'=>0));
        }
    }

    function upTaskRecord($done_time = 0 , $step = null,$memo_info = null){
        $data =array('u_time'=>time());
        if($memo_info){
            $data['hook_info'] = json_encode($memo_info);
        }

        if($done_time){
            $data['done_time'] = $done_time;
        }

        if($step){
            $data['step'] = $step;
        }elseif($step === 0){
            $data['step'] = $step;
        }

        if(arrKeyIssetAndExist($data,'done_time') && arrKeyIssetAndExist($this->_config,'goldcoin')){
            LogLib::appWriteFileHash(["in"]);
            $lib =  new UserService();
            $lib->addGoldcoin($this->_uid,$this->_config['goldcoin'],$this->_gold_type);
            $data['reward_time'] = time();


            $Cusdata = array(
                'typeId'=>1000,
                'taskConfigId'=>$this->_config['id'],
            );

            $tid = "";
            $mod = 4 - strlen($this->_config['id']);
//            LogLib::appWriteFileHash(["mod",$mod]);
            if($mod){
                for($i=0;$i<$mod;$i++){
                    $tid .= "0";
                }
//                LogLib::appWriteFileHash(["tid1:",$tid]);
                $tid .= $this->_config['id'];
//                LogLib::appWriteFileHash(["tid2:",$tid]);
            }else{
                $tid = $this->_config['id'];
            }
            $content = "1000{$tid}0000".$this->_config['goldcoin'];

            $lib = new PushXinGeLib();
            $rs = $lib->pushAndroidMsgOneMsgByToken($this->_uid,"task",$content,$Cusdata);
            $rs [] =  "upTaskRecord= ==== == = = = =    pushAndroidMsgOneMsgByToken ";
            LogLib::appWriteFileHash($rs);

        }

        $lib = new GamesService();
        if($this->_config['type'] == TaskConfigModel::DAILY_TYPE){
            $list = $lib->getDailyTaskDay($this->_uid);
        }else{
            $list = $lib->getGrowupTaskDay($this->_uid);
        }
        $dd = [];
//        echo json_encode($list)."<br/>"."<br/>";
        foreach ($list as $k=>$v) {
            if($v['id'] == $this->_config['user_task']['id']){
                foreach ($data as $k2=>$v2) {
                    $v[$k2] = $v2;
                }
//                    var_dump($v);echo "<br/>"."<br/>";
            }
            $dd[] = $v;
        }
//            echo json_encode($dd);
        if($this->_config['type'] == TaskConfigModel::DAILY_TYPE){
            $lib->setDailyTaskDay($this->_uid,$dd);
        }else{
            $lib->setGrowupTaskDay($this->_uid,$dd);
        }


//        exit;
        // task_user 分库分表逻辑; modify by XiaHB time:2019/06/27 Begin;
        if($this->_config['type'] == TaskConfigModel::GROWNUP_TYPE){
             $upRs = TaskUserModel::db()->upById($this->_config['user_task']['id'],$data);
        }else{
            // 其实这里需要做一个兼容处理,如果玩家;
            $sTime = strtotime("2019-07-04 00:00:00");
            if(time() < $sTime){
                $info = TaskUserMoreModel::getById($this->_config['user_task']['id']);
                if($info){
                    $upRs = TaskUserMoreModel::upById($this->_config['user_task']['id'],$data);
                }else{
                    $upRs = TaskUserModel::db()->upById($this->_config['user_task']['id'],$data);
                }
            }else{
                $upRs = TaskUserMoreModel::upById($this->_config['user_task']['id'],$data);
            }
        }

        // task_user 分库分表逻辑; modify by XiaHB time:2019/06/27   End;

        // $upRs = TaskUserModel::db()->upById($this->_config['user_task']['id'],$data);
        return $upRs;
    }



//    //今日登陆一次
//    function login(){
//        $cntLogin = LoginModel::getUserByDate($this->_uid);
//        if(!$cntLogin ){
//            return false;
//        }
//
//        $cnt = count($cntLogin);
//        if($cnt > $this->_config['step_num']) {//证明数据有问题,但是也得冗错一下吧~
//            $cnt = $this->_config['step_num'];
//        }
//
//        if($cnt == $this->_config['step_num']){//已完成
//            $rs = $this-> upTaskRecord(time(),$cnt, $this->_data );
//            return $rs;
//        }else{
//            $rs = $this-> upTaskRecord(0,$cnt, $this->_data );
//            return $rs;
//        }
//
//    }


//    function upStepRs($cnt){
//        if($cnt > $this->_config['step_num']) {//证明数据有问题,但是也得冗错一下吧~
//            $cnt = $this->_config['step_num'];
//        }
//
//        if($cnt == $this->_config['step_num']){//已完成
//            $rs = $this-> upTaskRecord(time(),$cnt, $this->_data );
//            return $rs;
//        }else{
//            $rs = $this-> upTaskRecord(0,$cnt, $this->_data );
//            return $rs;
//        }
//    }
//
//    function upTaskRecord($done_time = 0 , $step = null,$memo_info = null){
//        $data =array('u_time'=>time());
//        if($memo_info){
//            $data['hook_info'] = json_encode($memo_info);
//        }
//
//        if($done_time){
//            $data['done_time'] = $done_time;
//        }
//
//        if($step){
//            $data['step'] = $step;
//        }elseif($step === 0){
//            $data['step'] = $step;
//        }
//
//        $upRs = TaskUserModel::db()->upById($this->_config['user_task']['id'],$data);
//        return $upRs;
//    }
}
