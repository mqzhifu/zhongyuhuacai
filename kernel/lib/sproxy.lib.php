<?php
require_once PLUGIN.'/grpc/vendor/autoload.php';

class SproxyLib{
    public $_list = null;
    public $_zk_host = "127.0.0.1";
    public $_zk_port = "2181";
    static public $_ins = null;
    public $_s_name = null;
    static function ins(){
        if(self::$_ins){
            return self::$_ins;
        }

        return new self();
    }

    function getService($serviceName){
        $serviceConnectInfo = $this->getServiceConfig($serviceName);
    }

    function setServiceName($serviceName){
        $this->_s_name = $serviceName;
        return $this;
    }
//    function callMethod($serviceName,$methodName,$para){
    function __call($methodName, $arguments){
        if(!$this->_s_name){
            exit("service name is null");
        }
        $serviceName = $this->_s_name;
        $ucfirstServiceName = ucfirst($serviceName);
        $methodName =ucfirst( $methodName);

        include_once APP_CONFIG."/protobuf_class/{$ucfirstServiceName}/{$ucfirstServiceName}Client.php";
        include_once APP_CONFIG."/protobuf_class/{$ucfirstServiceName}/{$methodName}Reply.php";
        include_once APP_CONFIG."/protobuf_class/{$ucfirstServiceName}/{$methodName}Request.php";
        include_once APP_CONFIG."protobuf_class/GPBMetadata/$ucfirstServiceName.php";


        $serviceConnectInfo = $this->getServiceConfig($serviceName);
//        var_dump($serviceName);

        $class =ucfirst( "$ucfirstServiceName\\".$ucfirstServiceName."Client");
        $client = new $class($serviceConnectInfo['ip'].":".$serviceConnectInfo['port'], [
            'credentials' => Grpc\ChannelCredentials::createInsecure(),
        ]);


        $class= "$ucfirstServiceName\\{$methodName}Request";
        $request = new $class();
        foreach ($arguments as $k=>$v) {
            $setMethodName = "set".ucfirst($k);
            $request->$setMethodName($v);
        }

        list($reply, $status) = $client->$methodName($request)->wait();
        $message = $reply->getNickname();

        var_dump($message);exit;
        return $message;



    }

    function getServiceConfig($service){
        if(arrKeyIssetAndExist($this->_list,$service)){
            return $this->_list[$service];
        }

        $s = $this->setServiceConn($service);
        $s = explode(":",$s);
        $config=array("ip"=>$s[0],"port"=>$s[1]);
        $this->_list[$service] = $config;
        return $this->_list[$service];
    }

    function setServiceConn($service){
        $class = new Zookeeper ($this->_zk_host.":".$this->_zk_port);
        $userServiceDir = "/service/$service";
        $dirList = $class->getChildren ($userServiceDir);
        if(!$dirList){
            var_dump("$userServiceDir user no child");exit;
            return false;
        }

        $serviceList = [];
        foreach ($dirList as $k=>$v) {
            $childDir = $userServiceDir . "/" .$v;
            $info = $class->get($childDir);
            $serviceList[$childDir] = $info;
        }

        $mod = count($serviceList);
        $IP = get_client_ip();
        $v = (int) md5($IP) % $mod;
        $v++;
        $serverInfo = $serviceList[$userServiceDir . "/" .$v];
        return $serverInfo;
    }
}