<?php

class ProductService{
    public $limit = 10;

    const ORDER_TYPE =array(
        1=> array('id'=>1,"name"=>'默认1','field'=>"id"),
        2=> array('id'=>2,"name"=>'新品2','field'=>"id"),
        3=> array('id'=>3,"name"=>'销量3','field'=>"user_buy_total"),
        4=> array('id'=>4,"name"=>'价格4','field'=>'lowest_price'),
    );



    function getListByDb($where,$start,$limit ,$order = ""){
        return ProductModel::db()->getAll($where  . ProductModel::getDefaultOrder() . $order . " limit $start,$limit"   );
    }
    function getListCntByDb($where){
        return ProductModel::db()->getCount($where );
    }

    function getRecommendList($page,$limit,$type){
        if($type == 1){
            $where = " recommend_detail = ".ProductModel::RECOMMEND_TRUE;
        }else{
            $where = " recommend = ".ProductModel::RECOMMEND_TRUE;
        }

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

        if(arrKeyIssetAndExist($product,'desc_attr')){
           $tmp = json_decode($product['desc_attr'],true);
           $str = null;
           foreach ($tmp as $k=>$v){
               $str[] = array("key"=>$k,'value'=>$v);
           }
            $product['desc_attr_format'] = $str;
        }else{
            $product['desc_attr_format'] = "";
        }
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


    function getUserHistoryPVList($pid){
        if(!$pid){
            exit("pid is null");
        }
        $list = UserProductLogModel::db()->getAll(" pid = $pid group by uid order by a_time desc limit 20");
        if(!$list){
            return out_pc(200,$list);
        }
        $service =  new UserService();

        foreach ($list as $k=>$v){
            $list[$k]['nickname'] = '游客'.$k;
            $list[$k]['avatar'] = get_avatar_url("");
            $user = UserModel::db()->getById($v['uid']);
            if($user){
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = $user['avatar'];
            }
        }
        return out_pc(200,$list);
    }

    function upPvUv($pid,$uid){
        $data = array("pv"=>array(1));
        $info = UserProductLogModel::db()->getRow("uid = {$uid} and pid = $pid");
        if(!$info){
            $data['uv'] =  array(1);
        }

        $d = array('pid'=>$pid,'uid'=>$uid,'a_time'=>time());
        UserProductLogModel::db()->add($d);

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

    function search($condition,$page = 1,$limit = 10){
        $where = " 1 = 1 ";
        if($condition){
//            return out_pc(8978);
            if(arrKeyIssetAndExist($condition,'keyword')){
                $where .=" and ( title like '%{$condition['keyword']}%' or `desc`  like '%{$condition['keyword']}%')";
            }

            if(arrKeyIssetAndExist($condition,'category')){
                $where .= " and category_id = {$condition['category']}";
            }

        }

        $cnt = ProductModel::db()->getAll($where);
        if(!$cnt){
            return  out_pc(200,null);
        }

        $order = "";
        if(arrKeyIssetAndExist($condition,'orderType')){
            $order .= " order by ".self::ORDER_TYPE[$condition['orderType']]['field'];
            if(arrKeyIssetAndExist($condition ,'orderUpDown')){
                $order .=  " {$condition['orderUpDown']}";
            }else{
                $order .=  " asc ";
            }
        }

        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);
        $where .= " limit $order {$pageInfo['start']} , {$pageInfo['end']}}";
        $list = ProductModel::db()->getAll($where);
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