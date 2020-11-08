<?php
class ProductLinkCategoryAttrModel {
	static $_table = 'product_link_category_attr';
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

	static function getRelationFormatHtml($pid){
//        $relation = self::db()->getAll(" pid = $pid");
//        $productCategoryAttrParaGroup = array();
//        foreach ($relation as $k=>$v) {
//            $productCategoryAttrParaGroup[$v['pca_id']][] = $v['pcap_id'];
//        }
        $productCategoryAttrParaGroup = self::getAttrParaGroup($pid);
        $rs = array();
        foreach ($productCategoryAttrParaGroup as $k=>$v) {
            $productCatgoryAttr = ProductCategoryAttrModel::db()->getById($k);
            $row = $productCatgoryAttr;
            $collect = [];
            foreach ($v as $k2=>$v2) {
                $productCategoryAttrPara = ProductCategoryAttrParaModel::db()->getById($v2);
                $collect[] = $productCategoryAttrPara;
            }
            $row['para'] = $collect;

            $rs[] = $row;
        }

        return $rs;
    }

    static function getAttrParaGroup($pid){
        $relation = self::db()->getAll(" pid = $pid");
        $productCategoryAttrParaGroup = array();
        foreach ($relation as $k=>$v) {
            $productCategoryAttrParaGroup[$v['pca_id']][] = $v['pcap_id'];
        }

        return $productCategoryAttrParaGroup;
    }

}