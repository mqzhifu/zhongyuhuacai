<?php
namespace php_base\MsgQueue\Model;

use php_base\util\db\DB;
use php_base\app\Tool\Func;

class ExchangeBindQueueModel{
    static $_configKey = "php_base.database";
    static $_database = "connections.msg_queue";
    static $_ins = null;
    static $_table = "exchange_bind_queue";
    static $_primeKey = "id";

    //1已发送2发送异常3已消费4消费者直接拒绝5重试拒绝6运行时异常拒绝7超时8重试确认9重试中
    const DEL_TRUE = 1;
    const DEL_FALSE = 0;

    public function __construct(){

    }

    static function getIns(){
        if(self::$_ins){
            return self::$_ins;
        }

        self::$_ins = DB::getMaster(self::$_configKey,self::$_database );
        return self::$_ins;
    }

    static function create($data){
        $data['create_time'] = time();
        $data['up_time'] = time();
        $data['is_del'] = self::DEL_FALSE;

        $params = Func::joinKeys($data);
        $keys = trim($params['in_keys'],',');
        $tmp = $params['tmp'];
        $in_param = trim($params['in_param'], ',');
        $sql = "insert into ". self::$_table . " ($keys) values ($in_param)";
        $insertId = static::getIns()->insert($sql, $tmp);

        return $insertId;
    }

    static function hasBinding($exchangeName,$queueName,$bindKey){
        $sql = "select id from ".self::$_table ." where exchange_name = ? and queue_name = ?  and bind_key = ? and is_del = ?";
        $data = array($exchangeName,$queueName,$bindKey,self::DEL_FALSE);
        return static::getIns()->findOne($sql,$data);
    }

    static function clearByExchange($exchangeName){
        $data = array(self::DEL_TRUE,time(),$exchangeName);
        $sql = "UPDATE " .self::$_table ."  SET  `is_del` = ? ,`up_time` = ? WHERE  `exchange_name` = ? ";
        return static::getIns()->execute($sql, $data);
    }

    static function clearByQueue($queueName){
        $data = array(self::DEL_TRUE,time(),$queueName);
        $sql = "UPDATE " .self::$_table ."  SET  `is_del` = ? ,`up_time` = ? WHERE  `queue_name` = ? ";
        return static::getIns()->execute($sql, $data);
    }

    static function searchByBindKey($exchangeName,$bindKey){
        $sql = "select id,queue_name from ".self::$_table ." where exchange_name = ? and bind_key = ? and is_del = ? group by queue_name";
        $data = array($exchangeName,$bindKey,self::DEL_FALSE);
        return static::getIns()->findAll($sql,$data);
    }
}

