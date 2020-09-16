<?php
class AreaCountyModel {
	static $_table = 'area_county';
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

    static function getJsSelectOptions(){
        self::db()->_fetchArray = 1;
        $data = self::db()->getAll(" 1 order by sort ","","city_code, code,short_name,code");
        self::db()->_fetchArray = 0;
        $rs = [];
        foreach ($data as $k=>$v) {
            $row = $v;
            unset($row[0]);
//            $proviceCode = substr($v[0],0,2) . "0000";
//            $rs[$proviceCode][$v[0]][] = $row;
            $rs[$v[0]][] = $row;
        }
        return $rs;
    }

    static function getNameByCode($code){
        $row = self::db()->getRow(" code = '$code'");
        return $row['short_name'];
    }
}