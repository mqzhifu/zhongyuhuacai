<?php
class CurlNewLib{
    public $mCookFile            = null;
    public $mIsOutputHeader      = false;//是否输出head
    public $mIsNoBody            = false; //是否不输出body
    public $mIsLocation          = true;//是否允许跳转
    public $mVerbose             = false; //详细报告
    public $mReturntransfer      = 1;    //
    public $mTimeout             = 60;    //设置curl允许执行的最长秒数
    public $cTimeout             = 3; //设置curl等待连接的超时,php5.2.3可用
    public $mReturnHtml          = null; //返回的html内容
    public $mInfo                = null; //curl_getinfo返回值
    public $mProxy               = null; //代理ip
    public $mMethod              = "post";
    public $mVerifyHost          = 0;
    private $mSslCert   = null; //ssl 私钥证书string
    private $mSslCertPasswd = null;//ssl 私钥证书密码
    private $mSslKey    = null; //ssl 私钥证书string
    private $mSslKeyPasswd = null;//ssl 私钥证书密码
    private $mSslCaFile = null;
    private $mSslCheck  = false;
    private $logPath    = "/tmp";
    public $msg      = array();
    private $errorCode = 0; // curl最近一次错误码

    // 一次性的时间设定
    public $tmpConnTime = 0;
    public $tmpTime = 0;

    public $requestType = 'POST';

    public $mHttpHeader    = null;
    public $mReferer       = null;

    /**
     * @brief 构造函数
     *
     */
    function __construct(){
    }

    /**
     * @brief 设置过期时间，默认3秒
     *
     * @param $second int 过期的秒数
     * @returns boolean 成功:true, 失败:false
     */
    public function setTimeout($second){
        if(is_int($second)){
            $this->mTimeout = $second;
            return $this;
        }
        return $this;
    }

    /**
     * @brief 设置等待连接过期时间，默认2秒
     *
     * @param $second int 过期的秒数
     * @returns boolean 成功:true, 失败:false
     */
    public function setConnectTimeout($second){
        if(is_int($second)){
            $this->cTimeout = $second;
            return $this;
        }
        return $this;

    }
    public function post($host_uri = '',array $data = []){
        $this->requestType = 'POST';

        return $this->send($host_uri, $data);
    }

    public function get($host_uri,array $data = []){
        $this->requestType =  'GET';

        return $this->send($host_uri, $data);
    }

    public function send($host_uri, $params){
//        $url  = $this->getProxy(). $uri;
//        $url = $host . $uri;
//        $defaultHeaders = array(
//            "X-Trace-Id"=>$traceId,
//            'Host' => $server_name,
//        );

//        $defaultHeaders = [
//            'X-Request-Id: ' . $this->getRequestUniqId(),
//            'Host: '. $server_name,
//        ];

//        $headers = $this->formatUniqueHeader(array_merge($defaultHeaders, $this->dealRequestHeader($this->getZipkinTraceHeader()), (array) $this->mHttpHeader));
//        $zipkinHeader = $this->getZipkinTraceHeader();
        //合并       = 默认头 + 链路追踪头 + 用户设置的头
//        $mergeHeader = array_merge($defaultHeaders,$zipkinHeader,$this->mHttpHeader);
//        $finalHeader =  $this->dealRequestHeader($mergeHeader);

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
            case "PUT" :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->tmpConnTime > 0 ? $this->tmpConnTime : $this->cTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->tmpTime > 0 ? $this->tmpTime : $this->mTimeout);
        if ($this->mHttpHeader) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->mHttpHeader);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $start_time = \microtime(true);

        $output = curl_exec($curl);
        $error = curl_errno($curl);
        $errorstr = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $this->tmpTime = $this->tmpConnTime = 0;
//        $this->log($url, $params, $httpCode, $output, $start_time, $headers, $server_name, $error, $errorstr);

        if ($error || $httpCode != 200) {
            throw new \Exception("curl_code:[" . $error . "] http_code:[". $httpCode . "] request url:[" . $url . "] server_name:" . $server_name, $error > 0  ? $error : $httpCode);
        }

        return $output;
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

    /**
     * 获取请求的唯一Id
     */
    public function setTmpConnTime($tmpConnTime)
    {
        $this->tmpConnTime = $tmpConnTime;

        return $this;
    }

    public function setTmpTime($tmpTime)
    {
        $this->tmpTime = $tmpTime;

        return $this;
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

//    public function getProxy()
//    {
//        if (function_exists('config') && !empty($url = config('proxy.url'))) {
//            return $url;
//        } else if (\defined('JY_PROXY_URL')) {
//            if (!empty(JY_PROXY_URL)) {
//                return JY_PROXY_URL;
//            }
//        }
//
//        return "http://127.0.0.1:4140";
//    }

    public function setMHttpHeader(array $mHttpHeader)
    {
        $this->mHttpHeader = $mHttpHeader;

        return $this;
    }
}