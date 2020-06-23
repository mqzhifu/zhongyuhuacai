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


    function getShareQrCode(){
        $pid = _g('pid');
        if(!$pid){
            out_ajax(8366);

        }

        $share_uid = _g('share_uid');
        if(!$share_uid){
            out_ajax(8366);

        }

        $lib = new WxLittleLib();
        $lib->getQrCode($pid,$share_uid);
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