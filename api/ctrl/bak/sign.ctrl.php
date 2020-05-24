<?php
class SignCtrl extends BaseCtrl   {

    //获取24小时签到列表
    function get24List(){
        $list24 = $this->signService->getUser24List($this->uid);
        return $this->out(200,$list24);
    }
    //执行24小时签到
    function doing24(){
        $list24 = $this->signService->getUser24List($this->uid);
        $hour = date("H:i:s");
        $hour = (int)$hour;

        //已签到
        if($list24[$hour]['isSign'] == 1){
            return $this->out(8303);
        }

        $data = array('uid'=>$this->uid,'a_time'=>time());
        $rs = SignModel::add($data);
        $this->out(200,$rs);
    }
    //签到次数，累加，还能再开宝箱
    function getLotteryBoxList()
    {
        $today = dayStartEndUnixtime();

        $sign = SignModel::getUserLisByTime($this->uid,$today['s_time'],$today['e_time']);
        $cnt = count($sign);

        $data = $GLOBALS['main']['signLotteryBox'];

        if ($cnt >= 8) {
            $changeId = 4;
        } elseif ($cnt >= 6) {
            $changeId = 3;
        } elseif ($cnt >= 4) {
            $changeId = 2;
        } elseif ($cnt >= 2) {
            $changeId = 1;
        } else {
            $changeId = 0;
        }

        if($changeId){
            for($i=1;$i<=$changeId;$i++){
                $data[$i]['status'] = 1;
            }
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_sign_box']['key'], date("Y-m-d") . "-" . $this->uid, IS_NAME);
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

        return $this->out(200,$data);
    }
    //执行签到
    function getLotteryBoxReward($id,$adId){
        if(!$adId){
            return $this->out(8043);
        }
        $ad = AdLogModel::db()->getById($adId);
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

        if($ad['type'] != GoldcoinLogModel::$_type_ad_sign_box){
            return $this->out(8306);
        }

        if(!$id){
            return $this->out(8043);
        }
        $data = $GLOBALS['main']['signLotteryBox'];
        $f = 0;
        foreach ($data as $k=>$v) {
            if($v['id'] == $id){
                $f = 1;
                break;
            }
        }
        if(!$f){
            return $this->out(8315);
        }

        $today = dayStartEndUnixtime();
        $sign = SignModel::getUserLisByTime($this->uid,$today['s_time'],$today['e_time']);
        if(!$sign){
            return $this->out(8316);
        }
        $cnt = count($sign);
        if ($cnt >= 8) {
            $changeId = 4;
        } elseif ($cnt >= 6) {
            $changeId = 3;
        } elseif ($cnt >= 4) {
            $changeId = 2;
        } elseif ($cnt >= 2) {
            $changeId = 1;
        } else {
            $changeId = 0;
        }

        if(!$changeId){
            return $this->out(8317);
        }

        if($id > $changeId ){
            return $this->out(8318);
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_sign_box']['key'], date("Y-m-d") . "-" . $this->uid, IS_NAME);
        $hasCnt = RedisPHPLib::getServerConnFD()->hGetAll($key);
        if($hasCnt){
            foreach ($hasCnt as $k=>$v) {
                if($k == $id){
                    return $this->out(8228);
                }
            }
        }

        $info = $data[$id];
        RedisPHPLib::getServerConnFD()->hSet($key,$id,$info);
        RedisPHPLib::getServerConnFD()->expire($key,$GLOBALS['rediskey']['day_sign_box']['expire']);


        $reward = rand(80,150);
        $this->userService->addGoldcoin($this->uid,$reward,GoldcoinLogModel::$_type_ad_sign_box);

        $this->out(200);

    }

    function addActiveUser($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
//        var_dump($key);
        $now = strtotime( date("Ymd") );
        $time = 0;
        for($i=0;$i<7;$i++){
            $time = $now - $i * 24 * 60 *60 ;
            $ymd = date("Ymd",$time);
            echo $ymd . " " . date("Y-m-d")."<br/>";
            $rs = RedisPHPLib::getServerConnFD()->hSetNx($key,$ymd,$time);
            var_dump($rs);
        }

    }

    function getListLog(){
//        $signLog = $this->signService->getWeekList($this->uid);
        $clientInfo = get_client_info();
        $signLog = [];
//        $clientInfo['appversion'] = "1.1.0";
//        if(0){
        if( !arrKeyIssetAndExist($clientInfo,'app_version') ||  $clientInfo['app_version'] < '1.1.0'){
            $signLog = $this->signService->getJunction7Day($this->uid);
            if ($signLog){
                foreach ($signLog as $k=>$v) {
                    $signLog[$k]['dayStartTime'] = $v['day_start_time'];
                    $signLog[$k]['isSign'] = 1;
                }

                $end = count($signLog);
                if($end < 7){
                    $end = 7 - $end ;
                    for($i=0;$i<$end;$i++){
                        $signLog[] = array('dayStartTime'=>0,'isSign'=>0);
                    }
                }

            }else{
                for($i=0;$i<7;$i++){
                    $signLog[] = array('dayStartTime'=>0,'isSign'=>0);
                }
            }
        }else{
            $activeLog = $this->userService->getActiveContinue($this->uid);
            if ($activeLog){
                $i = 0;
                foreach ($activeLog as $k=>$v) {
                    if($i >=7){
                        break;
                    }
                    $i++;
                    $signLog[] = array('dayStartTime'=>$v,'isSign'=>1);
                }

                $end = count($activeLog);
                if($end < 7){
                    $end = 7 - $end ;
                    for($i=0;$i<$end;$i++){
                        $signLog[] = array('dayStartTime'=>0,'isSign'=>0);
                    }
                }

            }else{
                for($i=0;$i<7;$i++){
                    $signLog[] = array('dayStartTime'=>0,'isSign'=>0);
                }
            }
        }

        return $this->out(200,$signLog);
    }

    //获取拿到列表记录，并领取奖励
    function getListAndReward(){
//        var_dump(strtotime("2019-01-24 00:00:00"));exit;

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['signLock']['key'],$this->uid);
        $config = array('nx', 'ex'=>$GLOBALS['rediskey']['signLock']['expire']);

        $addLockRs = RedisPHPLib::getServerConnFD()->set($key,time(),$config);
        if(!$addLockRs) {
            return $this->out(8287);
        }

        $today = dayStartEndUnixtime();
        //取出7天签到的日志
        $signLog = $this->signService->getWeekList($this->uid);
//        LogLib::appWriteFileHash($signLog);
        $signLog = $signLog['msg'];
        //今天签到的情况
        $signInfo = [];
        foreach($signLog as $k=>$v){
            if($v['dayStartTime'] == $today['s_time']){
//                LogLib::appWriteFileHash($v['isSign']);
                if(!$v['isSign']){
                    $v['time'] = $k + 1;
                    $signInfo = $v;
                    $signLog[$k]['isSign'] = 1;
                    break;
                }
            }
        }
        $rewardGold = 0;
        if( $signInfo  ){
            $data = array(
                'uid'=>$this->uid,
                'a_time'=>time(),
                'reward'=>$signInfo['rewardGold'] + $signInfo['addition'],
                'day_start_time'=>$today['s_time'],
                'sign_time'=>$signInfo['time'],
            );

            $aid = UserSignModel::db()->add($data);
            $rewardGold = $signInfo['rewardGold'] + $signInfo['addition'];
            if($rewardGold){
                $this->userService->addGoldcoin($this->uid,$rewardGold,GoldcoinLogModel::$_type_sign);
            }
        }

        RedisPHPLib::delLock($key);

        $rs = array('historyList'=>$signLog,'rewardGold'=>$rewardGold);

        return $this->out(200,$rs);
    }

    function getStatus(){
        $today = dayStartEndUnixtime();
        //取出7天签到的日志
        $signLog = $this->signService->getWeekList($this->uid);

        $signLog = $signLog['msg'];
        //今天签到的情况
        $signInfo = [];
        foreach($signLog as $k=>$v){
            if($v['dayStartTime'] == $today['s_time']){
                    $v['time'] = $k + 1;
                    $signInfo = $v;
                    break;
            }
        }

        $rs = array('tomorrowRewardGoldcoin'=>0,'todayRewardGoldcoin'=>0);
        //今日已签到
        if( arrKeyIssetAndExist($signInfo,'isSign') ){
            $rs['todayIsSign'] = 1;
            if($signInfo['time'] == 7){
                $tomorrowSign = $GLOBALS['main']['signReward'][0];
            }else{
                $tomorrowSign = $signLog[$signInfo['time']];
//                LogLib::appWriteFileHash($tomorrowSign);
            }

            $rs['tomorrowRewardGoldcoin'] = $tomorrowSign['rewardGold'] + $tomorrowSign['addition'];
        }else{
            $Sign = $GLOBALS['main']['signReward'][$signInfo['time'] - 1];
            $rs['todayIsSign'] = 2;
            $rs['todayRewardGoldcoin'] = $Sign['rewardGold'] + $Sign['addition'];
        }
        return $this->out(200,$rs);
    }

    function add24Test($times,$uid = 0){
        if(!$uid){
            $uid = $this->uid;
        }

        if(!$times){
            exit("time null");
        }

        if($times > 10){
            exit("time > 10");
        }

        $s_hour = date("H");

        $out = $times + $s_hour;
        if($out >= 24){
            exit(" times > 24");
        }

        for($i=$s_hour - 1;$i<$out;$i++){
            $date = date("Ymd"). " ". $i . ":00:00";
            $data = array(
                'uid'=>$uid,
                'a_time'=>strtotime($date),
            );

            $newId = SignModel::add($data);
            var_dump($newId);
        }

        exit;

    }


    function addDayTest($uid){
        UserSignModel::db()->delete(" uid = $uid limit 10");

        $today = dayStartEndUnixtime();
        $today['s_time'] = $today['s_time'] - 1 *  24 * 60 * 60;//从昨天开始
        $x = 5;
        for($i=1;$i<=6;$i++){
            $day_start_time = $today['s_time'] - $x * 24 * 60 *60;
            $data = array(
                'uid'=>$uid,
                'a_time'=>$day_start_time+1,
                'reward'=>100,
                'day_start_time'=>$day_start_time,
                'sign_time'=>$i,
            );
            $x--;
            $rs = UserSignModel::db()->add($data);
            var_dump($rs);
        }

    }

//    //签到列表 - 以 月/周 为一个大维度
//    function getMonthList(){
//        $monthLastDay = get_month_last_day(date("Y"),date("m"));
//
//        //每月1号
//        $monthFirstDay = date("Ym")."1";
//
//        $monthLastDay = date("Ym").$monthLastDay;
//
//        $History = SignModel::db()->getAll(" uid = {$this->uid} and a_time >= $monthFirstDay and a_time <= $monthLastDay");
//
//
//        $data= array();
//        for($i=1;$i<=$monthLastDay;$i++){
//            $data[$i] = 0;//未签到
//            foreach($History as $k=>$v){
//                if($v['a_time'] == $i){
//                    $data[$i] = 1;
//                    break;
//                }
//            }
//        }
//    }
}