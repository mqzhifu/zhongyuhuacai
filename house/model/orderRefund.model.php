<?php
class OrderRefundModel {
	static $_table = 'order_refund';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";

    const PAY_WX_H5 = 11;
    const PAY_WX_H5_NATIVE = 12;
    const PAY_WX_PC = 13;
    const PAY_WX_APP_ANDROID = 14;

    const PAY_ALI_H5 = 21;
    const PAY_ALI_H5_NATIVE = 22;
    const PAY_ALI_PC = 23;
    const PAY_ALI_APP_ANDROID = 24;


	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	static function getNo(){
	    return uniqid(time());
    }

	static function getStatusOptions(){
	    $html = "";
	    foreach (self::STATUS_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
	    }
        return $html;
    }

    static function getPayTypeOptions(){
        $html = "";
        foreach (self::PAY_TYPE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getSomePayTypeDesc($payTypeIds ){
	    if(!is_array($payTypeIds)){
            $payTypeIds = explode(",",$payTypeIds);
        }

        $rs = array();
        foreach ($payTypeIds as $k=>$v) {
            $rs[$v] = self::PAY_TYPE_DESC[$v];
        }
        return $rs;

    }

    static function getListByUid($uid){
	    $list = self::db()->getAll(" uid = $uid");
	    return $list;
    }

    static function getListByAgentId($aid){
        $list = self::db()->getAll(" agent_id = $aid");
        return $list;
    }


}