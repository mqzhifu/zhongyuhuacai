<?php


class indexCtrl extends BaseCtrl
{

    function index() 
    {
        $this->addCss('assets/open/css/swiper-3.3.1.min.css');
        $this->addCss('assets/open/css/animate.min.css');
        $this->addCss('assets/open/css/welcome.css');
        $this->addCss('assets/open/css/bootstrap.min.css');
        $this->addJs('assets/open/scripts/swiper-3.3.1.min.js');
        $this->addJs('assets/open/scripts/swiper.animate.min.js');
        $this->addJs('assets/open/scripts/welcome.js');
        $this->addJs('assets/open/scripts/bootstrap.min.js');

        $this->display("welcome.html",'new');
    }

    function codeLogin()
    {
        if ($this->isLogin()) {
            jump("/");
            exit;
        }
        $this->addJs('assets/open/scripts/codelogin.js');
        $this->addCss('assets/open/css/codeLogin.css');
        $this->display("codeLogin.html",'new', 'noLogin');
    }
    function passLogin()
    {
        if ($this->isLogin()) {
            jump("/");
            exit;
        }
        $this->addJs('assets/open/scripts/passlogin.js');
        $this->addCss('assets/open/css/passLogin.css');
        $this->display("passLogin.html",'new', 'noLogin');
    }

    function login(){
        $cellphone = _g("cellphone");
        $ps = _g("password");
        $code = _g("code");

        if(!$ps && !$code)
            out_ajax(501,'请输入验证码或密码');

        if(!$cellphone)
            out_ajax(502,'请输入手机号');

        if(!FilterLib::regex($cellphone,'phone')){
            out_ajax(503, '手机号格式有误');
        }

        if ($ps) {
            $ps = md5($ps);
            $rs = $this->userService->pcLoginCellphonePs($cellphone, $ps);
        }
        else {
            $rs = $this->userService->pcLoginCellphoneSMS($cellphone, $code);
        }
        if($rs && $rs['code'] == 200){
            $user2 = $this->userService->getUinfoById($rs['msg']['id'], "instantplay");
            $uinfo = ['uid'=>$rs['msg']['id'], 'nickname'=>$rs['msg']['nickname'],'avatar'=>$user2['avatar']];
            $this->_sess->setSession(['user'=>$uinfo]);
            out_ajax(200,'ok');
        }else{
            out_ajax(505,"账号或密码有误");
        }
    }

    function sendsms(){
        $cellphone = _g("cellphone");
        if(!$cellphone)
            out_ajax(502,'cellphone null');

        if(!FilterLib::regex($cellphone,'phone')){
            out_ajax(503, 'phone error');
        }

        $class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone,$cellphone, 7);
        if($rs && $rs['code'] == 200){
            out_ajax(200,'ok');
        }else{
            out_ajax(506,$rs['msg']);
        }
    }

    function logout(){
        $this->_sess->none();
        Jump("/");
    }

    /**
     * time:2019/05/23;
     * 判断session;没有就让用户注册或者登陆;
     */
    public function thirdLogin(){
        if ($this->isLogin()) {
            jump("/game/show/");exit();
        }
        $this->display("login_hifun.html",'new', 'noLogin');
    }

    /**
     * time:2019/05/23;
     * regType：（fb:6;google:7）
     * First, verify whether the user information exists, register if it does not exist, and then save the session if it does exist;
     */
    public function thirdReg(){
        // parameter reception;
        $regType = _g('regType');
        $userID = _g('userID');
        $thirdNickname = _g('thirdNickname');
        $thirdAvatar = _g('thirdAvatar');

        if(!$regType || !$userID || !$thirdNickname || !$thirdAvatar){
            out_ajax(501,'请求参数缺失！');
        }
        if(UserModel::$_type_facebook != $regType &&  UserModel::$_type_google != $regType){
            out_ajax(502,'regType参数值不合法！');
        }

        // User table data validation;
        $uniqueId = (UserModel::$_type_facebook == $regType)?'facebook_uid':'google_uid';
        $userInfo = UserModel::db()->getRow(" $uniqueId = $userID ");
        if(!empty($userInfo) && isset($userInfo)){
            // session is reset if it exists;
            $uinfo = ['uid'=>$userInfo['id'], 'nickname'=>$userInfo['nickname'], 'avatar'=>getUserAvatar($userInfo)];
            $this->_sess->setSession(['user'=>$uinfo]);
            out_ajax(200,'ok');
        }else{
            // conduct account registration operation;
            $pcInfo = array(
                'nickname' => $thirdNickname,
                'avatar' => $thirdAvatar,
            );
            $rs = $this->userService->register($userID, '', $regType,null, $pcInfo);
            if($rs['code'] != 200){
                $this->outputJson($rs['code'], "注册失败");
            }

            // I don't know which brother wrote the note below;
            // 这行代码不能删除，不然，缓存会出错;
            $this->userService->getUinfoById($rs['msg']['uid'],IS_NAME);

            // 同步cdn
            // $this->openGamesService->rsyncToServer();

            // User table data validation;
            $uinfo = ['uid'=>$rs['msg']['id'], 'nickname'=>$rs['msg']['nickname'],'avatar'=>getUserAvatar($rs['msg'])];
            $this->_sess->setSession(['user'=>$uinfo]);

            // 推送腾讯（海外+生产环境调用此方法）;
            if(PCK_AREA == 'en' && ENV == 'release'){
                $this->pushTencentMsg($rs['msg']['id']);
            }

            $this->outputJson(200, "注册成功", ['uid'=>$rs['msg']['id']]);
        }

    }

    /**
     * 推送;
     * @param $uid
     */
    private function pushTencentMsg($uid){
        LogLib::writeGolgcoinHash('=========================Hifunopen_Tencent_Push_Begin=========================');
        $ImTencentLib = new ImTencentLib();
        $users  = UserModel::db()->getAll(" id = $uid ");
        if($users){
            $adminId = 200009;
            $userDetail = UserDetailModel::db()->getRow("uid = $adminId");
            $adminUserIm_tencent_sign = $userDetail['im_tencent_sign'];
            if($adminUserIm_tencent_sign){
                $url = "https://console.tim.qq.com/v4/im_open_login_svc/multiaccount_import?usersig=$adminUserIm_tencent_sign&identifier=$adminId&sdkappid={$ImTencentLib->getSdkAppId()}&contenttype=json&random=";
                $r = rand(100000,999999);
                $accounts = array("Accounts"=>$uid);
                $accounts = json_encode($accounts);
                $result = CurlLib::send($url.$r,2,$accounts,null,1);
            }
        }
        LogLib::writeGolgcoinHash("user_id：{$uid},tencent_sign：$adminUserIm_tencent_sign, res：$result");
        LogLib::writeGolgcoinHash('=========================Hifunopen_Tencent_Push_Over=========================');

    }


}
