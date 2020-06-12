<?php
namespace php_base\MsgQueue\Tools;
use php_base\config\PhpBaseConfig;

class RabbitmqApi{
    static $INC = null;
    private $_user = null;
    private $_host = null;
    private $_pwd = null;
    private $_vhost = null;
    private $_domain = "http://";

    static function getInstant(){
        if(self::$INC){
            return self::$INC;
        }

        self::$INC = new self();
        return self::$INC;
    }

    function __construct(){
        $conf = PhpBaseConfig::get('php_base.rabbitqueue','rabbitmq');
        $this->_host = $conf['host'];
        $this->_vhost = $conf['vhost'];
        $this->_user = $conf['user'];
        $this->_pwd = $conf['pwd'];

        $this->_domain .= $this->_host . ":15672/api/";

        if($this->_vhost == "/"){
            $this->_vhost = "%2f";
        }
    }

    function getQueueByName( $queueName){
        $url = $this->_domain ."queues/{$this->_vhost}/$queueName";
        $data = $this->sendByCurl($url);
        $returnData = array(
            'backing_queue_status_len'=>$data['backing_queue_status']['len'],
            'messages_ready'=>$data['messages_ready'],
            'messages'=>$data['messages'],
            'messages_unacknowledged'=>$data['messages_unacknowledged'],
        );
        return $returnData;
    }

    function getAllQueues(){
        $url = $this->_domain ."queues/{$this->_vhost}/";
        $data = $this->sendByCurl($url);

        $rs = [];
        foreach ($data as $k=>$v) {
            $row = array(
                'name'=>$v['name'],
                'backing_queue_status_len'=>$v['backing_queue_status']['len'],
                'messages_ready'=>$v['messages_ready'],
                'messages'=>$v['messages'],
            );
            $rs[] = $row;
        }

        return $rs;
    }

    function getAllExchanges(){
        $curl = curl_init();

        $url = $this->_domain ."exchanges/{$this->_vhost}/";
        $data = $this->sendByCurl($url);

        return $data;
    }

    function healthchecks(){
        $nodeName = urlencode("rabbit@carssbor-PC");
        $url = $this->_domain ."healthchecks/node/$nodeName";
        $data = $this->sendByCurl($url);
        var_dump($data);exit;

//        /api/healthchecks/node/{node}
    }

    function getNode(){
        $nodeName = urlencode("rabbit@carssbor-PC");
        $url = $this->_domain ."nodes";
        $data = $this->sendByCurl($url);
        $filterData = [];
        foreach ($data as $k=>$v) {
//            var_dump("new");
//
//            foreach ($v as $k2=>$v2) {
//                var_dump($k2);var_dump($v2);
//                echo "<br/>";
//            }

            $tmp = array(
                'mem_used'=>$v['mem_used'],
                'mem_limit'=>$v['mem_limit'],
                'mem_alarm'=>$v['mem_alarm'],
                'disk_free_limit'=>$v['disk_free_limit'],
                'disk_free_alarm'=>$v['disk_free_alarm'],
                'sockets_used'=>$v['sockets_used'],
                'proc_total'=>$v['proc_total'],
                'proc_used'=>$v['proc_used'],
                'name'=>$v['name'],
//                ''=>$v['aaa'],
            );
            $filterData[$v['name']] = $tmp;
        }

        return $filterData;
    }

    function getConsumer(){
        $url = $this->_domain ."api/nodes/";
        $data = $this->sendByCurl($url);
        var_dump($data);exit;
    }

    function sendByCurl($url){
        $curl = curl_init();


        $header[] = "Content-Type:application/json";
        $header[] = "Authorization: Basic ".base64_encode("{$this->_user}:{$this->_pwd}"); //添加头，在name和pass处填写对应账号密码

        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);


        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);
        $data = json_decode($output,"true");
        return $data;
    }


}