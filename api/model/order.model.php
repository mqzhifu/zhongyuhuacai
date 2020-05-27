<?php
class OrderModel {
	static $_table = 'orders';
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


    const PAY_TYPE_DESC = [
        self::PAY_WX_H5=>"微信-H5-普通浏览器",
        self::PAY_WX_H5_NATIVE=>"微信-H5-内部浏览器",
        self::PAY_WX_PC=>"微信-PC端",
        self::PAY_WX_APP_ANDROID=>"微信-APP-SDK-安卓",

        self::PAY_ALI_H5=>"支付宝-H5-普通浏览器",
        self::PAY_ALI_H5_NATIVE=>"支付宝-H5-内部浏览器",
        self::PAY_ALI_PC=>"支付宝-PC端",
        self::PAY_ALI_APP_ANDROID=>"支付宝-APP-SDK-安卓",
    ];

    const STATUS_WAIT_PAY = 1;
    const STATUS_PAYED = 2;
    const STATUS_CANCEL = 3;
    const STATUS_TIMEOUT = 4;
    const STATUS_TRANSPORT = 5;
    const STATUS_FINISH = 6;
    const STATUS_REFUND= 7;
    const STATUS_REFUND_FINISH= 8;

    const STATUS_DESC = [
        self::STATUS_WAIT_PAY=>"未支付",
        self::STATUS_PAYED=>"已支付",
        self::STATUS_CANCEL=>"用户取消",
        self::STATUS_TIMEOUT=>"超时取消",
        self::STATUS_TRANSPORT=>"运输中",
        self::STATUS_FINISH=>"已完成",
        self::STATUS_REFUND=>"退款中",
        self::STATUS_REFUND_FINISH=>"退款完成",
    ];

    const WITHDRAW_MONEY_STATUS_WAIT = 1;
    const WITHDRAW_MONEY_STATUS_OK = 2;
    const WITHDRAW_MONEY_STATUS = [
        self::WITHDRAW_MONEY_STATUS_WAIT =>"未操作",
        self::WITHDRAW_MONEY_STATUS_OK =>"已提现",
    ];

    const WITHDRAW_MONEY_AGENT_WAIT = 1;
    const WITHDRAW_MONEY_FACTORY_WAIT = 1;

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

    static function getListByUid($uid){
	    $list = self::db()->getAll(" uid = $uid");
	    return $list;
    }

    static function getListByAgentId($aid){
        $list = self::db()->getAll(" agent_uid = $aid");
        return $list;
    }


}