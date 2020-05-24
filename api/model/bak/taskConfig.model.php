<?php

/**
 *
 * 任务配置
 */
class TaskConfigModel {
    static $_table = 'task_config';
    static $_pk = 'id';
    static $_db_key =DEF_DB_CONN;
    static $_db = null;

    const DAILY_TYPE = 1;
    const GROWNUP_TYPE = 2;

    const FIX_TASK = 1;
    const RANDOM_TASK = 2;

    const OFF_TRUE = 1;
    const OFF_FALSE = 0;

    static function getRandomDailyTask($num = 3){
        $sql = "select * from ".self::$_table." where type = ".self::DAILY_TYPE. " and  type_sub =".self::RANDOM_TASK . " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return self::db()->getAllBySQL($sql);
    }

    static function getFixDailyTask($num = 2){
        $sql = "select * from ".self::$_table." where type = ".self::DAILY_TYPE. " and  type_sub =".self::FIX_TASK. " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return self::db()->getAllBySQL($sql);
    }

    static function getFixGrownupTask($num = 3){
        $sql = "select * from ".self::$_table." where type = ".self::GROWNUP_TYPE. " and  type_sub =".self::FIX_TASK. " and is_off = ".self::OFF_FALSE." limit 0,$num";
        return self::db()->getAllBySQL($sql);
    }

    static  function getAllGrouwnupTask(){
        $sql = "select * from ".self::$_table." where type = ".self::GROWNUP_TYPE." and is_off = ".self::OFF_FALSE;
        return self::db()->getAllBySQL($sql);
    }

    static function getByIds($ids){
        $sql = "select * from ".self::$_table." where id in ($ids) ";
        return self::db()->getAllBySQL($sql);
    }


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic(self::$_db_key,self::$_table,self::$_pk);
        return self::$_db;
    }

    static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }


}
