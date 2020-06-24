<?php
class WxLittleCallbackCtrl{
    public $_msgEncodingAESKey = "";
    public $_msgToken = "";

    function __construct(){
        $config = ConfigCenter::get(APP_NAME,"wx");
        $this->_msgEncodingAESKey = $config['little']['msgEncodingAESKey'];
        $this->_msgToken = $config['little']['msgToken'];
    }
    function receive(){
        $rs = $this->checkSignature();
        echo $_GET['echostr'];exit;
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->_msgToken;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }
    //接收小程序推送的日志
    function log($request){
        LogLib::inc()->debug(["WxLittleCallback ,receive log:",$request]);
        out_ajax(200,"ok");
    }

    function getShareQrCode(){
        $pid = _g('pid');
        if(!$pid){
            out_ajax(8072);

        }

        $share_uid = _g('share_uid');
        if(!$share_uid){
            out_ajax(8110);
        }

        $product = ProductModel::db()->getById($pid);
        if(!$product){
            out_ajax(1026);
        }

        $user = UserModel::db()->getById($share_uid);
        if(!$user){
            out_ajax(1036);
        }

        $agentService = new AgentService();
        $agent = $agentService->getOneByUid($share_uid)['msg'];
        if(!$agent){
            out_ajax(1037);
        }

        $tmpPath = "/$pid/$share_uid.jpg";
        $path = get_share_qr_code_path($tmpPath);
        if(file_exists($path)){
            return $path;
        }

        $lib = new WxLittleLib();
        $binaryImg = $lib->getQrCode($pid,$share_uid);

        $imService = new UploadService();
        $imService->saveAgentShareQrCode($binaryImg,$pid,$share_uid);

    }
    function share($request){
        LogLib::inc()->debug("share callback ");
        LogLib::inc()->debug($request);
    }

    function shareTest($request){
//        LogLib::inc()->debug("share test callback ");
//        LogLib::inc()->debug($request);
        $lib = new WxLittleLib();
        $rs = $lib->getQrCode();
    }
}