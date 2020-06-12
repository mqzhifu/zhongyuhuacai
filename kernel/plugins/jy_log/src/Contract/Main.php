<?php
namespace Jy\Log\Contract;

abstract class Main  implements MainInterface, PsrLoggerInterface {

    protected $_formatRule = array();
    protected $_delimiter = " | ";//一行内的消息块，分隔符

    protected $_msgFormat = "";//自定义日志格式
    //日志格式 ： 请求ID|日期时间|client-IP|进程ID|脚本文件名|类/方法|  XXX自定义信息  (rid|dt||cip|pid|tr )
//    protected $_formatRule = array("rid","dt",'cip','pid','tr');
    //日志格式中，日期时间的格式
    protected $_msgFormatDatetime = "Y-m-d H:i:s";

    protected $_filter = "";//可以过滤掉 内容 中的一些特定字符 ，如：换行符
    protected $_deepTrace = 2;//追踪回溯层级,0:全部

    protected $_showScreen = 0;//输出到屏幕

    protected $_replaceDelimiterLeft = "{";
    protected $_replaceDelimiterRight = "}";

    protected $_sysBaseInfoKey = array(
        'request_id',
        'project_name',
        'token',
        'server_addr',
        'duration',
        'url',
        'uid',
        'oid',
        'tid',
        'cip',
        'method',
        'upstream_addr',
        'upstream_domain',
        'error_code',
        'error_label',
    );
    protected $_sysBaseInfo = [];
    protected $_calledInfoKey = array('file_name', 'function_name', 'line',);
    protected $_calledInfo = [];

    protected $_selfRuleKey = array(
        'pid',
        "level",
        "request_time",
        'message',
        'context',
    );

    protected $_isJson = 1;

    function __construct(){
        $this->_formatRule = array_merge($this->_calledInfoKey,$this->_sysBaseInfoKey,$this->_selfRuleKey);
    }
    abstract function flush($info);


    public function setSysBaseInfo(array $info){
        if(!$info)
            return false;

        foreach ($info as $k=>$v) {
            foreach ($this->_sysBaseInfoKey as $k2=>$v2) {
                if($k == $v2){
                    $this->_sysBaseInfo[$k] = $v;
                    break;
                }
            }
        }
    }

    public function setCalledInfo(array $info){
        if(!$info)
            return false;

        foreach ($this->_calledInfoKey as $k2=>$v2) {
            foreach ($info as $k=>$v) {
                if($k == $v2){
                    $this->_calledInfo[$k] = $v;
                    break;
                }
            }
        }
    }

    function formatMsg($message ,array $context = array(),$level = ""){
        if(!$message){
            throw new \Exception("message is null.");
        }
        if(is_object($message)){
            throw new \Exception("message is object.");
        }
        if(is_array($message)){
            $message = json_encode($message,true);
        }

        $formatInfo = $this->placeholder($message,$context);
        $formatInfo = $this->replaceFormatMsg($formatInfo,$level);
        if($this->_isJson){
//            $formatInfo = explode("|",$formatInfo);
            $formatInfo = json_encode($formatInfo,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
        if($this->_showScreen == 1){
            echo $formatInfo . "\r\n";
        }

        return $formatInfo;
    }

    //调试信息
    function emergency($message ,array $context = array()){
        return $this->formatMsg($message,$context,"emergency");
    }
    //框架级日志
    function alert($message ,array $context = array()){
        return $this->formatMsg($message,$context,"alert");
    }
    //日常记录
    function critical($message ,array $context = array()){
        return $this->formatMsg($message,$context,"critical");
    }
    //警告
    function error($message ,array $context = array()){
        return $this->formatMsg($message,$context,"error");
    }
    //致命
    function warning($message ,array $context = array()){
        return $this->formatMsg($message,$context,"warning");
    }
    //以上均不满足，自定义
    function notice($message ,array $context = array()){
        return $this->formatMsg($message,$context,"notice");
    }

    function info($message ,array $context = array()){
        return $this->formatMsg($message,$context,"info");
    }

    function debug($message ,array $context = array()){
        return $this->formatMsg($message,$context,"debug");
    }

    function log($level,$message ,array $context = array()){
        $formatInfo = $this->placeholder($message,$context);
        $formatInfo = $this->replaceFormatMsg($formatInfo,$context);

        $formatInfo = $level . $this->_delimiter .$formatInfo;
        return $formatInfo;
    }

    //===============================================
    function setMsgFormat($info){
        $this->_msgFormat = $info;
    }

    function setDelimiter($str){
        $this->_delimiter = $str;
    }
    function setMsgFormatDatetime($str){
        $this->_msgFormatDatetime = $str;
    }

    function setFilter($str){
        $this->_filter = $str;
    }

    function setDeepTrace($str){
        $this->_deepTrace = $str;
    }

    function setShowScreen($show){
        $this->_showScreen =  $show;
    }

    function setIsJson($is){
        $this->_isJson =  $is;
    }

    //===============================以上是对外开放的接口
    function makeRequestId(){
        return uniqid(uniqid(time()));
    }

    function replaceFormatMsg($message,$level){
        if(!$this->_msgFormat){
            $format = $this->_formatRule;
        }else{
            $format = explode("|",$this->_msgFormat);
//            var_dump($format);exit;
        }

        $info = null;
        foreach ($format as $k=>$v) {
            $info[$v] = "";
        }

        foreach ($format as $k=>$rule){
            $rule = trim($rule);
            switch ($rule){
                case 'message':
                    $info['message'] = $message;
//                case 'rid':
//                    $info .= $this->makeRequestId() . $this->_delimiter;
//                    break;
                case 'request_time':
//                    $info .= date($this->_msgFormatDatetime,time()). $this->_delimiter;
                    $info['request_time'] = date($this->_msgFormatDatetime,time());
                    break;
                case 'pid':
//                    $info .= getmypid(). $this->_delimiter;
                    $info['pid'] =  getmypid();
                    break;
//
//                case "cip":
//                    $info .= $this->getClientIp(). $this->_delimiter;
//                    break;
                case "level":
                    $info['level'] = $level;
                    break;
//                case 'tr':
//                    $trace = debug_backtrace();
//                    $n = 0;
//                    foreach ($trace as $k =>$v){
//                        if($k <= 4){//这2层的追踪没意义
//                            continue;
//                        }
//
//                        if($this->_deepTrace && $n > $this->_deepTrace){
//                            break;
//                        }
//
//                        $info .= ($v['class'] . "^^" . $v['function'] . "^^" .$v['line'] . "#");
//                        $n++;
//                    }
//                    break;
            }
        }

        $info = $this->replaceCalledInfo($info);
        $info = $this->replaceSysBaseInfo($info);

        if(!$info){
            throw new \Exception("message format type value is error.");
        }

//        $info['content'] .= $message ;
        return $info;
    }

    public function replaceCalledInfo($info){
        if($this->_calledInfo){
            foreach ($info as $k=>$v) {
                foreach ($this->_calledInfo as $k2=>$v2) {
                    if($k2 == $k){
                        $info[$k2] = $v2;
                        break;
                    }
                }
            }
        }
        return $info;
    }

    public function replaceSysBaseInfo($info){
        if($this->_sysBaseInfo){
            foreach ($info as $k=>$v) {
                foreach ($this->_sysBaseInfo as $k2=>$v2) {
                    if($k2 == $k){
                        $info[$k2] = $v2;
                        break;
                    }
                }
            }
        }
        return $info;
    }

    // 获取客户端IP地址
    function getClientIp() {
        static $ip = NULL;
        if ($ip !== NULL) return $ip;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos =  array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip   =  trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
        return $ip;
    }

    function placeholder($message, array $context = array()){
        if(!$context){
            return $message;
        }

        foreach ($context as $key => $v) {
            if(is_array($message)){
                foreach ($message as $k2=>$msg) {
                    $message[$k2] = str_replace($this->_replaceDelimiterLeft . $key . $this->_replaceDelimiterRight,$v,$message[$k2]);
                }

            }else{
                $message = str_replace($this->_replaceDelimiterLeft . $key . $this->_replaceDelimiterRight,$v,$message);
            }
        }

        return $message;
    }

}