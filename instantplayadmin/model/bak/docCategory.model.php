<?php

/**
 * @Author: Kir
 * @Date:   2019-02-14 16:38:49
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-03-13 14:55:43
 */

class DocCategoryModel {
    static $_table = 'open_doc_category';
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


    static function getCategoryDesc() {
        $desc = [];
        $categories = self::db()->getAll();
        foreach ($categories as $cate) {
            $desc[$cate['id']] = $cate['name'];
        }
        return $desc;
    }

    public static function getCategoryInfo(){
        $sql = "SELECT * FROM ".self::$_table." ;";
        $result = self::db()->query($sql);
        if(!empty($result) && is_array($result)){
            return $result;
        }else{
            return [];
        }
    }

    public static function recoverDoc($sql){
        $result = self::db()->query($sql);
        if(!empty($result) && is_array($result)){
            return $result;
        }else{
            return [];
        }
    }


}