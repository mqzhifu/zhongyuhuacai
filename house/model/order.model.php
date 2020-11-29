<?php
class OrderModel {
	static $_table = 'orders';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = DB_CONN_DEFAULT;

    const TYPE_SALE = 1;
    const TYPE_TENANCY = 2;

    const TYPE_DESC = [
        self::TYPE_SALE=>"租赁",
        self::TYPE_TENANCY=>"售卖",
    ];
    const PAY_TYPE_MONTH = 1;
    const PAY_TYPE_QUARTER = 2;
    const PAY_TYPE_HALF_YEAR = 3;
    const PAY_TYPE_YEAR = 4;

    const PAY_TYPE_DESC = [
        self::PAY_TYPE_MONTH=>"月",
        self::PAY_TYPE_QUARTER=>"季",
        self::PAY_TYPE_HALF_YEAR=>"半年",
        self::PAY_TYPE_YEAR=>"整年",
    ];
    //支付类型 - 转换成具体的 天
    const PAY_TYPE_TURN_DAY = [
        self::PAY_TYPE_MONTH=>31,
        self::PAY_TYPE_QUARTER=>90,
        self::PAY_TYPE_HALF_YEAR=>183,
        self::PAY_TYPE_YEAR=>365,
    ];

    const CATE_MASTER = 1;
    const CATE_USER = 2;
    const CATE_DESC = [
        self::CATE_MASTER=>"房主",
        self::CATE_USER=>"用户",
    ];

    const FINANCE_INCOME = 1;
    const FINANCE_EXPENSE = 2;
    const FINANCE_DESC = [
        self::FINANCE_INCOME=>"收入",
        self::FINANCE_EXPENSE=>"支出",
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

    static function getTypeOptions(){
        $html = "";
        foreach (self::TYPE_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    static function getCateTypeOptions(){
        $html = "";
        foreach (self::CATE_DESC as $k=>$v) {
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

}