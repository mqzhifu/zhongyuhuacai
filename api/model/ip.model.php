<?php
/**
 * Created by PhpStorm.
 * User: XiaHb.
 * Date: 2019/3/29
 * Time: 14:36
 */
class ipModel {
    static $_table = 'ip';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    /**
     * 获取对应的IP地址（省市）;
     * @param $ip
     * @return array|mixed
     */
    public static function getByIp($ip){
        $selectSql = "select  start_ip, end_ip, province, city FROM ip WHERE '{$ip}' >= start_ip AND '{$ip}' <= end_ip limit 1 ;";
        $res = ipModel::db()->query($selectSql);
        if(!empty($res) && is_array($res)){
            return $res[0];
        }else{
            return [];
        }
    }
}