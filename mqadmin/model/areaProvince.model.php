<?php
class AreaProvinceModel {
	static $_table = 'area_province';
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

    static function getSelectOptionsHtml(){
	    $list = self::db()->getAll(1,self::$_table,"code,short_name");
	    $html = "";
	    foreach ($list as $k=>$v) {
            $html .= "<option value='{$v['code']}'>{$v['short_name']}</option>";
	    }
	    return $html;
    }

    static function getNameByCode($code){
        $row = self::db()->getRow(" code = '$code'");
        return $row['short_name'];
    }

//    static function getJsSelectOptions(){
//        $rs = AreaProvinceModel::db()->getAll(" 1 order by sort ","","code,short_name");
//        var_dump(array_values($rs));exit;
//    }



}