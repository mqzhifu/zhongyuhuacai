<?php

class GoodsModel {
    static $_table = 'goods';
    static $_pk = 'id';
    static $_db = null;
    static $_db_key = "instantplay";


    const STATUS_ON = 2;
    const STATUS_OFF = 1;
    const STATUS = [
        self::STATUS_ON => "上架",
        self::STATUS_OFF => "下架",
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

    static function getStatusDescById($id){
        return self::STATUS[$id];
    }

    static function getStatusSelectOptionHtml(){
        $html = "";
        foreach (self::STATUS as $k=>$v) {
            $html .= "<option value='{$k}'>{$v}</option>";
        }
        return $html;
    }

    static function getListByPid($pid){
        return self::db()->getAll(" pid = $pid ");
    }

    static function getField(){
        $rs =  self::db()->getFieldsByTable();
        return $rs;
    }

    static function addOne($data,$product,$attrPara,$upTotal = 1){
        $product_attr_ids = "";
        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE){
            $product_attr_ids = "";
            foreach ($attrPara as $k=>$v) {
                $product_attr_ids .= $k . "-" .$v . ",";
            }
            $product_attr_ids = substr($product_attr_ids,0,strlen($product_attr_ids)-1);
        }else{
            //这是特殊情况，产品无参数
        }

        if(arrKeyIssetAndExist($data,'sale_price')){
            $salePrice = $data['sale_price'];
            if($salePrice > 0 && int($salePrice) > 0){
                $data['sale_price'] = yuanToFen($salePrice);
            }
        }

        if(arrKeyIssetAndExist($data,'original_price')){
            $price = $data['original_price'];
            if($price > 0 &&  (int)$price > 0){
                $data['original_price'] = yuanToFen($price);
            }
        }

        if(arrKeyIssetAndExist($data,'sale_price')){
            $salePrice = $data['sale_price'];
            if($salePrice > 0 && (int)$salePrice > 0){
                $data['sale_price'] = yuanToFen($salePrice);
            }
        }


        $data['pid'] = $product['id'];
        $data['product_attr_ids'] = $product_attr_ids;
        $newId =  self::db()->add($data);
        if($upTotal){
            ProductModel::upTotal($product['id']);
//            ProductModel::upLowestPriceByGoods($product['id']);
        }
        $productService = new ProductService();
        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_TRUE){
            foreach ($attrPara as $k=>$v){
                $key = $k;
            }
            $productService->goodsLinkProductPCAP($newId,$product['id'],$product['category_id'],$key);
        }else{
            foreach ($attrPara as $k=>$v) {
                $productService->goodsLinkProductPCAP($newId,$product['id'],$product['category_id'],$k,$v);
            }
        }


        return $newId;

    }
}