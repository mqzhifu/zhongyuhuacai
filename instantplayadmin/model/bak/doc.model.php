<?php

/**
 * @Author: Kir
 * @Date:   2019-02-14 16:38:49
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-02-15 11:53:08
 */

class DocModel {
    static $_table = 'open_doc';
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
     * @param $docIds
     * @param $updata
     * @return int
     */
    public static function editDoc($docIds, $upData, $limits){
        $result = self::db()->update($upData, "id in ($docIds) limit $limits ;");
        return $result;
    }

}