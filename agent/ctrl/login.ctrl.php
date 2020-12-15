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

            if($agentUser['status']  != AgentService::STATUS_OK){
                out_ajax(8407);
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
    }
    //用户名/密码+手机号/短信 登陆
    function psSms(){
    }
    //
    function findPsBySms(){
    }

    function wordUserProtocol(){
        $this->display("word_user_protocol.html");
    }

    function wordPrivateProtocol(){
        $this->display("word_private_protocol.html");
    }

    function enterQrCode(){
//        $url = get_domain_url() ."agent/apply/type=2&aid=".$aid ;
//        $url = "http://agent-dev.xlsyfx.cn/";
        $url = get_domain_url() ;

        require_once PLUGIN . '/phpqrcode/qrlib.php';

        $value = $url;					//二维码内容
        $errorCorrectionLevel = 'L';	//容错级别
        $matrixPointSize = 11;			//生成图片大小

        $service = new UploadService();
        //生成二维码图片
        $filename = $service->getApplyAgentUploadPath(999);
        QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 0);

        $original_pic_path = get_agent_apply_original_pic_path(1 );
        $this->mergePic($original_pic_path,$filename);
    }

    function mergePic($src,$qrCode){
        header('Content-type: image/jpg');

        $srcImg = imagecreatefromjpeg($src);
        $qrCodeImg = imagecreatefrompng($qrCode);

        imagecopymerge($srcImg, $qrCodeImg, 235,697, 0,0, imagesx($qrCodeImg), imagesy($qrCodeImg), 100);
        imagejpeg($srcImg);
        exit;
//        $merge = 'merge.png';
//        var_dump(imagepng($srcImg,'./merge.png'));//bool(true)
    }

}