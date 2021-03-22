<?php
class HouseModel {
	static $_table = 'house';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key =DB_CONN_DEFAULT;


    const FITMENT_ROUGH = 1;
    const FITMENT_SIMPLE = 2;
    const FITMENT_DELICACY = 3;
    const FITMENT_LUXURY = 4;
    const FITMENT_UNKNOW = 5;
    const FITMENT_DESC = [
        self::FITMENT_ROUGH => "毛坯",
        self::FITMENT_SIMPLE => "简装",
        self::FITMENT_DELICACY => "精装",
        self::FITMENT_LUXURY => "豪华",
        self::FITMENT_UNKNOW => "未知",
    ];

    const STATUS_INIT = 4;
    const STATUS_WAIT = 1;
    const STATUS_USED = 2;
    const STATUS_CLOSE = 3;

    const STATUS = [
        self::STATUS_WAIT => "待租/售",
        self::STATUS_USED => "出租中",
        self::STATUS_CLOSE => "关闭",
        self::STATUS_INIT => "等待房主订单",
    ];

    const DIRECTION_EAST  = 1;
    const DIRECTION_SOUTH = 2;
    const DIRECTION_WEST  = 3;
    const DIRECTION_NORTH = 4;
    const DIRECTION_UNKNOW = 5;
    const DIRECTION_DESC = [
        self::DIRECTION_EAST => "东",
        self::DIRECTION_SOUTH => "南",
        self::DIRECTION_WEST => "西",
        self::DIRECTION_NORTH => "北",
        self::DIRECTION_UNKNOW => "未知",
    ];

    static function upStatus($oid,$status,$data = []){
        $upData = array("status"=>$status,'u_time'=>time());
        if ($data){
            $upData = array_merge($upData,$data);
        }
        return self::db()->upById($oid,$upData);
    }

    static function getStatusSelectOptionHtml(){
        $html = "";
        foreach (self::STATUS as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getDirectionSelectOptionHtml(){
        $html = "";
        foreach (self::DIRECTION_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getFitmentSelectOptionHtml(){
        $html = "";
        foreach (self::FITMENT_DESC as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
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
	
	static function login($uname,$ps){
//	    var_dump(" uname = '$uname' and ps = '$ps'");
		return self::db()->getRow(" uname = '$uname' and ps = '$ps'");
	}
	
	static function getName($uname){
		return self::db()->getRow(" uname = '$uname' ");
	}

	static function getOption(){
        $list = self::db()->getAll(1);
        $str = "";
        foreach($list as $k=>$v){
            $str.= "<option value='{$v['id']}'>{$v['nickname']}</option>";
        }

        return $str;
    }

    static function getFieldById($adminUid,$field){
	    if(!$adminUid){
            return "--";
        }
        $user = self::db()->getById($adminUid);
        if($user[$field]){
            return $user[$field];
        }

        return "默认昵称";
    }

}