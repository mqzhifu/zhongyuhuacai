<?php

class ProductService{

    function getRecommendList(){
        $data = ProductModel::getRecommendList();
        out_ajax(200,$data);
    }

    function getListByCategory(){

    }

    function getDetail($request){
        $pid = get_request_one($request['oid'],0);
        if(!$pid){

        }

        $product = ProductModel::db()->getById($pid);
        if(!$product){

        }

        $goods = $this->getGoodsListByPid($pid);
        if(!$goods){

        }
    }

    function getGoodsListByPid($pid){
        return GoodsModel::db()->getAll(" pid = $pid ");
    }
}