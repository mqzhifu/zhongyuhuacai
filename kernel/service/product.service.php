<?php

class ProductService{

    function getRecommendList(){
        $list = ProductModel::getRecommendList();
        $list = $this->format($list);
        return out_pc(200,$list);
    }

    function getListByCategory($categoryId){
        $list = ProductModel::getListByCategory($categoryId);
        $list = $this->format($list);
        return out_pc(200,$list);
    }

    function getOneDetail($id,$includeGoods){
        if(!$id){
            return out_pc(8072);
        }

        $product = ProductModel::db()->getById($id);
        if(!$product){
            return out_pc(1026);
        }

        if($includeGoods == 2){
            return out_pc(200,$product);
        }


        $goods = $this->getGoodsListByPid($id);
        $product['goods_list'] = $goods;
        return out_pc(200,$product);
    }

    function getGoodsListByPid($pid){
        return GoodsModel::db()->getAll(" pid = $pid ");
    }

    function search($keyword){
        $list = ProductModel::search($keyword);
        $list = $this->format($list);
        return out_pc(200,$list);
    }

    function format($list){
        if(!$list){
            return $list;
        }

        $data = null;
        foreach ($list as $k=>$v){
            $row = $v;
            if(arrKeyIssetAndExist($v,'pic')){
                $row['pic'] = get_product_url($v['pic']);
            }
            $data[] = $row;
        }

        return $data;
    }
}