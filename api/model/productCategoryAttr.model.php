<?php
class ProductCategoryAttrModel {
	static $_table = 'product_category_attr';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";

    const NO_ATTR_TRUE = 1;
    const NO_ATTR_FALSE = 2;

	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	static function getRowByNameByCategoryId($name,$categoryId){
        $categoryAttrDb = ProductCategoryAttrModel::db()->getRow(" name = '$name' and pc_id = {$categoryId}");
        return $categoryAttrDb;
    }

}