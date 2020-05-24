<?php
// * 发送短信
class SmsLib  {
    public $_send_cellphone_max = 50;//一次最多发送多少条短信
    public $rule = null;
    //真的发送，走第3方接口
    function realSend($mobile, $content) {
//        $sms = $GLOBALS['main']['sms'];
//        $data = array(
//            'from' => 'liaozhan',
//            'mobile' => $mobile,
//            'appid' => $sms['appid'],
//            'content' => $content
//        );
//        ksort($data);
//        $json = json_encode($data);
//        $sign = md5("msg.send{$json}{$sms['sign']}");
//        $urlencodeJson = urlencode($json);
//        $param = array(
//            'name' => 'msg.send',
//            'data' => $urlencodeJson,
//            'sign' => $sign
//        );
//
//        $rs = CurlLib::send($sms['url'],2,$param);
//        if($rs['code'] != 200){
//            return $rs;
//        }
//
//        $res = json_decode($rs['msg'], true);
//        if (is_array($res) && isset($res['code']) && $res['code'] == 0) {
//            return out_pc(200,$res);
//        }else{
//            return out_pc(5009,$res);
//        }



//        $content = iconv("UTF-8",'GBK',$content);


        $md5key = md5($mobile.'ASDxcv8234sfsj12'.date('y-m'));
        $geturl="http://interface.kaixin001.com/interface/sms/send.php?mobile=$mobile&content=".urlencode($content)."&sig=$md5key&subnum=566001&monitor=app";

        $rs = CurlLib::send($geturl,1);
        return $rs;
    }

    public function sendSome($mobiles, $msg , $type = 'all') {
//        $data = array(
//            'uid',
//            'rule_id','content','status','a_time','IP','errinfo','cellphone'
//        );
//        SmsLogModel::db()->add();


        if(!is_array($mobiles)){
            return out_pc();
        }
        //去重复
        array_unique($mobiles);
        if(count($mobiles) > $this->_send_cellphone_max){
            return out_pc();
        }

        $key = $GLOBALS['rediskey']['sms']['key'];
        foreach($mobiles as $k=>$phone){
            //这个值要参与去重，所以不能无用的数据

            $queue_content = getIP().$this->_delimiter.$phone .$this->_delimiter .$type ;

            //往队列头尾部插入
            RedisPHPLib::getServerConnFD()->lPush();


            if(!$queue_num){
                $log->append("sendsms", date("Y-m-d H:i:s") . PHP_EOL . var_export(array('errmsg'=>"k:$k,phone:$phone".'set redis queue fail'), 1) . PHP_EOL . PHP_EOL);
                return array('status'=>1,'msg'=>"k:$k,phone:$phone".'set redis queue fail');
            }


            $rs  = Sys_Redis::inst()->set(get_SMS_redis_msg_key().$queue_content,$msg.$this->_delimiter.time().$this->_delimiter.$type);
        }
        return $arr = array('status'=>1);

    }


}
