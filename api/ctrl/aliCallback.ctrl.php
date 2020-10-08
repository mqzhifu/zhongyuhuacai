<?php
//阿里 SMS  回执
class AliCallbackCtrl{
    public $_jsonData = null;
    public $_data = null;
    public $_request;
    public $_template_status = array(
        'approved'=>AliSmsLib::SMS_TEMPLATE_STATUS_OK,
        'rejected'=>AliSmsLib::SMS_TEMPLATE_STATUS_FAIL,
    );
    function __construct($request){
        $jsonData = $request['input'];
        if(!$jsonData){
            $this->response(510,"request json data is null.");
        }
        $this->_jsonData = $jsonData;
        $this->_data = json_decode($jsonData,true);
    }

    //模板状态
    function template($request){
        LogLib::inc()->debug("test AliCallbackCtrl template:".$request['input']);
//        $this->response(200,"ok");

        //审批成功

//        [
//            {
//                "reason":"",
//                "template_name":"登陆",
//                "template_status":"approved",
//                "template_content":"验证码为:${code}，将在5分钟后失效，勿告他人。如非本人操作，请忽略。",
//                "remark":"正常网站H5登陆的时候，需要短信验证码",
//                "template_type":"验证码",
//                "create_date":"2020-10-08 16:34:54",
//                "order_id":"136924819",
//                "template_code":"SMS_204112309"
//            }
//        ]

            //审批失败

//        [
//            {
//                "reason":"*验证码模板只支持一个变量单位，例如：验证码：${code} 其余变量需转换为文字；;*验证码模板应如：验证码${code}，该验证码5分钟内有效，请勿泄漏于他人；",
//                "template_name":"修改密码",
//                "template_status":"rejected",
//                "template_content":"尊敬的用户您好${nickname}，验证码为：${code}，如非本人操作，请忽略，将在${expiretime}后失效。",
//                "remark":"修改密码时，需要使用手机验证一下，保证是本人操作",
//                "template_type":"验证码",
//                "create_date":"2020-10-08 17:58:24",
//                "order_id":"136945221",
//                "template_code":"SMS_204127351"
//            }
//        ]

        
        foreach ($this->_data as $k=>$v){
            $thirdId  = $v['template_code'];
            $rule = SmsRuleModel::db()->getRow("third_template_id = '$thirdId'");
            if(!$rule){
                $this->response(520,"template_code err , not in db");
            }

            $upData = array(
                'third_callback_time'=>time(),
                'third_callback_info'=> json_encode($v),
                'third_reason'=>$v['reason'] ,
                'third_status'=>$this->_template_status[$v['template_status']],
            );
            SmsRuleModel::db()->upById($rule['id'],$upData);
        }

        $this->response(200,"ok");
    }
    //签名状态
    function signName($request){
        LogLib::inc()->debug("test AliCallbackCtrl signName:".$request['input']);
        $this->response(200,"ok");

//        foreach ($data as $k=>$v){
//            $row = SmsLogModel::db()->getById("out_no = '{$v['out_id']}'");
//            if(!$row){
//
//            }
//            $upData = array(
//                'third_callback_info'=>$jsonData,
//                'third_callback_time'=>time(),
//                'third_callback_status'=>$data['err_code'],
//            );
//            SmsLogModel::db()->upById($row['id'],$upData);
//        }
//        var_dump($request);exit;
    }
    //短信状态
    function msg($request){
        LogLib::inc()->debug("test AliCallbackCtrl msg:".$request['input']);
//        $this->response(200,"ok");

        //发送成功
//        {
//            "send_time":"2020-10-08 14:15:59",
//            "report_time":"2020-10-08 14:16:05",
//            "success":true,
//            "err_msg":"用户接收成功",
//            "err_code":"DELIVERED",
//            "phone_number":"13522536459",
//            "sms_size":"1",
//            "biz_id":"627306802137758472^0",
//            "out_id":"16021377586842"
//        }

        foreach ($this->_data as $k=>$v){
            $row = SmsLogModel::db()->getById("out_no = '{$v['out_id']}'");
            if(!$row){
                continue;
            }
            $upData = array(
                'third_callback_info'=>json_encode($v),
                'third_callback_time'=>time(),
                'third_callback_status'=>"{$v['success']}-{$v['err_msg']}-{$v['err_code']}",
                'third_callback_report_time'=>$v['report_time'],
            );
            SmsLogModel::db()->upById($row['id'],$upData);
        }
        $this->response(200,"ok");
    }
    //上行短信，没搞懂
    function up($request){
        LogLib::inc()->debug("test AliCallbackCtrl up:".$request['input']);
        $this->response(200,"ok");
    }

    function response($code,$msg){
        $rs = array('code'=>$code,'msg'=>$msg);
        echo json_encode($rs);
        exit;
    }
}