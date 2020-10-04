<?php
class AgentLoginCtrl extends BaseCtrl  {
    public $request = null;
    public $agent = null;

    function __construct($request)
    {
        parent::__construct($request);

        $agent = $this->agentService->getOneByUid($this->uid);
        if(!$agent){
            out_ajax(8368);
        }

        $this->agent = $agent;
    }
//    //用户名+密码 登陆
//    function ps(){
//        $name = get_request_one( $this->request,'ids',"name");
//        $ps = get_request_one( $this->request,'ids',"ps");
//        $verifierCode = get_request_one( $this->request,'ids',"verifier_code");
//
////        $verifierCodeLib = new ImageAuthCodeLib();
////        $verifierCodeLib->authCode();
//        $rs = $this->userService->selfLogin($name,$ps);
//        if($rs['code'] != 200 ){
//
//        }
//        $user = $rs['msg'];
//        $token = $this->userService->createToken($user['id']);
//    }
//    //手机号 + 短信登陆
//    function sms(){
//        $cellphone = get_request_one( $this->request,'ids',"name");
//        $smsCode = get_request_one( $this->request,'ids',"smsCode");
////        $rs = $this->selfLogin($cellphone,null,null,$smsCode);
//        $this->userService->selfLogin($cellphone,$ps);
//    }
//    //用户名/密码+手机号/短信 登陆
//    function psSms(){
//
//    }
//    //
//    function findPsBySms(){
//        $cellphone = get_request_one( $this->request,'ids',"cellphpne");
//        $smsCode = get_request_one( $this->request,'ids',"smsCode");
//    }

}