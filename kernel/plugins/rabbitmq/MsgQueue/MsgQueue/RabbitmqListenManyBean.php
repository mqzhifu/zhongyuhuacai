<?php
namespace php_base\MsgQueue\MsgQueue;
use php_base\MsgQueue\MsgQueue\MessageQueue;

class RabbitmqListenManyBean extends MessageQueue{

    function quickSubscribe($consumerTag ,$retry = [],$autoDel = false,$durable = true,$noAck = false){
        $this->subscribe($consumerTag ,$retry = [],2,$autoDel,$durable,$noAck);
    }
}