<?php
namespace php_base\MsgQueue\Model;

use php_base\util\db\DB;
use php_base\app\Tool\Func;

class ErrorRecordModel{
    static $_configKey = "php_base.database";
    static $_database = "connections.msg_queue";
    static $_ins = null;
    static $_table = "error_record";
    static $_primeKey = "id";

    //1已发送2发送异常3已消费4消费者直接拒绝5重试拒绝6运行时异常拒绝7超时8重试确认9重试中
    const TYPE_CALLBACK = 1;
    const TYPE_EXT = 2;

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

        $params = Func::joinKeys($data);
        $keys = trim($params['in_keys'],',');
        $tmp = $params['tmp'];
        $in_param = trim($params['in_param'], ',');
        $sql = "insert into ". self::$_table . " ($keys) values ($in_param)";
        $insertId = static::getIns()->insert($sql, $tmp);

        return $insertId;
    }

}

