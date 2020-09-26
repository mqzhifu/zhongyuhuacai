<?php
class LoginCtrl extends BaseCtrl  {
    public $request = null;
    public $agent = null;

    function __construct($request){
        parent::__construct($request);

        $this->request = $request;
//        $agent = $this->agentService->getOneByUid($this->uid);
//        if(!$agent){
//            out_ajax(8368);
//        }
//
//        $this->agent = $agent;
    }

    function logout(){
        $this->_sess->none();
        jump("/");
    }

    function index(){
        $this->setTitle('登陆');
        $this->setSubTitle('登陆');

        if($this->uinfo){
            jump("/");
        }

        if(_g("opt")){

            $mobile = get_request_one( $this->request,'phone',"");
            $smsCode = get_request_one( $this->request,'smsCode',"");

            if(!$mobile){
                out_ajax(8000);
            }

            if(!FilterLib::preg($mobile,'phone')){
                out_ajax(8003);
            }

            if(!$smsCode){
                out_ajax(8389);
            }

            $smsCode = (int)$smsCode;
            if(!$smsCode){
                out_ajax(8387);
            }

            if(strlen($smsCode) != 6){
                out_ajax(8388);
            }

            $VerifierCodeClass = new VerifierCodeLib();
            $VerifierCode = $VerifierCodeClass->authCode(VerifierCodeLib::TypeCellphone,$mobile,$smsCode,SmsRuleModel::$_type_login);
            if($VerifierCode['code'] != 200){
                out_ajax($VerifierCode['code'],$VerifierCode['msg']);
            }

            $agentUser = AgentModel::db()->getRow(" mobile = '$mobile' ");
            if(!$agentUser){
                out_ajax(8386);
            }

            $this->_sess->setValue("uinfo",$agentUser);
            out_ajax(200);
        }

//        $this->assign("payTypeOptions", OrderModel::getPayTypeOptions());
        $this->display("login.html");
    }

    //用户名+密码 登陆
    function ps(){
        $mobile = get_request_one( $this->request,'mobile',"");
        $ps = get_request_one( $this->request,'ps',"");
        $verifierCode = get_request_one( $this->request,'verifier_code',"");

        if(!$mobile){
            out_ajax(8000);
        }

        if(!$ps){
            out_ajax(8001);
        }

        if(!$verifierCode){
            out_ajax(8002);
        }

        if(!FilterLib::regex($ps,'md5')){
            return out_pc(8102);
        }

//        $code = $this->session->getImgCode();
//        if(strtolower($verifierCode) != strtolower($code))
//            out_ajax(501,'verify err');


        $agentUser = AgentModel::db()->getRow(" mobile = '$mobile' and ps = '$ps'" );
        if(!$agentUser){
            out_ajax(8386);
        }
        $token = $this->userService->createToken($agentUser['id']);
    }
    //手机号 + 短信登陆
    function sms(){
        $cellphone = get_request_one( $this->request,'ids',"name");
        $smsCode = get_request_one( $this->request,'ids',"smsCode");
//        $rs = $this->selfLogin($cellphone,null,null,$smsCode);
        $this->userService->selfLogin($cellphone,$ps);
    }
    //用户名/密码+手机号/短信 登陆
    function psSms(){

    }
    //
    function findPsBySms(){
        $cellphone = get_request_one( $this->request,'ids',"cellphpne");
        $smsCode = get_request_one( $this->request,'ids',"smsCode");
    }

}