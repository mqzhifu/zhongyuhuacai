<?php

class ProductService
{
    public $limit = 10;
    private $originalPricePercent = 0.1;
    const ORDER_TYPE = array(
        1 => array('id' => 1, "name" => '默认1', 'field' => "id"),
        2 => array('id' => 2, "name" => '新品2', 'field' => "id"),
        3 => array('id' => 3, "name" => '销量3', 'field' => "user_buy_total"),
        4 => array('id' => 4, "name" => '价格4', 'field' => 'lowest_price'),
    );


    function getListByDb($where, $start, $limit, $order = "")
    {
        return ProductModel::db()->getAll($where . ProductModel::getDefaultOrder() . $order . " limit $start,$limit");
    }

    function getListCntByDb($where)
    {
        return ProductModel::db()->getCount($where);
    }

    function getRecommendList($page = 1, $limit = 3, $type)
    {
        $returnPageInfo = array(
            'page' => $page,
            'limit' => $limit,
            'record_cnt' => 0,
            'page_cnt' => 0,
            'list' => null,
        );

        if ($type == 1) {
            $where = " recommend_detail = " . ProductModel::RECOMMEND_TRUE;
        } else {
            $where = " recommend = " . ProductModel::RECOMMEND_TRUE;
        }

        $cnt = $this->getListCntByDb($where);
        if (!$cnt) {
            return out_pc(200, null);
        }

        if (!$limit) {
            $limit = $this->limit;
        }

        $pageInfo = PageLib::getPageInfo($cnt, $limit, $page);
        $list = $this->getListByDb($where, $pageInfo['start'], $pageInfo['end']);
        $list = $this->format($list);
        $list = $this->formatShow($list);

        $returnPageInfo['page'] = $page;
        $returnPageInfo['limit'] = $limit;
        $returnPageInfo['page_cnt'] = $pageInfo['totalPage'];
        $returnPageInfo['record_cnt'] = $cnt;
        $returnPageInfo['list'] = $list;


        return out_pc(200, $returnPageInfo);
    }

//    function getListByCategory($categoryId,$page,$limit){
//        if(!$categoryId){
//            return out_pc(8993);
//        }
//        $where = " category_id = ".$categoryId;
//        $cnt = $this->getListCntByDb($where);
//        if(!$cnt){
//            return  out_pc(200,null);
//        }
//
//        if(!$limit){
//            $limit = $this->limit;
//        }
//
//        $pageInfo = PageLib::getPageInfo($cnt,$limit,$page);
//        $list = $this->getListByDb($where,$pageInfo['start'],$pageInfo['end']);
//        $list = $this->format($list);
//        return out_pc(200,$list);
//    }


    function initPCAPDataFromDb($categoryAttrParaIds)
    {
        //下面两个变量，是优化读DB过多的情况
        $pca_ids = [];
        $pcap_ids = [];
        foreach ($categoryAttrParaIds as $k => $v) {
            $pca_ids[] = $k;
            foreach ($v as $k2 => $v2) {
                $pcap_ids[] = $v2;
            }
        }

        $pca_ids_str = implode(",", $pca_ids);
        $pcap_ids_str = implode(",", $pcap_ids);
        $pca = ProductCategoryAttrModel::db()->getAll(" id in ( $pca_ids_str ) ");
        $pcap = ProductCategoryAttrParaModel::db()->getAll(" id in ( $pcap_ids_str ) ");

        return array('pca' => $pca, 'pcap' => $pcap);
    }

    function searchPcap($data, $id)
    {
        foreach ($data as $k => $v) {
            if ($v['id'] == $id) {
                return $v;
            }
        }
    }

    function goodsListCoverPrice($goodsList){
        foreach ($goodsList as $k=>$v){
            $goodsList[$k]['original_price'] = ProductService::formatDataPrice(2, $v, 'original_price');
            $goodsList[$k]['sale_price'] = ProductService::formatDataPrice(2, $v, 'sale_price');
        }

        return $goodsList;
    }

    function processProductGoodsInfo($product,$ProductLinkCategoryAttrDb){
        //产品 属性参数  是否为空
        $productCategoryAttrParaData = null;
        $goodsList = null;
        $goodsLowPriceRow = null;
        $stock = 0;
        if ($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE) {
            //获取该产品下的所有商品列表
            $goodsDb = GoodsModel::db()->getAll(" pid = {$product['id']}",null," id,status,a_time,stock,is_del,sale_price,original_price "  );
            if (!$goodsDb) {
                return out_pc(8979);
            }
            $goodsDb = $this->goodsListCoverPrice($goodsDb);
            //格式化 属性参数   以ID 形式
            //属性1 =》  N个参数  ,属性2 =》  N个参数
            $categoryAttrParaIds = null;
            foreach ($ProductLinkCategoryAttrDb as $k => $v) {
                $categoryAttrParaIds[$v['pca_id']][] = $v['pcap_id'];
            }
            //将PCAP ID 从DB中一次性取出
            $ProductLinkCategoryAttrDbData = $this->initPCAPDataFromDb($categoryAttrParaIds);
            //将ID形式 转换成 多维数组，主要是为了获取汉字描述，给前端展示使用
            foreach ($categoryAttrParaIds as $k => $v) {
                //先获取分类属性(pca)的  一条记录值
                $row = $this->searchPcap($ProductLinkCategoryAttrDbData['pca'], $k);
                $para = null;
                //再获取该属性(pca)下的所有参数的值(pcap)
                foreach ($v as $k2 => $v2) {
                    //从初始化好的DB数据中，找到该记录
                    $subRow = $this->searchPcap($ProductLinkCategoryAttrDbData['pcap'], $v2);
                    //此PCAP 规格，默认是否展示。 先初始化为0
                    $subRow['default_low_sel'] = 0;
                    $para[]  = $subRow;
                }
                $row['category_attr_para'] = $para;
                $productCategoryAttrParaData[] = $row;
            }
            $goodsLowPriceRow = $goodsDb[0];
            //处理 商品：最低价 总库存 商品关联PCAP 数据
            foreach ($goodsDb as $k => $v) {
                $row = $v;
                //价格最低的那个商品
                if($goodsLowPriceRow['sale_price'] > $v['sale_price'] ){
                    $goodsLowPriceRow = $v;
                }
                //总库存
                $stock += $v['stock'];

                //获取每个商品对应的  分类属性参数
                $linkList = GoodsLinkCategoryAttrModel::db()->getAll(" gid = {$v['id']}",null," pca_id , pcap_id");
                $row['goods_link_category_attr'] = $linkList;
                $goodsList[]= $row;
            }

        } else {//这里是，空属性的产品
            //获取该产品下的所有商品列表
            exit("暂时处理不了，没有属性的产品，后续优化");
        }

        $productCategoryAttrParaData = $this->defaultSelGoods($productCategoryAttrParaData,$goodsList,$goodsLowPriceRow);


        $data = array('productCategoryAttrParaData'=>$productCategoryAttrParaData,'goodsList'=>$goodsList,'goodsLowPriceRow'=>$goodsLowPriceRow,'stock'=>$stock);

        return out_pc(200,$data);
//        //这里是方便测试，如果一个产品的一个属性，下面有太多的参数选项，会导致产品详情页爆了.
//            foreach ($categoryAttrParaIds as $k=>$v){
//                if(count($v ) >5){
//                    $tmp = null;
//                    foreach ($v as $k2=>$v2){
//                        if($k2 >5 ){
//                            break;
//                        }
//                        $tmp[] = $v2;
//                    }
//                    $categoryAttrParaIds[$k] = $tmp;
//                }
//            }
    }
    //找到最低价格那个，默认把 PCAP 规格参数 给选中 状态
    private function defaultSelGoods($productCategoryAttrParaData,$goodsList,$goods_low_price_row){

        foreach ($goodsList as $k=>$v){
            if($v['id'] == $goods_low_price_row['id']){
                $goods_low_price_row['pcap'] = $v['goods_link_category_attr'];
                break;
            }
        }

        foreach ($productCategoryAttrParaData as $k=>$v){
            $f = 0;
            foreach ($v['category_attr_para'] as $k2=>$v2){
                foreach ($goods_low_price_row['pcap'] as $k3=>$v3){
                    if($v2['id'] == $v3['pcap_id']){
                        $productCategoryAttrParaData[$k]['category_attr_para'][$k2]['default_low_sel'] = 1;
                        $f = 1;
                        break;
                    }
                }
                if($f){
                    break;
                }
            }
        }

        return $productCategoryAttrParaData;
    }

    function getOneDetail($id, $includeGoods = 1, $uid = 0)
    {
        if (!$id) {
            return out_pc(8072);
        }

        $stock = 0;//总库存
        $product = ProductModel::db()->getById($id);
        if (!$product) {
            return out_pc(1026);
        }

        $goodsList = null;//商品及属性列表
        $productCategoryAttrParaData = null;//产品属性列表
        $goodsDb = null;
        $goodsLowPriceRow = null;//价格最低的那个商品，要默认给选中状态(规格选中)
        //获取一个产品的，所有类型 属性 参数
        $ProductLinkCategoryAttrDb = ProductLinkCategoryAttrModel::db()->getAll(" pid = {$product['id']}");
        if (!$ProductLinkCategoryAttrDb) {
            return out_pc(8980);
        }

        if ($includeGoods) {
            $processProductGoodsInfoRs = $this->processProductGoodsInfo($product,$ProductLinkCategoryAttrDb);
            if($processProductGoodsInfoRs['code'] != 200 ){
                return out_pc($processProductGoodsInfoRs['code'],$processProductGoodsInfoRs['msg']);
            }
            $processProductGoodsInfo = $processProductGoodsInfoRs['msg'];
            $productCategoryAttrParaData = $processProductGoodsInfo['productCategoryAttrParaData'];
            $goodsList = $processProductGoodsInfo['goodsList'];
            $stock = $processProductGoodsInfo['stock'];
            $goodsLowPriceRow = $processProductGoodsInfo['goodsLowPriceRow'];
        }

        $product['goods_list'] = $goodsList;
        $product['pcap'] = $productCategoryAttrParaData;
        $product['stock'] = $stock;
        $product['goodsLowPriceRow'] = $goodsLowPriceRow;


        if (arrKeyIssetAndExist($product, 'desc_attr')) {
            $tmp = json_decode($product['desc_attr'], true);
            $str = null;
            foreach ($tmp as $k => $v) {
                $str[] = array("key" => $k, 'value' => $v);
            }
            $product['desc_attr_format'] = $str;
        } else {
            $product['desc_attr_format'] = "";
        }

        $upService = new UpService();
        $collectService = new CollectService();

        $hasLiked = 0;//产品 是否 已经 点赞过
        $hasCollect = 0; //产品 是否 已经 收藏 过
        if ($uid) {
            if ($upService->exist($id, $uid)) {
                $hasLiked = 1;
            }

            if ($collectService->exist($id, $uid)) {
                $hasCollect = 1;
            }

            //更新 pv uv
            $this->upPvUv($id, $uid);
        }

        $product['has_collect'] = $hasCollect;
        $product['has_up'] = $hasLiked;

        $product = $this->formatRow($product);
        //价格 由分 转换 元
        $product['original_price'] = $product['lowest_price'] + ($product['lowest_price'] * $this->originalPricePercent);
        $product['original_price'] = ProductService::formatDataPrice(2, $product, 'original_price');
        $product['lowest_price'] = ProductService::formatDataPrice(2, $product, 'lowest_price');



//        var_dump(strlen(json_encode($product)));
//        echo json_encode($product);exit;
//        var_dump($product);exit;
        return out_pc(200, $product);
    }

    static function formatDataPrice($type, $data, $key)
    {
        if (!arrKeyIssetAndExist($data, $key)) {
            return 0;
        }

        $salePrice = $data[$key];
        if ($salePrice > 0 && (int)($salePrice) > 0) {
            if ($type == 1) {//元 转换 分
                return yuanToFen($salePrice);
            } else {//分 转换 元
                return fenToYuan($salePrice);
            }
        }

        return 0;
    }

    //获取用户访问一个产品的记录
    //$top:只取最后新的20条
    function getUserHistoryPVList($pid, $top = 0)
    {
        if (!$pid) {
            return out_pc(8072);
        }

        $limit = "";
        if ($top) {
            $limit .= " limit 0,20";
        }

        $list = UserProductLogModel::db()->getAll(" pid = $pid group by uid order by a_time desc $limit");
        if (!$list) {
            return out_pc(200, $list);
        }

        foreach ($list as $k => $v) {
            $list[$k]['nickname'] = '游客' . $k;
            $list[$k]['avatar'] = get_avatar_url("");
            $user = UserModel::db()->getById($v['uid']);
            if ($user) {
                $list[$k]['nickname'] = $user['nickname'];
                $list[$k]['avatar'] = get_avatar_url($user['avatar']);
            }
        }
        return out_pc(200, $list);
    }

    function upPvUv($pid, $uid)
    {
        $data = array("pv" => array(1));
        $info = UserProductLogModel::db()->getRow("uid = {$uid} and pid = $pid");
        if (!$info) {
            $data['uv'] = array(1);
        }

        $d = array('pid' => $pid, 'uid' => $uid, 'a_time' => time());
        UserProductLogModel::db()->add($d);

        $rs = ProductModel::db()->upById($pid, $data);
        return $rs;
    }

    function formatShow($list)
    {
        if (!$list) {
            return $list;
        }
        $data = null;
        foreach ($list as $k => $v) {
            $pic = "";
            if (arrKeyIssetAndExist($v, 'pic')) {
                $pic = explode(",", $v['pic']);
                $pic = get_product_url($pic[0]);
            }


            $row = array(
                'id' => $v['id'], 'goods_total' => $v['goods_total'], 'pic' => $pic,
                'lowest_price' => fenToYuan($v['lowest_price']), 'title' => $v['title'], 'user_buy_total' => $v['user_buy_total'],
            );

            if (isset($v['has_cart'])) {
                $row['has_cart'] = $v['has_cart'];
            }

            $data[] = $row;
        }
        return $data;
    }

    function getGoodsListByPid($pid)
    {
        return GoodsModel::db()->getAll(" pid = $pid ");
    }

    function search($condition, $page = 1, $limit = 10, $uid = 0)
    {
        $returnPageInfo = array(
            'page' => $page,
            'limit' => $limit,
            'record_cnt' => 0,
            'page_cnt' => 0,
            'list' => null,
        );

        $where = " 1 = 1 ";
        if ($condition) {
//            return out_pc(8978);
            if (arrKeyIssetAndExist($condition, 'keyword')) {
                $where .= " and ( title like '%{$condition['keyword']}%' or `desc`  like '%{$condition['keyword']}%')";
            }

            if (arrKeyIssetAndExist($condition, 'category')) {
                $where .= " and category_id = {$condition['category']}";
            }
        }
        LogLib::inc()->debug($where);
        $cnt = self::getListCntByDb($where);
        if (!$cnt) {
            return out_pc(200, $returnPageInfo);
        }

        $order = "";
        if (arrKeyIssetAndExist($condition, 'orderType')) {
            $order .= " order by " . self::ORDER_TYPE[$condition['orderType']]['field'];
            if ($order == 'lowest_price') {
                if (arrKeyIssetAndExist($condition, 'orderUpDown')) {
                    $order .= " desc ";
                } else {
                    $order .= " asc ";
                }
            } else {
                $order .= " desc";
            }

        }

        $pageInfo = PageLib::getPageInfo($cnt, $limit, $page);
        $where .= " $order limit {$pageInfo['start']} , {$pageInfo['end']}  ";

        LogLib::inc()->debug($where);

        $list = ProductModel::db()->getAll($where);


        $list = $this->format($list);

        if ($uid) {
            foreach ($list as $k => $v) {
                $userHasCart = 0;
                $dbRs = CartModel::db()->getRow(" pid = {$v['id']} and uid = $uid");
                if ($dbRs) {
                    $userHasCart = 1;
                }
                $list[$k]['has_cart'] = $userHasCart;
            }
        }


        $returnPageInfo['record_cnt'] = $cnt;
        $returnPageInfo['page_cnt'] = $pageInfo['totalPage'];
        $returnPageInfo['list'] = $list;

        return out_pc(200, $returnPageInfo);
    }

    function format($list)
    {
        if (!$list) {
            return $list;
        }

        $data = null;
        foreach ($list as $k => $v) {
            $row = $this->formatRow($v);
            $data[] = $row;
        }

        return $data;
    }

    function formatRow($row)
    {
        if (arrKeyIssetAndExist($row, 'pic')) {
            $picsTmpUrl = explode(",", $row['pic']);
            $realUrl = "";
            foreach ($picsTmpUrl as $k => $v) {
                //data-lazy-src=\"https:\/\/cbu01.alicdn.com\/img\/ibank\/2019\/972\/967\/10464769279_1593857209.jpg,https:\/\/cbu01.alicdn.com\/cms\/upload\/other\/lazyload.png\"
                //这里有个BUG,应该是正则匹配的时候，出的问题，先不处理
                if (strpos($v, 'src') === false) {
                    $realUrl .= get_product_url($v) . ",";
                }
//                $realUrl .=get_product_url($v) . ",";
            }
            $realUrl = substr($realUrl, 0, strlen($realUrl) - 1);
            $row['pic'] = $realUrl;

        }

        return $row;
    }

    function addOne($data, $categoryAttrNull = 0, $categoryAttrPara = [])
    {
        $attribute = [];
        if ($categoryAttrNull) {
            $attribute[$categoryAttrNull] = [];
            $data['category_attr_null'] = ProductModel::CATE_ATTR_NULL_TRUE;
        } else {
            foreach ($categoryAttrPara as $k => $v) {
                $tmp = explode("_", $v);
                $attribute[$tmp[0]][] = $tmp[1];
            }
            $data['category_attr_null'] = ProductModel::CATE_ATTR_NULL_FALSE;
        }

        $data['attribute'] = json_encode($attribute);
        if (!arrKeyIssetAndExist($data, 'recommend')) {
            $data['recommend'] = 2;
        }
        $addId = ProductModel::db()->add($data);

        out(" productService addOne,so add product is ok ~id:$addId , next add ProductLinkCategoryAttrModel");
        if ($categoryAttrPara) {
            foreach ($categoryAttrPara as $k => $v) {
                $exp = explode("_", $v);
                $categoryAttr = $exp[0];
                $categoryAttrPara = $exp[1];

                $exist = ProductLinkCategoryAttrModel::db()->getRow(" pid = $addId and pca_id = $categoryAttr and pcap_id = $categoryAttrPara");
                if ($exist) {
                    continue;
                }

                $addData = array(
                    'pid' => $addId,
                    'pc_id' => $data['category_id'],
                    'pca_id' => $categoryAttr,
                    'pcap_id' => $categoryAttrPara,
                );

                ProductLinkCategoryAttrModel::db()->add($addData);
            }
        } else {
            $noParaAttr = ProductCategoryAttrModel::db()->getRow(" pc_id = {$data['category_id']} and is_no = " . ProductCategoryAttrModel::NO_ATTR_TRUE);
            $data = array(
                'pid' => $addId,
                'pc_id' => $data['category_id'],
                'pca_id' => $noParaAttr['id'],
                'pcap_id' => 0,
            );
            $newNewId = ProductLinkCategoryAttrModel::db()->add($data);
        }

//        $this->insertProductLinkGoods($addId);
        return $addId;
    }

    function goodsLinkProductPCAP($gid, $pid, $categoryId, $categoryAttrId, $categoryAttrParaId = 0)
    {
//        if(!$categoryAttrParaId){//证明是个空属性
//        }else{
//        }

        $data = array(
            'gid' => $gid,
            'pc_id' => $categoryId,
            'pca_id' => $categoryAttrId,
            'pcap_id' => $categoryAttrParaId
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