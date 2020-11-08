<?php
class CurlNewLib{
//    public $mCookFile            = null;
//    public $mIsOutputHeader      = false;//是否输出head
//    public $mIsNoBody            = false; //是否不输出body
//    public $mIsLocation          = true;//是否允许跳转
//    public $mVerbose             = false; //详细报告
//    public $mReturntransfer      = 1;    //
//    public $mReturnHtml          = null; //返回的html内容
//    public $mInfo                = null; //curl_getinfo返回值

//    public $mMethod              = "post";
//    public $mVerifyHost          = 0;

//    private $mSslCert   = null; //ssl 私钥证书string
//    private $mSslCertPasswd = null;//ssl 私钥证书密码
//    private $mSslKey    = null; //ssl 私钥证书string
//    private $mSslKeyPasswd = null;//ssl 私钥证书密码
//    private $mSslCaFile = null;
//    private $mSslCheck  = false;
//    private $logPath    = "/tmp";

//    public $mReferer       = null;

    private $_referer = "";
    private $_useragent = "";
    private $_execTimeout             = 60;    //设置curl允许执行的最长秒数
    private $_connectTimeout             = 3; //连接超时

    private $responseBody      = null;
    private $responseHeader    = null;
    private $response          = "";
    private $responseHttpCode = -1;
    private $errorCode = 0; // curl最近一次错误码
    private $errorMsg = "";// curl最近一次错误信息

    private $requestType = 'POST';
//    private $mHttpHeader    = null;
    private $_requestHeader = null;
    private $_transferInfo = null;//最后一次传输的统计信息
    static private $_inc = null;

    function __construct(){
    }

    static function getInc(){
        if(self::$_inc){
            return self::$_inc;
        }

        self::$_inc = new self();
        return self::$_inc;
    }

    public function getResponse(){
        return $this->response;
    }

    //执行超时时间
    public function setExecTimeout($second){
        $this->_execTimeout = $second;
    }
    //连接超时
    public function setConnectTimeout($second){
        $this->_connectTimeout = $second;
    }
    //data : 可以是数组  也可以是  json字符串
    public function post($host_uri = '',$data = ""){
        $this->requestType = 'POST';
        return $this->send($host_uri, $data);
    }
    //data : 可以是数组  也可以是  json字符串
    public function get($host_uri,  $data = ""){
        $this->requestType =  'GET';
        return $this->send($host_uri, $data);
    }

    public function setReferer($referer){
        $this->_referer = $referer;
    }

    public function setUserAgent($ua){
        $this->_useragent = $ua;
    }

    public function setRequestHeader(array $headers){
        $this->_requestHeader = $headers;
    }

    public function send($host_uri, $params){
        $url = $host_uri;
        $curl = curl_init();
        switch ($this->requestType) {
            case "GET" :
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                $url .= is_array($params) ? http_build_query($params) : $params;
                break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
                //curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
//            case "PUT" :
//                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
//                break;
//            case "PATCH":
//                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
//                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
//                break;
//            case "DELETE":
//                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
//                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
//                break;
            default :
                exit("request type is err.");
                break;
        }
        //设置请求源
        if($this->_referer){
            curl_setopt ($url, CURLOPT_REFERER, $this->_referer);
        }
        //设置UA
        if($this->_useragent){
            curl_setopt($curl, CURLOPT_USERAGENT,$this->_useragent);
        }
        //设置头信息
        if ($this->_requestHeader) {
            $requestNormalHeader = $this->httpHeaderArrCoverNormal($this->_requestHeader);
            curl_setopt($curl, CURLOPT_HTTPHEADER,$requestNormalHeader);
        }
        //连接超时
        if($this->_connectTimeout){
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
        }
        //执行超时
        if($this->_execTimeout){
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_execTimeout);
        }
        //设置URL地址
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //不输出，返回到变量中
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //响应头，默认打开，就是麻烦点，
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, true);
        //开始执行
        $response = curl_exec($curl);
        $this->response = $response;
        //传输统计信息
        $this->_transferInfo = curl_getinfo($curl);
        //处理响应头
        $responseHeader = null;
        $bodyStart = 0;
        if(arrKeyIssetAndExist($this->_transferInfo,'header_size')){
            $responseHeader = substr( $response, 0 ,$this->_transferInfo['header_size']);
            $responseHeader = $this->httpHeaderCoverArr($responseHeader);
            $bodyStart = $this->_transferInfo['header_size'];
        }
        $this->responseHeader = $responseHeader;
        //处理响应体
        $responseBody = substr($response,$bodyStart);
        $this->responseBody = $responseBody;
        //错误码
        $this->errorCode = curl_errno($curl);
        //错误信息
        $this->errorMsg = curl_error($curl);
        //HTTP响应状态码
        $this->responseHttpCode = $this->_transferInfo['http_code'];

        curl_close($curl);

        return $this->responseBody;
    }
    //将头为arr 的转换为  : 分隔
    function httpHeaderArrCoverNormal($header){
        $rs = null;
        foreach ($header as $k=>$v){
            $rs[] = $k . ":" .$v;
        }

        return $rs;
    }

    function httpHeaderCoverArr($header){
        if(!$header){
            return $header;
        }
        $header = trim($header);
        $header = explode("\n",$header);
        $arr = null;
        foreach ($header as $k=>$v){
            $v = trim($v);
            if(!$v){
                continue;
            }

            if (strpos($v, ':') !== false) {
                $tmpArr = explode(":",$v);
                $key = trim($tmpArr[0]);
                $value = trim($tmpArr[1]);
                $arr[$key] = $value;
            }


        }
        return $arr;
    }

    public function formatUniqueHeader(array $headers)
    {
        $arr = [];
        foreach ($headers as $value) {

            if (stripos($value, ":") === false) {
                continue;
            }

            $name = substr($value, 0, stripos($value, ":"));
            $value = substr($value, stripos($value, ":")+1);

            $arr[$name] = trim($value);
        }

        $headers = [];
        foreach ($arr as $key => $value) {
            $headers[] = $key. ": " . $value;
        }

        return $headers;
    }

    public function dealRequestHeader(array $headers)
    {
        $heads = [];
        foreach($headers as $name => $value){
            if (\is_int($name)) continue;

            $heads[] = $name.": " .$value;
        }

        return $heads;
    }

    //所有自定义的头信息，会转换成以HTTP_开头
    public function getHttpDiyHeaders()
    {
        $headers = [];

        foreach ($_SERVER ?? [] as $name => $value) {
            if (strncmp($name, 'HTTP_', 5) === 0) {
                //删除掉前5位的 HTTP_
                $headerParaKey = substr($name, 5);
//                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ',  $headerParaKey ))));
                $headers[$headerParaKey] = $value;
            }
        }

        return $headers;
    }

    function getInfoDataFormat(){
//        {
//            "url":"http://baidu.com/",
//            "content_type":"text/html",
//            "http_code":200,
//            "header_size":305,
//            "request_size":117,
//            "filetime":-1,
//            "ssl_verify_result":0,
//            "redirect_count":0,
//            "total_time":0.036075,
//            "namelookup_time":0.000698,
//            "connect_time":0.017864,
//            "pretransfer_time":0.017949,
//            "size_upload":0,
//            "size_download":81,
//            "speed_download":2250,
//            "speed_upload":0,
//            "download_content_length":81,
//            "upload_content_length":0,
//            "starttransfer_time":0.036051,
//            "redirect_time":0,
//            "redirect_url":"",
//            "primary_ip":"220.181.38.148",
//            "certinfo":[
//
//                ],
//            "primary_port":80,
//            "local_ip":"192.168.31.234",
//            "local_port":49299,
//            "http_version":2,
//            "protocol":1,
//            "ssl_verifyresult":0,
//            "scheme":"HTTP",
//            "appconnect_time_us":0,
//            "connect_time_us":17864,
//            "namelookup_time_us":698,
//            "pretransfer_time_us":17949,
//            "redirect_time_us":0,
//            "starttransfer_time_us":36051,
//            "total_time_us":36075
//        }
    }
}