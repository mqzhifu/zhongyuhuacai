<?php
class LotteryService{
    //抽宝箱
    function doBox($uid){
        //5分钟间隔，抽一个
        $allow  = $this->getResetTimeImpl($uid);
        if($allow){
            return out_pc(8288);
        }

        $freeTime = $this->getBoxTodayFreeTime($uid);
        if(!$freeTime || intval($freeTime) <= 0){
            return out_pc(8289);
        }

        $r = rand(1,100);
        $rewardGold = 0;
        if(PCK_AREA == 'en'){// 2019/06/12 海外金币值有所上调，概率值也有所变化 add by XiaHB;
            if ($r <= 30) {
                $rewardType = 3;
            } elseif ($r <= 40) {
                $rewardType = 1;
                $rewardGold = 30;
            } elseif ($r <= 20) {
                $rewardType = 1;
                $rewardGold = 80;
            } elseif ($r <= 5) {
                $rewardType = 1;
                $rewardGold = 100;
            } elseif ($r <= 100) {
                $rewardType = 1;
                $rewardGold = 20;
            }
        }else {
            if ($r <= 30) {
                $rewardType = 3;//玩小游戏
            } elseif ($r <= 50) {
                $rewardType = 1;
                $rewardGold = 50;
            } elseif ($r <= 90) {
                $rewardType = 1;
                $rewardGold = 30;
            } elseif ($r <= 95) {
                $rewardType = 1;
                $rewardGold = 100;
            } elseif ($r <= 100) {
                $rewardType = 1;
                $rewardGold = 10;
            }
        }
        $data = array(
            'uid'=>$uid,
            'a_time'=>time(),
            'reward_type'=>$rewardType,
            'reward_goldcoin'=>$rewardGold,
        );

        $aid = LuckBoxModel::db()->add($data);

//        if($rewardType == 1){
//            $lib = new UserService();
//            $lib->addGoldcoin($uid,$rewardGold,GoldcoinLogModel::$_type_luck_box,$aid);
//        }

        $lib = new TaskService();
        $lib->trigger($uid,7);

        $rs = array('rewardType'=>$rewardType,'rewardGold'=>$rewardGold);
        return out_pc(200,$rs);
    }
    //获取今日抽宝箱免费次数
    function getBoxTodayFreeTime($uid){
        $a_time = strtotime("today");
        $freeTime = LuckBoxModel::db()->getCount(" uid = ".$uid." and a_time > ".$a_time);
        $freeTime = 100 - $freeTime;
        return $freeTime;
    }

    public function getResetTimeImpl($uid){
        $where = ' uid = '.$uid.' ORDER BY a_time DESC LIMIT 1';
        $lastOpenTime = LuckBoxModel::db()->getRow($where);
        $resetTime = 0;
        if ($lastOpenTime) {
            // 距上次开宝箱间隔
            $interval = time() - $lastOpenTime['a_time'];
            // 配置宝箱间隔  -- 5min
            $defTime = 5*60;
            $resetTime = $interval > $defTime ? 0 : ($defTime-$interval);
        }
        return $resetTime;
    }




    //金币欢乐送
    function goldcoinCarnivalInfo($uid){
        $list = $this->goldcoinCarnivalList($uid);
        $today = dayStartEndUnixtime();



        $hasDoing = GoldcoinCarnivalModel::db()->getRow(" uid = $uid and is_use = 1 and do_time >= ".$today['s_time']);
        $is_free = 2;
        if(!$hasDoing){
            $is_free = 1;
        }
//        foreach ($list as $k=>$v) {
//            if($v['is_use'] == 1 && $v['do_time'] >= $today['s_time']){
////                if($v['is_free'] == 1){
//                    $is_free = 2;
//                    break;
////                }
//            }
//        }

        $multipleTimes = 0;
        $multiple = 0;

        $times = $list[0]['times'];
        if($times <=   5){
            $multipleTimes = 6 - $times ;
            $multiple = 2;
        }elseif($times <=   15){
            $multipleTimes = 16 - $times;
            $multiple = 5;
        }elseif($times <=   35){
            $multipleTimes = 36 - $times;
            $multiple = 10;
        }elseif($times <=   85){
            $multipleTimes = 86 - $times;
            $multiple = 20;
        }

        $rs = array('isFree'=>$is_free,'multipleTimes'=>$multipleTimes,'multiple'=>$multiple );

        return $rs;
    }

    function doGoldcoinCarnival($uid){
        $list = $this->goldcoinCarnivalList($uid);
        $info = $list[0];

        $today = dayStartEndUnixtime();
        $hasDoing = GoldcoinCarnivalModel::db()->getRow(" uid = $uid and is_use = 1 and do_time >= ".$today['s_time']  );
        $is_free = 2;
        if(!$hasDoing){
            $is_free = 1;
        }

        if($is_free == 2){
            $userService = new UserService();
            $rs = $userService->lessGoldcoin($uid,-100,GoldcoinLogModel::$_type_goldcoin_carnival_less);
            if($rs['code']!= 200){
                return $rs;
            }
        }

        $data = array(
            'do_time'=>time(),
            'is_free'=>$is_free,
            'is_use'=>1
        );

        GoldcoinCarnivalModel::db()->upById($info['id'],$data);
//        var_dump($data);exit;

        $userService = new UserService();
        $rs = $userService->addGoldcoin($uid,$info['reward_goldcoin'],GoldcoinLogModel::$_type_goldcoin_carnival);

        return $info['reward_goldcoin'];
    }

    function goldcoinCarnivalList($uid){
        $totalCnt = 86;
        $list = GoldcoinCarnivalModel::db()->getAll(" uid = {$uid} and is_use = 2 order by times asc ");
        if(!$list){
            for($i=1;$i<=86;$i++){
                $rewardGoldcoin = 0;
                if($i ==   6){
                    $rewardGoldcoin = 400;
                }elseif($i ==   16){
                    $rewardGoldcoin = 600;
                }elseif($i ==   36){
                    $rewardGoldcoin = 400;
                }elseif($i ==   86){
                    $rewardGoldcoin = 800;
                }else{
                    //因为有3次会出现 翻倍，不计算在内
                    $r = rand(1,$totalCnt  -4);
                    if($r <= 62){
                        $rewardGoldcoin = 40;
                    }elseif($r <= 76){
                        $rewardGoldcoin = 120;
                    }elseif($r <= 80){
                        $rewardGoldcoin = 200;
                    }elseif($r <= 82){
                        $rewardGoldcoin = 400;
                    }
                }

                $data = array(
                    'uid'=>$uid,
                    'is_free'=>2,
                    'is_use'=>2,
                    'times'=>$i,
                    'reward_goldcoin'=>$rewardGoldcoin,
                    'a_time'=>time(),
                );

                GoldcoinCarnivalModel::db()->add($data);
                $list[] = $data;
            }

            $list = GoldcoinCarnivalModel::db()->getAll(" uid = {$uid} and is_use = 2 order by times asc ");
        }

        return $list;

    }

    function newUserSendMoneyCoupon($uid,$couponId = 0 ){
        if(!$couponId){
            $couponId = 1;
        }

        $coupoin_config = $GLOBALS['main']['money_coupon'];
        $counInfo = $coupoin_config[$couponId];
        $today = dayStartEndUnixtime();
        $data = array(
            'uid'=>$uid,
            'a_time'=>time(),
            'valid_time'=>$today['s_time'] + 24 * 60 * 60,
            'expire_time'=>$today['s_time'] + $counInfo['expire'],
            'use_time'=>0,
            'money'=>$counInfo['money'],
            'element_id'=>1,
        );
        $addId = UserMoneyCouponModel::db()->add($data);

        return out_pc(200,$addId);
    }
}