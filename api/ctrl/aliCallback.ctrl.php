<?php
//阿里 SMS  回执
class AliCallbackCtrl{
//    public $_request = null;
//
//    function __construct($_request)
//    {
//        $this->_request = $_request;
//    }

    //模板状态
    function template($request){
//        [
//          {
//              "template_type":"验证码",
//            "reason":"test",
//            "template_name":"短信测试模版1957",
//            "orderId":"14130019",
//            "template_content":"签名测试签名测试签名测试签名测试",
//            "template_status":"approved",
//            "remark":"test",
//            "template_code":"SMS_123123242",
//            "create_date":"2019-05-30 19:58:25"
//          }
//        ]
        $thirdId  = $request['template_code'];
        $rule = SmsRuleModel::db()->getRow("third_id = '$thirdId'");
        if(!$rule){
            $this->response(510,"template_code err , not in db");
        }
        var_dump($request);exit;
    }
    //签名状态
    function sign($request){
        $jsonData = $request['input'];
        if(!$jsonData){

        }

        $data = json_decode($jsonData,true);
        foreach ($data as $k=>$v){
            $row = SmsLogModel::db()->getById("out_no = '{$v['out_id']}'");
            if(!$row){

            }
            $upData = array(
                'thrid_callback_info'=>$jsonData,
                'thrid_callback_time'=>time(),
                'thrid_callback_status'=>$data['err_code'],
            );
            SmsLogModel::db()->upById($row['id'],$upData);
        }
        var_dump($request);exit;
    }
    //短信状态
    function msg($request){
        var_dump($request);exit;
    }

    function response($code,$msg){
        $rs = array('code'=>$code,'msg'=>$msg);
        echo json_encode($rs);
        exit;
    }
}