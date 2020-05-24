<?php

class UserCtrl extends BaseCtrl   {

    function upInfo($nickname= 0,$avatar = 0,$sex = 0,$sign = 0,$summary = 0 ,$ps = ''){
        $data = array(
            'nickname'=>$nickname,'sex'=>$sex,'sign'=>$sign,'summary'=>$summary ,'ps'=>$ps,
        );

//        LogLib::appWriteFileHash("im in upInfo");

        if($ps){
            if(!FilterLib::regex($ps,'md5')){
                return $this->out(8102);
            }

            if(arrKeyIssetAndExist($this->uinfo,'ps')){
                return $this->out(8047);
            }

            if(!arrKeyIssetAndExist($this->uinfo,'cellphone')){
                return $this->out(8282);
            }
        }

        $rs = $this->userService->upUserInfo($this->uid,$data);
        return $this->out($rs['code'],$rs['msg']);
    }

    function isBind($type,$unicode){
        if(!$type){
            return $this->out(8004);
        }

        if(!UserModel::keyInRegType($type)){
            return $this->out(8210);
        }

        if(!$unicode){
            return $this->out(8011);
        }

        if($type == UserModel::$_type_cellphone ){
            $uniq = $this->userService->getCellphoneUnique($unicode);
        }else{
            $uniq = $this->userService->getThirdUnique($type,$unicode);
        }

        $rs = 2;
        if($uniq){
            $rs = 1;
        }

        return $this->out(200,$rs);
    }

    function upAvatar(  $avatar = ""){
        $uploadService = new uploadService();
        $rs = $uploadService->uploadFileByApp('avatar','avatar','user',1 , APP_NAME,$this->uid);
        if($rs['code'] != 200){
            return $this->out($rs['code'],$GLOBALS['code'][$rs['code']]);
        }

        return $this->out($rs['code'], $rs['msg']);
    }

    function upAvatarOld($avatar = ""){
        $uploadService = new uploadService();
        $rs = $uploadService->upAvatar($this->uid, $avatar);
        if($rs['code'] != 200){
            return $this->out($rs['code'],$GLOBALS['code'][$rs['code']]);
        }

        return $this->out($rs['code'], $rs['msg']);
    }

    //uid:存在，代表看别人，否则是自己
    function getOne($toUid = 0 ){
        $user = $this->uinfo;
        if($toUid){
            $user = $this->userService->getUinfoById($toUid);
        }

        if($user){
//            $user['uid'] = $user['id'];

            unset($user['id']);
            unset($user['ps']);
            unset($user['third_uid']);
            unset($user['birthday']);
            unset($user['real_name']);
            unset($user['id_card_no']);
            unset($user['country']);
            unset($user['province']);
            unset($user['city']);
            unset($user['tags']);
            unset($user['school']);
            unset($user['education']);
            unset($user['u_time']);
            unset($user['push_token']);
            unset($user['goldcoin']);
            unset($user['diamond']);
            unset($user['email']);
            unset($user['vip_endtime']);
            unset($user['is_online']);
            unset($user['avatars']);
            unset($user['language']);

            unset($user['language']);

            unset($user['push_type']);
//            unset($user['uid']);
            unset($user['channel']);
            unset($user['company']);
            unset($user['telphone']);
            unset($user['fax']);
//            unset($user['invite_code']);


            unset($user['point']);

            $user['isFriend'] = 2;
            $user['isFollow'] = 2;

            if (!isset($user['money_token'])) {
                $user['money_token'] = '';
            }

            if($toUid){
//                $isFriend = $this->friendService->isFriend($this->uid,$user['id']);
//                if($isFriend){
//                    $user['isFriend'] = 1;
//                }
                $isFollow = $this->fansService->isFollow($this->uid,$toUid);
                if($isFollow){
                    $user['isFollow'] = 1;
                }else{
                    $user['isFollow'] = 2;
                }

                $isBlack = FansBlackModel::db()->getRow(" uid = $toUid and to_uid = {$this->uid}");
                if($isBlack){
                    $user['isBlack'] = 1;
                }else{
                    $user['isBlack'] = 2;
                }

                $selfBlack = FansBlackModel::db()->getRow(" uid = {$this->uid} and to_uid = $toUid ");
                if($selfBlack){
                    $user['selfBlack'] = 1;
                }else{
                    $user['selfBlack'] = 2;
                }

                $isBother = FansBotherModel::db()->getRow(" uid = $toUid and to_uid = {$this->uid}");
                if($isBother){
                    $user['isBother'] = 1;
                }else{
                    $user['isBother'] = 2;
                }

//                unset($user['point']);
                unset($user['type']);
                unset($user['email']);
                unset($user['cellphone']);
            }
            $prefix = substr($user['avatar'],0,strrpos($user['avatar'],"://"));
            $suffixal = trim(strrchr($user['avatar'], ':'),':');
            $user['avatar'] = ('https' == $prefix)?$user['avatar']:"https:{$suffixal}";
            $dev = OpenUserModel::db()->getRow(" uid = {$this->uid}");
            if($dev){
                $user['developer'] = 1;
            }else{
                $user['developer'] = 2;
            }
            $this->out(200,$user);
        }else{
            $this->out(1000);
        }


    }
//    //获取附近的人
//    function getNearUserList(){
//        $userGeo = LoginModel::db()->getRow(" uid = {$this->uid} order by a_time limit 1");
//        if(!$userGeo){
//            return $this->out(1015);
//        }
//
//        if(!$userGeo['gps_geo_code']){
//            return $this->out(1016);
//        }
//
//        $list = LoginModel::getNearUserByGeoHash($userGeo['gps_geo_code']);
//        if(!$list){
//            return $this->out(200,$list);
//        }
//
//        $rs = [];
//        foreach($list as $k=>$v){
//            if($v['uid'] != $this->uid){//过滤掉自己
//                $user = $this->userService->getUinfoById($v['uid']);
//                $distance = get_distance($v['lon'],$v['lat'],$userGeo['lon'],$userGeo['lat']);
//                $rs[] = array('distance'=>$distance,'uid'=>$v['uid'],'avatar'=>$user['avatar'],'nickname'=>$user['nickname']);
//
//            }
//        }
//
//        return $this->out(200,$rs);
//    }
    //举报用户
    function reportUser($toUid,$content){
        if(!$toUid){
            return $this->out(8026);
        }

        if($this->uid == $toUid){
            return $this->out(8268);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return $this->out(1020);
        }

        if(!$content){
            return $this->out(8040);
        }


        $data = array(
            'from_uid'=>$this->uid,
            'to_uid'=>$toUid,
            'a_time'=>time(),
            'status'=>ReportUserModel::$_status_wait,
            'content'=>$content,
        );

        $id = ReportUserModel::db()->add($data);

        return $this->out(200,$id);
    }

    function setUserSystemContact($list){


//        $arr = array(
//            array( 'name'=>'张三', 'cellphone'=>"13522XX55XX",),
//            array( 'name'=>'李四', 'cellphone'=>"13522XX55XX",),
//        );
//
//        var_dump(json_encode($arr));exit;

        if(!$list){
            return $this->out(8028,$GLOBALS['code'][8028]);
        }
        $list = stripslashes ($list);
        $list = json_decode($list,true);
        if(!$list || !is_array($list)){
            return $this->out(8267,$GLOBALS['code'][8028]);
        }
        foreach($list as $k=>$v){
            $data = array(
                'a_time'=>time(),
                'name'=>$v['name'],
                'cellphone'=>$v['cellphone'],
            );
            UserSysContactModel::db()->add($data);
        }

        return $this->out(200,1);
    }
    //更新 PUSH 状态
    function setPush($status){
        $status = intval($status);
        if(!$status){
            $this->out(8033,$GLOBALS['code'][8033]);
        }

        if(!UserModel::keyInPush($status)){
            $this->out(8245,$GLOBALS['code'][8245]);
        }

        $arr = array('push_status'=>$status);
        $rs = $this->userService->upUserInfo($this->uid,$arr);

        return $this->out($rs['code'],$rs['msg']);
    }
    //关闭GPS
    function setHiddenGps($status){
        $status = intval($status);
        if(!$status){
            $this->out(8033,$GLOBALS['code'][8033]);
        }

        if(!UserModel::keyInGps($status)){
            $this->out(8245,$GLOBALS['code'][8245]);
        }

        $arr = array('hidden_gps'=>$status);
        $rs = $this->userService->upUserInfo($this->uid,$arr);

        return $this->out($rs['code'],$rs['msg']);
    }

    function blackList(){
        $list = FansBlackModel::db()->getAll(" uid = ".$this->uid);
        if($list){
            foreach($list as $k=>$v){
                $user = $this->userService->getUinfoById($v['to_uid']);
                if($user){
                    $list[$k]['nickname'] = $user['nickname'];
                    $list[$k]['avatar'] = $user['avatar'];
                    $list[$k]['sex'] = $user['sex'];
                }
                $list[$k]['uid'] = $v['to_uid'];
                unset($list[$k]['id']);
                unset($list[$k]['to_uid']);
                unset($list[$k]['a_time']);
            }
        }
        return $this->out(200,$list);
    }

    function addBlack($toUid){
        if(!$toUid){
            return $this->out(8026,$GLOBALS['code'][8026]);
        }

        if($this->uid == $toUid){
            return $this->out(8254,$GLOBALS['code'][8254]);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return $this->out(1020,$GLOBALS['code'][1020]);
        }

        $isBlack = FansBlackModel::db()->getRow(" uid = ".$this->uid . " and to_uid = $toUid");
        if($isBlack){
            return $this->out(8269,$GLOBALS['code'][8269]);
        }

        $data = array(
            'a_time'=>time(),
            'uid'=>$this->uid,
            'to_uid'=>$toUid
        );

        $id = FansBlackModel::db()->add($data);

        return $this->out(200,$id);
    }

    function cancelBlack($toUid){
        if(!$toUid){
            return $this->out(8026,$GLOBALS['code'][8026]);
        }

        if($this->uid == $toUid){
            return $this->out(8273,$GLOBALS['code'][8273]);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return $this->out(1020,$GLOBALS['code'][1020]);
        }

        $isBlack = FansBlackModel::db()->getRow(" uid = ".$this->uid . " and to_uid = $toUid");
        if(!$isBlack){
            return $this->out(8270,$GLOBALS['code'][8270]);
        }

        $id = FansBlackModel::db()->delById($isBlack['id']);
        return $this->out(200,$id);
    }

    function addBother($toUid){
        if(!$toUid){
            return $this->out(8026,$GLOBALS['code'][8026]);
        }

        if($this->uid == $toUid){
            return $this->out(8273,$GLOBALS['code'][8273]);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return $this->out(1020,$GLOBALS['code'][1020]);
        }

        $isBlack = FansBotherModel::db()->getRow(" uid = ".$this->uid . " and to_uid = $toUid");
        if($isBlack){
            return $this->out(8275,$GLOBALS['code'][8275]);
        }

        $data = array(
            'a_time'=>time(),
            'uid'=>$this->uid,
            'to_uid'=>$toUid
        );

        $id = FansBotherModel::db()->add($data);

        return $this->out(200,$id);
    }

    function cancelBother($toUid){
        if(!$toUid){
            return $this->out(8026,$GLOBALS['code'][8026]);
        }

        if($this->uid == $toUid){
            return $this->out(8273,$GLOBALS['code'][8273]);
        }

        $userService = new UserService();
        $toUserInfo = $userService->getUinfoById($toUid);
        if(!$toUserInfo){
            return $this->out(1020,$GLOBALS['code'][1020]);
        }

        $isBlack = FansBotherModel::db()->getRow(" uid = ".$this->uid . " and to_uid = $toUid");
        if(!$isBlack){
            return $this->out(8274,$GLOBALS['code'][8274]);
        }

        $id = FansBotherModel::db()->delById($isBlack['id']);
        return $this->out(200,$id);
    }

    /**
     * Lucky user_info;
     */
    public function getUserLuckyInfo(){
        /*$bank = new BankService();
        $bankInfo = $bank->getGoldcoinInfo($this->uid);
        $bankInfo = $bankInfo['msg'];*/
        $userLib = new UserService();
        $userInfo = $userLib->getUinfoById($this->uid);
        if($userInfo){
            $returnMsg = array(
                'goldcoin' => $userInfo['goldcoin'],
                'balance' => sprintf("%.2f", ($userInfo['goldcoin'] * $GLOBALS['main']['goldcoinExchangeRMB'])),
                'nickname' => $userInfo['nickname'],
                'avatar' => $userInfo['avatar'],
            );
            $this->out(200, $returnMsg);
        } else{
            $this->out(1000);
        }
    }

    /**
     * Lucky0点，11点，18点卡牌数据获取;
     */
    public function getluckyUserCoin(){
        $uid = $this->uid;
        $time_end = strtotime('tomorrow');
        // 时间处理;
        $rs = luckyTimesConfigModel::db()->getRow();
        $tmp1 = $rs['click_one'];
        $tmp2 = $rs['click_two'];
        $tmp3 = $rs['click_three'];

        $day1 = (array)new DateTime("$tmp1:00:00");
        $day2 = (array)new DateTime("$tmp2:00:00");
        $day3 = (array)new DateTime("$tmp3:00:00");

        $time_one = strtotime($day1['date']);
        $time_two = strtotime($day2['date']);
        $time_three = strtotime($day3['date']);
        $now_time = time();

        // 逻辑校验;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['lucky_day_three_times']['key'],$uid.date("Ymd"),IS_NAME);
        $res = array();
        if($now_time >= $time_one && $now_time <= $time_two){
            $result = RedisPHPLib::getServerConnFD()->hGet($key, 'click_one');
            $res['is_show'] = $result;
        }elseif ($now_time >= $time_two && $now_time <= $time_three){
            $result = RedisPHPLib::getServerConnFD()->hGet($key, 'click_two');
            $res['is_show'] = $result;
        }elseif ($now_time >= $time_three && $now_time < $time_end){
            $result = RedisPHPLib::getServerConnFD()->hGet($key, 'click_three');
            $res['is_show'] = $result;
        }
        $this->out(200, $res);
    }

    /**
     * Lucky0点，11点，18点卡牌数据设置;
     */
    public function setluckyUserCoin(){
        $uid = $this->uid;
        $time_end = strtotime('tomorrow');
        // 时间处理;
        $rs = luckyTimesConfigModel::db()->getRow();
        $tmp1 = $rs['click_one'];
        $tmp2 = $rs['click_two'];
        $tmp3 = $rs['click_three'];

        $day1 = (array)new DateTime("$tmp1:00:00");
        $day2 = (array)new DateTime("$tmp2:00:00");
        $day3 = (array)new DateTime("$tmp3:00:00");

        $time_one = strtotime($day1['date']);
        $time_two = strtotime($day2['date']);
        $time_three = strtotime($day3['date']);
        $now_time = time();

        // 逻辑校验;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['lucky_day_three_times']['key'],$uid.date("Ymd"),IS_NAME);
        $res = array();
        if($now_time >= $time_one && $now_time <= $time_two){
            $result = RedisPHPLib::getServerConnFD()->hSet($key, 'click_one', 0);
            $res['is_show'] = $result;
        }elseif ($now_time >= $time_two && $now_time <= $time_three){
            $result = RedisPHPLib::getServerConnFD()->hSet($key, 'click_two', 0);
            $res['is_show'] = $result;
        }elseif ($now_time >= $time_three && $now_time < $time_end){
            $result = RedisPHPLib::getServerConnFD()->hSet($key, 'click_three', 0);
            $res['is_show'] = $result;
        }
        $this->out(200, $res);
    }

//分享东西
//    function share($type,$gameId,$platform = null,$toUid = 0 ){
//        if(!$gameId){
//            return $this->out(8027);
//        }
//
//        $gameInfo = GamesModel::db()->getById($gameId);
//        if(!$gameInfo){
//            return $this->out(1013);
//        }
//
//        //是否是3方注册，或者没有绑定3方的
////        if(!$this->uinfo['third_uid']){
////            return $this->out(8235);
////        }
//
//        $uname = $this->uinfo['nickname'];
//        $content = $uname." 刚刚玩了".$gameInfo['name']."游戏，分享给你";
//
//        if($type == 1){//1在游戏bar 中，分享指定-好友
//            if(!$toUid){
//                return $this->out(8026);
//            }
//
//            $this->imSevice->receive($this->uid,$toUid,$type,$content);
//        }
//
//
//
//        $data = array(
//            'a_time'=>time(),
//            'uid'=>$this->uid,
//            'to_uid'=>$toUid,
//            'status'=>1,
//            'type'=>$type,
//            'platform'=>$platform,
//            'game_id'=>$gameId,
//            'content'=>$content,
//        );
//
//        ShareModel::db()->add($data);
//
//    }
    //读取用户手机联系人，写入DB


    //    function pushUserInfo($nickname = null,$gender =null,$avatarUrl = null,$country = null,$province = null,$city = null){
//        $data = [];
//
//        if($gender){
//            $data['gender'] = $gender;
//        }
//
//        if($avatarUrl){
//            $data['avatar'] = urldecode($avatarUrl);
//        }
//
//        if($nickname){
//            $data['name'] = $nickname;
//        }
//
//        $area= AreaLib::getByIp();
//
//        if($country){
//            $data['country'] = $country;
//        }else{
//            $data['country'] =$area['country'];
//        }
//
//        if($province){
//            $data['province'] = $province;
//        }else{
//            $data['province'] =$area['country'];
//        }
//
//        if($city){
//            $data['city'] = $city;
//        }else{
//            $data['city'] =$area['city'];
//        }
//
//        $data['IP'] =$area['IP'];
//
//
//        UserModel::db()->setNames('utf8mb4');
//        UserModel::db()->upById($this->uid,$data);
//
//        $this->userLib->upCacheUinfoByField($this->uid,$data);
//
//        $key = $GLOBALS['rediskey']['userinfo']['key'];
//        foreach($data as $k=>$v){
//            RedisPHPLib::getServerConnFD()->hmset($key,$k,$v);
//        }
//
//        out_ajax(200,"ok");
//    }


}