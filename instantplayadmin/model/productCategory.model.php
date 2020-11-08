<?php
class ProductCategoryModel {
	static $_table = 'product_category';
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

	static function getSelectOptionHtml(){
        $list = self::db()->getAll();
        if(!$list){
            return "";
        }
        $html = "";
        foreach ($list as $k=>$v) {
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";
        }
        return $html;
    }

    static function getNameById($id){
        $list = self::db()->getById($id);
        if(!$list){
            return "--";
        }

        return $list['name'];

    }

}