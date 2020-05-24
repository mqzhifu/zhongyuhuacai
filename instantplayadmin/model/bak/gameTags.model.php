<?php

/**
 * @Author: xuren
 * @Date:   2019-06-04 11:02:15
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-04 15:29:08
 */
class GameTagsModel {
    static $_table = 'game_tags';
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
     * 获取所有tags详细信息
     * @return [type] [description]
     */
    static function getAllTagsInfo(){
    	return self::db()->getAll();
    }

    

}