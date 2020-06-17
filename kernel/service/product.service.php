<?php

class ProductService{
    public $limit = 10;
    private $originalPricePercent = 10;
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
        $returnPageInfo = array(
            'page'=>$page,
            'limit'=>$limit,
            'record_cnt'=>0,
            'page_cnt'=>0,
            'list'=>null,
        );


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
        $list = $this->formatShow($list);

        $returnPageInfo['page'] = $page;
        $returnPageInfo['limit'] = $limit;
        $returnPageInfo['page_cnt'] = $pageInfo['totalPage'];
        $returnPageInfo['record_cnt'] = $cnt;
        $returnPageInfo['list'] = $list;


        return out_pc(200,$returnPageInfo);
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
        if(!$goodsDb){
            return out_pc(8979);
        }
        //产品 属性参数  是否为空
        if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE){
            //获取一个产品的，所有类型 属性 参数
            $ProductLinkCategoryAttrDb = ProductLinkCategoryAttrModel::db()->getAll(" pid = $id");
            if(!$ProductLinkCategoryAttrDb){
                return out_pc(8980);
            }
            //格式化 属性参数   以ID 形式
            //属性1 =》  N个参数  ,属性2 =》  N个参数
            $categoryAttrParaIds = null;
            foreach ($ProductLinkCategoryAttrDb as $k=>$v){
                $categoryAttrParaIds[ $v['pca_id']][] = $v['pcap_id'];
            }

            foreach ($categoryAttrParaIds as $k=>$v){

                //这里是方便测试，如果一个产品的一个属性，下面有太多的参数选项，会导致产品详情页爆了.
                if(count($v ) >5){
                    $tmp = null;
                    foreach ($v as $k2=>$v2){
                        if($k2 >5 ){
                            break;
                        }
                        $tmp[] = $v2;
                    }
                    $categoryAttrParaIds[$k] = $tmp;
                }
            }

            //将ID形式 转换成 多维数组，主要是为了获取汉字描述
            foreach ($categoryAttrParaIds as $k=>$v){
                //先获取分类属性的  一条记录值
                $row = ProductCategoryAttrModel::db()->getById($k);
                $para = null;
                //再获取该属性下的所有参数的值
                foreach ($v as $k2=>$v2){
                    $para[] = ProductCategoryAttrParaModel::db()->getById($v2);
                }
                $row['category_attr_para'] = $para;
                $productCategoryAttrParaData[] = $row;
            }
//            //遍历该产品下的所有商品列表
            foreach ($goodsDb as $k=>$v){
                $stock += $v['stock'];
//                $row = $v;
//                //获取每个商品对应的  分类属性参数
//                $row['category_attr_para'] = GoodsLinkCategoryAttrModel::db()->getAll(" gid = {$v['id']}");
//                $goodsList[]= $row;
            }
        }

        $product['pcap'] = $productCategoryAttrParaData;
//        $product['goods_list'] = $goodsList;
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

        $orderService = new OrderService();
        $product['cart_num'] = $orderService->getUserCartNum($uid)['msg'];


        $upService = new UpService();
        $collectService = new CollectService();

        $hasLiked = 0;
        if($upService->exist($id,$uid)){
            $hasLiked = 1;
        }

        $hasCollect = 0;
        if($collectService->exist($id,$uid)){
            $hasCollect = 1;
        }

        $product['has_collect'] = $hasCollect;
        $product['has_up'] = $hasLiked;



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

        $product['original_price'] = $product['lowest_price'] * $this->originalPricePercent;

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
                $list[$k]['avatar'] = get_avatar_url($user['avatar']);
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
        if(!$list){
            return $list;
        }
        $data = null;
        foreach ( $list as $k=>$v){
            $pic = "";
            if(arrKeyIssetAndExist($v,'pic')){
                $pic = explode(",",$v['pic']);
                $pic = get_product_url($pic[0]);
            }


            $row = array(
                'id'=>$v['id'],'goods_total'=>$v['goods_total'],'pic'=>$pic,
                'lowest_price'=>$v['lowest_price'],'title'=>$v['title'],'user_buy_total'=>$v['user_buy_total'],
            );

            if(isset($v['has_cart'])){
                $row['has_cart'] = $v['has_cart'];
            }

            $data[] = $row;
        }
        return $data;
    }

    function getGoodsListByPid($pid){
        return GoodsModel::db()->getAll(" pid = $pid ");
    }

    function search($condition,$page = 1,$limit = 10 ,$uid = 0){
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
        LogLib::inc()->debug($where);
        $cnt = self::getListCntByDb($where);
        if(!$cnt){
            return  out_pc(200,null);
        }

        $order = "";
        if(arrKeyIssetAndExist($condition,'orderType')){
            $order .= " order by ".self::ORDER_TYPE[$condition['orderType']]['field'];
            if(arrKeyIssetAndExist($condition ,'orderUpDown')){
                $order .=  " desc ";

            }else{
                $order .=  " asc ";
            }
        }

        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);
        $where .= " $order limit {$pageInfo['start']} , {$pageInfo['end']}  ";
        $list = ProductModel::db()->getAll($where);



        $list = $this->format($list);

        if($uid){
            foreach ($list as $k=>$v){
                $userHasCart = 0;
                $dbRs = CartModel::db()->getRow(" pid = {$v['id']} and uid = $uid");
                if($dbRs){
                    $userHasCart = 1;
                }
                $list[$k]['has_cart'] = $userHasCart;
            }
        }


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
                //data-lazy-src=\"https:\/\/cbu01.alicdn.com\/img\/ibank\/2019\/972\/967\/10464769279_1593857209.jpg,https:\/\/cbu01.alicdn.com\/cms\/upload\/other\/lazyload.png\"
                //这里有个BUG,应该是正则匹配的时候，出的问题，先不处理
                if(strpos($v,'src') === false){
                    $realUrl .=get_product_url($v) . ",";
                }
//                $realUrl .=get_product_url($v) . ",";
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

        out(" productService addOne,so add product is ok ~id:$addId , next add ProductLinkCategoryAttrModel");
        if($categoryAttrPara){
            foreach ( $categoryAttrPara as $k=>$v) {
                $exp = explode("_",$v);
                $categoryAttr = $exp[0];
                $categoryAttrPara = $exp[1];

                $exist = ProductLinkCategoryAttrModel::db()->getRow(" pid = $addId and pca_id = $categoryAttr and pcap_id = $categoryAttrPara");
                if($exist){
                    continue;
                }
                
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