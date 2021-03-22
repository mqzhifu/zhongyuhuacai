<?php
class OrderPayListModel {
	static $_table = 'orders_pay_list';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    const PAY_WX_H5 = 11;
    const PAY_WX_H5_NATIVE = 12;
    const PAY_WX_PC = 13;
    const PAY_WX_APP_ANDROID = 14;

    const PAY_ALI_H5 = 21;
    const PAY_ALI_H5_NATIVE = 22;
    const PAY_ALI_PC = 23;
    const PAY_ALI_APP_ANDROID = 24;
    const PAY_BANK = 3;

    const PAY_TYPE_DESC = [
        self::PAY_WX_H5=>"微信-H5-普通浏览器",
        self::PAY_WX_H5_NATIVE=>"微信-H5-内部浏览器",
        self::PAY_WX_PC=>"微信-PC端",
        self::PAY_WX_APP_ANDROID=>"微信-APP-SDK-安卓",

        self::PAY_ALI_H5=>"支付宝-H5-普通浏览器",
        self::PAY_ALI_H5_NATIVE=>"支付宝-H5-内部浏览器",
        self::PAY_ALI_PC=>"支付宝-PC端",
        self::PAY_ALI_APP_ANDROID=>"支付宝-APP-SDK-安卓",
        self::PAY_BANK=>"银行转账",

    ];

    const STATUS_WAIT = 1;
    const STATUS_OK = 2;

    const STATUS_DESC = [
        self::STATUS_WAIT=>"未处理",
        self::STATUS_OK=>"已支付",
    ];

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

    static function upStatus($oid,$status,$data = []){
        $upData = array("status"=>$status,'u_time'=>time());
        if ($data){
            $upData = array_merge($upData,$data);
        }
        return self::db()->upById($oid,$upData);
    }

}