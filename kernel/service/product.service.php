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

    function getOneDetail($id,$includeGoods,$uid){
        if(!$id){
            return out_pc(8072);
        }

        $stock = 0;
        $product = ProductModel::db()->getById($id);
        if(!$product){
            return out_pc(1026);
        }

        $goodsList = null;//商品及属性列表
        $productCategoryAttrParaData = null;//产品属性列表
        $goodsDb = GoodsModel::db()->getAll(" pid = $id");
        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE){
            $ProductLinkCategoryAttrDb = ProductLinkCategoryAttrModel::db()->getAll(" pid = $id");
            $categoryAttrParaIds = null;
            foreach ($ProductLinkCategoryAttrDb as $k=>$v){
                $categoryAttrParaIds[ $v['pca_id']][] = $v['pcap_id'];
            }


            foreach ($categoryAttrParaIds as $k=>$v){
                $row = ProductCategoryAttrModel::db()->getById($k);
                $para = null;
                foreach ($v as $k2=>$v2){
                    $para[] = ProductCategoryAttrParaModel::db()->getById($v2);
                }
                $row['category_attr_para'] = $para;
                $productCategoryAttrParaData[] = $row;
            }


            foreach ($goodsDb as $k=>$v){
                $stock += $v['stock'];
                $row = $v;
                $row['category_attr_para'] = GoodsLinkCategoryAttrModel::db()->getAll(" gid = {$v['id']}");
                $goodsList[]= $row;
            }
        }

        $product['pcap'] = $productCategoryAttrParaData;
        $product['goods_list'] = $goodsList;
        $product['stock'] = $stock;

        //处理 产品 属性-参数
//        $attribute = $product['attribute'];
//        //该产品所有包含的属性参数，但是不一定每种属性组合都有商品(库存)
//        $attribute = json_decode($attribute,true);
//        $categoryAttrArr = null;
//        $categoryAttrParaArr = null;
//        foreach ($attribute as $k=>$v){
//            $categoryAttr = ProductCategoryAttrModel::db()->getById($k);
//            $categoryAttrArr[$categoryAttr['id']] = $categoryAttr;
//            $tmpArr = null;
//            foreach ($v as $k2=>$v2){
//                $tmpArr[] = ProductCategoryAttrParaModel::db()->getById($v2);
//            }
//            $categoryAttrParaArr[$categoryAttr['id']] = $tmpArr;
//        }
//
//        $product['categoryAttrArr'] = $categoryAttrArr;
//        $product['categoryAttrParaArr'] = $categoryAttrParaArr;

//        if($includeGoods){
//            if($includeGoods == 2){
//                return out_pc(200,$product);
//            }
//        }
//        $goods = $this->getGoodsListByPid($id);
//        $product['goods_list'] = $goods;
        $this->upPvUv($id,$uid);
        $product = $this->formatRow($product);

        return out_pc(200,$product);
    }

    function upPvUv($pid,$uid){
        $data = array("pv"=>array(1));
        $info = UserProductLogModel::db()->getRow("uid = {$uid} and pid = $pid");
        if(!$info){
            $d = array('pid'=>$pid,'uid'=>$uid,'a_time'=>time());
            UserProductLogModel::db()->add($d);
            $data['uv'] =  array(1);
        }

        $rs = ProductModel::db()->upById($pid,$data);
        return $rs;
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
        if(!$keyword){
            return out_pc(8976);
        }
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
            $row = $this->formatRow($v);
            $data[] = $row;
        }

        return $data;
    }

    function formatRow($row){
        if(arrKeyIssetAndExist($row,'pic')){
            $picsTmpUrl = explode(",",$row['pic']);
            $realUrl = "";
            foreach ($picsTmpUrl as $k=>$v){
                $realUrl .=get_product_url($v) . ",";
            }
            $realUrl = substr($realUrl,0,strlen($realUrl)-1);
            $row['pic'] = $realUrl;
        }

        return $row;
    }

    function addOne($data,$categoryAttrNull = 0,$categoryAttrPara = []){
        $attribute = [];
        if($categoryAttrNull){
            $attribute[$categoryAttrNull] = [];
            $data['category_attr_null'] = ProductModel::CATE_ATTR_NULL_TRUE;
        }else{
            foreach ($categoryAttrPara as $k=>$v) {
                $tmp = explode("_",$v );
                $attribute[$tmp[0]][] = $tmp[1];
            }
            $data['category_attr_null'] = ProductModel::CATE_ATTR_NULL_FALSE;
        }

        $data['attribute'] = json_encode($attribute);
        if(!arrKeyIssetAndExist($data,'recommend')){
            $data['recommend'] = 2;
        }
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
        }else{
            $noParaAttr = ProductCategoryAttrModel::db()->getRow(" pc_id = {$data['category_id']} and is_no = ".ProductCategoryAttrModel::NO_ATTR_TRUE);
            $data = array(
                'pid'=>$addId,
                'pc_id'=>$data['category_id'],
                'pca_id'=>$noParaAttr['id'],
                'pcap_id'=>0,
            );
            $newNewId = ProductLinkCategoryAttrModel::db()->add($data);
        }

//        $this->insertProductLinkGoods($addId);
        return $addId;
    }

    function goodsLinkProductPCAP($gid,$pid,$categoryId,$categoryAttrId,$categoryAttrParaId = 0){
//        if(!$categoryAttrParaId){//证明是个空属性
//        }else{
//        }

        $data = array(
            'gid'=>$gid,
            'pc_id'=>$categoryId,
            'pca_id'=>$categoryAttrId,
            'pcap_id'=>$categoryAttrParaId
        );
        $rs = GoodsLinkCategoryAttrModel::db()->add($data);
    }

//    function insertProductLinkGoods($pid){
//        $product = ProductModel::db()->getById($pid);
//        var_dump($product['category_attr_null']);
//        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_TRUE){
//            $noParaAttr = ProductCategoryAttrModel::db()->getRow(" pc_id = {$product['category_id']} and is_no = ".ProductCategoryAttrModel::NO_ATTR_TRUE);
//            var_dump($noParaAttr);
//            $exist = ProductLinkCategoryAttrModel::db()->getRow(" pid = $pid and pc_id = {$product['category_id']} and  'pca_id'={$noParaAttr['id']}");
//            var_dump($exist);
//            if(!$exist){
//                $data = array(
//                    'pid'=>$pid,
//                    'gid'=>0,
//                    'pc_id'=>$product['category_id'],
//                    'pca_id'=>$noParaAttr['id'],
//                    'pcap_id'=>0,
//                );
//                $newId = ProductLinkCategoryAttrModel::db()->add($data);
//                var_dump($newId);exit;
//            }
//        }else{
//            foreach ( $product['attribute'] as $k=>$v) {
//                $exp = explode("_",$v);
//                $categoryAttr = $exp[0];
//                $categoryAttrPara = $exp[1];
//                $addData = array(
//                    'pid'=>$pid,
//                    'gid'=>0,
//                    'pc_id'=>$product['category_id'],
//                    'pca_id'=>$categoryAttr,
//                    'pcap_id'=>$categoryAttrPara,
//                );
//                ProductLinkCategoryAttrModel::db()->add($addData);
//            }
//        }
//    }
}