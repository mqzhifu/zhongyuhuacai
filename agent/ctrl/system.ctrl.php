<?php
class SystemCtrl extends BaseCtrl {
    public $request = null;
    public $agent = null;

    function __construct($request){
        parent::__construct($request);

        $this->request = $request;
    }
    function verifyImg(){
        $lib = new ImageAuthCodeLib();
        $lib->showImg();

        $this->session->setImgCode($lib->code);
    }

    function sendSms(){
        $phone = $this->request['phone'];
        $ruleId =  $this->request['rule'];
        $class = new VerifierCodeLib();
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone,$phone,$ruleId);

        out_ajax($rs['code'],$rs['msg']);
    }

    function sendLoginSms(){
        $phone = $this->request['phone'];
        $class = new VerifierCodeLib();
//        $rs = array('code'=>200,'msg'=>'ok');
        $rs = $class->sendCode(VerifierCodeLib::TypeCellphone,$phone,SmsRuleModel::$_type_login);

        out_ajax($rs['code'],$rs['msg']);
    }
}