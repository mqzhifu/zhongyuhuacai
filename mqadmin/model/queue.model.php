<?php
class QueueModel {
	static $_table = 'queue';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";


	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

    static function getOption(){
        $list = self::db()->getAll(1);
        $str = "";
        foreach($list as $k=>$v){
            $str.= "<option value='{$v['id']}'>{$v['name']}</option>";
        }

        return $str;
    }
}