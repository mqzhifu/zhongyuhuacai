<?php
/**
 * Created by PhpStorm.
 * User: XiaHB.
 * Date: 2019/5/6.
 * Time: 16:46.
 */

/**
 * Class issueAdminLogModel
 */
class issueGamesIncomeSummaryModel {
    static $_table = 'issue_games_income_summary';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = DEF_DB_CONN;


    static function db(){
        if(self::$_db)
            return self::$_db;

        self::$_db = DbLib::getDbStatic('',self::$_table,self::$_pk);
        return self::$_db;
    }

    public static function __callStatic($func, $arguments){
        return call_user_func_array(array(self::db(),$func), $arguments);
    }
}