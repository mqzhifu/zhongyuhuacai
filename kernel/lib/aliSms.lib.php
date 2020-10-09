<?php
class AliSmsLib{
    public $_conf = null;

    const SMS_TEMPLATE_TYPE_CODE = 0;
    const SMS_TEMPLATE_TYPE_NOTIFY = 1;
    const SMS_TEMPLATE_TYPE_MARKET = 2;
    const SMS_TEMPLATE_TYPE_INTER = 3;


    const SMS_TEMPLATE_TYPE_DESC = array(
        self::SMS_TEMPLATE_TYPE_CODE=>"验证码",
        self::SMS_TEMPLATE_TYPE_NOTIFY=>"通知",
        self::SMS_TEMPLATE_TYPE_MARKET=>'营销',
        self::SMS_TEMPLATE_TYPE_INTER=>"国际"
    );

    const SMS_TEMPLATE_STATUS_AUDIT = 0;
    const SMS_TEMPLATE_STATUS_OK = 1;
    const SMS_TEMPLATE_STATUS_FAIL = 2;
    const SMS_TEMPLATE_STATUS_DESC = [
        self::SMS_TEMPLATE_STATUS_AUDIT=>'审核中',
        self::SMS_TEMPLATE_STATUS_OK=>'审核通过',
        self::SMS_TEMPLATE_STATUS_FAIL=>'审核失败',//请在返回参数Reason中查看审核失败原因。
    ];
    //html select options  ，给后台用
    static function getTemplateTypeOptionHtml($selectedId = null){
        $html = "";
        foreach (self::SMS_TEMPLATE_TYPE_DESC as $k=>$v) {
            $selected = "";
            if($selectedId !== null){
                if($k == $selectedId){
                    $selected = "selected";
                }
            }
            $html .= "<option value='{$k}' $selected>{$v}</option>";
        }
        return $html;
    }
    //html select options  ，给后台用
    static function getTemplateStatusOptionHtml(){
        $html = "";
        foreach (self::SMS_TEMPLATE_STATUS_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }



    function __construct(){
        $conf = ConfigCenter::get(APP_NAME,"main")['ali_sms'];
        $this->_conf = $conf;
    }

    function SendSms($cellphoneNumber,$templateId ,$replaceContent , $outNo = 0 , $uid = 0){
        $params = array ();
        $action = "SendSms";
        $params["PhoneNumbers"] =$cellphoneNumber;
        //短信模板Code， https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateId;
        //可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
//        $params['TemplateParam'] = Array (
//            'code'=>$content,
//        );
        $params['TemplateParam'] = $replaceContent;
//        $params['OutId'] = get_rand_uniq_str(14);//设置发送短信流水号
        if($outNo){
            $params['OutId'] = $outNo;
        }
        //上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        if($uid){
            $params['SmsUpExtendCode'] = $uid;
        }
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request($action,$params);

//        var_dump($content);exit;
//        array(4) { ["Message"]=> string(2) "OK" ["RequestId"]=> string(36) "AF9CF23F-9C2B-441C-8215-D1D056C96334" ["BizId"]=> string(20) "500416002058299850^0" ["Code"]=> string(2) "OK" }

//        $returnData = array(
//            'message'=>$content['Message'],
//            'requestId'=>$content['RequestId'],
//            'bizId'=>$content['BizId'],
//            'code'=>$content['Code'],
//        );

        return $content;
    }

    function AddSmsTemplate($type,$name,$content,$remark){
        $params = array(
            'TemplateType'=>$type,
            'TemplateName'=>$name,
            'TemplateContent'=>$content,
            'Remark'=>$remark,
        );

        $action = "AddSmsTemplate";
        $helper = new SignatureHelper();
        $content = $helper->request($action,$params);

//        {
//            "TemplateCode": "SMS_204112123",
//            "Message": "OK",
//            "RequestId": "0F4D1D82-3FAD-4C19-A00F-644E41F1064C",
//            "Code": "OK"
//        }


        return $content;
    }

    function ModifySmsTemplate($TemplateCode,$type,$name,$content,$remark){
        $params = array(
            'TemplateType'=>$type,
            'TemplateName'=>$name,
            'TemplateContent'=>$content,
            'Remark'=>$remark,
            'TemplateCode'=>$TemplateCode,
        );

        $action = "ModifySmsTemplate";
        $helper = new SignatureHelper();
        $content = $helper->request($action,$params);

//        {
//            "TemplateCode": "SMS_204112123",
//            "Message": "OK",
//            "RequestId": "457F45E9-DC37-4E04-86F6-4541A2127DEC",
//            "TemplateContent": "验证码为：${code}，5分钟后失效",
//            "TemplateName": "登陆验证",
//            "TemplateType": 0,
//            "Code": "OK",
//            "CreateDate": "2020-10-07 20:27:28",
//            "Reason": "无审批备注",
//            "TemplateStatus": 0
//        }
        return $content;
    }

    function DeleteSmsTemplate($TemplateCode){
        $params = array(
            'TemplateCode'=>$TemplateCode,
        );

        $action = "DeleteSmsTemplate";
        $helper = new SignatureHelper();
        $content = $helper->request($action,$params);
        return $content;
    }

    function QuerySmsTemplate($TemplateCode){
        $params = array(
            'TemplateCode'=>$TemplateCode,
        );

        $action = "QuerySmsTemplate";
        $helper = new SignatureHelper();
        $content = $helper->request($action,$params);

//        {
//            "TemplateCode": "SMS_204117035",
//            "Message": "OK",
//            "RequestId": "D0FA2FFF-CB1E-44AE-9010-13A29EE43698",
//            "TemplateContent": "尊敬的${username}您好，验证码为：${code}，${expire}分钟后失效，尽快找回密码哟~~",
//            "TemplateName": "找回密码",
//            "TemplateType": 0,
//            "Code": "OK",
//            "CreateDate": "2020-10-07 18:37:30",
//            "Reason": "*验证码模板只支持一个变量单位，例如：验证码：${code} 其余变量需转换为文字；;*验证码模板应如：验证码${code}，该验证码5分钟内有效，请勿泄漏于他人；",
//            "TemplateStatus": 2
//        }

        return $content;
    }
}


class SignatureHelper {

    function __construct()
    {
        $conf = ConfigCenter::get(APP_NAME,"main")['ali_sms'];
        $this->_conf = $conf;
    }

    /**
     * 生成签名并发起请求
     *
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @param $method boolean 使用GET或POST方法请求，VPC仅支持POST
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
//    必填：是否启用https
//$security = false;
    public function request( $action,$params, $security=false ) {
        $method='POST';

        $accessKeyId = $this->_conf['AccessKeyID'];
        $accessKeySecret = $this->_conf['AccessKeySecret'];
        $params["SignName"] = $this->_conf['SignName'];// "短信签名";
        $domain = $this->_conf['domain'];
        $params["RegionId"] = $this->_conf['RegionId'] ;
        $params["Action"] = $action;
        $params["Version"] = '2017-05-25';

        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "${method}&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&",true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http')."://{$domain}/";

        try {
            $content = $this->fetchContent($url, $method, "Signature={$signature}{$sortedQueryStringTmp}");
            $decodeContent =  json_decode($content,true);
            $back_code = 500;
            if($decodeContent['Code'] == "ok" || $decodeContent['Code'] == "OK"){
                $back_code = 200;
            }
            $decodeContent['back_code'] = $back_code;
            return $decodeContent;
        } catch( \Exception $e) {
            return false;
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function fetchContent($url, $method, $body) {
        $ch = curl_init();

        if($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        } else {
            $url .= '?'.$body;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if(substr($url, 0,5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if($rtn === false) {
            // 大多由设置等原因引起，一般无法保障后续逻辑正常执行，
            // 所以这里触发的是E_USER_ERROR，会终止脚本执行，无法被try...catch捕获，需要用户排查环境、网络等故障
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }
}