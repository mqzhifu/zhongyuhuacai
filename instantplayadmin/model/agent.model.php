<?php
class AgentModel {
	static $_table = 'agent';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";


    const STATUS_AUDITING = 1;
    const STATUS_REJECT = 2;
    const STATUS_OK = 3;

    const STATUS = [
        self::STATUS_AUDITING => "审核中",
        self::STATUS_REJECT => "拒绝",
        self::STATUS_OK => "通过",
    ];

    const ROLE_LEVEL_ONE = 1;
    const ROLE_LEVEL_TWO = 2;
    const ROLE_FACTORY = 3;

    const ROLE = [
        self::ROLE_LEVEL_ONE=>"一级代理",
        self::ROLE_LEVEL_TWO=>"二级代理",
        self::ROLE_FACTORY=>"工厂",
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

    static function getStatusSelectOptionHtml(){
        $html = "";
        foreach (self::STATUS as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getAddrStrById($agentUid){
        $agent = self::db()->getById($agentUid);
        $province = AreaProvinceModel::getNameByCode($agent['province_id']);
        $city = AreaCityModel::getNameByCode($agent['city_id']);
        $county = AreaCountyModel::getNameByCode($agent['county_id']);
        $street = AreaStreetModel::getNameByCode($agent['towns_id']);
        $addrStr = $province . "-" .  $city  . "-" . $county  . "-" .$street . "-" .$agent['address'] ;
        return $addrStr;
    }
}