<?php
class UserBlackModel {
    static $_table = 'user_black';
    static $_pk = 'id';
    static $_db_key = DEF_DB_CONN;
    static $_db = null;

    static $_type = array(1=>'访问次数过于频繁');
    static $_status = array(1=>'禁止中',2=>'已解禁');

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    static function isBlocked($uid) {
        $rs = self::db()->getRow(" uid = $uid and status = 1");
        if (!$rs) {
            return false;
        }
        if ($rs['expire_time'] && time() > $rs['expire_time']) {
            self::db()->upById($rs['id'], ['status'=>2]);
            return false;
        }
        return true;
    }

    static function block($uid,$type,$mome,$expiredTime) {
        $data = [
            'uid'=>$uid,
            'mome'=>$mome,
            'status'=>1,
            'type'=>$type,
            'a_time'=>time(),
            'expire_time'=>$expiredTime,
        ];
        if ($record = self::db()->getRow("uid = $uid")) {
            $rs = self::db()->upById($record['id'], $data);
        } else {
            $rs = self::db()->add($data);
        }
        return $rs;
    }

}