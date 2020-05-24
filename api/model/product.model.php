<?php
class ProductModel {
	static $_table = 'product';
	static $_pk = 'id';
	static $_db = null;
    static $_db_key = "instantplay";

    const STATUS_ON = 2;
    const STATUS_OFF = 1;
    const STATUS = [
        self::STATUS_ON => "上架",
        self::STATUS_OFF => "下架",
    ];

    const RECOMMEND_TRUE = 1;
    const RECOMMEND_FALSE = 2;
    const RECOMMEND = [
        self::RECOMMEND_TRUE => "是",
        self::RECOMMEND_FALSE => "否",
    ];

    static  $_field = array(
            'title'=>'',
            'subtitle'=>'',
            'desc'=>'',
            'brand'=>'',
            'attribute'=>'',
            'notice'=>'',
            'category_id'=>0,
            'status'=>1,
            'a_time'=>0,
            'lables'=>'',
            'admin_id'=>0,
            'pv'=>0,
            'uv'=>0,
            'recommend'=>2,
            'pic'=>'',
            'lowest_price'=>0,
            'factory_uid'=>1,
            'desc_attr'=>'',
            'spider_source_type'=>1,
            'spider_source_pid'=>"",
            'category_attr_null'=>2,
    );

    const SPIDER_TYPE_1688 = 1;

    const CATE_ATTR_NULL_TRUE = 1;
    const CATE_ATTR_NULL_FALSE = 2;


	static function db(){
		if(self::$_db)
			return self::$_db;

		self::$_db = new DbLib(self::$_db_key,self::$_table,self::$_pk);
		return self::$_db;
	}
	
	public static function __callStatic($func, $arguments){
		return call_user_func_array(array(self::db(),$func), $arguments);
	}

	static function getStatusDescById($id){
        return self::STATUS[$id];
    }

    static function upGoodsTotal($pid){
        $total = GoodsModel::db()->getCount(" pid = $pid");
        if(!$total){
            $rs = self::db()->upById($pid,array('goods_total'=>0));
        }else{
            $rs = self::db()->upById($pid,array('goods_total'=>$total));
        }

        return $rs;
    }

    static function upTotal($pid){
        self::upLowestPriceByGoods($pid);
        self::upGoodsTotal($pid);
    }

    static function upLowestPriceByGoods($pid){
	    $lowest = GoodsModel::db()->getRow(" pid = $pid order by sale_price asc limit 1");
	    if(!$lowest){
            $rs = self::db()->upById($pid,array('lowest_price'=>0));
        }else{
            $rs = self::db()->upById($pid,array('lowest_price'=>$lowest['sale_price']));
        }

        return $rs;
    }

    static function attrParaParserToName($attribute){
        $attribute = json_decode($attribute,true);
        $attributeArr = [];
        foreach ($attribute as $k=>$v) {
            $attrTmp = ProductCategoryAttrModel::db()->getById($k);
            $str = "";
            $attributeArr[$attrTmp['name']] = "";
            foreach ($v as $k2=>$v2) {
                $parTmp = ProductCategoryAttrParaModel::db()->getById($v2);
                $str .= $parTmp['name'] . " ";
            }
            $attributeArr[$attrTmp['name']] = $str;
        }
        return $attributeArr;
    }

    static function getStatusSelectOptionHtml(){
        $html = "";
        foreach (self::STATUS as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getRecommendOptionHtml(){
        $html = "";
        foreach (self::RECOMMEND as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getField(){
	    $rs =  self::db()->getFieldsByTable();
	    return $rs;
    }

    static function addOne($data,$categoryAttrNull = 0,$categoryAttrPara = []){
        $attribute = [];
        if($categoryAttrNull){
            $attribute[$categoryAttrNull] = [];
            $data['category_attr_null'] = 1;
        }else{
            foreach ($categoryAttrPara as $k=>$v) {
                $tmp = explode("_",$v );
                $attribute[$tmp[0]][] = $tmp[1];
            }
            $data['category_attr_null'] = 2;
        }

        $data['attribute'] = json_encode($attribute);

        $addId = ProductModel::db()->add($data);

        if($categoryAttrPara){
            foreach ( $categoryAttrPara as $k=>$v) {
                $exp = explode("_",$v);
                $categoryAttr = $exp[0];
                $categoryAttrPara = $exp[1];
                $addData = array(
                    'pid'=>$addId,
                    'pc_id'=>$data['category_id'],
                    'pca_id'=>$categoryAttr,
                    'pcap_id'=>$categoryAttrPara,
                );

                ProductLinkCategoryAttrModel::db()->add($addData);
            }
        }

        return $addId;
    }

    function getRecommendList(){
	    return self::db()->getAll(" recommend = ".self::RECOMMEND_TRUE . " order by sort desc");
    }

    function search($keyword){
        $data = self::db()->getAll(" title like '%$keyword%' or `desc` like '%$keyword%' ");
        return $data;
    }

}