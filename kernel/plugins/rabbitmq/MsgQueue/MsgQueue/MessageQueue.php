<?php
namespace php_base\MsgQueue\MsgQueue;
use php_base\MsgQueue\Facades\MsgQueue;
use php_base\MsgQueue\Facades\Log;

abstract class MessageQueue{
    //一但设置了确认模式或者事务模式就不能再变更，这两种模式是互斥的
    private $_debug = 2;//调试模式
    private $_topicName = "";//分类名
    private $_mode = 0;//如下
    private $_modeDesc = array(0=>'普通模式',1=>'确认模式',2=>'事务模式');
    private $_childClassName = "";//子类继承标识
    private $_retry = null;//重试机制
    //每个consumer最大同时可处理消息数
    private $_consumerQos = 1;
    //consumer 类型 ,描述如下
    private $_consumerSubscribeType = 0;
    //consumer 类型描述
    private $_consumerSubscribeTypeDesc = array(1=>'直接bean类开启consumer,监听一个bean',2=>'同时监听多个bean',3=>'同时监听多个bean,使用工具类自动生成');
    //一个consumer绑定多个bean的时候，保存值
    private $_customBindBean = [];

    /*
     * $provide:队列烦死 ，rabbitmq kafka
     * $conf : server 连接配置信息
     * debugFlag:测试标识
     * extType: 使用扩展的类型，1 composer PHP明文代码 类库 2 PHP 扩展类库
     * $mode:默认开启模式   普通  事务  确认
     * */
    function __construct($provide = "rabbitmq",$conf = null,$debugFlag = 0,$extType = 2,$mode = 1){
        $this->_debug = $debugFlag;
        $info = null;
        if($conf){
            $info = json_encode($conf);
        }
        $this->out(" construct provide:$provide debugFlag:$debugFlag extType:$extType conf:$info");

        //子类名，即：传输协议标识ID
        $this->setClassFlag();
        MsgQueue::getInstance($provide,$conf,$debugFlag,$extType);
        MsgQueue::getInstance()->regUserCallback($this->_childClassName,array($this,"serverCallback"));
        //默认开启确认模式
        if($mode){
            $this->setMode($mode);
        }
    }
    //调试模式,0:关闭，1：只输出到屏幕 2：只记日志 3：输出到屏幕并记日志
    //注：开启 日志模式，记得引入log包
    function setDebug($flag){
        $this->_debug = $flag;
        return MsgQueue::setDebug($flag);
    }

    function throwException($code){
        MsgQueue::getInstance()->throwException($code);
    }

    //生产者，设定当前脚本和rabbitmq Server 交互模式  1普通 2确认模式 3事务模式  注：2 跟 3 互斥
    //默认为普通模式，加速性能
    //设置 模式
    function setMode(int $mode){
//        $this->out("set mode :".$mode . " ".$this->_mode );
        if(!in_array($mode,array_flip($this->_modeDesc))){
            MsgQueue::getInstance()->throwException(504);
        }

        if($this->_mode && $this->_mode != $mode){
            MsgQueue::getInstance()->throwException(505);
        }

        if( $this->_mode == $mode){
            return true;
        }

        if($mode == 1){
            MsgQueue::getInstance()->confirmSelectMode();
        }

        $this->_mode = $mode;
//        var_dump($this->_mode);
    }

    //开启一个事务
    function  transactionStart(){
        $this->out("transactionStart:{$this->_mode}");
        $this->setMode(2);
        return MsgQueue::getInstance()->txSelect();
    }
    //提交一个事务
    function  transactionCommit(){
        $this->setMode(2);
        return MsgQueue::getInstance()->txCommit();
    }
    //回滚一个
    function  transactionRollback(){
        $this->setMode(2);
        return MsgQueue::getInstance()->txRollback();
    }

    function getTopicName(){
        return $this->_topicName;
    }
    //此方法，暂时不能用。因为只有一个 exchange
    function setTopicName($name){
        $this->_topicName = $name;
        MsgQueue::getInstance()->setTopicName($name);
    }
    //生产者可以DIY，消费者也可以DIY，以最后设置的为准。
    //也可以不设置，父类里有默认值
    function setRetryTime(array $retry){
        $this->_retry = $retry;
    }
    //获取类重试机制
    function getRetryTime(){
        return $this->_retry;
    }
    //发送者，发送消息体，最大值
    //因为 领导要求，必须要把整个对象 全传到队列的消息体内，只能用PHP序列化，会有点大。
    function setMessageMaxLength(int $num){
        MsgQueue::getInstance()->setMessageMaxLength($num);
    }

    function setClassFlag($flag = false){
        if($flag){
            $this->_childClassName = $flag;
            $this->out(" setClassFlag by arguments :".$flag);
        }else{
            $this->_childClassName = get_called_class();
            $this->out(" setClassFlag by get_called_class:".get_called_class());
        }
    }

    //==============================以上是公共方法

    //===============================以下是生产者相关

    //生产者-注册ACK回调
    function regUserCallbackAck(callable $callback){
        $this->out("regUserCallbackAck");
        MsgQueue::getInstance()->regUserFunc($this->_childClassName,"ack",$callback);
    }
    //生产者-注册N-ACK回调
    function regUserCallBackNAck(callable $callback){
        $this->out("regUserCallBackNAck");
        MsgQueue::getInstance()->regUserFunc($this->_childClassName,"nack",$callback);
    }
    //rabbitmq server 有任何回调，会先调base类，base再调用此方法，统一入口
    function serverCallback($type,$data){
        $this->out("serverCallback type:".$type);
        if($type == 'ack'){
            if(!isset($data['headers']) ||  !$data['headers']){
//                if(isset($attr['application_headers']) &&  $attr['application_headers']){
//                foreach ($attr['application_headers'] as $k=>$v) {
                $this->throwException(520);
            }

            $body = $this->transcodingMsgBody($data['body'],2,$data['content_type']);
            $func = MsgQueue::getInstance()->getRegUserFunc($this->_childClassName,'ack');
            if($func){
                call_user_func($func,$body);
            }else{
                $this->out(" user not register callback func.");
            }

        }elseif($type == "nack"){
            $this->out("nack");
        }elseif($type == 'rabbitmqReturnErr' ){
            $this->out("rabbitmqReturnErr");
        }elseif($type == 'serverBackConsumer' || $type == 'serverBackConsumerRetry'){
            $body = $this->transcodingMsgBody($data['body'],2,$data['content_type']);
            $func = MsgQueue::getInstance()->getRegUserFunc($this->_childClassName,'serverBackConsumer');
            if($func){
                call_user_func($func,$body);
            }else{
                $this->out(" user not register callback func.");
            }
        }else{
            $this->out("serverCallback type err.");
        }
    }
    /*
     *  发送一条消息给路由器
        $msgBody:发送消息体，可为json object string
        $arguments:对消息体的一些属性约束
        $header:主要是发送延迟队列时，使用
    */
    function send($arguments = null,$header = null,$isRetry = 0){
//        if(!$msgBody)
//            $this->throwException(500);

//        if(is_bool($msgBody))
//            $this->throwException(501);

        $msgBody = $this;
        $msgId = $this->createUniqueMsgId();
        $arguments = $this->setCommonArguments($arguments,$msgId,$msgBody);
        $msg = $this->transcodingMsgBody($msgBody,1);
        $msgBody = $msg['msg'];
        $arguments['content_type'] = $msg['content_type'];

        MsgQueue::getInstance()->_outInit($this->_childClassName)->publish($msgBody,$this->_topicName,null,$header,$arguments);
//        $this->publish($msgBody,$this->_exchange,"",$rabbitHeader,$arguments);
        if($this->_mode == 1){
            MsgQueue::getInstance()->baseWait();
        }



        return $msgId;
    }
    //生成消息唯一ID
    function createUniqueMsgId(){
        return uniqid(time());
    }
    //发送一条延迟消息
    function sendDelay(int $msTime ){
        $arr = array('x-delay'=>$msTime);
        $this->send(null,$arr);
    }
    //一个consumer最多可同时接收rabbitmq 消费数
    function setReceivedServerMsgMaxNumByOneTime(int $num){
        $this->out("setReceivedServerMsgMaxNumByOneTime :  $num");
        $this->_consumerQos = $num;
        return MsgQueue::getInstance()->setReceivedServerMsgMaxNumByOneTime($num);
    }
    //进程意外退出，如：超时，会执行此函数。类似析构函数
    //但是：如果shell 里直接kill pid  ,或 ctrl+c ，信号可以捕捉到，但不会执行此方法
    //如果用户态 可执行 quitConsumerDemon，就没必要执行此方法了。
    function regConsumerShutdownCallback($func){
        MsgQueue::getInstance()->regUserCallbackShutdown($func);
    }
    //设置一个消费者守护进程，同时接收server最大消息数
    function setUserConsumerCallbackExecTimeout(int $time){
        MsgQueue::getInstance()->setUserConsumerCallbackExecTimeout($time);
    }
    //消费者开启守护模式，即是死循环，如果有特殊情况想退出，可以使用此方法
    //调用此函数后，原<执行代码空间>，后面的代码即可执行
    function quitConsumerDemon(){
        MsgQueue::getInstance()->quitConsumerDemon();
    }
    //给单元测试工具类使用 - 忽略
    function getProvider(){
        return MsgQueue::getInstance();
    }
    //生产者发送消息的时候，如果队列不存在，或者路由不到队列，rabbitmq server 会给出警告
    //领导要求，忽略提示，就这么干，消息如果丢失就丢失，是业务(使用者) 的事儿。
    function setIgnoreSendNoQueueReceive(int $flag){
        MsgQueue::getInstance()->setIgnoreSendNoQueueReceive($flag);
    }
    //type:1 encode 2 decode
    //发消息的时候：类库，应该自己给消息加上属性
    //主要是给 消息内容 加上头信息，告诉 rabbitmq 消息体的内容类型
    function transcodingMsgBody($msgBody,$type = 1,$contentType = ""){
        $rs = array("msg"=> $msgBody,"content_type"=>"");
        if($type == 1){
            if(is_object($msgBody)){
                $rs['msg'] = serialize($msgBody);
                $rs['content_type'] = "application/serialize";
            }
            elseif(is_array($msgBody) ){
                $rs['msg'] = json_encode($msgBody);
                $rs['content_type'] = "application/serialize";
            }
        }elseif($type == 2){
            if($contentType == "application/serialize"){
                $rs = unserialize($msgBody);
            }elseif($contentType == "application/json"){
                $rs = json_encode($msgBody,true);
            }
        }

        return $rs;
    }
    //设置 默认 参数值
    function setCommonArguments($arguments,$msgId){
        if($arguments){
            if(isset($arguments['message_id']) && $arguments['message_id']){
                $this->throwException(502);
            }

            if(isset($arguments['type']) && $arguments['type']){
                $this->throwException(503);
            }

            $arguments['message_id'] = $msgId;
        }else{
            $arguments = array( "message_id"=>$msgId);
        }

        if(isset($arguments['timestamp']) && $arguments['timestamp']){
            $this->throwException(512);
        }

        $arguments['timestamp'] = time();
        $arguments['delivery_mode'] = 2;


        if($this->_mode == 1){//确认模式
            $arguments['type'] = "confirm";
        }elseif($this->_mode == 2){//事务模式
            $arguments['type'] = "tx";
        }else{
            $arguments['type'] = "normal";
        }

        return $arguments;
    }

    //========================================以上是生产者相关，以下是消费者相关

    //检查一个队列是否存在 ，如果不存在则创建
    function checkQueueAndCreate($queueName,$autoDel = false,$durable = true){
        if(!MsgQueue::getInstance()->queueExist($queueName)){
            MsgQueue::getInstance()->setQueue($queueName,null,$durable,$autoDel);
        }
    }
    //消费者 开启 订阅 监听
    //消费者 - 想监听 - 多个事件 的时候，需要 初始化 队列 信息
    function subscribe($consumerTag ,$retry = [],$listenManyBeanType = 1 ,$autoDel = false,$durable = true,$noAck = false){
        if(!$consumerTag){
            $this->throwException(508);
        }

        $autoDel = false;//暂不支持此参数
        $durable = true;//必须持久化
        $noAck = false;//必须手动ACK 并由类库操作

        $queueName = $this->_childClassName ."_". $consumerTag;
        $this->out(" rabbitmqBean subscribe start: queueName:$queueName consumerTag:$consumerTag noAck:$noAck listenManyBeanType:$listenManyBeanType");

        $this->checkQueueAndCreate($queueName,$autoDel,$durable);

        if(!$this->_customBindBean){
            MsgQueue::getInstance()->throwException(522);
        }
        //注册绑定的bean
        $header = array("x-match"=>'any',$queueName=>$queueName,$this->_childClassName=>$this->_childClassName);
        foreach ($this->_customBindBean as $k=>$v) {
            $header[$v['beanName']] = $v['beanName'];
        }
        MsgQueue::getInstance()->bindQueue($queueName,"",null,$header);
        //回去调函数
        $consumerCallback = function($recall) use ($noAck){
            if (!$noAck) {
                return $this->mappingBeanCallbackSwitch($recall);
            }
        };
        //重试机制
        $this->setSubscribeRetry($retry);

        $this->setConsumerSubscribeType($listenManyBeanType);
        if(!$this->_consumerQos){
            $this->setReceivedServerMsgMaxNumByOneTime($this->_consumerQos);
        }
        //注册重试入口
        MsgQueue::getInstance()->regUserCallback($queueName,array($this,"serverCallback"));
        //注册统一回调入口
        MsgQueue::getInstance()->regUserFunc($this->_childClassName,"serverBackConsumer",$consumerCallback);

        MsgQueue::getInstance()->baseSubscribe("",$queueName, $consumerTag,$noAck);
    }

    function setConsumerSubscribeType(int $type){
        $this->out("setConsumerSubscribeType:$type");
        if(!in_array($type,array_flip($this->_consumerSubscribeTypeDesc))){
            $this->throwException(535);
        }
        $this->_consumerSubscribeType = $type;
    }

    //快速开启 一个consumer订阅一个队列
    function groupSubscribe(callable $userCallback,$consumerTag ,$autoDel = false,$durable = true,$noAck =false,$retry = []){
//        $autoDel = false;//暂不支持此参数
//        $durable = true;//必须持久化
        $noAck = false;//必须手动ACK 并由类库操作

        $this->out("start groupSubscribe autoDel:$autoDel , durable:$durable , noAck:$noAck ");
        if(!$consumerTag){
            MsgQueue::getInstance()->throwException(508);
        }

        $queueName = $this->_childClassName . "_".$consumerTag;
        $this->out(" queueName:$queueName ");

        $this->checkQueueAndCreate($queueName,$autoDel,$durable);

        $this->setReceivedServerMsgMaxNumByOneTime($this->_consumerQos);
        $this->setConsumerSubscribeType(1);

        $this->setSubscribeRetry($retry);

        MsgQueue::getInstance()->regUserCallback($queueName,array($this,"serverCallback"));

        $header = array($queueName,$this->_childClassName);
        MsgQueue::getInstance()->_outInit($header);

        MsgQueue::getInstance()->regUserFunc($this->_childClassName,"serverBackConsumer",$userCallback);
//        MsgQueue::getInstance()->regUserFunc($this->_childClassName,"serverBackConsumerRetry",$userCallback);

        MsgQueue::getInstance()->bindQueue($queueName);
        MsgQueue::getInstance()->baseSubscribe(null,$queueName,$consumerTag,$noAck);
    }

    function setSubscribeRetry($retry = null){
        if($retry){
            $this->out(" set retryTimes By arguments  ");
            MsgQueue::getInstance()->setRetryTime($retry);
        }else{
            if($this->_retry){
                $this->out(" set retryTimes By member variable ");
                MsgQueue::getInstance()->setRetryTime($this->_retry);
            }else{
                $this->out(" no set retryTimes times.");
            }
        }
    }
    //rabbitmq push consumer 时，将消息分发给 不同的bean
    function mappingBeanCallbackSwitch($body){
        $this->out("im mappingBeanCallbackSwitch func.");
        $className = get_class($body);

        foreach ($this->_customBindBean as $k=>$v) {
            if($v['beanName'] == $className){
                call_user_func($v['callback'],$body);
                return true;
            }
        }

        $this->throwException(513);

    }
    //一个consumer 监听多个bean时，需要先设置监听的bean
    function regListenerBean(array $beans ){
        if($this->_customBindBean){
            $this->throwException(537);
        }

        foreach ($beans as $k=>$bean) {
            if(!is_object($bean)){
                $this->throwException(521);
            }
            $beanClassName =get_class($bean);
            //类可能包含命名空间的，1是反斜杠不能创建函数名，要么得改字符2是太长
            //只取真正的类名，忽略命名空间
            $tmpClassName  =  explode('\\',$beanClassName);
            $realClassName = $tmpClassName[count($tmpClassName) - 1];
            //验证子类(继承类)是否注册了-handle callback
            $relClass = new \ReflectionClass(get_called_class());
            $methods = $relClass->getMethods();
            $f = 0;
            foreach ($methods as $k=>$v) {
                if( strtolower($v->getName() )== strtolower("handle" .$realClassName ) ){
                    $f = 1;
                    break;
                }
            }
            if(!$f){
                MsgQueue::throwException(523,array("handle".$realClassName));
            }

//            $this->setServerListenerBean($bean,array($this,"handle".$realClassName));
            $this->_customBindBean[] =array("beanName"=>$beanClassName, 'callback'=>array($this,"handle".$realClassName)) ;
        }
    }
    //一个consumer 监听多个bean时，需要先设置监听的bean
    function regListenerBeanByClassName(array $beansClass  ){
        if($this->_customBindBean){
            $this->throwException(537);
        }

        foreach ($beansClass as $className=>$callback) {
            if(!class_exists($className)){
                $this->throwException(536,array($className));
            }

            if(!is_callable($callback)){
                $this->throwException(538,array($className));
            }

            $this->_customBindBean[] =array("beanName"=>$className, 'callback'=>$callback) ;
        }
    }

    //如下是:工具方法
    function getClassFinalName($className = ""){
        if(!$className){
            $className = __CLASS__;
        }
        $class = explode("\\",$className);
        return $class[count($class) -1];
    }
    function getOs(){
        $os = strtoupper(substr(PHP_OS,0,3));
        return $os;
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
}