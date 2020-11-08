<?php
//这行语句，是无法避免，在项目最最开始的时候，引用此文件
include_once KERNEL_DIR .DS ."functions" . DS ."str_arr.php";
class TraceLib{
    static $_inc = null;
    private $_traceId = "";
    private $_requestId = "";

    private $_host = "";
    private $_uri = "";
    private $_port = "";
    static function getInc(){
        if(self::$_inc){
            return self::$_inc;
        }
        self::$_inc = new self();
        return self::$_inc;
    }

    function check(){
        if(get_os() == 'WIN'){
            return false;
        }

        if(! defined("OPEN_TRACE") || !OPEN_TRACE){
            return false;
        }
    }

    function setHost($host){
        $this->_host = $host;
    }

    function setUri($uri){
        $this->_uri = $uri;
    }

    function setPort($port){
        $this->_port = $port;
    }

    function getUrl(){
//        "http://127.0.0.1:9411/api/v2/spans";
        return  "http://".$this->_host .":" .$this->_port . "" . $this->_uri;
    }

    function createTraceId($bit){
        return get_rand_uniq_str($bit);
    }

    function createRequestId(){
        return get_rand_uniq_str();
    }

    function createSpanId(){
        return get_rand_uniq_str(14);
    }

    function getRequestId(){
        if($this->_requestId){
            return $this->_requestId;
        }

        $this->_requestId = $this->createRequestId();
        return $this->_requestId;
    }

    public function getTraceId($bit = 32){
        if($this->_traceId){
            return $this->_traceId;
        }

        if (isset($_SERVER) && isset($_SERVER['HTTP_X_TRACE_ID'])) {
            $traceId = $_SERVER['HTTP_X_TRACE_ID'];
        } else{
            $traceId = $this->createTraceId($bit);
        }
        $this->_traceId = $traceId;
        return $this->_traceId;
    }

    function tracing($localEndpoint,$remoteEndpoint){
        if(!$this->check()){
            return false;
        }

        $zipKinTraceData = $this->getZipkinTraceData($localEndpoint,$remoteEndpoint);
        $host_uri = $this->getUrl();
        CurlNewLib::getInc()->post($host_uri,json_encode($zipKinTraceData));
        CurlNewLib::getInc()->getResponse();
//        var_dump($rs);
//        exit;
    }

    function getZipkinTraceData($localEndpoint,$remoteEndpoint){
//        $curl = new CurlNewLib();
//        $headers = $curl->getHttpDiyHeaders();
//
//        $traceHeaders = [];
//        foreach($headers as $name => $value) {
//            if (\stripos($name, 'ctx') !== false) {
//                $traceHeaders[$name] = $value;
//            }
//        }
//
//        return $traceHeaders;

        $serviceName = APP_NAME;

        $spanId = $this->createSpanId();
        $traceId = $this->getTraceId();
        $now = time();
        $s_time = $now . "000000";
        $data = array(
            array(
                "id"=>$spanId,
                "traceId"=>$traceId,
//            "parentId"=>$traceId,
                "kind"=>"CLIENT",
                "name"=>"get /real_test.php",
                "timestamp"=>$s_time,
                "duration"=>4000,
                "localEndpoint"=>array(
                    "serviceName"=>$localEndpoint,
                    "ipv4"=>"127.0.0.1"
                ),
                "remoteEndpoint"=>array(
                    "serviceName"=>$remoteEndpoint,
                    "ipv4"=>"127.0.0.1",
                    "port"=>9999
                ),
                "annotations"=>array(
                    array(
                        "timestamp"=>$s_time+10,
                        "value"=>"ws",
                    ),
                    array(
                        "timestamp"=>$s_time+20,
                        "value"=>"wr"
                    )
                ),
                "tags"=>array(
//                    "clnt/finagle.label"=>"#/io.l5d.fs/real_test_label",
//                    "clnt/finagle.version"=>"20.4.1",
//                    "http.req.host"=>"web",
//                    "http.req.method"=>"GET",
//                    "http.req.version"=>"HTTP/1.1",
//                    "http.rsp.content-type"=>"text/html; charset=UTF-8",
//                    "http.rsp.transfer-encoding"=>"chunked",
//                    "http.rsp.version"=>"HTTP/1.1",
//                    "http.uri"=>"/real_test_uri.php"
                    "service_name"=>$serviceName,
                )
            ),
        );

        return $data;

//    [{
//        "id":"aabbcc1",
//        "traceId":"0dd8d1f45dcc8a11",
//        "parentId":"0dd8d1f45dcc8a11",
//        "kind":"CLIENT",
//        "name":"get /first_.php",
//        "timestamp":1597724166000000,
//        "duration":4000,
//        "localEndpoint":{
//            "serviceName":"#/io.l5d.fs/first",
//            "ipv4":"127.0.0.1"
//        },
//        "remoteEndpoint":{
//            "serviceName":"#/io.l5d.fs/web",
//            "ipv4":"127.0.0.1",
//            "port":9999
//        },
//        "annotations":[
//            {
//                "timestamp":1597724166000001,
//                "value":"ws"
//            },
//            {
//                "timestamp":1597724166000002,
//                "value":"wr"
//            }
//        ],
//        "tags":{
//            "clnt/finagle.label":"#/io.l5d.fs/first",
//            "clnt/finagle.version":"20.4.1",
//            "http.req.host":"web",
//            "http.req.method":"GET",
//            "http.req.version":"HTTP/1.1",
//            "http.rsp.content-type":"text/html; charset=UTF-8",
//            "http.rsp.transfer-encoding":"chunked",
//            "http.rsp.version":"HTTP/1.1",
//            "http.uri":"/first_.php"
//        }
//}]
    }

}