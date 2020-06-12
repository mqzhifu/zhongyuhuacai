<?php
namespace php_base\MsgQueue\MsgQueue;

use php_base\MsgQueue\Exception\RejectMsgException;
use php_base\MsgQueue\Exception\RetryException;
use php_base\MsgQueue\Exception\CodeException;

use php_base\MsgQueue\Contract\AmqpBaseInterface;
use php_base\MsgQueue\Facades\Log;
use php_base\MsgQueue\Model\MsgRecordModel;
use php_base\MsgQueue\Model\ExchangeBindQueueModel;
use php_base\MsgQueue\Model\ErrorRecordModel;

//use php_base\MsgQueue\MsgQueue\RabbitmqPhpExt;
//use php_base\MsgQueue\MsgQueue\RabbitmqComposerLib;


//use Jy\Log\Facades\Log;

class RabbitmqBase implements AmqpBaseInterface {
//    private $_exchange = "many.header.delay";
    private $_exchange = "test.header.delay";

    //调试模式,0:关闭，1：只输出到屏幕 2：只记日志 3：输出到屏幕并记日志
    private $_debug = 2;//注：开启 日志模式，记得引入log包
    private $_debugDesc = array(0=>"closed",1=>"print screen",2=>"log file",3=>"print screen && log file");
    public  $_extProvider = null;//具体实现的类
    private $_extType = 2;//1:composer 2php ext
    private $_extTypeDesc = array(1=>'\php_base\MsgQueue\MsgQueue\RabbitmqComposerLib',2=>"\php_base\MsgQueue\MsgQueue\RabbitmqPhpExt");
    private $_conn = null;//SOCK FD
    private $_conf = null;//连接，配置信息
    private $_confKey = array('host','port','user','pwd','vhost');//连接，配置信息，KEY值

    //一条消息最大值，KB为单位
    private $_messageMaxLength = 1024;
    //用户取消了绑定，结束守护
    private $_consumerStopLoop = 0;
    private $_retryTime  = array(10,30,60);//重试 次数及时间
    public  $_timeOutCallback = null;//超时，回调函数
    private $_userCallbackFuncExecTimeout = 60;//用户回调函数，超时时间
    //跟consumerTag值相同，用于发消息给rabbitmq 取消订阅
    private $_loopDeadConsumerTag = "";
    public $_pidPath = "/tmp/php_consumer_rabbitmq.pid";
    //忽略队列是否接收消息
    private $_ignoreSendNoQueueReceive = true;
    private $_ignoreSendNoQueueReceivePool = [];

    private $_userCallBackType = array('ack','nack','rabbitmqReturnErr','serverBackConsumer',"serverBackConsumerRetry");
    private $_userShutdownCallback = "";//consumer进程结束守护时，回调用户自定义<收尾>函数
    private $_userCallBack = null;
    //无奈PHP序列化不支持闭包，借此方法，暂存子类的FUNC 吧
    public $_userRegFunc = array();

    public $_header = null;
    public $_consumerBindQueueName = "";
    private $_codeErrMessage = null;

    private $_doingProcessMsgId = "";
    function __construct(){
        $this->_codeErrMessage = CodeException::getCode();
    }

    function init(){
        $this->testENV();
        if(!$this->_conf){
            $this->throwException(515);
        }

        $className = $this->_extTypeDesc[$this->_extType];
        $this->_extProvider = new $className();

        $this->getConn();//创建连接
        $this->out("readTimeout:".$this->_extProvider->getReadTimeOut());
        $this->out("writeTimeout:".$this->_extProvider->getWriteTimeOut());
        $this->out("heartbeatInterval:".$this->_extProvider->getHeartbeatInterval());

        $this->checkExchange();
        $this->regDefaultAllCallback();
    }

    function checkExchange(){
        if(!$this->_exchange){
            $this->throwException(511);
        }

        $this->out($this->_exchange . " : test checkExchange exist ......");
        try{
            $arguments = array( 'x-delayed-type'=>'headers');
            $this->_extProvider->exchangeDeclare($this->_exchange,'x-delayed-message',$arguments);
            $this->out("queue exist : true");
            return 1;
        }catch (\Exception $e){
            $this->out("queue exist : false");
            $this->throwException(540);
        }
    }

    function setConf(array $conf){
        if(!$conf){
            $this->throwException(515);
        }
        $this->checkConfigFormat($conf);
        $this->_conf = $conf;
    }

    function setTopicName(string $topName){
        //暂时不支持这个功能
        $this->_exchange = $topName;
    }

    function setExtType(int $type){
        if(!in_array($type,array_flip($this->_extTypeDesc))){
            $this->throwException(534);
        }
        $this->_extType = $type;
    }

    function testENV(){
        $os = $this->getOs();
        $this->out("os:".$os);
        if($os === "WIN"){
            $this->out("notice : linux OS is very good.");
        }

        if(!$this->supportsPcntlSignals()){
            $this->out("notice : php ext <pcntl> ,not supports. OS signal will be lose...");
        }

        if(!extension_loaded('posix')){
            $this->out("notice : php ext <posix> ,not supports. kill process is unknow");
        }

        if(!$this->is_cli()){
            $this->out("warning : exec env not CLI ,please set_time_limit(0) | max_execution_time(0)  if  u r  consumer");
        }

        if(ini_get("max_execution_time")){
            $this->out("warning : please set max_execution_time = 0 ");
        }

        if($this->_extType == 1){
            if(!class_exists("\PhpAmqpLib\Channel\AMQPChannel")){
                $this->out("warning :  PhpAmqpLib composer lib ,not supports ");
            }

            if(!extension_loaded('sockets')){
                $this->out("warning : php ext <sockets>,not supports.");
            }
        }else{
            if(!extension_loaded('amqp')){
                $this->out("warning : php ext <amqp> ,not supports ");
            }
        }

        //php-amqp 2.11 以后需要
//        phpseclib/phpseclib
//        if(!extension_loaded('mbstring')){
//            $this->out("warning :php ext: sockets ,not supports.");
//        }

//        if(class_exists("Jy\Log\Facades\Log")){
//            $this->out("notice : depend on :Log composer bag");
//        }
    }

    function is_cli(){
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }

    function getConn(){
        if($this->_conn){
            //虽然 连接有数据，但可能 连接已经断了
            if($this->_conn->isConnected()){
                return $this->_conn;
            }

            $this->resetConn();
        }
        $conn = $this->_extProvider->connect($this->_conf);

        $conf = $this->_conf;
        $conf['pwd'] = "********";
        $this->out("connect rabbit config;".json_encode($conf));

        if(!$this->_extProvider->isConnected()){
            $this->throwException(509);
        }
        $this->_conn = $conn;
        return $this->_conn;
    }
    //检查配置文件格式是否正确
    function checkConfigFormat(array $config ){
        foreach ( $this->_confKey as $k=>$v) {
            $f = 0;
            foreach ($config as $k2=>$v2) {
                if($v == $k2 && $v2){
                    $f = 1;
                    break;
                }
            }
            if(!$f){
                $this->throwException(516);
            }
        }
    }

    function resetConn(){
        $this->out("reset connect");
        $this->_conn = null;
        $this->init();
    }

    function throwException(int $code,array $replace = []){
        if(!$code){
            $data = array(
                'code'=>400,
                'type'=>ErrorRecordModel::TYPE_CALLBACK,
            );
            ErrorRecordModel::create($data);

            throw new \Exception($this->_codeErrMessage[400]);
        }

        if(!isset($this->_codeErrMessage[$code]) || !$this->_codeErrMessage[$code]){
            $data = array(
                'code'=>401,
                'type'=>ErrorRecordModel::TYPE_CALLBACK,
            );
            ErrorRecordModel::create($data);

            throw new \Exception($this->_codeErrMessage[401]);
        }
        if(!$replace){
            $data = array(
                'code'=>$code,
                'type'=>ErrorRecordModel::TYPE_CALLBACK,
                'content'=>$this->_codeErrMessage[$code],
            );
            ErrorRecordModel::create($data);
            throw new \Exception($this->_codeErrMessage[$code]);
        }else{
            $message = $this->_codeErrMessage[$code];
            foreach ($replace as $key => $v) {
                $message = str_replace("{" . $key ."}",$v,$message);
            }

            $data = array(
                'code'=>$code,
                'content'=>$message,
                'type'=>ErrorRecordModel::TYPE_CALLBACK,
            );
            ErrorRecordModel::create($data);

            throw new \Exception($message);
        }
    }

    function setExchangeName(string $exchangeName){
        $this->_exchange = $exchangeName;
    }

    function setDebug(int $flag){
        if(!in_array($flag,array_flip($this->_debugDesc))){
            $this->throwException(539);
        }
        $this->_debug =  $flag;
    }
    //consumer同时接收SERVER返回最大消息数
    function setReceivedServerMsgMaxNumByOneTime(int $num){
        if(!$num || $num <= 0){
            $this->throwException(532);
        }
        $this->_extProvider->setBasicQos($num);
    }

    function setRabbitmqAckCallback($ackFunc,$nackFunc){
        $this->_extProvider->setRabbitmqAckCallback($ackFunc,$nackFunc);
    }

    function setRabbitmqErrCallback($clientReturnListener){
        $this->_extProvider->setRabbitmqErrCallback($clientReturnListener);
    }
    //进程结束后回调函数
    function regUserCallbackShutdown($func){
        $this->_userShutdownCallback = $func;
    }

    function regUserCallback($key,callable $func){
        $this->out(" regUserCallback key:".$key);
        $this->_userCallBack[$key] = $func;
    }

    function regUserFunc($key,$type,callable $func){
        $this->out("regUserFunc ". $key . " type:".$type);
        $this->_userRegFunc[$key][$type] = $func;
    }

    function getRegUserFunc($key = null,$type = null){
        if($key && $type){
            if(isset($this->_userRegFunc[$key][$type]) && $this->_userRegFunc[$key][$type]){
                return $this->_userRegFunc[$key][$type];
            }else{
                $this->out("notice : getRegUserFunc($key , $type) is null");
                return false;
            }

        }
        return $this->_userRegFunc;
    }

    function userCallbackExec(string $type,$data){
        $this->out("userCallbackExec type:".$type);
        if($this->_userCallBack){
            //证明是consumer模式，并开启了死循环
            if($this->_loopDeadConsumerTag && $this->_consumerBindQueueName){
                //重试的消息,有点复杂
                    if(!isset($this->_userCallBack[$this->_consumerBindQueueName]) || !$this->_userCallBack[$this->_consumerBindQueueName]){
                        $this->throwException(533);
                    }
                    call_user_func($this->_userCallBack[$this->_consumerBindQueueName],'serverBackConsumerRetry',$data);

            }else{
                //生产者模式 - confirm
                $f = 0;
                foreach ($data['headers'] as $k=>$v) {
                    if(isset($this->_userCallBack[$k]) && $this->_userCallBack[$k]){
                        call_user_func($this->_userCallBack[$k],$type,$data);
                        $f = 1;
                        break;
                    }
                }
                if( !$f ){
                    $this->out("userCallbackExec type($type): no match headers" . json_encode($data['headers']));
                }
            }
        }else{
            $this->out(" _userCallback: not set");
        }
    }
    //退出守护进程
    function quitConsumerDemon(){
        $this->_consumerStopLoop = 1;
    }
    //重试机制
    function setRetryTime(array $time){
        $this->out(" setRetryTime ". json_encode($time));
        foreach ($time as $k=>$v) {
            $int = (int) $v;
            if(!$int || $int < 0){
                $this->throwException(525);
            }
            if($v > 24 * 60 * 60){
                $this->throwException(524);
            }
        }

        $this->_retryTime = $time;
    }
    function getRetryTime(){
        return $this->_retryTime;
    }
    //consumer超时
    function setUserConsumerCallbackExecTimeout(int $second){
        if(!$second){
            $this->throwException(526);
        }

        if($second < 10){
            $this->throwException(527);
        }

        if($second > 600){
            $this->throwException(528);
        }

        $this->_userCallbackFuncExecTimeout = $second ;
    }

    function setMessageMaxLength(int $num){
        $this->_messageMaxLength = $num;
    }

    function confirmSelectMode(){
        $this->out("start confirm_select mode:");
        $this->_extProvider->confirmSelectMode();
    }
    //开启一个事务
    function txSelect(){
        $this->out("txSelect");
        $this->_extProvider->txSelect();
    }

    function txCommit(){
        $this->out("txCommit");
        $this->_extProvider->txCommit();
    }

    function txRollback(){
        $this->out("rollback");
        $this->_extProvider->txRollback();
    }

    //创建一个队列
    function setQueue( $queueName, $arguments = null,$durable = true,$autoDelete = false){
        if(!$queueName){
            $this->throwException(510);
        }

        $this->out("setQueue $queueName , arguments:".json_encode($arguments) . " durable : $durable , autoDelete : $autoDelete");
        try{
            $this->_extProvider->queueDeclare($queueName,false,$durable,false,$autoDelete,true,$arguments);
        }catch(\Exception $e){
            $data = array(
                'code'=>$e->getCode(),
                'content'=>$e->getMessage(),
                'type'=>ErrorRecordModel::TYPE_EXT,
            );
            ErrorRecordModel::create($data);
            throw $e;
        }

//        $this->_extProvider->baseWait();
    }
    //绑定一个队列
    function bindQueue( $queueName, $exchangeName = null,$routingKey = '',$header = null){
        if(!$exchangeName){
            if($this->_exchange){
                $exchangeName = $this->_exchange;
            }else{
                $this->throwException(511);
            }
        }
        $this->out("bindQueue  queueName:$queueName exchangeName:$exchangeName ");
        if(!$header){
            $header = $this->_header;
        }
        if($header){
            foreach ($header as $k=>$v) {
                $this->out(" header ".$v);
            }
        }
        try{
            $this->_extProvider->queueBind($queueName,$exchangeName,$routingKey,true,$header);
        }catch (Exception $e){
            $this->out( "bindQueue failed.");
            $this->out($e->getMessage());
            exit;
        }

//        foreach ($header as $k=>$v) {
//            if($k == "x-match"){
//                continue;
//            }
//
//            $hasBindInfo  = ExchangeBindQueueModel::hasBinding($exchangeName,$queueName,$v);
//            if(!$hasBindInfo){
//                $data = array(
//                    'exchange_name'=>$exchangeName,
//                    'queue_name'=>$queueName,
//                    'bind_key'=>$v,
//                );
//                ExchangeBindQueueModel::create($data);
//            }
//        }

        $this->out(" binding end.");
    }
    //判断队列是否已经存在
    function queueExist($queueName,$arguments= null,$durable= null,$autoDel= null){
        $this->out("test queue exist ......");
        try{
            $this->_extProvider->queueDeclare($queueName,true,$durable,false,$autoDel,false,$arguments);
            $this->out("queue exist : true");
            return 1;
        }catch (\Exception $e){
            $this->resetConn();
            $this->out("queue exist : false");
            return 0;
        }
    }

    function deleteQueue($queueName){
        $this->_extProvider->deleteQueue($queueName);
//        ExchangeBindQueueModel::clearByQueue($queueName);
    }

    //创建一个exchange
    function setExchange($exchangeName,$type,$arguments = null){
        if(!$exchangeName){
            $this->throwException(511);
        }
        $this->out("setExchange $exchangeName , type:$type , arguments:".json_encode($arguments));
//        try{
//            $this->getChannel()->exchange_declare($exchangeName,$type,true,false,true,false,false);
//            $this->out(" ok exist");
//        }catch (Exception $e){
//            $this->out("not exist :".$e->getMessage());
//            $this->resetConn();

        $this->_extProvider->exchangeDeclare($exchangeName,$type,$arguments);
//            $this->out("create exchange .");
//        }
    }

    function unbindExchangeQueue($exchangeName,$queueName,$routingKey = "",$arguments = null){
        $this->_extProvider->queue_unbind($queueName,$exchangeName,$routingKey,$arguments);
    }

    function deleteExchange($exchangeName){
        $this->_extProvider->deleteExchange($exchangeName);
//        ExchangeBindQueueModel::clearByExchange($exchangeName);
    }
    //exchange 相关end========================================================


    //发布一条消息
    function publish($msgBody ,$exchangeName,$routingKey = '',$header = null,$arguments = null,$is_fix_redelivery = 0){
        if(!$exchangeName){
            if(!$this->_exchange){
                $this->throwException(511);
            }
            $exchangeName = $this->_exchange;
        }

        if($header){
            if($this->_header)
                $header = array_merge($this->_header,$header);
        }else{
            $header =  $this->_header;
        }

        $info = "publish  ex:$exchangeName , route key:".$routingKey ;

        $dbRecordData = array('content'=>str_replace("\0","#cp#",$msgBody),"topic"=>$exchangeName,'routing_key'=>$routingKey,'fix_status'=>0,'is_fix_redelivery'=>$is_fix_redelivery);
        if($header){
            $info .= " . header:".json_encode($header);
        }
        if($arguments){
            $info .= " . arguments:".json_encode($arguments);
            $dbRecordData['attribute'] = json_encode($arguments);
            $dbRecordData['message_id'] = $arguments['message_id'];
        }
        $this->out($info);
        $finalArguments = [];
        if($header){
            $preProcessHeader = $this->preProcessHeader($header);
//            $finalArguments['application_headers'] = $preProcessHeader;
            $finalArguments['headers'] = $preProcessHeader;

            if(isset($preProcessHeader['x-delay']) && $preProcessHeader['x-delay']){
                $dbRecordData['type'] = 2;
            }else{
                $dbRecordData['type'] = 1;
            }
            $dbRecordData['events'] = json_encode($preProcessHeader);
        }

        if($arguments){
            $finalArguments = array_merge($finalArguments,$arguments);
        }

        if( strlen($msgBody) / 1024 >= $this->_messageMaxLength){
            $this->throwException(530,array($this->_messageMaxLength . " kb "));
        }


//        Log::debug(array("msg"=>$msgBody,'exchangeName'=>$exchangeName,'finalArguments'=>$finalArguments));
//        $dbRecordData['status'] = MsgRecordModel::STATUS_SENT;//已发送
//        foreach ($header as $k=>$bindKey) {
//            if($k == "x-delay"){
//                continue;
//            }
//
//            if($k == "x-match"){
//                continue;
//            }
//
//            $queues = ExchangeBindQueueModel::searchByBindKey($exchangeName,$bindKey);
//            if(!$queues){
//                continue;
//            }
//
//            foreach ($queues as $k2=>$queue) {
////                $mysqlHeader = array("x-match"=>"any");
////                $mysqlHeader[$bindKey] = $bindKey;
//                $dbRecordData['event_item'] = $bindKey;
//                $dbRecordData['queue_name'] = $queue['queue_name'];
//                $this->createMysqlMsgRecord($dbRecordData);
//            }
//        }
//        try{
            $rs = $this->_extProvider->basicPublish($exchangeName,$routingKey,$msgBody,$finalArguments);
//        }catch(\Exception $e){
//            var_dump(33333);
//            $data = array(
//                'code'=>$e->getCode(),
//                'content'=>$e->getMessage(),
//                'type'=>ErrorRecordModel::TYPE_CALLBACK,
//            );
//            ErrorRecordModel::create($data);
//
//            $this->throwException(541,$e->getCode() . " " . $e->getMessage());
//        }

        return $rs;
    }

    function createMysqlMsgRecord($data ){
        $id = MsgRecordModel::create($data);
        $this->out(" create mysql msg record,new id :$id");
    }

    //预处理，头信息
    function preProcessHeader($header = null){
        //校验 延迟队列 的时间值
        if(isset($header['x-delay']) && $header['x-delay']){
            $delayTime = (int)$header['x-delay'];
            if(!$delayTime ){
                $this->throwException(517);
            }

            if($delayTime < 1000){
                $this->throwException(518);
            }

            $day = 7 * 24 * 60 *60 * 1000;
            if( $delayTime > $day){
                $this->throwException(519);
            }
        }

        //主要是给，延迟队列
        $rabbitHeader = $this->_header;
        if($header && $rabbitHeader ){
            $rabbitHeader = array_merge($rabbitHeader,$header);
        }
        return $rabbitHeader;
    }

    function baseWait(){
        $this->_extProvider->baseWait();
    }

    function getRetryPolicy($msgRetry = null){
        $this->out("get retry  policy");
//        $beanRetry = $obj->getRetryTime();
        if($msgRetry){
            $this->out(" level 1 : msg has  ".json_encode($msgRetry));
            return $msgRetry;
        }
        $beanRetry = $this->getRetryTime();
        if(!$beanRetry){
            $this->out("no set retry times");
            return false;
        }
        $this->out(" level 2 : used system default RabbitmqBase Retry .".json_encode($beanRetry));
        return $beanRetry;

    }


    //重试机制
    function retry($exchange,$backData,$backQueue,$msgRetry = null){
        $parseBackData = $this->_extProvider->parseBackDataToUniteArr($backData);
        $beanRetry = $this->getRetryPolicy($msgRetry);
        if(!$beanRetry){
            return false;
        }
        $retryMax = count($beanRetry);
        $this->out(" retryMax: ".$retryMax);
        //重复-已发送次数
        $retryCount = 0;
        if (isset($parseBackData['headers']['x-retry-count']) && $parseBackData['headers']['x-retry-count']) {
            $retryCount = $parseBackData['headers']['x-retry-count'];
        }
        $this->out("now retry count:$retryCount");
//        $this->out("delivery_tag:".$msg->delivery_info['delivery_tag']);
        //判断 是否 超过 最大投递次数
        if ($retryCount >=  $retryMax ) {
            $this->exceptionRecordMysql($parseBackData,MsgRecordModel::STATUS_RETRY_REJECT);


            $this->out("$retryCount > getRetryMax ( ".$retryMax." )");
            $this->_extProvider->reject($backData,$backQueue);

            $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_RETRY_REJECT,$parseBackData['message_id']);

            return false;
        }

//原消息不要了，重新 再发送一条 延迟消息(body:一样)
//        try{
//            $this->txSelect();

        $msgId = $parseBackData['message_id'];

        $body = $parseBackData['body'];
        unset($parseBackData['header']);
        unset($parseBackData['body']);

        $header = array("match"=>'any');
        //这里注意一下，新的retry，防止一条消息重新 发送，指定到该消息的队列，不给其它队列发送
        $header[$this->_consumerBindQueueName] = $this->_consumerBindQueueName;
        //延迟时间
        $header['x-delay'] = $beanRetry[$retryCount] * 1000;
        $header['x-retry-count'] = $retryCount+1;
        $parseBackData['headers'] = $header;

        //原消息不要了，回复 确认
        $this->_extProvider->ack($backData,$backQueue);
        $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_RETRY_ACK,$msgId);


        $parseBackData['timestamp'] = time();
        $parseBackData['message_id'] = $this->createUniqueMsgId();

//        $mysqlRecordMsgArguments = [];
//        foreach ($parseBackData as $k=>$v) {
//            if($k == 'headers'){
//                 continue;
//            }
//
//            if($k == 'body'){
//                continue;
//            }
//
//            $mysqlRecordMsgArguments[$k] = $v;
//        }

//        $mysqlData = array(
//            'message_id'=>$parseBackData['message_id'],
//            'content'=>$parseBackData['body'],
//            'status'=>MsgRecordModel::STATUS_RETRYING,
//            'topic'=>$exchange,
//            'events'=>json_encode($header),
//            'attribute'=>json_encode($mysqlRecordMsgArguments),
//            'routing_key'=>"",
//            'type'=>2,
//            'content'=>str_replace("\0","#cp#",$body),
//            'back_msg_id'=>$msgId,
//            'event_item'=>$this->_consumerBindQueueName,
//            'queue_name'=>$this->_consumerBindQueueName,
//            'fix_status'=>0,
//        );
        //发送新 消息
//        $this->out("basicPublish body".json_encode($body));
//        $this->out("retry basicPublish parseBackData ".json_encode($parseBackData));
//        $this->createMysqlMsgRecord($mysqlData);
        $this->_extProvider->basicPublish($exchange,"",$body,$parseBackData);

//            $this->txCommit();
//        }catch (\Exception $e){
//            $this->txRollback();
//            $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'],true);
//        }
    }

    //生成消息唯一ID
    function createUniqueMsgId(){
        return uniqid(time());
    }

    function execUserShutdownCallback(){
        if($this->_userShutdownCallback){
            $this->out("  exec user shutdown callback func.");
            call_user_func($this->_userShutdownCallback);
        }else{
            $this->out(" user not reg shutdown callback func.");
        }
    }

    function delPid(){
        if($this->getOs() !== "WIN"){
            if($this->supportsPcntlSignals()){
                $this->out("del pid");
                unlink($this->_pidPath);
            }
        }
    }

    function regSignals(){
        $this->_timeOutCallback = function () {
            $this->out("oh no~ nightmare !!!,exec time out , start shutdown process:");
            $this->execUserShutdownCallback();
            $this->delPid();

            if($this->_doingProcessMsgId){
                $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_TIMEOUT,$this->_doingProcessMsgId);
            }else{
                $this->out("_doingProcessMsgId is null");
            }

            $this->_extProvider->disconnect();

            if (extension_loaded('posix')) {
                $this->out("posix_kill pid:".getmypid());
                posix_kill(getmypid(), SIGKILL);
            }
            exit(" script done.");
        };

        if($this->supportsPcntlSignals()){
            pcntl_async_signals(true);
//            $this->out("reg SIGTERM");
//            pcntl_signal(SIGTERM, function(){
//                $this->execUserShutdownCallback();
//                $this->delPid();
//                exit(" SIGTERM , no goods 1.\n");
//            });
//            pcntl_signal(SIGINT, function() {
//                $this->execUserShutdownCallback();
//                $this->delPid();
//                exit(" SIGTERM , no goods 2.\n");
//            });
        }
    }

    function upMysqlMsgRecordStatus($status,$msgId){
//        if($this->_consumerBindQueueName){
//            $rs = MsgRecordModel::upStatusByConsumerQueue($status,$msgId,$this->_consumerBindQueueName);
//        }else{
//            $rs = MsgRecordModel::upStatus($status,$msgId);
//        }
        $this->out("up mysql msg record status:$status by $msgId , {$this->_consumerBindQueueName}");
    }

    function setTimeoutSignal(){
        $this->out("set timeout signal ");
        if(!$this->supportsPcntlSignals()){
            $this->out(" not support");
            return false;
        }
        $this->out(" timeout "  . $this->_userCallbackFuncExecTimeout);
        pcntl_signal(SIGALRM, $this->_timeOutCallback);
        pcntl_alarm($this->_userCallbackFuncExecTimeout);
    }

    function cancelTimeoutSignal(){
        $this->out("cancel timeout signal " );
        if(!$this->supportsPcntlSignals()){
            $this->out(" not support");
            return false;
        }
        $this->out(" ok ");

        pcntl_alarm(0);
    }

    //消费者 - 回调
    function subscribeCallback($backData,$backQueueObj = null ,$exchange,$noAck){
        $this->out("im in base subscribeCallback");
//        $msgArr = $this->_extProvider->parseBackDataToUniteArr($backData);
//        $body = $this->transcodingMsgBody($msgArr['body'],2,$msgArr['content_type']);
//        $info = $this->_extProvider->debugMergeInfo($backData);

//        $this->out("rabbitmq return msg arrt:".json_encode($info));
//        $recall = array("AMQPMessage" => $msg, 'body' => $body, 'attr' => $attr);
        $parseBackData = $this->_extProvider->parseBackDataToUniteArr($backData);
//        $this->out(json_encode($parseBackData));
        $this->_doingProcessMsgId = $parseBackData['message_id'];
        if($noAck){
            //非确认机制，不需要走重试机制
            $this->out(" no ack ");

            $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_USER_RUNNING,$parseBackData['message_id']);
            $this->userCallbackExec('serverBackConsumer',$parseBackData);
            $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_CONSUMED,$parseBackData['message_id']);
        }else{
            try{
                $this->setTimeoutSignal();
                $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_USER_RUNNING,$parseBackData['message_id']);
                $this->out(" exec user callback function");
                $this->userCallbackExec("serverBackConsumer",$parseBackData);
                $this->cancelTimeoutSignal();

                $this->_extProvider->ack($backData,$backQueueObj);
                $this->out(" return rabbitmq ack . loop for waiting msg...");
                $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_CONSUMED,$parseBackData['message_id']);
            }catch (RejectMsgException $e){
                $this->exceptionRecordMysql($parseBackData,MsgRecordModel::STATUS_CONSUMER_REJECT);

                $this->out("subscribeCallback RejectException");
                $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_CONSUMER_REJECT,$parseBackData['message_id']);
                $this->_extProvider->reject($backData,$backQueueObj);
            }catch (RetryException $e){
                $msgRetry = $e->getRetry();
                $info = "null";
                if($msgRetry)
                    $info = json_encode($msgRetry);

                $this->out("subscribeCallback RetryException ,RetryException->getRetry rs :".$info);
                $this->retry($exchange,$backData,$backQueueObj,$msgRetry);
            }catch (\Exception $e) {
                $this->exceptionRecordMysql($parseBackData,MsgRecordModel::STATUS_RUNTIME_EXCEPTION_REJECT);

                $exceptionInfo = $e->getMessage();
                $code = $e->getCode();
                $this->out("subscribeCallback runtime exception code:" .$code . " , exceptionInfo".$exceptionInfo);
                $this->_extProvider->reject($backData,$backQueueObj);
                $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_RUNTIME_EXCEPTION_REJECT,$parseBackData['message_id']);
            }
        }
    }

    function exceptionRecordMysql($parseBackData,$status){
        $dbRecordData = array(
            'content'=>str_replace("\0","#cp#",$parseBackData['body']),
            "topic"=>$this->_exchange,
            'routing_key'=>$parseBackData['routing_key'],
            'fix_status'=>0,
            'message_id'=>$this->createUniqueMsgId(),
            'queue_name'=>$this->_consumerBindQueueName,
            'status'=>$status,
        );

        if(isset($parseBackData['headers']['x-delay']) && $parseBackData['headers']['x-delay']){
            $dbRecordData['type'] = 2;
        }else{
            $dbRecordData['type'] = 1;
        }

        $mysqlHeader = array("x-match"=>"any",$this->_consumerBindQueueName=>$this->_consumerBindQueueName);
        $dbRecordData['event_item'] = $this->_consumerBindQueueName;
        $dbRecordData['events'] = json_encode( $mysqlHeader);

        unset($parseBackData['headers']);
        unset($parseBackData['body']);

        $dbRecordData['attribute'] = json_encode($parseBackData);
        $this->out("exceptionRecordMysql:". json_encode($dbRecordData));

        $this->createMysqlMsgRecord($dbRecordData);
    }

    //consumer 订阅 一个队列
    function baseSubscribe($exchangeName = "",$queueName,$consumerTag = "" ,$noAck = false){
        if(!$exchangeName){
            if($this->_exchange){
                $exchangeName = $this->_exchange;
            }else{
                $this->throwException(511);
            }
        }

        $this->out("Subscribe a  new Consume : queue:$queueName , consumerTag:$consumerTag ,noAck: $noAck , exchangeName : $exchangeName. ");
        if(!$consumerTag){
            $consumerTag = $queueName . time();
        }

        $this->_consumerBindQueueName = $queueName;
        $this->_loopDeadConsumerTag = $consumerTag;

        $self = $this;
        $this->out("set basic consumer callback func ");
        $baseCallback = function($msg,$backQueueObj = null) use($self,$exchangeName,$noAck,$consumerTag){
            $this->out( " basic consumer receive msg");
            $self->subscribeCallback($msg,$backQueueObj,$exchangeName,$noAck);

            if($this->_consumerStopLoop){
                $this->out("trigger consumerStopLoop flag,cancel consumer tag: $consumerTag");
                $this->out("notify Rabbitmq Server cancel consumer.");
                $this->_extProvider->listenerCancel($consumerTag);
                $this->execUserShutdownCallback();
                $this->_consumerStopLoop = 0;
                $this->out("stop dead loop");
                return false;

            }
            return true;
        };

        $this->startListenerWait($queueName,$consumerTag,$baseCallback,$noAck);

    }

    function getOs(){
        $os = strtoupper(substr(PHP_OS,0,3));
        return $os;
    }

    //消费者开启 守护 状态
    function startListenerWait($queueName,$consumerTag,$baseCallback,$noAck){
        $this->out(" start Listener Wait... ");

        if($this->getOs() !== "WIN"){
            if($this->supportsPcntlSignals()){
                $this->out($this->_pidPath);
                if(file_exists($this->_pidPath)){
                    $this->out(" notice _pidPath has exist!");
                    $oldPid = file_get_contents($this->_pidPath);
                    $this->out("old pid:".$oldPid);
                }

                $fd = fopen($this->_pidPath,"w");
                $pid = getmypid();
                fwrite($fd,$pid);
                $this->out($this->_pidPath . ":$pid");
            }
        }

        $this->regSignals();
        $this->_extProvider->basicConsume($queueName,$consumerTag,$baseCallback);

//        while (1){
//            if($this->_consumerStopLoop){
//                $this->out("trigger consumerStopLoop flag,cancel consumer tag: $consumerTag");
//                $this->out("notify Rabbitmq Server cancel consumer.");
//                $this->_extProvider->listenerCancel($consumerTag);
//                $this->execUserShutdownCallback();
//                $this->_consumerStopLoop = 0;
//                $this->out("stop dead loop");
//                break;
//            }
//
//            try{
//                $this->_extProvider->basicConsume($queueName,$consumerTag,false,$noAck,false,false,$baseCallback);
////                $this->_extProvider->listenerCancel($consumerTag);
//            }catch(\Exception $e){
//                $data = array(
//                    'code'=>$e->getCode(),
//                    'content'=>$e->getMessage(),
//                    'type'=>ErrorRecordModel::TYPE_EXT,
//                );
//                ErrorRecordModel::create($data);
//                throw $e;
//            }
//
//        }
    }
//    function cancelRabbitmqConsumer(){
//        $this->out(" cancel consumer.");
//        $this->listenerCancel($this->_loopDeadConsumerTag);
//    }
//
//
//    function listenerCancel($consumerTag){
//        $this->_extProvider->listenerCancel($consumerTag);
//    }
    //基类就这一个实例化，但是 生产者  消费都 都 在用，且还要区分header
    //此方法，就是每次执行之前，需要 设置的  header 也就是child class name
    function _outInit($flag){
        $header = null;
        if(is_array($flag)){
            foreach ($flag as $k=>$v) {
                $this->out("set flag:".$v);
                $header[$v] = $v;
            }
        }else{
            $this->out("set flag:".$flag);
            $header[$flag] = $flag;
        }

        //默认情况下，把用户自定义的类 类名，当做关键字，绑定到header exchange 上
        $this->_header = array_merge(array("x-match"=>'any'),$header);
        return $this;
    }

    function getClassFinalName($className = ""){
        if(!$className){
            $className = __CLASS__;
        }
        $class = explode("\\",$className);
        return $class[count($class) -1];
    }

    function out($info ,$br = 1){
        $msg = $this->getClassFinalName(__CLASS__) . "$$ ".$info;
        if(!$this->_debug){
            return -1;
        }
        $echoMsg = $msg;
        if($br){
            $os = $this->getOs();
            if (preg_match("/cli/i", php_sapi_name())){
                if($os == "WIN"){
                    $echoMsg .= "\r\n";
                }else{
                    $echoMsg .= "\n";
                }
            }else{
                $echoMsg .=  "<br/>";
            }
        }

        if($this->_debug == 1 ||  $this->_debug == 3){
            echo $echoMsg;
        }

        if($this->_debug == 2 ||  $this->_debug == 3){
//            Log::info($msg);
        }

        return true;
    }

    function supportsPcntlSignals(){
        return extension_loaded('pcntl');
    }
    function setIgnoreSendNoQueueReceive(int $flag){
        $this->_ignoreSendNoQueueReceive = $flag;
    }
    //初始化，创建3个默认回调函数
    function regDefaultAllCallback(){
        $this->out("regDefaultAllCallback : ack n-ack server_return_listener");
        $clientAck = function ($backData){
            $this->out("Rabbitmq Server callback Producer ConfirmMode ack ,DeliveryTag=$backData ");
            $data = $this->_extProvider->getMsgByDeliveryTag($backData);
            if(!$data){
                $this->out("notice: getMsgByDeliveryTag($backData) is null ");
                return false;
            }
            if($this->_ignoreSendNoQueueReceive){
                if(in_array($data['message_id'],$this->_ignoreSendNoQueueReceivePool)){
                    $this->out(" msgId in _ignoreSendNoQueueReceivePool ");
                    return true;
                }
            }

            $this->out(" destroyDeliveryPool ");
            $this->_extProvider->destroyDeliveryPool($backData);

            $this->userCallbackExec('ack',$data);
            return false;
        };

        $clientNAck = function ($backData){
            $this->out("Rabbitmq Server callback Producer ConfirmMode N-ack ");
            $data = $this->_extProvider->getMsgByDeliveryTag($backData);
            $this->userCallbackExec('nack',$data);
            $this->throwException(506,array(json_encode($data)));
            return false;
        };

//        $clientReturnListener = function ($code,$errMsg,$exchange,$routingKey,$AMQPMessage) use ($clientAck){
//            $info = "return error info:   code:$code , err_msg:$errMsg , exchange $exchange , routingKey : $routingKey body:".$AMQPMessage->body ."";
        $clientReturnListener = function ($code, $errMsg,$exchange,$routing_key,$properties,$body) use ($clientAck){
            $data = $this->_extProvider->parseBackDataAttrToUniteArr($properties);
            $this->out("rabbitmq server callback clientReturnListener ". json_encode($data));
            if($code == 312 ){
                if($this->_ignoreSendNoQueueReceive){
                    $this->_ignoreSendNoQueueReceivePool[] = $data['message_id'];
                    $this->out(" ignoreSendNoQueueReceive ");
                    return false;
                }
                //这里实际上是一个兼容，延迟插件不支持mandatory flag

//                $attr = RabbitmqBase::getReceiveAttr($AMQPMessage);
//                if(isset($data['application_headers']) && $data['application_headers']){
                if(isset($data['headers']) && $data['headers']){
//                    foreach ($data['application_headers'] as $k=>$v) {
                    foreach ($data['headers'] as $k=>$v) {
                        if($k  == 'x-delay'){//除了正常延迟消息外，还有重试的延迟消息
                            $this->out(" delayed plugin no ack ,but return notice ");
                            if($k == 'x-retry-count'){
                                $this->out(" this msg is retry . ");
                            }
                            return false;
                        }
                    }
                }
            }

            $this->upMysqlMsgRecordStatus(MsgRecordModel::STATUS_SENT_FAIL,$data['message_id']);
            $this->throwException(507,array(json_encode($data)));
        };

        $this->setRabbitmqAckCallback($clientAck,$clientNAck);
        $this->setRabbitmqErrCallback($clientReturnListener);
    }
}