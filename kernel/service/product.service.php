<?php

class ProductService{
    public $limit = 10;

    function getListByDb($where,$start,$limit){
        return ProductModel::db()->getAll($where  . ProductModel::getDefaultOrder() . " limit $start,$limit"   );
    }
    function getListCntByDb($where){
        return ProductModel::db()->getCount($where );
    }

    function getRecommendList($page,$limit){
        $where = " recommend = ".ProductModel::RECOMMEND_TRUE;
        $cnt = $this->getListCntByDb($where);
        if(!$cnt){
            return  out_pc(200,null);
        }

        if(!$limit){
            $limit = $this->limit;
        }

        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);
        $list = $this->getListByDb($where,$pageInfo['start'],$pageInfo['end']);
        $list = $this->format($list);
        return out_pc(200,$list);
    }

    function getListByCategory($categoryId,$page,$limit){
        $where = " category_id = ".$categoryId;
        $cnt = $this->getListCntByDb($where);
        if(!$cnt){
            return  out_pc(200,null);
        }

        if(!$limit){
            $limit = $this->limit;
        }

        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);
        $list = $this->getListByDb($where,$pageInfo['start'],$pageInfo['end']);
        $list = $this->format($list);
        return out_pc(200,$list);


//        $list = ProductModel::getListByCategory($categoryId);
//        $list = $this->format($list);
//        return out_pc(200,$list);
    }

    function getOneDetail($id,$includeGoods){
        if(!$id){
            return out_pc(8072);
        }

        $product = ProductModel::db()->getById($id);
        if(!$product){
            return out_pc(1026);
        }



        //处理 产品 属性-参数
        $attribute = $product['attribute'];
        $attribute = json_decode($attribute,true);
        $categoryAttrArr = null;
        $categoryAttrParaArr = null;
        foreach ($attribute as $k=>$v){
            $categoryAttr = ProductCategoryAttrModel::db()->getById($k);
            $categoryAttrArr[$categoryAttr['id']] = $categoryAttr;
            $tmpArr = null;
            foreach ($v as $k2=>$v2){
                $tmpArr[] = ProductCategoryAttrParaModel::db()->getById($v2);
            }
            $categoryAttrParaArr[$categoryAttr['id']] = $tmpArr;
        }

        $product['categoryAttrArr'] = $categoryAttrArr;
        $product['categoryAttrParaArr'] = $categoryAttrParaArr;
        //处理 产品 属性-参数



        if($includeGoods){
            if($includeGoods == 2){
                return out_pc(200,$product);
            }
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