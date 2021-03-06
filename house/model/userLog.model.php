<?php
class UserLogModel {
	static $_table = 'user_log';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key =DB_CONN_DEFAULT;


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }
    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function getListByUid($uid,$limit = 100){
        $list = self::db()->getAll(" uid = $uid order by id desc limit $limit");
        return $list;
    }


}