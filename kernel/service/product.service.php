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

    function formatShow($list){
        $data = null;
        foreach ( $list as $k=>$v){
            $pic = "";
            if(arrKeyIssetAndExist($v,'pic')){
                $pic = explode(",",$v['pic']);
                $pic = get_product_url($pic[0]);
            }
            $data[] = array( 'id'=>$v['id'],'goods_total'=>$v['goods_total'],'pic'=>$pic,'lowest_price'=>$v['lowest_price'],'title'=>$v['title'],'user_buy_total'=>$v['user_buy_total']);
        }
        return $data;
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