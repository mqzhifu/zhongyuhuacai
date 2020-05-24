<?php
class UserSafeCtrl extends BaseCtrl {

    //找回密码
    //$type:1手机 2 邮箱
    function findPS($type,$addr, $code,$imgCode, $uniqueCode){
//        $authImgClass = new ImageAuthCodeLib();
//        $authImgCode = $authImgClass->authCode($uniqueCode,$imgCode);
//        if($authImgCode['code']!= 200){
//            out_ajax($authImgCode['code']);
//        }
//
//        $VerifierCodeClass = new VerifierCodeLib();
//        $VerifierCode = $VerifierCodeClass->authCode($type,$addr,$code,SmsRuleModel::$_type_findPs);
//        if($VerifierCode['code']!= 200){
//            out_ajax($VerifierCode['code']);
//        }

        $usersafe = new UsersafeLib();
        $rs = $usersafe->forgetPs($addr,$type);
        var_dump($rs);exit;
    }

    function bindEmail($email){

    }
    function bindCellphone($cellphone,$smsCode){
        $lib = new VerifierCodeLib( );
        $rs = $lib->authCode(1,$cellphone,$smsCode,2);
        if($rs['code'] != 200){
            return $this->out($rs['code'],$rs['msg']);
        }

        $isExists = $this->userService->getCellphoneUnique($cellphone);
        if($isExists){
            return $this->out(8276,$GLOBALS['code'][8276]);
        }

        $rs = $this->userService->upUserInfo($this->uid,array('cellphone'=>$cellphone));

        return $this->out(200,$rs);

    }

    function bindThird($type,$uniqueId){
        if(!$type){
            return $this->out(8004);
        }

        if(!UserModel::keyInRegType($type)){
            return $this->out(8210);
        }

        $isExists = $this->userService->getThirdUnique($type,$uniqueId);
        if(!$isExists){
            if($type == UserModel::$_type_qq){
                $data = array('qq_uid'=>$uniqueId);
            }elseif($type == UserModel::$_type_wechat){
                $data = array('wechat_uid'=>$uniqueId);
            }elseif($type == UserModel::$_type_facebook){
                $data = array('facebook_uid'=>$uniqueId);
            }elseif($type == UserModel::$_type_google){
                $data = array('google_uid'=>$uniqueId);
            }

            $this->userService->upUserInfo($this->uid,$data);
            return $this->out(200);
        }

        return $this->out(8295,$GLOBALS['code'][8295]);
    }


    //找回密码-重置
    function resetPS($ps,$confimPS,$code){
        $usersafe = new UsersafeLib();
        $rs = $usersafe->resetPS($ps,$confimPS,$code);

        var_dump($rs);exit;
    }


    // 修改密码（验证码）
    function upPs($ps,$smsCode) {
//        LogLib::appWriteFileHash(" im upps usersafe --------------");
//        LogLib::appWriteFileHash($ps);
        if (!$ps) {
            return $this->out(8010, $GLOBALS['code'][8010]);
        }

        if (!FilterLib::regex($ps,'md5')) {
            return $this->out(8102, $GLOBALS['code'][8102]);
        }

        if (!$smsCode) { //必须有手机短信验证码
            return $this->out(8014, $GLOBALS['code'][8014]);
        }

        //这里要再加密一次，防止MD5 碰撞
//        $ps = md5($ps);
        if ($this->uinfo['ps'] == $ps) {
            return $this->out(6106, $GLOBALS['code'][6106]);
        }

        if ( ! arrKeyIssetAndExist($this->uinfo,'cellphone')  ) {
            return $this->out(8282, $GLOBALS['code'][8282]);
        }

        // 验证短信
        $VerifierCodeClass = new VerifierCodeLib();
        $VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone, $this->uinfo['cellphone'], $smsCode, SmsRuleModel::$_type_upPs);
        if ($VerifierCode['code'] != 200) {
            return $this->out($VerifierCode['code'], $VerifierCode['msg']);
        }
        // 修改密码
        $data = array('ps'=>$ps);
        $rs = $this->userService->upUserInfo($this->uid,$data);

        return $this->out(200, 1);
    }

    
    //游客绑定FB，如果已经绑定过的，算做 更新用户 信息
    function guestBindThirdPlatform($type,$uniqueId , $nickname , $avatar , $sex){
        if(!$uniqueId){
            return $this->out(8030);
        }

        if(!$nickname){
            return $this->out(8031);
        }

        if(!$avatar){
            return $this->out(8032);
        }

        if(!$type){
            return $this->out(8004);
        }

        if(!UserModel::keyInRegType($type)){
            return $this->out(8210);
        }

        if($this->userService->getTypeMethod($type) != 'third'){
            return $this->out(8242);
        }

        if(!$this->uinfo['third_uid']){//首次绑定
            if($this->uinfo['type'] != UserModel::$_type_guest){
                //只有注册类型为<游客>才可以绑定
                $this->out(8244);
           }

        }else{
            if($type != 6){
                $this->out(8243);//已经绑定过了
            }
        }


        $thirdInfo = array(
            'nickname'=>$nickname,'avatar'=>$avatar,'sex'=>$sex,
        );

        $rs = $this->userService->loginRegister($uniqueId,null,$type,null,$thirdInfo);
        if(!$this->uinfo['third_uid']){//首次绑定
            $data = array('type'=>$type);
            $this->userService->upUserInfo($this->uid,$data);
        }

        return $this->out($rs['code'],$rs['msg']);

    }
    //设置3方推送的TOKEN值
    function setXinGeThirdPushToken($accessToken){
//        LogLib::appWriteFileHash(["(((((((((((((((((((((((((((((",$accessToken]);
        $data = array('push_xinge_touken'=>$accessToken);
        $rs = $this->userService->upUserDetailInfo($this->uid,$data);

//        $rs[] = "im in setXinGeThirdPushToken---------------";
//        LogLib::appWriteFileHash($rs);

        return $this->out(200,"ok");
    }
    //添加 身份 实名验证
    function addReadIdAuth($idNo,$realName){
        if(!$idNo){
            return $this->out(8057);
        }

        if(!$realName){
            return $this->out(8058);
        }
        $info = IdValidateModel::db()->getRow(" uid = ".$this->uid);
        if($info){
            return $this->out(8298);
        }

        $data = array(
            'uid'=>$this->uid,
            'no'=>$idNo,
            'status'=>1,
            'real_name'=>$realName
        );

        $id = IdValidateModel::db()->add($data);
        return $this->out(200,"ok".$id);

    }

    function isReadIdAuth(){
        $info = IdValidateModel::db()->getRow(" uid = ".$this->uid);
        $rs = 2;
        if($info){
            $rs = 1;
        }

        return $this->out(200,$rs);
    }

    //安全级别
    function safeLevel(){

    }

    function uploadIDInfo($idNo,$realName,$topPic,$backPic,$selfPic){

    }


    function bindMoneyToken($moneyToken)
    {
        if (!$moneyToken) {
            return $this->out(8035);
        }
        $uid = $this->uid;
        $userDetail = UserDetailModel::db()->getRow("uid = $uid");
        if (!$userDetail) {
            return $this->out(1000);
        }
        // 已绑定口令
        if ($userDetail['money_token']) {
            return $this->out(8299);
        }

        $tokens = $this->getMoneyToken();
        if (!in_array($moneyToken, $tokens)) {
            return $this->out(8300);
        }

        UserDetailModel::db()->update(["money_token"=>$moneyToken], "uid=$uid limit 1");

        $this->userService->upCacheUinfoByField($this->uid, ["money_token"=>$moneyToken]);

        return $this->out(200,"ok");
    }

    function getMoneyToken()
    {
        $date = strtotime('today');
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['money_token_day']['key'],$date);
        $token = RedisPHPLib::get($key);
        if (!$token) {
            $token = $this->createMoneyToken();
            RedisPHPLib::set($key, $token, $GLOBALS['rediskey']['money_token_day']['expire']);
        }
        return json_decode($token);
    }

    function createMoneyToken()
    {
        $keys = [];
        
        $pattern='1234567890abcdefghijkmnpqrstuvwxyz'; // 无 l o
        for ($i=0; $i<5; $i++) {
            $key = '';
            for( $j=0; $j<6; $j++ ) {
                $key .= $pattern[mt_rand(0, 33)];
            }
            $keys[$i] = $key;
        }
        return json_encode($keys);
    }
}