<?php
//用户基类~ 注册   等
class UserService{
    //是否开启用户个人信息缓存
    public $redisCacheUser = 0;

    //注册
    // type:类型
    // $name:用户名/手机号/邮箱/三方ID
    // $data:用户信息,目前注册基本都是最简，详细信息后续面再补
    // $ps:有些注册类型，可能需要填写密码
    function register($name,$ps = '',$type ,$userInfo = null){
        if(!$name){
            return out_pc(8009, "未填写 用户名/手机号/邮箱/三方ID");
        }

//        if(!$type){
//            $type = $this->calcTypeByUname($name);
//        }else{
//            if(!UserModel::keyInRegType($type)){
//                return out_pc(8103);
//            }
//        }

        $data = $this->getUserDataInit();
        if($this->getTypeMethod($type) == UserModel::$_type_cate_self){
//            if($type == UserModel::$_type_cellphone){
//                //手机注册不需要密码，验证码已经验证过了
//            }else{
//                if(!$ps){
//                    return out_pc(8010, "未填写密码");
//                }
//            }

//            if($type == UserModel::$_type_cellphone || $type == UserModel::$_type_cellphone_ps){
            if($type == UserModel::$_type_cellphone){
                $uniq = $this->getCellphoneUnique($name);
            }elseif($type == UserModel::$_type_email){
                $uniq = $this->getEmailUnique($name);
            }elseif($type == UserModel::$_type_name){
                $uniq = $this->getNameUnique($name);
            }
            //判断唯一
            if($uniq){
                return out_pc(6003);
            }

            $data['nickname'] = $name;
            $data['type'] = $type;
            if($ps){
                $data['ps'] = md5($ps);
            }
//            if($type == UserModel::$_type_cellphone_ps){
//                if($ps){
//                    $data['ps'] = $ps;
//                }
//            }
//            if($type == UserModel::$_type_cellphone_ps){
//                isset($pcInfo['nickname']) && $data['nickname'] = $pcInfo['nickname'];
//                isset($pcInfo['sex']) && $data['sex'] = $pcInfo['sex'];
//                isset($pcInfo['avatar']) && $data['avatar'] = $pcInfo['avatar'];
//            }

//            if($type == UserModel::$_type_cellphone || $type == UserModel::$_type_cellphone_ps){
            if($type == UserModel::$_type_cellphone){
                $data['mobile'] = $name;
            }elseif($type == UserModel::$_type_email){
                $data['email'] = $name;
            }else{
                $data['name'] = $name;
            }
        }elseif($this->getTypeMethod($type) == UserModel::$_type_cate_guest){
            $data = array(
                "type"=>$type,
                "name"=>$name,
                'nickname'=>$name,
            );
        }else{
            //3方注册的过来的
            $data = array(
                "type"=>$type,
            );

            if($type == UserModel::$_type_wechat){
                $data['wx_open_id'] = $name;
            }elseif($type == UserModel::$_type_qq){
                $data['qq_uid'] = $name;
            }elseif($type == UserModel::$_type_facebook){
                $data['facebook_uid'] = $name;
            }elseif($type == UserModel::$_type_google){
                $data['google_uid'] = $name;
            }
        }

        if($userInfo){
            if(arrKeyIssetAndExist($userInfo,'avatar')){
                $data['avatar'] = $userInfo['avatar'];
            }

            if(arrKeyIssetAndExist($userInfo,'nickname')){
                $data['nickname'] = $userInfo['nickname'];
            }

            if(arrKeyIssetAndExist($userInfo,'sex')){
                $data['sex'] = $userInfo['sex'];
            }
        }

        $id = UserModel::db()->add($data);
        $data['uid'] = $id;

//        $detailData = array('uid'=>$id,'sign'=>'','summary'=>'','avatars'=>'','language'=>'','channel'=>'','company'=>'','telphone'=>'','fax'=>'','invite_code'=>'','feidou_uid'=>0);
//        $sign = $this->generateSign($id);
//        $detailData['im_tencent_sign'] = $sign;
//        $detailData['invite_code'] = $this->getInviteCode($id);
//        UserDetailModel::db()->add($detailData);

        $data['id'] = $id;

        $uid_str = intToStr($id);
        UserModel::db()->upById($id,array('uid_str'=>$uid_str));

        return out_pc(200,$data);

    }
    //手机/邮箱/用户名 + 密码
    function login($name,$ps){
        $type = $this->calcTypeByUname($name);
        $rs = $this->selfLogin($name,$ps,$type);
        return $rs;
    }

    function loginCellphoneSMS($cellphone,$smsCode,$imgCode = 0){
        $rs = $this->selfLogin($cellphone,null,null,$smsCode);
//        $rs = $this->selfLogin($cellphone,$ps,UserModel::$_type_cellphone_ps);
//        if($rs['code'] != 200){
//            return out_pc($rs['code'],$rs['msg']);
//        }
//
//        $user = $rs['msg'];
//        $this->loginRecord($user,$clientInfo,UserModel::$_type_cellphone_ps);
//        $token = $this->createToken($user['id']);
//
//        return out_pc(200,array('token'=>$token,'isReg'=>0,'uid'=>$user['id']));
    }
     //3方登陆
    function thirdLogin($thirdUid,$thirdType){
        $user = $this->getThirdUser($thirdUid,$thirdType);
        if($user){
//            $this->loginRecord($user,$clientInfo,$type);
            $token = $this->createToken($user['id']);
            return out_pc(200,$token);
        }
        return out_pc(1006);
    }
    //登陆注册，现在比较流行，手机/3方 - 登陆 ，如果用户ID 不存在，就直接注册，减少交互流程，后期再补用户信息
    //name:用户名/手机号/邮箱地址/3方UID
    //ps:登陆密码，可以为空：3方、短信验证码.
    //type:登陆类型，N种
    //clientInfo:客户端信息，如IOS ,12.0版本，经纬度等
    //$thirdInfo:3方返回的用户信息
    //$smsCode：如果是手机登陆方式，短信验证码必填
    function loginRegister($name,$ps,$type,$clientInfo = null ,$thirdInfo = null,$smsCode = 0,$loginUid = 0){
        $reg = 0;
//        $task = new TaskService();

        if($this->getTypeMethod($type) == "self"){
            $rs = $this->selfLogin($name,$ps,$type,$smsCode);
        }elseif($this->getTypeMethod($type) == "guest"){
            $reg = 1;
            $tmp_name = (PCK_AREA == 'en')?'Player':'游客';// 海外项目区分游客名称;（add by XiaHB time:2019/05/28）
            $name = $tmp_name.rand(10000,99999);
            $rs = $this->register($name,null,UserModel::$_type_guest);

//            $task->addUserGrowUpTask($rs['msg']['id']);

            $lotteryService = new LotteryService();
            $lotteryService->newUserSendMoneyCoupon($rs['msg']['id']);
        }else{
            $rs = $this->thirdLogin($name,$type);
        }

//        LogLib::appWriteFileHash($rs['code']);


        if($rs['code'] == 200){
            $user = $rs['msg'];

            if($this->getTypeMethod($type) == "third" && $thirdInfo){
                if($type == UserModel::$_type_wechat){
                    $thirdInfo['wechat_uid'] = $name;
                    if(arrKeyIssetAndExist($thirdInfo,'unionId')){
                        $thirdInfo['wx_union_id'] = $thirdInfo['unionId'];
                        $this->upUserInfo($user['id'],$thirdInfo);
                    }
                }elseif($type == UserModel::$_type_qq){
                    $thirdInfo['qq_uid'] = $name;
                    if(arrKeyIssetAndExist($thirdInfo,'unionId')){
                        $thirdInfo['qq_union_id'] = $thirdInfo['unionId'];
                        $this->upUserInfo($user['id'],$thirdInfo);
                    }
                }elseif($type == UserModel::$_type_facebook){
                    $thirdInfo['facebook_uid'] = $name;
                }elseif($type == UserModel::$_type_google){
                    $thirdInfo['google_uid'] = $name;
                }
                LogLib::appWriteFileHash(["======================",$loginUid,$thirdInfo]);
            }

        }elseif($rs['code']==1006){//验证都没有问题，但是用户不存在，需要将当前游客绑定到该账号下
            LogLib::appWriteFileHash($clientInfo);
            $reg = 1;
            if(!arrKeyIssetAndExist( $clientInfo,'app_version' ) || $clientInfo['app_version'] <= '1.0.6'){
                $rs = $this->register($name,$ps,$type,$clientInfo);
                if($rs['code'] == 200){
                    $user = $rs['msg'];
                    //3方登陆，可能会拿到用户一些基础信息，需要再更新一下
                    if($this->getTypeMethod($type) == "third"){
                        $thirdInfo['type'] = $type;


                        if($type == UserModel::$_type_wechat){
                            $thirdInfo['wechat_uid'] = $name;
                        }elseif($type == UserModel::$_type_qq){
                            $thirdInfo['qq_uid'] = $name;
                        }elseif($type == UserModel::$_type_facebook){
                            $thirdInfo['facebook_uid'] = $name;
                        }elseif($type == UserModel::$_type_google){
                            $thirdInfo['google_uid'] = $name;
                        }

//                        LogLib::appWriteFileHash(["----------------",$user['id'],$thirdInfo]);

                        $this->upUserInfo($user['id'],$thirdInfo);
//                        LogLib::appWriteFileHash($rs);
                    }elseif($type == UserModel::$_type_cellphone){
                        $data = array('nickname'=>"玩家".substr($name,-4),'type'=>$type,'cellphone'=>$name);
                        $this->upUserInfo($user['id'],$data);
                    }
                    $task->addUserGrowUpTask($user['id']);
                }
            }else{
                $user = $this->getUinfoById($loginUid);
                //3方登陆，可能会拿到用户一些基础信息，需要再更新一下
                if($this->getTypeMethod($type) == "third"){
                    $thirdInfo['type'] = $type;


                    if($type == UserModel::$_type_wechat){
                        $thirdInfo['wechat_uid'] = $name;
                        if(arrKeyIssetAndExist($thirdInfo,'unionId'))
                            $thirdInfo['wx_union_id'] = $thirdInfo['unionId'];
                    }elseif($type == UserModel::$_type_qq){
                        $thirdInfo['qq_uid'] = $name;
                        if(arrKeyIssetAndExist($thirdInfo,'unionId'))
                            $thirdInfo['qq_union_id'] = $thirdInfo['unionId'];
                    }elseif($type == UserModel::$_type_facebook){
                        $thirdInfo['facebook_uid'] = $name;
                    }elseif($type == UserModel::$_type_google){
                        $thirdInfo['google_uid'] = $name;
                    }

                    LogLib::appWriteFileHash(["----------------",$loginUid,$thirdInfo]);

                    $rs = $this->upUserInfo($loginUid,$thirdInfo);
                    LogLib::appWriteFileHash($rs);
                }elseif($type == UserModel::$_type_cellphone){
                    $data = array('nickname'=>"玩家".substr($name,-4),'type'=>$type,'cellphone'=>$name);
                    $this->upUserInfo($loginUid,$data);
                }
            }
        }else{
            return out_pc($rs['code'],$rs['msg']);
        }

        $this->loginRecord($user,$clientInfo,$type);
        $token = $this->createToken($user['id']);

        return out_pc(200,array('token'=>$token,'isReg'=>$reg,'uid'=>$user['id']));
    }

    function createToken($uid){
        $token = TokenLib::create($uid);
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
//        RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['token']['expire']);
        return $token;
    }

    function loginRecord($user,$clientInfo,$type){
//        $areaInfo = AreaLib::getByIp();
        $data = array(
            'uid'=>$user['id'],
            'a_time'=>time(),
            'ip'=>$clientInfo['ip'],
            'type'=>1,//1登入 2退出
            'login_type'=>$type,
            'cate'=>$clientInfo['cate'],
            'lon'=>$clientInfo['lon'],
            'lat'=>$clientInfo['lat'],//纬度
            'os'=>$clientInfo['os'],
            'os_version'=>$clientInfo['os_version'],
            'app_version'=>$clientInfo['app_version'],
            'device_model'=>$clientInfo['device_model'],
            'device_version'=>$clientInfo['device_version'],
            'browser_model'=>$clientInfo['browser_model'],
            'browser_version'=>$clientInfo['browser_version'],
            'ref'=>$clientInfo['ref'],
            'user_agent'=>$clientInfo['user_agent'],
            'sim_imsi'=>$clientInfo['sim_imsi'],
            'cellphone'=>$clientInfo['cellphone'],
            'dpi'=>$clientInfo['dpi'],

//            'addr'=>$areaInfo['addr'],
//            'channel'=>$areaInfo['channel'],
//            'area'=>json_encode($areaInfo),
//            'country'=>$areaInfo['country'],
//            'province'=>$areaInfo['province'],
//            'city'=>$areaInfo['city'],
        );


        if(!$data['lon']  || !$data['lat']){
            //纬度最大是90度，大于90度的一定是经度。
            $data['lon'] = '116.404844';
            $data['lat'] = '39.916706';
        }

        $class = new GeohashLib();
        $geoCode = $class->encode($data['lat'],$data['lon'] );
        $data['gps_geo_code'] = $geoCode;

        $id  = LoginModel::db()->add($data);

        // 新增登陆日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29 Begin;
        LoginMoreModel::add($data);
        // 新增登陆日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29   End;

        $data = array("login_time"=>time());
        $this->upUserInfo($user['id'],$data);

        return $id;
    }

    function offline($uid){
        $data = array('is_online'=>2);
        $rs = $this->upUserInfo($uid,$data);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
        RedisPHPLib::getServerConnFD()->del($key);

        return $rs;
    }

    //手机 邮箱  用户名 登陆
    //$isNoPs:不需要验证密码，短信已经验证过了
    function selfLogin($name,$ps,$type = 0,$smsCode=0){
//        LogLib::appWriteFileHash(['para:',$name,$ps,$type]);
        if(!$name){
            return out_pc(8009);
        }

        if(!$type)
            $type = $this->calcTypeByUname($name);

        if(!UserModel::keyInRegType($type)){
            return out_pc(8103);
        }

        if($type == UserModel::Log){
            if(!$smsCode){//手机登陆必须得有  手机短信验证码
                return out_pc(8014);
            }

            $rs = FilterLib::regex($name,'phone');
            if(!$rs){
                return out_pc(8119);
            }

            $VerifierCodeClass = new VerifierCodeLib();
            if($type == UserModel::$_type_cellphone){
                $VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone,$name,$smsCode,SmsRuleModel::$_type_login);
            }else{
                $VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone,$name,$smsCode,SmsRuleModel::$_type_pc_login);
            }

            if($VerifierCode['code'] != 200){
                return out_pc($VerifierCode['code'],$VerifierCode['msg']);
            }

            $where = " cellphone = '$name' ";
        }elseif($type == UserModel::$_type_email) {
            $rs = FilterLib::regex($name, 'email');
            if (!$rs) {
                return out_pc(8101);
            }
            $where = " email = '$name' ";
        }elseif($type == UserModel::$_type_cellphone_ps || $type == UserModel::$_type_pc_cellphone_ps){
            if(!$ps){
                return out_pc(8010);
            }
            if(!FilterLib::regex($ps,'md5')){
                return out_pc(8102);
            }

            //手机号+密码登陆
            $where = " cellphone = '$name' ";
        }else{
            if(!$ps){
                return out_pc(8010);
            }

            if(!FilterLib::regex($ps,'md5')){
                return out_pc(8102);
            }

            $where = " name = '$name' ";
        }

//        var_dump($where);
        $user = UserModel::db()->getRow($where);
//        var_dump($user);
        if(!$user){
            return out_pc(1006);
        }

        if( ( $type == UserModel::$_type_cellphone && $smsCode) || ( $type == UserModel::$_type_pc_cellphone_sms && $smsCode )  ){

        }else{
            if($user['ps'] != $ps){
                return out_pc(8201);
            }
        }

        return out_pc(200,$user);
    }

    function checkPassword($uid, $ps){
        $ps = md5($ps);
        $where = ' id = '.$uid;
        $user = UserModel::db()->getRow($where);
        if(!$user){
            return 0;
        }
        if($user['ps'] != $ps){
            return 0;
        }
        return 1;
    }
    //根据TYPE，获取3方来源的类型，再根据字段值匹配
    function getThirdUser($thirdUid,$type){
        if($type == UserModel::$_type_qq){
            $where = " qq_uid = '$thirdUid'";
        }elseif($type == UserModel::$_type_wechat){
            $where = " wx_open_id = '$thirdUid'";
        }elseif($type == UserModel::$_type_facebook){
            $where = " facebook_uid = '$thirdUid'";
        }elseif($type == UserModel::$_type_google){
            $where = " google_uid = '$thirdUid'";
        }else{
            return false;
        }
//        return UserModel::db()->getRow("type = $type and third_uid = '$thirdUid'");
        return UserModel::db()->getRow($where);
    }

    function getFieldById($uid,$field){
        $user = $this->getUinfoById($uid);
//        LogLib::appWriteFileHash(["aaaaaaaaaaaaaa",$uid,$field,$user[$field]]);
        if(isset($user[$field])){
            return $user[$field];
        }

        return "";
    }

    function getUinfoById($uid,$appName = ""){
        if(!$uid){
            return out_pc(8002);
        }

        if(!$appName){
            $appName = IS_NAME;
        }

        $uid = intval($uid);
        if(!is_int($uid)){
            return out_pc(8233);
        }

        if($this->redisCacheUser){
            $uinfo = $this->getUserCache($uid,$appName);
            // var_dump($uinfo);exit;
            if($uinfo){
                if(!arrKeyIssetAndExist($uinfo,'im_tencent_sign')){
                    $sign = $this->generateSign($uid);
                    UserDetailModel::db()->upById($uid,array('im_tencent_sign'=>$sign));
                    $uinfo['im_tencent_sign'] =$sign;
                }else{
                    $authRs = $this->authImTencentSign($uinfo['im_tencent_sign'],$uid);
                    if(!$authRs['rs']){
                        UserDetailModel::db()->upById($uid,array('im_tencent_sign'=>$authRs['sig']));
                        $uinfo['im_tencent_sign'] = $authRs['sig'];
                    }
                }

                return $uinfo;
            }
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            return false;
        }

        $user['avatar'] = getUserAvatar($user);

//        if(!arrKeyIssetAndExist($user,'avatar')){
//            $user['avatar'] = "默认图占位符";
//        }
//
//        if(!arrKeyIssetAndExist($user,'nickname')){
//            $user['nickname'] = "默认昵称";
//        }

//        $userDetail = UserDetailModel::db()->getRow(" uid = $uid");
//        if(!$userDetail){
//            $userDetail = array(
//                'channel'=>'','company'=>'','telphone'=>'','fax'=>'','invite_code'=>'','sign'=>'','push_xinge_touken'=>'','feidou_uid'=>0,
//                'summary'=>'','avatars'=>'','language'=>'','im_tencent_sign'=>'','invite_code'=>'','invite_uid'=>0);
//        }else{
//            if(APP_NAME == IS_NAME){
//                if(!arrKeyIssetAndExist($userDetail,'im_tencent_sign')){
//                    $sign = $this->generateSign($uid);
//                    UserDetailModel::db()->upById($uid,array('im_tencent_sign'=>$sign));
//                    $userDetail['im_tencent_sign'] = $sign;
//                }else{
//                    $authRs = $this->authImTencentSign($userDetail['im_tencent_sign'],$uid);
//                    if(!$authRs['rs']){
//                        UserDetailModel::db()->upById($uid,array('im_tencent_sign'=>$authRs['sig']));
//                        $userDetail['im_tencent_sign'] = $authRs['sig'];
//                    }
//                }
//            }
//        }

//        unset($userDetail['id']);
//        $user = array_merge($user,$userDetail);


        if($this->redisCacheUser){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid,$appName);
            $rs = RedisPHPLib::getServerConnFD()->hmset($key,$user);
            RedisPHPLib::getServerConnFD()->expire($key,$GLOBALS['rediskey']['userinfo']['expire']);
        }

        return $user;
    }

    function getCellphoneUnique($cellphone){
        $rs = UserModel::db()->getRow(" mobile = '$cellphone'");
        return $rs;
    }

    function getThirdUnique($type,$uniqId){
        if($type == UserModel::$_type_qq){
            return UserModel::db()->getRow(" qq_uid = '$uniqId'");
        }elseif($type == UserModel::$_type_wechat){
            return UserModel::db()->getRow(" wechat_uid = '$uniqId'");
        }elseif($type == UserModel::$_type_facebook){
            return UserModel::db()->getRow(" facebook_uid = '$uniqId'");
        }elseif($type == UserModel::$_type_google){
            return UserModel::db()->getRow(" google_uid = '$uniqId'");
        }else{
            return false;
        }
    }

    function getEmailUnique($email){
        $rs = UserModel::db()->getRow(" email = '$email'");
        return $rs;
    }

    function getNameUnique($uname){
        $rs = UserModel::db()->getRow(" name = '$uname)'");
        return $rs;
    }
    //手机号 用户名 邮箱 密码，为自平台处理
    function getTypeMethod($type ){
        if(in_array($type,UserModel::getTypeCateSelfDesc())){
            return UserModel::$_type_cate_self;
        }elseif(in_array($type,UserModel::getTypeGuestDesc())){
            return UserModel::$_type_cate_guest;
        }else{
            return UserModel::$_type_cate_third;
        }
    }

    function calcTypeByUname($name){
        $rs = FilterLib::regex($name,'phone');
        if($rs){
            $type = UserModel::$_type_cellphone;
        }elseif(FilterLib::regex($name,'email')){
            $type = 4;
        }else{
            $type = 5;
        }
        return $type;
    }

    //目前只允许修改：
    function upUserInfo($uid,$info){
        $data = array();
        if(arrKeyIssetAndExist($info,'is_online')){
            $data['is_online'] = $info['is_online'];
        }

        if(arrKeyIssetAndExist($info,'sex')){
            if(UserModel::keyInSex($info['sex'])){
                $data['sex'] = $info['sex'];
            }
        }

        if(arrKeyIssetAndExist($info,'type')){
            if(UserModel::keyInRegType($info['type'])){
                $data['type'] = $info['type'];
            }
        }

        if(arrKeyIssetAndExist($info,'nickname')){
            $data['nickname'] = $info['nickname'];
        }

        if(arrKeyIssetAndExist($info,'avatar')){
            $data['avatar'] = $info['avatar'];
        }

        if(arrKeyIssetAndExist($info,'push_status')){
            $data['push_status'] = $info['push_status'];
        }

        if(arrKeyIssetAndExist($info,'hidden_gps')){
            $data['hidden_gps'] = $info['hidden_gps'];
        }

        if(arrKeyIssetAndExist($info,'cellphone')){
            $data['cellphone'] = $info['cellphone'];
        }

        if(arrKeyIssetAndExist($info,'wechat_uid')){
            $data['wechat_uid'] = $info['wechat_uid'];
        }

        if(arrKeyIssetAndExist($info,'wx_union_id')){
            $data['wx_union_id'] = $info['wx_union_id'];
        }

        if(arrKeyIssetAndExist($info,'qq_union_id')){
            $data['qq_union_id'] = $info['qq_union_id'];
        }

        if(arrKeyIssetAndExist($info,'qq_uid')){
            $data['qq_uid'] = $info['qq_uid'];
        }

        if(arrKeyIssetAndExist($info,'ps')){
            $data['ps'] = $info['ps'];
        }

        if(arrKeyIssetAndExist($info,'goldcoin_sum')){
            $data['goldcoin_sum'] = $info['goldcoin_sum'];
        }

        if(arrKeyIssetAndExist($info,'goldcoin_sum_less')){
            $data['goldcoin_sum_less'] = $info['goldcoin_sum_less'];
        }

        if(arrKeyIssetAndExist($info,'third_uid')){
            $data['third_uid'] = $info['third_uid'];
        }

        if(arrKeyIssetAndExist($info,'facebook_uid')){
            $data['facebook_uid'] = $info['facebook_uid'];
        }

        if(arrKeyIssetAndExist($info,'google_uid')){
            $data['google_uid'] = $info['google_uid'];
        }

//        LogLib::appWriteFileHash(" im in upuserinfo =========");
//        LogLib::appWriteFileHash($data);

        if(!$data){
            return out_pc(8220);
        }
        $rs = UserModel::db()->upById($uid,$data);
        if($this->redisCacheUser){
            if(arrKeyIssetAndExist($data,'avatar')){
                $user = $this->getUinfoById($uid);
                $user['avatar'] = $data['avatar'];
                $newAvatar = getUserAvatar($user);
                $data['avatar'] = $newAvatar;
            }
            $this->upCacheUinfoByField($uid,$data, IS_NAME);
        }

//        $this->upCacheUinfoByField($uid,$data);

        // 触发task_id = 17 任务 add by XiaHB time:2019/06/27 Begin;
        // 用户完善资料，其实只有第三方登陆的时候，才会更新头像等字段游客也没有单独的入口，所有说这是唯一的入口，然后就是点编辑的时候完善性别（user->upInfo也最终会走到这）;
        // 由于创建和修改都会走这个方法，要先反查下;
        /*$insertData = array(
            'uid' => $uid,
            'status' => 1,
            'a_time' => time(),
            'u_time' => time()
        );
        $tmp = new TaskService();
        $user_info = UserModel::db()->getRow("id = $uid",'user','nickname, avatar, sex');
        if(!empty($data['nickname']) && empty($user_info['nickname'])){
            $insertData['message_type'] = 1;
            perfectPresonalMessageModel::db()->add($insertData);
            $tmp->trigger($uid, 17);
        }
        if(!empty($data['avatar']) && empty($user_info['avatar'])){
            $insertData['message_type'] = 2;
            perfectPresonalMessageModel::db()->add($insertData);
            $tmp->trigger($uid, 17);
        }
        if(!empty($data['sex']) && empty($user_info['sex'])){
            $insertData['message_type'] = 3;
            perfectPresonalMessageModel::db()->add($insertData);
            $tmp->trigger($uid, 17);
        }*/
        // 触发task_id = 17 任务 add by XiaHB time:2019/06/27   End;

        return out_pc(200,$rs);

    }

    //================日活统计相关===============================

//    function getDayActiveUser($uid ,$day = ''){
////        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
////        if(!$day){
////            return RedisPHPLib::getServerConnFD()->hGetAll($key);
////        }
//    }
//
//    function setDayActiveUser($uid){
////        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['day_active_user']['key'],$uid,IS_NAME);
////        $file =  date("Ymd");
////        return RedisPHPLib::getServerConnFD()->hSetNx($key,$file,time());
//    }
//
//    function delGoldCoin3log($uid){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['goldcoin_3_log']['key'],$uid,IS_NAME);
//        return RedisPHPLib::getServerConnFD()->del($key);
//    }
//
//    function delUserRedisInfo($uid){
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid);
//        RedisPHPLib::getServerConnFD()->del($key);
//    }
//
//
//    //获取用户连续活跃天数
//    function getActiveContinue($uid){
//        $game = new GamesService();
//        $activeTimeList = $game->getDayActiveUser($uid);
//        if(!$activeTimeList){
//            return $activeTimeList;
//        }
//
//        krsort($activeTimeList);
//        $end = count($activeTimeList);
//
//        $newActiveTimeList = [];
//        foreach ($activeTimeList as $k=>$v) {
//            $newActiveTimeList[] = strtotime($k);
//        }
//
//        $active = 0;
//        $list = [];
//        for($i=0;$i < $end;$i++){
////            echo date("Y-m-d",$newActiveTimeList[$i])."<br/>";
//            if($i == $end - 1){//证明是最后一个
//                $active++;
//                $list[] = $newActiveTimeList[$i];
//            }else{
//                //如果当天跟前一天，相隔24小时，证明断了~非连续签到
//                if($newActiveTimeList[$i] - $newActiveTimeList[$i+1] > 24 * 60 * 60){
//                    $active++;
//                    $list[] = $newActiveTimeList[$i];
//                    break;
//                }else{
//                    $active++;
//                    $list[] = $newActiveTimeList[$i];
//                }
//            }
//        }
//
//        return $list;
//    }

//    function upUserDetailInfo($uid,$info){
//        $data = array();
//        if(arrKeyIssetAndExist($info,'im_tencent_sign')){
//            $data['im_tencent_sign'] = $info['im_tencent_sign'];
//        }
//
//        if(arrKeyIssetAndExist($info,'invite_uid')){
//            $data['invite_uid'] = $info['invite_uid'];
//        }
//
//        if(arrKeyIssetAndExist($info,'push_xinge_touken')){
//            $data['push_xinge_touken'] = $info['push_xinge_touken'];
//        }
//
//        if(arrKeyIssetAndExist($info,'feidou_uid')){
//            $data['feidou_uid'] = $info['feidou_uid'];
//        }
//
//        if(!$data){
//            return out_pc(8220);
//        }
//
//        $rs = UserDetailModel::db()->update($data," uid = $uid limit 1");
//        if($this->redisCacheUser){
//            $this->upCacheUinfoByField($uid,$data);
//        }
//
//        $rs = $this->upCacheUinfoByField($uid,$data);
//        return out_pc(200,$rs);
//    }

    //================以上，日活统计相关===============================


    //================金币 积分相关===============================
//    function upGoldcoin($uid,$num,$opt,$type,$memo,$title = "",$content = "",$isDbLog = 0){
//        LogLib::appWriteFileHash([$uid,$num,$opt,$type,$memo,$title,$content]);
//        if(!$type){
//            return out_pc(8004);
//        }
//
//        if(!GoldcoinLogModel::keyInType($type)){
//            return out_pc(8210);
//        }
//
//        $user = UserModel::db()->getById($uid);
////        LogLib::appWriteFileHash([$uid,$user]);
//        if($opt == 2){
//            if($num > $user['goldcoin'] ){
//                return out_pc(8203);
//            }
//        }
//
//        if(!$title){
//            $title = GoldcoinLogModel::getTypeTitleByKey($type);
//        }
//
//        if(!$content){
//            $content = GoldcoinLogModel::getTypeDescByKey($type);
//            if($type == GoldcoinLogModel::$_type_gold_user_rank_first){
//                $yesterday = time() - 24 * 60 *60 ;
//                $content = str_replace("#date#",ymdTurnCn($yesterday),$content);
//            }
//        }
//
////        $bankLib =  new BankService();
////        $goldinfo = $bankLib->getGoldcoinInfo($uid);
////        $goldinfo = $goldinfo['msg'];
//
//        $data = array(
//            'num'=>$num,
//            'memo'=>$memo,
//            'uid'=>$uid,
//            'type'=>$type,
//            'a_time'=>time(),
//            'opt'=>$opt,
//            'title'=>$title,
//            'content'=>$content,
//        );
//
//        $aid = GoldcoinLogModel::db()->add($data);
//
//        // 新增金币日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29 Begin;
//        GoldCoinLogMoreModel::add($data);
//        // 新增金币日志表按月拆分逻辑，总表插入逻辑不变化; add by XiaHB time:2019/06/29   End;
//
//        //添加  用户最近3日金币日志  到redis中
//        $content = $aid . "##" .$type . "##".time()."##".$num;
//        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['goldcoin_3_log']['key'],$uid,IS_NAME);
//        $rs = RedisPHPLib::getServerConnFD()->zAdd($key,time(),$content);
//
////        LogLib::appWriteFileHash(["zadd goldcoin_3_log",$content]);
//
//
//        $goldcoin = $user['goldcoin'];
//        if( !$goldcoin){
//            $goldcoin = 0;
//        }
//
//        $addNum = $goldcoin + $num;
//        $data = array(
//            'goldcoin'=>$addNum,
//        );
//
//        //修改 金币总数 及缓存
//        $cache = array('goldcoin'=>$goldcoin + $num);
//        $this->upCacheUinfoByField($uid,$cache,IS_NAME);
//        //修改 金币总数 DB
//        $upRs = UserModel::db()->upById($uid,$data);
//
//
//
//
//        $gameService =  new GamesService();
//        if($num > 0){
//            //更新获取总金币数
//            $data =array('goldcoin_sum'=>$user['goldcoin_sum'] + $num);
//            UserModel::db()->upById($uid,$data);
//            $this->upCacheUinfoByField($uid,$data,IS_NAME);
//            // 更新玩家获取的金币数 add by XiaHB time:20190/07/01 Begin;
//            $bankTmp = new BankService();
//            $bankTmp->setAdditiveSumGold($uid, $num);
//            // 更新玩家获取的金币数 add by XiaHB time:20190/07/01   End;
//            LogLib::appWriteFileHash([" up goldcoin_sum cache:",$user['goldcoin_sum'] + $num]);
//            //更新今日获取金币数
//            $gameService->setToadyUserSumGoldcoin($uid,$num);
//            //更新 今日游戏时长
//            if($type == GoldcoinLogModel::$_type_play_games){
//                $gameService->setToadyUserPlayGameGoldcoin($uid,$num);
//                LogLib::appWriteFileHash(['log played time:',$uid,$num]);
//            }
//        }else{
//            $data = array('goldcoin_sum_less'=>$user['goldcoin_sum_less'] + $num);
//            UserModel::db()->upById($uid,$data);
//            $this->upCacheUinfoByField($uid,$cache,IS_NAME);
//        }
//
//        $arr = array("aid"=>$aid,'up-user-rs'=>$upRs);
//        return out_pc(200,$arr);
//    }

//    function addGoldcoin($uid,$num,$type,$memo = null,$title = "",$content = ""){
//        if(!$num){
//            return out_pc(8021);
//        }
//
//        if( (int)$num <= 0  ){
//            return out_pc(8220);
//        }
//
////        $today = dayStartEndUnixtime();
////        $todayAddGoldcoin = GoldcoinLogModel::db()->getRow(" uid = $uid and a_time >=  ".$today['s_time'],null," sum(num) as total ");
//        $todayAddGoldcoin = $this->getFieldById($uid,'goldcoin_today');
////        if(arrKeyIssetAndExist($todayAddGoldcoin,'total')){
//        if($todayAddGoldcoin){
//            //单日金币上限
//            // 增加金币不做金额限制条件;
//            // 1、常量AC已定义；
//            // 2、调用方法为abcd；
//            // 3、入口为Web；
//            // 4、运行环境为dev；
//            if(defined('AC') && 'abcd' == AC && 'dev' == ENV && 'WEB' == RUN_ENV){
//
//            }else{
//                if($todayAddGoldcoin >= 32188){
//                    //提现，预扣金币，如果返还的话，不需要限制
//                    if($type != GoldcoinLogModel::$_type_get_money_back){
//                        return out_pc(7008);
//                    }
//                }
//            }
//        }
//
//        if($type == GoldcoinLogModel::$_type_friend_play_game){
//            $lib = new GamesService();
//            $friendIncome = $lib->getFiendIncome($uid);
//            if($friendIncome && $friendIncome >= 100 * 10000 ){
//                return out_pc(7009);
//            }
//
//            //好友贡献，总金币上限
////            $todayAddGoldcoin = GoldcoinLogModel::db()->getRow(" uid = $uid and type = ".GoldcoinLogModel::$_type_friend_play_game,null," sum(num) as total ");
////            if(arrKeyIssetAndExist($todayAddGoldcoin,'total')){
////                if($todayAddGoldcoin['total'] >= 100 * 10000 ){
////                    return out_pc(7009);
////                }
////            }
////
////            //好友贡献，总金币单日上限
////            $todayAddGoldcoin = GoldcoinLogModel::db()->getRow(" uid = $uid and type = ".GoldcoinLogModel::$_type_friend_play_game . " and a_time >=  {$today['s_time']} ",null," sum(num) as total ");
////            if(arrKeyIssetAndExist($todayAddGoldcoin,'total')){
////                if($todayAddGoldcoin['total'] >= 5000 ){
////                    return out_pc(7010);
////                }
////            }
//        }
//
//        return $this->upGoldcoin($uid,$num,1,$type,$memo,$title,$content);
//    }
//
//    function lessGoldcoin($uid,$num,$type,$memo = null,$title = "",$content = "",$isDbLog = 0){
//        if(!$num){
//            return out_pc(8021);
//        }
//
//        if( (int)$num >= 0  ){
//            return out_pc(8209);
//        }
//
//        return $this->upGoldcoin($uid,$num,2,$type,$memo,$title,$content,$isDbLog);
//    }
//
//    function addPoint($uid,$num,$type,$memo = null){
//        if(!$num){
//            return out_pc(8021);
//        }
//
//        if( (int)$num <= 0  ){
//            return out_pc(8220);
//        }
//
//        return $this->upPoint($uid,$num,1,$type,$memo);
//    }
//
//    function lessPoint($uid,$num,$type,$memo = null){
//        if(!$num){
//            out_ajax(8021);
//        }
//
//        if( (int)$num >= 0  ){
//            out_ajax(8209);
//        }
//        return $this->upPoint($uid,$num,2,$type,$memo);
//    }

    //================金币 积分相关===============================


//    //腾讯-IM ，签名验证
//    function authImTencentSign($sign,$uid){
//        $ImTencentLib = new ImTencentLib();
//        $verifySig = $ImTencentLib->verifySig($sign,$uid);
//        if(!$verifySig['rs'] ){
//            //验证失败,重新生成一个
//            $sig = $this->generateSign($uid);
//            return array('rs'=>false,'sig'=>$sig);
//        }
//        //180失效
//        if( time() >=   $verifySig['init_time'] +  $verifySig['expire_time']){
//            //忆失效重新生成一个
//            $sig = $this->generateSign($uid);
//            return array('rs'=>false,'sig'=>$sig);
//        }
//        return array('rs'=>true);
//    }
//    //腾迅IM-SDK 登陆 SIGN
//    function generateSign($uid){
//        $ImTencentLib = new ImTencentLib($uid);
//        $sign = $ImTencentLib->generateSign($uid);
//        return $sign;
//    }


    //循环，获取一个唯一邀请码
//    function getInviteCode($uid){
//        while(1){
//            $code = $this->whileInviteCode($uid);
//            $row = UserDetailModel::db()->getRow(" invite_code = '$code'");
//            if(!$row){
//                return $code;
//            }
//        }
//    }
//    //邀请码生成规则
//    function whileInviteCode($uid){
//        $one = rand(0,5);
//        $two = rand(0,5);
//
//        $first = substr($uid,$one,1);
//        $second = substr($uid,$two,1);
//
//
//        $str_arr = [];
//        $str = "qwertyuiopasdfghjklzxcvbnmzxcvbnmqwertyuiopasdfghjkl";
//        for($i=0;$i<4;$i++){
//            $r = rand(0,strlen($str)-1);
//            $str_arr[] = substr($str,$r,1);
//        }
//        //根据UID 最后一位，决定，2位数字放在哪个位置
//        $last = substr($uid,5,1);
//        $mod = $last % 3;
//
//        if($mod == 1){
//            $rs = $first.$str_arr[0].$second.$str_arr[1].$str_arr[2].$str_arr[3];
//        }elseif($mod == 2){
//            $rs = $str_arr[0].$first.$str_arr[1].$second.$str_arr[2].$str_arr[3];
//        }else{
//            $rs = $str_arr[0].$str_arr[1].$str_arr[2].$str_arr[3].$first.$second;
//        }
//
//        return $rs;
//    }
//=============================邀请码相关==============================


    function getUserCache($uid,$appName ){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid,$appName);
        return RedisPHPLib::getServerConnFD()->hGetAll($key);
    }

    function upCacheUinfoByField($uid,$fields, $appName=null){
        //初始化写入缓存,防止没有缓存，结果写入一个字段，造成，用户信息应该整条缓存，却只缓存某一个值
        $this->getUinfoById($uid);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid, $appName);
        $rs = RedisPHPLib::getServerConnFD()->hmset($key,$fields);
        return $rs;
    }

    function getUserDataInit(){
        //初始化数据，防止接口返回数据类型 不对

//        $data['push_status'] = UserModel::$_push_open;
//        $data['hidden_gps'] = UserModel::$_gps_open;
//        $data['is_online'] = UserModel::$_online_true;

        $data['uname'] = '';
        $data['a_time'] = time();
        $data['u_time'] = time();
        $data['inner_type']= UserModel::INNER_TYPE_HUMAN;
        $data['nickname'] = "";
        $data['sex'] = 0;
        $data['avatar'] = "";

//        $data['point'] = 0;
//        $data['birthday'] = 0;
//        $data['goldcoin'] = 0;
//        $data['diamond'] = 0;
//        $data['vip_endtime'] = 0;
//        $data['push_type'] = 0;

//        if(!arrKeyIssetAndExist($data,'cellphone'))
//            $data['cellphone'] = "";
//        if(!arrKeyIssetAndExist($data,'ps'))
//            $data['ps'] = "";

//        $data['third_uid'] = "";
        $data['real_name'] = "";
        $data['id_card_no'] = "";
        $data['country'] = "";
        $data['province'] = "";
        $data['city'] = "";
        $data['tags'] = "";
        $data['school'] = "";
        $data['education'] = 0;
        $data['push_token'] = "";
        $data['email'] = "";
        $data['ps'] = "";
        $data['inner_type'] = UserModel::INNER_TYPE_HUMAN;
//        $data['point'] = 1000;//测试用
//        $area= AreaLib::getByIp();
//        $data['province'] =$area['province'];
//        $data['city'] =$area['city'];
//        $data['country'] =$area['country'];
//        $data['IP'] =$area['IP'];
//        $data['client_info'] =json_encode($clientInfo);
        return $data;
    }

}