<?php
class OrderPayListRecordModel {
	static $_table = 'orders_pay_list_record';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    static function getPayTypeOptions(){
        $html = "";
        foreach (self::PAY_TYPE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }


	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

    static function getStatusOptions(){
        $html = "";
        foreach (self::STATUS_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

}