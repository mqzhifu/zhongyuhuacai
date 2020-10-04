<?php
class TraceLib{
    static $_inc = null;

    private $_traceId = "";
    private $_requestId = "";
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

    function createTraceId($bit){
        return get_rand_uniq_str($bit);
    }

    function createRequestId(){
        return get_rand_uniq_str();
    }

    function createSpanId(){
        return get_rand_uniq_str(32);
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
//        if(!$this->check()){
//            return false;
//        }

        $ZipkinTraceData = $this->getZipkinTraceHeader($localEndpoint,$remoteEndpoint);
//        $data = array("a"=>2,'c'=>333);
//        CurlNewLib::getInc()->setRequestHeader($ZipkinTraceData);
        $data = $ZipkinTraceData;
        $host_uri = "http://127.0.0.1:9411/api/v2/spans";
        CurlNewLib::getInc()->post($host_uri,$data);
        $rs = CurlNewLib::getInc()->getResponse();
        var_dump($rs);
        exit;
    }

    function getZipkinTraceHeader($localEndpoint,$remoteEndpoint){
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