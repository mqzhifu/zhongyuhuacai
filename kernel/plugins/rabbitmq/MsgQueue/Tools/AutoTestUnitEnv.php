<?php
namespace php_base\MsgQueue\Tools;

use php_base\MsgQueue\Tools\RabbitmqApi;
use php_base\MsgQueue\MsgQueue\MessageQueue;

include_once "testUnitConfig.php";

//测试工具类

//未测试内容：集群、分布式、镜像队列、SSL、日志文件、TCP连接策略(heartbeat keepAlive)、vhost user权限
//rabbit.conf .log(level) advanced.log channel_operation_timeout
//监控：IO wait . TCP  延迟 吞吐量 (ESTABLISHED, CLOSE_WAIT, TIME_WAIT)  . disk free FD .


//因为目前只有rabbitmq一种队列，它是erlang 纯(异步|全双工)编程模型，没有同步通知这个概念。即使你发了一条消息成功，再通过API调，也不一定是最新的结果。
//测试的过程中，想验证结果的话，建议本地搭建一个rabbitmq-server，开启可视化


class AutoTestUnitEnv extends  MessageQueue{
    private $_projectId = 0;
    private $_exchangeType = array("direct",'topic','headers','fanout','x-delayed-message');
    private $queueArguments =  array('x-message-ttl','x-expires','x-max-length','x-max-length-bytes','x-dead-letter-exchange','x-dead-letter-routing-key','x-max-priority',);
    public $_rabbitMq = null;

    public $_config = null;

    function __construct($provide = "rabbitmq",$conf = null,$debugFlag = 0,$extType = 2,$mode = 1){
        parent::__construct($provide,$conf,$debugFlag,$extType,$mode);
        $this->_config = $GLOBALS['mqConfig'];
        $this->_rabbitMq = $this->getProvider();
    }

    function throwException($msg){
        throw new \Exception($msg);
    }

    function getExchangeType(){
        return $this->_exchangeType;
    }

    function setProjectId(int $pid){
        if(!$pid){
            $this->throwException("pid is null");
        }
        $this->_projectId = $pid;
    }

    function hasConsumer($consumers,$name){
        $consumer = $this->findConsumerByName($consumers,$name);
        if(!$consumer){
            $this->throwException("find consumer err:".$name);
        }
    }

    function findConsumerByName($consumer , $name){
        foreach ($consumer as $k=>$v) {
            if($v['name'] == $name){
                return $v;
            }
        }
    }

    function checkProjectConfig(int $pid){
        if(!$pid){
            $this->throwException("pid is null");
        }

        if(!isset($GLOBALS['mqConfig'][$pid]) || !$GLOBALS['mqConfig'][$pid]){
            $this->throwException("pid not include exchange config");
        }

        $exchangeConfig = $GLOBALS['mqConfig'][$pid]['exchange'];
        $this->checkRepeat($exchangeConfig);
        foreach ($exchangeConfig as $k=>$exchange) {
            if(!$this->keyIssetExist($exchange,'type')){
                $this->throwException("exchange must define type");
            }

            if(!in_array($exchange['type'],$this->_exchangeType)){
                $this->throwException("exchange type err");
            }

            if(!$this->keyIssetExist($exchange,'queue')){
                $this->throwException("exchange must define queue");
            }

            $this->checkRepeat($exchange['queue']);
            foreach ($exchange['queue'] as $k2=>$queue) {
                if( $this->keyIssetExist($queue,"arguments") ){
                    if( $this->keyIssetExist($queue['arguments'],'x-dead-letter-exchange') ){
                        $this->hasExchange($exchangeConfig,$queue['arguments']['x-dead-letter-exchange']);
                    }
                }

//                if($this->keyIssetExist($queue,'consumerType') && $queue['consumerType'] != -1){
//                    $this->hasConsumer($GLOBALS['mqConfig']['consumer'], $queue['consumerType']);
//                }
            }
        }
    }

    function getQueues(){
        $queue = null;
        foreach ($this->_config[$this->_projectId]['exchange'] as $k=>$v) {
            foreach ($v['queue'] as $k2=>$v2) {
                $queue[] = $v2['name'];
            }
        }

        return $queue;
    }

    function keyIssetExist($arr,$key){
        if(isset($arr[$key]) && $arr[$key]){
            return true;
        }
        return false;
    }

    function hasExchange($allExchange , $name){
        $exchange = $this->findExchangeByName($allExchange , $name);
        if(!$exchange){
            $this->throwException("find exchange err:".$name);
        }
    }

    function findExchangeByName($allExchange , $name){
        foreach ($allExchange as $k=>$v) {
            if($v['name'] == $name){
                return $v;
            }
        }
    }

    function checkRepeat($config){
        foreach ($config as $k=>$v) {
            foreach ($config as $k2=>$v2) {
                if($k == $k2){
                    continue;
                }
                if($v['name'] == $v2['name']){
                    $this->throwException("repeat name :".$v['name']);
                }
            }
        }
    }

    function out($msg ,$br = 1){
        if($br){
            if (preg_match("/cli/i", php_sapi_name())){
                echo $msg . "\n";
            }else{
                echo $msg . "<br/>";
            }
        }else{
            echo $msg;
        }
    }

    function clearAll(){
        for ($i=1 ; $i <=count($this->_config) ; $i++) {
            $this->clearByProject($i);
        }
        exit;
    }

    function initProjectExchangeQueue($pid){
        if(!$pid){
            $this->throwException("pid is null");
        }

        $this->checkProjectConfig($pid);
        $exchangeConfig = $GLOBALS['mqConfig'][$pid]['exchange'];
        foreach ($exchangeConfig as $k=>$exchange) {
            $arguments = null;
            if($this->keyIssetExist($exchange,"x-delayed-type")){
                $arguments['x-delayed-type'] = $exchange['x-delayed-type'];
            }

            if($this->keyIssetExist($exchange,"alternate-exchange")){
                $arguments['alternate-exchange'] = $exchange['alternate-exchange'];
            }

            $this->_rabbitMq->setExchange($exchange['name'],$exchange['type'],$arguments);

            foreach ($exchange['queue'] as $k2=>$queue) {
                $arguments = null;
                if($this->keyIssetExist($queue,'arguments')){
                    foreach ($queue['arguments'] as $k3=>$v) {
                        if(!$v){
                            unset($queue['arguments'][$k3]);
                        }
                    }
                    if($queue['arguments']){
                        $this->checkQueueArguments($queue['arguments']);
                        $arguments = $queue['arguments'];
                    }
                }
                $this->_rabbitMq->setQueue($queue['name'],$arguments);


                $bind_routing_key = "";
                if($this->keyIssetExist($queue,'bind_routing_key')){
                    $bind_routing_key = $queue['bind_routing_key'];
                }

                if($exchange['type'] == "headers" || ( isset($exchange['x-delayed-type']) && $exchange['x-delayed-type'] == "headers" ) ){
                    $headerTable = null;
                    if($this->keyIssetExist($queue,'bind_header_map')){
                        $headerTable = $queue['bind_header_map'];
//                        $headerTable = new AMQPTable($header);
                    }
                    $this->_rabbitMq->bindQueue($queue['name'],$exchange['name'],$bind_routing_key,$headerTable);
                }else{
                    $this->_rabbitMq->bindQueue($queue['name'],$exchange['name'],$bind_routing_key);
                }
            }
        }

    }

    function checkQueueArguments($arguments){
        foreach ($arguments as $k2=>$v) {
            if($this->keyIssetExist($this->queueArguments,$k2)){
                $this->throwException("checkQueueArguments err : ".$v);
            }
        }
    }



    function clearRabbitmqAll(){
//        $TestConfig = new Tools($this->getProvider());

        $exchangeInfo = RabbitmqApi::getInstant()->getAllExchanges();
        if(!$exchangeInfo){
            echo "no exchangeInfo <br/>";
        }else{
            foreach ($exchangeInfo as $k=>$v) {
                if($v['name'] && strpos($v['name'], "exchange-") !== false){
                    $this->_rabbitMq->deleteExchange($v['name']);
                }
            }
        }

        $queueInfo = RabbitmqApi::getInstant()->getAllQueues();
        if(!$queueInfo){
            exit("no queueInfo");
        }

        foreach ($queueInfo as $k=>$v) {
            $this->_rabbitMq->deleteQueue($v['name']);
        }
        exit;
    }

    function createCaseAndDelOldCase($pid,$isDel = 1){
//        $TestConfig = new Tools($this->getProvider());
        $this->setProjectId($pid);

        if($isDel){
            $this->clearByProject($pid);
        }

        $this->initProjectExchangeQueue($pid);
        exit;
    }


    function clearByProject($pid){
        if(!$pid){
            $this->throwException("pid is null");
        }
        $this->checkProjectConfig($pid);
        $exchangeConfig = $GLOBALS['mqConfig'][$pid]['exchange'];
        $this->out("start clear :");
        foreach ($exchangeConfig as $k=>$exchange) {
            //        $if_unused = false,
            //        $nowait = false,
            $this->_rabbitMq->deleteExchange($exchange['name']);
            $this->out("delete exchange:".$exchange['name']);
            foreach ($exchange['queue'] as $k2=>$queue) {
                $this->out("delete queue:".$queue['name']);
                $this->_rabbitMq->deleteQueue($queue['name']);
            }
        }
    }

    function argcInit($argc,$argv){
        if(!$argc || $argc < 2){
            exit("argc is null or not array or count < 2");
        }

        $config = null;
        foreach ($argv as $k=>$v) {
            if($k == 0){
                continue;
            }
            $tmp = explode("=",$v);
            $config[$tmp[0]] = $tmp[1];
        }

        return $config;
    }



    static function Capability(Lib $lib,$exchangeName,$routingKey,$max,$info){
        $stime=microtime(true);

        for ($i=0 ; $i < $max; $i++) {
            $lib->publish($info,$exchangeName,$routingKey);
        }
        $etime= round(microtime(true),3);
        $total=$etime-$stime;
        out("执行时间:$total");

    }
    static function CapabilityTx(Lib $lib,$exchangeName,$routingKey,$max,$info){
        $stime=microtime(true);

        for ($i=0 ; $i < $max; $i++) {
            try{
                $lib->publish($info,$exchangeName,$routingKey);
                $lib->txCommit();
            }catch (Exception $e){
                $lib->txRollback();
            }
        }
        $etime= round(microtime(true),3);
        $total=$etime-$stime;
        out("执行时间:$total");

    }
}