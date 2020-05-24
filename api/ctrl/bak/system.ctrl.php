<?php
class SystemCtrl extends BaseCtrl  {
    //发送短信，如：验证码
        function sendSMS($cellphone,$ruleId){
        $class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone,$cellphone,$ruleId);

        return $this->out($rs['code'],$rs['msg']);
    }
    //发送邮件，如：通知、找回密码，验证码
    function sendEmail($email,$ruleId){
        $class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeEmail,$email,$ruleId);
        var_dump($rs);exit;
    }
    //获取图片验证码
    //type :1 注册验证码图片
    function getVerifierImg($unicode){
        $c = new ImageAuthCodeLib();
        $c->showImg(130,35,6);
        $c->saveCodeToRedis($unicode);

    }
    //验证图片验证码
    function authVerifierImgCode($unicode,$imgCode){
        $c = new ImageAuthCodeLib();
        $rs = $c->authCode($unicode,$imgCode);
    }
    //获取APP版本升级 - 描述信息
    function getAppUpgradeInfo($appVersion){
        $topVersion = AppVersionModel::db()->getRow(" status = 1 order by id desc limit 1");

        $appInfo = AppVersionModel::db()->getRow(" version = '$appVersion'");
        if($topVersion['id'] > $appInfo['id']){
            return $topVersion;
        }
    }
    //用户反馈信息
    function feedback($type,$contact,$content,$pics){
        // 2019/04/25上线，新增显示当前玩家所有app版本号字段;
        $clientInfo = get_client_info();
        $data = array(
            'uid'=>$this->uid,
            'type'=>$type,
            'contact'=>$contact,
            'content'=>$content,
            'a_time'=>time(),
            'status'=>1,
            'app_version'=>$clientInfo['app_version']
        );
        $aid = FeedbackModel::db()->add($data);

        return $this->out(200,$aid);

    }

    function pushAndroidMsgOneMsgByToken($title,$content,$typeId){
//        $accessToken = "b63b76b58e3c0d9b8d6cc659c338cebb8e065ee1";
        $lib =  new PushXinGeLib();
        $lib->pushAndroidMsgOneMsgByToken($this->uid,$title,$content,array('typeId'=>$typeId,'taskConfigId'=>1));

    }

    function pushAndroidNotifyOneMsgByToken($title,$content,$typeId){
//        $accessToken = "b63b76b58e3c0d9b8d6cc659c338cebb8e065ee1";
        $lib =  new PushXinGeLib();
        $lib->pushAndroidNotifyOneMsgByToken($this->uid,$title,$content,array('typeId'=>$typeId,'taskConfigId'=>2));

    }


    function pushAndroidAll($title,$content,$typeId){
        $lib =  new PushXinGeLib();
        $lib->pushAll( 1,$title,$content,$typeId);

    }

    function share($type,$platform,$toUid = 0,$gameId = 0 ,$platformMethod = 0){
        if(!$type){
            return $this->out(8004);
        }

        if(!GoldcoinLogModel::keyInType($type)){
            return $this->out(8210);
        }

        if(!$platform){
            return $this->out(8046);
        }

        $rs = $this->systemService->share($this->uid,$type,$platform,$toUid,$gameId ,$platformMethod );

        if($type == GoldcoinLogModel::$_type_share_income){
            $rs1 = $this->taskService->trigger($this->uid,8);
        }elseif($type == GoldcoinLogModel::$_type_share_friend){
            $rs1 = $this->taskService->trigger($this->uid,9);
        }

        // 新增日常任务 add by XiaHB time:2019/06/27 Begin;
        if(0 != $gameId){
            $this->taskService->trigger($this->uid,15);
            $this->taskService->trigger($this->uid,16);
        }
        // 新增日常任务 add by XiaHB time:2019/06/27   End;

        return $this->out($rs['code'],$rs['msg']);
    }
    //$type:
    function adStart($type){
        $rs = $this->systemService->adStart($this->uid,$type);
        return $this->out($rs['code'],$rs['msg']);
    }

    function adEnd($id){
        $rs = $this->systemService->adEnd($this->uid,$id);
        return $this->out($rs['code'],$rs['msg']);
    }

    function imAdminSendOne(){
        $lib = new ImTencentLib();
        $uinfo = $this->userService->getUinfoById(100000);
        $lib->sendOne($uinfo['im_tencent_sign'],100000);
    }

    function upAdminInfo(){
        $lib = new ImTencentLib();
        $rs = $lib->upAdminInfo();
        var_dump($rs);exit;
    }

    function adminSendOne(){
        $lib = new ImTencentLib();
        $rs = $lib->adminSendOne(308078,'wdy_test_avatar',4);
        var_dump($rs);exit;
    }
    //金币每日排行榜  前100名
    function dayGoldUserRankList(){
        $mod = date("i") / 10 + 1;
        $date = date("YmdH"). (int)$mod;
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_gold_rank']['key'],$date);

        $list = RedisPHPLib::get($key,1);
        if(!$list){
            $today = dayStartEndUnixtime();
            $list = GoldcoinLogModel::db()->getALL(" a_time >= {$today['s_time']}  and type != ".GoldcoinLogModel::$_type_gold_user_rank_first." group by uid order by goldNum desc limit 100",null," uid ,sum(num) as goldNum ");
            RedisPHPLib::set($key,$list,RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_gold_rank']['expire']),1);
        }

        foreach ($list as $k=>$v) {
            $user = $this->userService->getUinfoById($v['uid']);
            $list[$k]['avatar'] = $user['avatar'];
            $list[$k]['nickname'] = $user['nickname'];
            $list[$k]['sex'] = $user['sex'];
            $list[$k]['rank'] = $k+1;
        }

        return $this->out(200,$list);

    }
    //金币每日排行榜 昨日冠军
    function dayGoldUserRankFirst($day = 0){
        if($day){
            if(!  FilterLib::preg($day,'dateformat')){
                return $this->out(8121);
            }
        }else{
            $day = date("Ymd",time() - 24*60*60);
            //默认是取昨天的
        }
        $info = DayGoldUserRankModel::db()->getRow(" `day` = '$day' ");
        if($info){

            $user = $this->userService->getUinfoById($info['uid']);
            $info['avatar'] = $user['avatar'];
            $info['nickname'] = $user['nickname'];
            $info['sex'] = $user['sex'];

            $info['goldNum'] = $info['gold_num'] ;
            $info['rewardGoldNum'] = $info['reward_gold_num'] ;

            unset($info['gold_num']);
            unset($info['reward_gold_num']);
            unset($info['a_time']);
            unset($info['day']);

        }
        return $this->out(200,$info);
    }


    /**
     * @return array
     */
    public function banner(){
        // 版本兼容处理;
        $versionInfo = $this->clientInfo;
        $app_version = $versionInfo['app_version'];
        if($app_version < '1.1.5'){
            $bannerList = bannerModel::db()->getAll(" banner_location = 1 ");
            $uploadService = new UploadService();
            if(!empty($bannerList)){
                foreach ($bannerList as &$value){
                    $value['is_relative'] = (int)1;
                    $value['relative_path'] = (int)1;
                    $result = substr($value['img'],0,strrpos($value['img'],"/"));
                    if('/banner' == $result){
                        // 不同域,需要对路径进行处理;
                        $path = $uploadService->getStaticBaseUrl();
                        $pathNew = substr($path,0,strrpos($path,"/"));
                        $value['img'] = $pathNew."/instantplayadmin".$value['img'];
                    }else{
                        $value['img'] = get_static_file_url_by_app('banner', $value['img'], 'instantplayadmin');
                    }
                }
                foreach ($bannerList as &$v){
                    if(1 == $v['banner_location']){
                        $v['relative_path'] = 2;
                    }
                    if(4 == $v['banner_location']){
                        $v['relative_path'] = 2;
                    }
                    $v['banner_name'] = '';
                }
                return $this->out(200, $bannerList);
            }else{
                return $this->out(0, []);
            }
        }


        // 查询当前最新的APP版本号;
        $bannerType = !empty(_g('bannerType'))?_g('bannerType'):0;
        $res = bannerModel::db()->getRow(" banner_location = $bannerType ORDER BY app_version DESC LIMIT 1 ");
        // 1 == $bannerType || 4 == $bannerType
        if(true){
            if($app_version >= $res['app_version']){
                $bannerList = bannerModel::db()->getAll("1 = 1 AND banner_location = {$bannerType} ORDER BY weight DESC ", '', 'banner_location,banner_skip,weight,game_id,img,img_link,is_relative,relative_path');
            }else if($app_version < $res['app_version']){
                $bannerList = bannerModel::db()->getAll("1 = 1 AND banner_location = {$bannerType} AND app_version <= '{$app_version}' ORDER BY weight DESC ", '', 'banner_location,banner_skip,weight,game_id,img,img_link,is_relative,relative_path');
            }
        }/*else{
            $bannerList = bannerModel::db()->getAll("1 = 1 AND banner_location = {$bannerType} ORDER BY weight DESC ", '', 'banner_location,banner_skip,weight,game_id,img,img_link,is_relative,relative_path');
        }*/

        $uploadService = new UploadService();
        if(!empty($bannerList)){
            foreach ($bannerList as &$value){
                $result = substr($value['img'],0,strrpos($value['img'],"/"));
                $value['relative_path'] = $value['banner_skip'];
                $skipInfo = bannerSkipModel::db()->getById($value['banner_skip']);
                $value['banner_name'] = $skipInfo['name'];
                $value['is_relative'] = (int)1;
                if('/banner' == $result){
                    // 不同域,需要对路径进行处理;
                    $path = $uploadService->getStaticBaseUrl();
                    $pathNew = substr($path,0,strrpos($path,"/"));
                    $value['img'] = $pathNew."/instantplayadmin".$value['img'];
                }else{
                    $value['img'] = get_static_file_url_by_app('banner', $value['img'], 'instantplayadmin');

                }
            }
            foreach ($bannerList as $k => $v){
                unset($bannerList[$k]['banner_skip']);
                // unset($bannerList[$k]['img_link']);
                unset($bannerList[$k]['weight']);
            }

            return $this->out(200, $bannerList);
        }else{
            return $this->out(0, []);
        }
    }

    /**
     * @return array
     */
    public function banner_bak(){
        $bannerList = bannerModel::db()->getAll('1 = 1', '');
        foreach ($bannerList as $kk => $vv){
            if(1 != $vv['banner_location'] && 4 != $vv['banner_location']){
                unset($bannerList[$kk]);
            }
        }
        $uploadService = new UploadService();
        if(!empty($bannerList)){
            foreach ($bannerList as &$value){
                $value['is_relative'] = (int)1;
                $value['relative_path'] = (int)1;
                $result = substr($value['img'],0,strrpos($value['img'],"/"));
                if('/banner' == $result){
                    // 不同域,需要对路径进行处理;
                    $path = $uploadService->getStaticBaseUrl();
                    $pathNew = substr($path,0,strrpos($path,"/"));
                    $value['img'] = $pathNew."/instantplayadmin".$value['img'];
                }else{
                    $value['img'] = get_static_file_url_by_app('banner', $value['img'], 'instantplayadmin');
                }
            }
            return $this->out(200, $bannerList);
        }else{
            return $this->out(0, []);
        }

    }


}