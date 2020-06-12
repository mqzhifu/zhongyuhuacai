<?php
namespace php_base\MsgQueue\Model;

use php_base\util\db\DB;
use php_base\app\Tool\Func;

class MsgRecordModel{
    static $_configKey = "php_base.database";
    static $_database = "connections.msg_queue";
    static $_ins = null;
    static $_table = "msg_record";
    static $_primeKey = "id";

    //1已发送2发送异常3已消费4消费者直接拒绝5重试拒绝6运行时异常拒绝7超时8重试确认9重试中
    const STATUS_SENT = 1;
    const STATUS_SENT_FAIL = 2;
    const STATUS_CONSUMED = 3;
    const STATUS_CONSUMER_REJECT = 4;
    const STATUS_RETRY_REJECT = 5;
    const STATUS_RUNTIME_EXCEPTION_REJECT = 6;
    const STATUS_TIMEOUT = 7;
    const STATUS_RETRY_ACK = 8;
    const STATUS_RETRYING = 9;
    const STATUS_USER_RUNNING = 10;

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


        $params = Func::joinKeys($data);
        $keys = trim($params['in_keys'],',');
        $tmp = $params['tmp'];
        $in_param = trim($params['in_param'], ',');
        $sql = "insert into ". self::$_table . " ($keys) values ($in_param)";
        $insertId = static::getIns()->insert($sql, $tmp);

        return $insertId;
    }

    static function upStatus($status,$msgId){
        $sql = "UPDATE " .self::$_table ."  SET  `status` = ? ,`up_time` = ? WHERE  `message_id` = ?  ";
        $res = static::getIns()->execute($sql, [$status,time(),$msgId]);
        return $res;
    }

    static function fixDoneByMsgId($id){
        $sql = "UPDATE " .self::$_table ."  SET  `fix_status` = ? ,`up_time` = ? WHERE  `id` = ? ";
        $data =  [2,time(),$id];
        $res = static::getIns()->execute($sql,$data);
        return $res;
    }

    static function upStatusByConsumerQueue($status,$msgId,$queueName){
        $sql = "UPDATE " .self::$_table ."  SET  `status` = ? ,`up_time` = ? WHERE  `message_id` = ? and queue_name = ?  LIMIT 1 ";
        $res = static::getIns()->execute($sql, [$status,time(),$msgId,$queueName]);
        return $res;
    }

//    static function upSendFailAndNoFixStatusByMsgId($msgId,$limit = 100){
//        $sql = "UPDATE " .self::$_table ."  SET  message_id = ? , `fix_status` = ? ,`up_time` = ? WHERE  status = ? and fix_status = ? limit $limit ";
//        $data = array($msgId,1,time(),MsgRecordModel::STATUS_SENT_FAIL,0);
//        return static::getIns()->execute($sql,$data);
//    }

    static function getSendFailListAndNoFix($limit = 100){
        $sql = "select id,message_id,content,attribute,events from ".self::$_table ." where   fix_status = ?   order by id asc limit $limit";
        $data = array( 0 );
        return static::getIns()->findAll($sql,$data);
    }

    static function getListByMsgId($msgId){
        $sql = "select * from ".self::$_table ." where message_id = ?";
        $data = array($msgId);
        return static::getIns()->findAll($sql,$data);
    }
}

