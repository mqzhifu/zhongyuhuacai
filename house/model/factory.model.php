<?php
class FactoryModel {
	static $_table = 'factory';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";

    const STATUS_WAIT = 1;
    const STATUS_OK = 2;
    const STATUS_REJECT  = 3;
    const STATUS_DESC = [
        self::STATUS_WAIT=>'等待审核',
        self::STATUS_OK=>'通过',
        self::STATUS_REJECT=>'驳回',
    ];

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
            $html .= "<option value='$k'>$v</option>";
	    }
        return $html;
    }

}