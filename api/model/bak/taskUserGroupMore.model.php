<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/7/1
 * Time: 20:47
 */
class TaskUserGroupMoreModel {
    static $_table = 'task_user_group_';
    static $_pk = 'id';
    static $_db_key = 'kxgame_log';
    static $_db = null;

    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    /**
     * @param $day
     * @return string
     */
    static function getTableByDay($day){
        $table  = self::$_table . $day;
        return $table;
    }

    /**
     * @param $num_suffix
     * @return string
     */
    static function getTable($num_suffix){
        $table  = self::$_table . $num_suffix;
        return $table;
    }

    /**
     * @param $func
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }

    /**
     * @param $data
     * @param $num_suffix
     * @return int
     */
    static function add($data, $num_suffix){
        return self::db()->add($data,self::getTable($num_suffix));
    }

    /**
     * @param $id
     * @param $data
     * @param $num_suffix
     * @return int
     */
    static function upById($id, $data, $num_suffix){
        return self::db()->upById($id,$data,self::getTable($num_suffix));
    }

    /**
     * @param $where
     * @param $num_suffix
     * @param $field
     * @return mixed
     */
    static function getRow($where, $num_suffix , $field){
        return self::db()->getRow($where, self::getTable($num_suffix), $field);
    }

    /**
     * 暂时用不到，获取游戏排行榜的时候应该会用到;
     * @param $where
     * @param $num_suffix 游戏id末尾数字
     * @param $field
     * @return mixed
     */
    static function getAll($where, $num_suffix , $field){
        return self::db()->getRow($where, self::getTable($num_suffix), $field);
    }

}