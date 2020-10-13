<?php
class ProductTbModel {
	static $_table = 'product_tb';
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

    static function addReq($adminId,$cate,$sub,$ctrl,$ac){
	    $request = null;
	    if($_REQUEST){
	        foreach ($_REQUEST as $k=>$v) {
                if(strpos($k,'columns') !== false){
                    continue;
                }
                $request[$k] = $v;
	        }
        }
        $data = array(
        	'cate'=>$cate,
        	'sub'=>$sub,
            'ctrl'=>$ctrl,
            'ac'=>$ac,
            'a_time'=>time(),
            'ip'=>get_client_ip(),
            'request'=>json_encode($request),
            'admin_uid'=>$adminId
        );

        $id = self::db()->add($data);
        return $id;
    }
}