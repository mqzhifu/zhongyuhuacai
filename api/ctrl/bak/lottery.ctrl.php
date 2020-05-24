<?php
class LotteryCtrl extends BaseCtrl {
    private $_randBoxCountdown = 4 * 60 * 60 ;

//    function __construct()
//    {
//        parent::__construct();
//        if(ENV == 'dev'){
//            $this->_randBoxCountdown = 120;
//        }
//    }
    //开心大转盘 - 执行抽奖
    function happyDoing($id){
        if(!$id){
            return $this->out(8043);
        }
        $ad = AdLogModel::db()->getById($id);
        if(!$ad){
            return $this->out(1021);
        }

        if(!$ad['e_time']){
            return $this->out(8304);
        }

        if($ad['e_time'] < $ad['a_time']){
            return $this->out(8305);
        }

        if($this->uid != $ad['uid']){
            return $this->out(8277);
        }

        if($ad['type'] != GoldcoinLogModel::$_type_ad_happy_lottery){
            return $this->out(8306);
        }

        $HappyLotteryPlayTime = $this->gamesService->getHappyLotteryPlayTime($this->uid);
        //5分钟一次
        if($HappyLotteryPlayTime < 5 * 60 ){
            return $this->out(8310);
        }
        //抽奖历史记录
        $history = HappyLotteryModel::db()->getRow(" uid = ".$this->uid );
        if(!$history){//首次抽，必中一张0.3卷
            $coupoin_config = $GLOBALS['main']['money_coupon'];
            $counInfo = $coupoin_config[1];
            $today = dayStartEndUnixtime();
            $data = array(
                'uid'=>$this->uid,
                'a_time'=>time(),
                'valid_time'=>$today['s_time'] + $counInfo['valid_time'],
                'expire_time'=>$today['s_time'] + $counInfo['expire'],
                'use_time'=>0,
                'money'=>$counInfo['money'],
                'element_id'=>1,
            );
            $addId = UserMoneyCouponModel::db()->add($data);
            $rs = array('type'=>2,'num'=>$counInfo['money']);


            $insertArray = array(
                'uid'=>$this->uid,
                'a_time'=>time(),
                'reward_type'=>2,
                'reward_goldcoin'=>0,
                'reward_coupon_id '=>$addId,
            );
            HappyLotteryModel::db()->add($insertArray);
        }else{
            $ProbabilityId=$this->getHappyKValue($this->uid);
            $Probability = $this->getHappyProbability($ProbabilityId);
            $sum = 1000 / 100;//系数，放大10倍
            $Probability_arr = null;
            $start = 0;
            foreach ($Probability as $k=>$v) {
                if(!$v){
                    $Probability_arr[] = 0;
                    $start = 0;
                    continue;
                }
                $start  = $v * $sum + $start;
                $Probability_arr[] = $start;
            }

            $r = rand(1,1000);
            $reward_desc = null;
            foreach ($Probability_arr as $k=>$v) {
                if($r < $v){
                    $reward_desc = $this->getHappyReward($k+1);
                    // $reward_desc = $this->getHappyReward(5);
                    break;
                }
            }

            if($reward_desc['type'] == 2){
                $coupoin_config = $GLOBALS['main']['money_coupon'];
                $counInfo = $coupoin_config[$reward_desc['money_coupon_id']];
                $today = dayStartEndUnixtime();
                $data = array(
                    'uid'=>$this->uid,
                    'a_time'=>time(),
                    'valid_time'=>$today['s_time'] + $counInfo['valid_time'],
                    'expire_time'=>$today['s_time'] + $counInfo['expire'],
                    'use_time'=>0,
                    'money'=>$counInfo['money'],
                    'element_id'=>$reward_desc['money_coupon_id'],
                );
                $addId = UserMoneyCouponModel::db()->add($data);
                $rs = array('type'=>2,'num'=>$counInfo['money']);
            }else{
                $upRs = $this->userService->addGoldcoin($this->uid,$reward_desc['gold'],GoldcoinLogModel::$_type_ad_happy_lottery);
                $rs = array('type'=>1,'num'=>$reward_desc['gold']);
            }

            $insertArray = array(
                'uid'=>$this->uid,
                'a_time'=>time(),
                'reward_type'=>$reward_desc['type'],
                'reward_goldcoin'=>(2 == $reward_desc['type'])?0:$reward_desc['gold'],
                'reward_coupon_id '=>(2 == $reward_desc['type'])?$addId:0,
            );
            HappyLotteryModel::db()->add($insertArray);

        }

        $this->gamesService->delHappyLotteryPlayTime($this->uid);

        return $this->out(200,$rs);

    }

    function getHappyLotteryPlayTime(){
        $time = $this->gamesService->getHappyLotteryPlayTime($this->uid);
        if(!$time){
            $time = 0;
        }
        return $this->out(200,$time);
    }

    function getHappyKValue($uid){
        $list = HappyLotteryModel::db()->getAll(" uid = ".$uid);
        $gold = 0;
        if($list){
            $coupoin_config = $GLOBALS['main']['money_coupon'];
            foreach ($list as $k=>$v) {
                if($v['reward_type'] == 2){//提现卷
                    $gold += $coupoin_config[$v['reward_coupon_id']]['equalGoldcoin'];
                }else{
                    $gold += $v['reward_goldcoin'];
                }
            }
        }

        $k = 500 * count($list) -$gold;

        if($k < 1500){
            $id = 1;
        }elseif($k < 2500){
            $id = 2;
        }elseif($k < 5000){
            $id = 3;
        }else{
            $id = 4;
        }

        return $id;
    }

    function getHappyReward($id){
        $arr = array(
            //type:1金币 2提现卷
            1=>array('type'=>1,'gold'=>50,'money_coupon_id'=>0),
            2=>array('type'=>1,'gold'=>100,'money_coupon_id'=>0),
            3=>array('type'=>1,'gold'=>200,'money_coupon_id'=>0),
            4=>array('type'=>2,'gold'=>0,'money_coupon_id'=>1),
            5=>array('type'=>2,'gold'=>0,'money_coupon_id'=>2),
            6=>array('type'=>2,'gold'=>0,'money_coupon_id'=>3),
        );

        return $arr[$id];
    }

    //开心抽奖，概率
    function getHappyProbability($id){
        $arr = array(
            1=>array(46,33,17,4,0,0),
            2=>array(38,41,15,5,1,0),
            3=>array(39,38,15,5,2.5,0.5),
            4=>array(40,35,15,6,3,1),
        );

        return $arr[$id];
    }

    //进入页面
    function pre(){
        $freeTime = $this->getFreeTime();
//        $reward = array('freeTime'=>$freeTime,'reward'=>array("goldcoin10", "ad" ,"goldcoin30", "ad","goldcoin50", "ad","goldcoin100"));
        return $this->out(200,$freeTime);
    }

    private function getFreeTime(){
//        $a_time = strtotime("today");
//        $freeTime = LotteryModel::db()->getCount(" uid = ".$this->uid." and a_time > ".$a_time);


        $freeTime = $this->gamesService->getLotteryFreeTime($this->uid);
        $freeTime = 100 - $freeTime;
        return $freeTime;
    }
    //执行 抽奖动作
    function doing(){
        $freeTime = $this->getFreeTime();
        if(!$freeTime || intval($freeTime) <= 0){
            return $this->out(8289,$GLOBALS['code'][8289]);
        }

        $r = rand(1,100);
        $rewardGold = 0;
        if($r <= 50){
            $rewardType = 2;//广告
        }elseif($r <= 70){
            $rewardType = 1;
            $rewardGold = 50;
        }elseif($r <= 90){
            $rewardType = 1;
            $rewardGold = 30;
        }elseif($r <= 95){
            $rewardType = 1;
            $rewardGold = 100;
        }elseif($r <= 100){
            $rewardType = 1;
            $rewardGold = 10;
        }

        $data = array(
            'uid'=>$this->uid,
            'a_time'=>time(),
            'reward_type'=>$rewardType,
            'reward_goldcoin'=>$rewardGold,
        );

        $aid = LotteryModel::db()->add($data);

        if($rewardType == 1){

            $lib = new UserService();
            $lib->addGoldcoin($this->uid,$rewardGold,GoldcoinLogModel::$_type_luck_lottery,$aid);
        }

        $this->gamesService->incLotteryFreeTime($this->uid,1);

        $lib = new TaskService();
        $lib->trigger($this->uid,6);

        $rs = array('rewardType'=>$rewardType,'rewardGold'=>$rewardGold,'freeTime'=>  $freeTime);
        return $this->out(200,$rs);
    }
    //金币欢乐送 - 获取基本信息
    function goldcoinCarnivalInfo(){
        $rs = $this->lotteryService->goldcoinCarnivalInfo($this->uid);
        return $this->out(200,$rs);
    }
    //金币欢乐送 - 抽奖日志
    function goldcoinCarnivalLog(){
        $log = GoldcoinCarnivalModel::db()->getAll(" uid = ".$this->uid. " and is_use = 1 order by id desc ",null,' reward_goldcoin ');
        return $this->out(200,$log);
    }
    //金币欢乐送 - 公告/轮播 中奖用户
    function goldcoinCarnivalBulletin(){
        $arr = array(
            array('nickname'=>'若西','reward'=>'5元现金红包'),
            array('nickname'=>'可口可乐不可口','reward'=>'2元现金红包'),
            array('nickname'=>'陈暖央','reward'=>'1元现金红包'),
            array('nickname'=>'紫薯精','reward'=>'ipone xs'),
            array('nickname'=>'仙女不吃肉肉','reward'=>'5元现金红包'),
            array('nickname'=>'年糕姐姐','reward'=>'1元现金红包'),
            array('nickname'=>'三岁橙子','reward'=>'2元现金红包'),
            array('nickname'=>'Alin闪闪发光','reward'=>'5元现金红包'),
            array('nickname'=>'王小豆子','reward'=>'2元现金红包'),
            array('nickname'=>'友情岁月','reward'=>'1元现金红包'),
            array('nickname'=>'MMMia Liu','reward'=>'ipone xs'),
            array('nickname'=>'余温','reward'=>'2元现金红包'),
            array('nickname'=>'深夜一只猫','reward'=>'1元现金红包'),
            array('nickname'=>'今何在','reward'=>'2元现金红包'),
            array('nickname'=>'水上人','reward'=>'5元现金红包'),
            array('nickname'=>'小鹿','reward'=>'1元现金红包'),
            array('nickname'=>'朵朵','reward'=>'1元现金红包'),
            array('nickname'=>'岁月静好','reward'=>'5元现金红包'),
            array('nickname'=>'小叮当','reward'=>'1元现金红包'),
            array('nickname'=>'笑笑同学啊','reward'=>'1元现金红包'),
            array('nickname'=>'','reward'=>''),
        );

        $list = GoldcoinCarnivalModel::db()->getAll(" is_use = 1 group by uid limit 15");
//        $list = GoldcoinCarnivalModel::db()->getAll(" 1 group by uid limit 15");

        if($list){
            $newList = [];
            foreach ($list as $k=>$v) {
                $nickname = $this->userService->getFieldById($v['uid'],'nickname');
                $newList[] =array('nickname'=>$nickname,'reward'=>"{$v['reward_goldcoin']}金币");
            }

            $rand = [];
            $randLimit = 20 - count($list);
            while(count($rand) != $randLimit){
                $r = rand(0,19);
                if(!in_array($r,$rand)){
                    $rand[] = $r;
                }
            }

            $rand_data = array();
            foreach ($rand as $k=>$v) {
                $rand_data[] = $arr[$v];
            }
//            var_dump($rs);echo "<br/><br/>";
            $merge = array_merge($rand_data,$newList);
//            var_dump($merge);;echo "<br/><br/>";
//            exit;

            shuffle($merge);

//            $rs = [];
//            foreach ($merge as $k=>$v) {
//                $rs[] = $v;
//            }
            return $this->out(200,$merge);
        }else{
            return $this->out(200,$arr);
        }

    }

    //金币欢乐送执行抽奖动作
    function doGoldcoinCarnival(){
        $rs = $this->lotteryService->doGoldcoinCarnival($this->uid);
        return $this->out(200,$rs);
    }

    //获取随机 幸运宝箱
    function getRandomLuckBox(){
        $info = RandomLuckBoxModel::db()->getRow(" uid = ".$this->uid. " order by id desc ");
//        LogLib::appWriteFileHash(["=================,", $info['a_time']]);
        if(!$info || time() - $info['a_time'] > $this->_randBoxCountdown){
            $data = array(
                'uid'=>$this->uid,
                'a_time'=>time(),
                'status'=>1,
            );

            $this->userService->addGoldcoin($this->uid,20,GoldcoinLogModel::$_type_rand_luck_box_step1);
            $aid  = RandomLuckBoxModel::db()->add($data);

            // 点击事件统计
            $data = array(
                'category'=>CntActionLogModel::$_category_app,
                'type'=>CntActionLogModel::$_type_box_tap,
                'a_time'=>time(),
                'uid'=>$this->uid,
            );

            CntActionLogModel::db()->add($data);

            $rs = array(
                'rewardGold'=>20,
                'rewardGoldByShareWX'=>40,
                'rewardGoldByShareQQ'=>40,
                'countdown'=>$this->_randBoxCountdown,
            );

//            LogLib::appWriteFileHash(['countdown======',$rs,time()]);

            return $this->out(200,$rs);
        }

        //此接口先特殊处理下
        $rs = array(
            'rewardGold'=>20,
            'rewardGoldByShareWX'=>40,
            'rewardGoldByShareQQ'=>40,
            'countdown'=>0,

        );

        LogLib::appWriteFileHash(["-------------------------------", 8279]);

        return $this->out(8297,$rs);

    }

    function getRandomLuckBoxCountdown(){
        $info = RandomLuckBoxModel::db()->getRow(" uid = ".$this->uid. " order by id desc ");

        if(!$info || time() - $info['a_time'] > $this->_randBoxCountdown){
            $countdown = 0;
        }else{
            $countdown = $this->_randBoxCountdown - ( time() -  $info['a_time'] );
        }
        LogLib::appWriteFileHash(['countdown====info==',$countdown,$info]);

        return $this->out(200,$countdown);
    }

    //执行 抽奖动作
    function doingEn($ad_id){
        if (!$ad_id) {
            return out_pc(8043);
        }

        $info = AdLogModel::db()->getById($ad_id);
        if (!$info) {
            return out_pc(1021);
        }

        $freeTime = $this->getFreeTime();
        if(!$freeTime || intval($freeTime) <= 0){
            return $this->out(8289,$GLOBALS['code'][8289]);
        }
/*        100/80/30/20
        抽不中概率 ：30%
        80金币概率：20%
        30金币概率：40%
        100金币概率：5%
        20金币概率：5%*/
        $r = rand(1,100);
        $rewardGold = 0;
        if($r <= 30){
            $rewardType = 2;
        }elseif($r <= 70){
            $rewardType = 1;
            $rewardGold = 30;
        }elseif($r <= 20){
            $rewardType = 1;
            $rewardGold = 80;
        }elseif($r <= 85){
            $rewardType = 1;
            $rewardGold = 100;
        }elseif($r <= 100){
            $rewardType = 1;
            $rewardGold = 20;
        }

        $data = array(
            'uid'=>$this->uid,
            'a_time'=>time(),
            'reward_type'=>$rewardType,
            'reward_goldcoin'=>$rewardGold,
        );

        $aid = LotteryModel::db()->add($data);

        if($rewardType == 1){

            $lib = new UserService();
            $lib->addGoldcoin($this->uid,$rewardGold,GoldcoinLogModel::$_type_luck_lottery,$aid);
        }

        $this->gamesService->incLotteryFreeTime($this->uid,1);

        $lib = new TaskService();
        $lib->trigger($this->uid,6);

        $rs = array('rewardType'=>$rewardType,'rewardGold'=>$rewardGold,'freeTime'=>  $freeTime);
        return $this->out(200,$rs);
    }

    /**
     * 每日宝箱次数
     */
    function getDayLotteryBoxTimes() {
        $today = date("Y-m-d", strtotime("today"));
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_box_times']['key'],$today."_".$this->uid);
        $times = RedisPHPLib::get($key);
        if (!$times) {
            $times = 0;
        }
        $left = 5 - $times;
        return $this->out(200, $left);
    }

    function doingDayLotteryBox($ad_id) {
        if (!$ad_id) {
            return $this->out(8043, $GLOBALS['code'][8043]);
        }

        // 判断是否看完广告
        $type = GoldcoinLogModel::$_type_daily_lottery_box;
        $uid = $this->uid;
        $adLog = AdLogModel::db()->getRow("id = $ad_id and type = $type and uid = $uid");
        if (!$adLog) {
            return $this->out(1021, $GLOBALS['code'][1021]);
        } elseif (!$adLog['e_time']) {
            return $this->out(8304, $GLOBALS['code'][8304]);
        } elseif ($adLog['e_time'] <= $adLog['a_time']) {
            return $this->out(8305, $GLOBALS['code'][8305]);
        }

        // 判断今日宝箱次数(上限5次)
        $today = date("Y-m-d");
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_box_times']['key'],$today."_".$this->uid);
        $times = RedisPHPLib::get($key);
        if ($times && $times >= 5) {
            return $this->out(8314, $GLOBALS['code'][8314]);
        }

        // 随机给予80-150金币
        $goldcoinNum = mt_rand(80, 150);
        $us = new UserService();
        $us->addGoldcoin($uid,$goldcoinNum,GoldcoinLogModel::$_type_daily_lottery_box,$ad_id);

        // 次数累加1
        $times = RedisPHPLib::getServerConnFD()->incr($key);
        RedisPHPLib::getServerConnFD()->expire($key, $GLOBALS['rediskey']['daily_box_times']['expire']);
        $left = 5 - $times;

        return $this->out(200, ['goldcoin'=>$goldcoinNum, 'left'=>$left]);
    }


    /**
     * 每日福利红包
     */
    function getDailyRedPacketInfo() {
        $info = $this->getRedPacketRedisInfo();
        $packets = [];
        foreach ($info as $key => $val) {
            $packets[] = [
                'id' => $key,
                'num' => $this->getRedPacketGoldcoins()[$key],
                'seconds' => $this->getRedPacketRequiredTime()[$key],
                'status' => $val,
            ];
        }
        return $this->out(200, ['packets'=>$packets]);
    }

    function openDailyRedPacket($packetId, $adId) {
        if (!$adId || !$packetId) {
            return $this->out(8043, $GLOBALS['code'][8043]);
        }
        // 判断是否看完广告
        $type = GoldcoinLogModel::$_type_daily_red_packet;
        $uid = $this->uid;
        $adLog = AdLogModel::db()->getRow("id = $adId and type = $type and uid = $uid");
        if (!$adLog) {
            return $this->out(1021, $GLOBALS['code'][1021]);
        } elseif (!$adLog['e_time']) {
            return $this->out(8304, $GLOBALS['code'][8304]);
        } elseif ($adLog['e_time'] <= $adLog['a_time']) {
            return $this->out(8305, $GLOBALS['code'][8305]);
        }

        // 判断游戏时长是否达到要求
        $playedTime = $this->gamesService->getToadyUserPlayGameTime($this->uid);
        $needTime = $this->getRedPacketRequiredTime()[$packetId];
        if ($playedTime < $needTime) {
            return $this->out(8319, $GLOBALS['code'][8319]);
        }

        // 判断是否已领取过
        if ($this->getRedPacketRedisInfo()[$packetId] == 1) {
            return $this->out(8320, $GLOBALS['code'][8320]);
        }
        // 给予金币奖励并记录
        $goldcoinNum = $this->getRedPacketGoldcoins()[$packetId];
        if (!$goldcoinNum) {
            return $this->out(8321, $GLOBALS['code'][8321]);
        }
        $us = new UserService();
        $us->addGoldcoin($uid,$goldcoinNum,GoldcoinLogModel::$_type_daily_red_packet,$adId);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_red_packets']['key'], date("Y-m-d")."_".$this->uid);
        RedisPHPLib::getServerConnFD()->hSet($key, $packetId, 1);

        return $this->out(200, $goldcoinNum);
    }

    /**
     * 获取福利红包redis存储信息，没有则初始化
     */
    private function getRedPacketRedisInfo() {
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['daily_red_packets']['key'], date("Y-m-d")."_".$this->uid);
        $info = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if (!$info) {
            $info = [];
            for ($i=1; $i <=6 ; $i++) { 
                $info[$i] = 0;
            }
            RedisPHPLib::getServerConnFD()->hMset($key, $info);
            RedisPHPLib::getServerConnFD()->expire($key, $GLOBALS['rediskey']['daily_red_packets']['expire']);
        }
        return $info;
    }

    /**
     * 红包金币配置
     */
    private function getRedPacketGoldcoins() {
        return [
            1 => 10,
            2 => 16,
            3 => 24,
            4 => 36,
            5 => 48,
            6 => 60,
        ];
    }

    /**
     * 红包所需游戏时长配置
     */
    private function getRedPacketRequiredTime() {
        return [
            1 => 2*60,
            2 => 4*60,
            3 => 8*60,
            4 => 12*60,
            5 => 16*60,
            6 => 20*60,
        ];
    }

    /**
     * 执行开心翻翻卡;
     * @param int $isDownload
     * @param int $adId
     * @return array
     */
    public function doingFlipCards($isDownload = 0){
        // 验证用户有效性;
        $uid = $this->uid;
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        // 获取单玩家单日翻翻卡游戏次数;
        $nums = self::getTodayPlayedCardsTimes($uid);
        if($nums <= 0){
            return $this->out(8323, $GLOBALS['code'][8323]);
        }
        // 获取权重配比;
        $ret = self::getWeightings();
        $userService = new UserService();
        switch ($ret){
            case 1:
                $goldcoins = (1 == $isDownload)?550:150;
                $result = $userService -> addGoldcoin($uid, $goldcoins, GoldcoinLogModel::$_type_play_flip_cards);
                break;
            case 2:
                $goldcoins = mt_rand(10, 20);
                $result = $userService -> addGoldcoin($uid, $goldcoins, GoldcoinLogModel::$_type_play_flip_cards);
                break;
            case 3:
                $goldcoins = mt_rand(20, 40);
                $result = $userService -> addGoldcoin($uid, $goldcoins, GoldcoinLogModel::$_type_play_flip_cards);
                break;
            default:
                $goldcoins = 0;
        }
        if(!empty($result) && 200 == $result['code'] && isset($result['msg']['aid']) && $result['msg']['aid']){
            $aid = $result['msg']['aid'];
        }else{
            $aid = 0;
        }
        // 削减相应的游戏次数;
        if($goldcoins){
            self::subTodayPlayedCardsTimes($uid);
            if(1 == $nums){
                // 触发任务;
                $tmpService = new TaskService();
                $tmpService->trigger($uid, 24);
            }
        }
        $rs = array('goldCoins' => $goldcoins, 'grade' => $ret, 'aid' => $aid);
        return $this->out(200, $rs);
    }

    /**
     * 执行开心翻翻卡看广告后三倍奖励;
     * @param int $isDownload
     * @param int $adId
     * @return array
     */
    public function doingFlipCardsTriple($adId = 0, $aid = 0, $goldCoins = 0){
        // 验证用户有效性;
        $uid = $this->uid;
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        // 广告id验证;
        if(!$adId){
            return $this->out(8335, $GLOBALS['code'][8335]);
        }else{
            $ad = AdLogModel::db()->getById($adId);
            if(!$ad){
                $this->out(1021, $GLOBALS['code'][1021]);
            }
            if(!$ad['e_time']){
                $this->out(8304, $GLOBALS['code'][8304]);
            }
            if($ad['e_time'] < $ad['a_time']){
                $this->out(8305, $GLOBALS['code'][8305]);
            }
        }

        if(!$aid){
            $this->out(8330, $GLOBALS['code'][8330]);
        }
        if(!$goldCoins){
            $this->out(8331, $GLOBALS['code'][8331]);
        }

        $info = GoldcoinLogModel::db()->getById($aid);
        if(!$info){
            $this->out(8332, $GLOBALS['code'][8332]);
        }
        if($goldCoins != $info['num']){
            $this->out(8333, $GLOBALS['code'][8333]);
        }

        $goldcoinsNew = (int)$info['num']*2;
        $goldcoinsAll = (int)$info['num']*3;
        $userService = new UserService();
        $userService -> addGoldcoin($uid, $goldcoinsNew, GoldcoinLogModel::$_type_play_flip_cards);
        GoldcoinLogModel::db()->upById($aid, array('num'=>$goldcoinsAll));
        return $this->out(200, $goldcoinsAll);
    }

    /**
     * 获取当天开心翻翻卡所剩游戏机会;
     * @param $uid
     * @return bool|int|string
     */
    private static function getTodayPlayedCardsTimes($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['flip_Cards_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $nums = RedisPHPLib::getServerConnFD()->get($key);
        // 获取当日翻翻卡游戏次数,没有则初始化;
        if(false === $nums){
            RedisPHPLib::getServerConnFD()->set($key, 50);
            $nums = RedisPHPLib::getServerConnFD()->get($key);
            RedisPHPLib::getServerConnFD()->expire($key, $GLOBALS['rediskey']['flip_Cards_times']['expire']);
            return $nums;
        }else{
            return $nums;
        }
    }

    /**
     * 更新当天开心翻翻卡所剩游戏机会（-1操作）;
     * @param $uid
     * @return int
     */
    private static function subTodayPlayedCardsTimes($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['flip_Cards_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $nums = RedisPHPLib::getServerConnFD()->get($key);
        if(!$nums){
            return 0;
        }else{
            $nums = $nums - 1;
            RedisPHPLib::getServerConnFD()->set($key, $nums);
        }
    }

    /**
     * 开心翻翻卡权重配比;
     * @return int|string
     */
    private static function getWeightings(){
        $pro = [
            '1' =>10,
            '2' =>30,
            '3' =>60,
        ];
        $ret = '';
        $sum = array_sum($pro);
        foreach($pro as $k => $v) {
            $r = mt_rand(1, $sum);
            if($r <= $v){
                $ret = $k;
                break;
            } else {
                $sum = max(0, $sum - $v);
            }
        }
        return $ret;
    }

    /**
     * 获取当日翻翻卡游戏次数;
     * @return array|bool|int|string
     */
    public function getTodayCardsTimes(){
        $uid = $this->uid;
        if(!$uid){
            $this->out(8002, $GLOBALS['code'][8002]);
        }
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['flip_Cards_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $nums = RedisPHPLib::getServerConnFD()->get($key);
        // 不存在则设置（50）;
        if(false === $nums){
            $nums = RedisPHPLib::getServerConnFD()->set($key, 50);
            // 处理失效数据;
            RedisPHPLib::getServerConnFD()->expire($key, $GLOBALS['rediskey']['flip_Cards_times']['expire']);
        }
        $this->out(200, $nums);
    }

    /**
     * 执行开心大轮盘;
     * @param $adId
     * @return array
     */
    public function dohappyBigWheel(){
        $uid = $this->uid;
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        $nums = self::gethappyBigWheelTimes($uid);
        if($nums <= 0){
            return $this->out(8334, $GLOBALS['code'][8334]);
        }
        $returnArray = array();
        $ret = self::getWeightWheel();
        if(in_array($ret, ['1','2','3','4'])){
            $returnArray['type'] = 1;
            $returnArray['goldcoins'] = 0;
            $returnArray['aid'] = 0;
        }else{
            $returnArray['type'] = 2;
            $goldCoins = rand(8, 50);
            $returnArray['goldcoins'] = $goldCoins;
        }
        $goldcoins = $returnArray['goldcoins'];
        if($goldcoins){
            $userService = new UserService();
            $rs = $userService -> addGoldcoin($uid, $goldcoins, GoldcoinLogModel::$_type_play_big_wheel);
            if(200 == $rs['code'] && isset($rs['msg']['aid'])){
                $returnArray['aid'] = $rs['msg']['aid'];
            }
        }
        self::sethappyBigWheelTimes($uid);
        // 仅剩1次游戏机会，此次结束后触发游戏钩子;
        if(1 == $nums){
            $tmpService = new TaskService();
            $tmpService->trigger($uid, 25);
        }
        return $this->out(200, $returnArray);

    }

    /**
     * 开心大轮盘金币数翻倍;
     * @param $adId
     * @param $aid
     * @return array
     */
    public function dohappyBigWheelDouble($adId, $aid, $goldCoins){
        $uid = $this->uid;
        if(!$uid){
            $this->out(8002, $GLOBALS['code'][8002]);
        }
        if(!$adId){
            $this->out(1021, $GLOBALS['code'][1021]);
        }else{
            $ad = AdLogModel::db()->getById($adId);
            if(!$ad){
                $this->out(1021, $GLOBALS['code'][1021]);
            }
            if(!$ad['e_time']){
                $this->out(8304, $GLOBALS['code'][8304]);
            }
            if($ad['e_time'] < $ad['a_time']){
                $this->out(8305, $GLOBALS['code'][8305]);
            }
        }
        if(!$aid){
            $this->out(8330, $GLOBALS['code'][8330]);
        }
        if(!$goldCoins){
            $this->out(8331, $GLOBALS['code'][8331]);
        }
        $info = GoldcoinLogModel::db()->getById($aid);
        if(!$info){
            $this->out(8332, $GLOBALS['code'][8332]);
        }
        if($goldCoins != $info['num']){
            $this->out(8333, $GLOBALS['code'][8333]);
        }

        $goldcoinsNew = (int)$info['num']*2;
        $goldcoinsAll = (int)$info['num']*3;
        $userService = new UserService();
        $userService -> addGoldcoin($uid, $goldcoinsNew, GoldcoinLogModel::$_type_play_big_wheel);
        GoldcoinLogModel::db()->upById($aid, array('num'=>$goldcoinsAll));

        $this->out(200, $goldcoinsAll);
    }


    /**
     * 获取开心大转盘宝箱列表+游戏剩余机会;
     * @return array
     */
    public function getHappyBigWheelBoxList(){
        $uid = $this->uid;
        if(!$uid){
            return $this->out(8002, $GLOBALS['code'][8002]);
        }
        // 当前剩余游戏次数;
        $cnt = self::gethappyBigWheelTimes($uid);
        // 已经玩了的次数;
        $cnt_played = 100 - $cnt;
        $data = $GLOBALS['main']['happyBigWheelBox'];
        if ($cnt_played >= 100) {
            $changeId = 4;
        } elseif ($cnt_played >= 60) {
            $changeId = 3;
        } elseif ($cnt_played >= 30) {
            $changeId = 2;
        } elseif ($cnt_played >= 5) {
            $changeId = 1;
        } else {
            $changeId = 0;
        }
        if($changeId){
            for($i=1; $i<=$changeId; $i++){
                $data[$i-1]['status'] = 1;
            }
        }
        // status 0：未激活；1：已激活；2：已领取；
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_box']['key'], date("Y-m-d") . "-" . $this->uid, IS_NAME);
        $hasCnt = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($hasCnt){
            foreach ($hasCnt as $k=>$v) {
                foreach ($data as $k2=>$v2) {
                    if($k == $v2['id']){
                        $data[$k2]['status'] = 2;
                    }
                }
            }
        }
        // residue_degree:剩余游戏次数;
        $rs = array('residue_degree'=>$cnt, 'box_datas'=>$data);
        return $this->out(200, $rs);
    }

    /**
     * 执行开心大转盘宝箱奖励;
     * @param $boxId
     * @param $adId
     * @return array
     */
    function dohappyBigWheelBox($boxId){
        $uid = $this->uid;
        if(!$uid){
            $this->out(8002, $GLOBALS['code'][8002]);
        }
        if(!$boxId){
            $this->out(8325, $GLOBALS['code'][8325]);
        }
        // 验证宝箱ID;
        $data = $GLOBALS['main']['happyBigWheelBox'];
        $boxCheck = 0;
        foreach ($data as $k=>$v) {
            if($v['id'] == $boxId){
                $boxCheck = 1;
                break;
            }
        }
        if(!$boxCheck){
            $this->out(8326, $GLOBALS['code'][8326]);
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $cnt = RedisPHPLib::getServerConnFD()->get($key);
        $cnt = 100 - $cnt;
        if ($cnt >= 100) {
            $changeId = 4;
        } elseif ($cnt >= 60) {
            $changeId = 3;
        } elseif ($cnt >= 30) {
            $changeId = 2;
        } elseif ($cnt >= 5) {
            $changeId = 1;
        } else {
            $changeId = 0;
        }

        // 判断游戏次数是否满足;
        if(!$changeId){
             $this->out(8327, $GLOBALS['code'][8327]);
        }
        // 验证宝箱id;
        if($boxId > $changeId ){
             $this->out(8328, $GLOBALS['code'][8328]);
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_box']['key'], date("Y-m-d") . "-" . $this->uid, IS_NAME);
        $hasCnt = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($hasCnt){
            foreach ($hasCnt as $k=>$v) {
                if($k == $boxId){
                    $this->out(8329, $GLOBALS['code'][8329]);
                }
            }
        }
        $info = $data[$boxId];
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_box']['key'], date("Y-m-d") . "-" . $this->uid, IS_NAME);
        RedisPHPLib::getServerConnFD()->hSet($key, $boxId, $info);
        RedisPHPLib::getServerConnFD()->expire($key,$GLOBALS['rediskey']['happy_big_wheel_box']['expire']);

        $goldCoinsReward = self::getWheelGoldCoins();
        $reward = $goldCoinsReward[$boxId];
        $this->userService->addGoldcoin($uid, $reward, GoldcoinLogModel::$_type_play_big_wheel_box);
        $this->out(200, $reward);
    }

    /**
     * 开心大轮盘剩余游戏次数;
     * @param $uid
     * @return bool|string
     */
    private static function gethappyBigWheelTimes($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $nums = RedisPHPLib::getServerConnFD()->get($key);
        if(false === $nums){
            RedisPHPLib::getServerConnFD()->set($key, 100);
            $nums = RedisPHPLib::getServerConnFD()->get($key);
            // 处理失效数据;
            RedisPHPLib::getServerConnFD()->expire($key, $GLOBALS['rediskey']['happy_big_wheel_times']['expire']);
        }
        return $nums;
    }

    /**
     * @param $uid
     */
    private static function sethappyBigWheelTimes($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['happy_big_wheel_times']['key'], date("Y-m-d") . "-" . $uid,IS_NAME );
        $nums = RedisPHPLib::getServerConnFD()->get($key);
        RedisPHPLib::getServerConnFD()->set($key, $nums-1);
    }

    /**
     * 权重配比;
     * @return int|string
     */
    private static function getWeightWheel(){
        $pro = [
            '1' =>15,
            '2' =>15,
            '3' =>15,
            '4' =>15,
            '5' =>10,
            '6' =>10,
            '7' =>10,
            '8' =>10,
        ];
        $ret = '';
        $sum = array_sum($pro);
        foreach($pro as $k => $v) {
            $r = mt_rand(1, $sum);
            if($r <= $v){
                $ret = $k;
                break;
            } else {
                $sum = max(0, $sum - $v);
            }
        }
        return $ret;
    }

    /**
     * 金币配比;
     * @return array
     */
    private static function getWheelGoldCoins() {
        return [
            1 => 80,
            2 => 300,
            3 => 500,
            4 => 800,
        ];
    }

}