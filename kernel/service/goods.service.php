<?php

class GoodsService
{
//    public $limit = 10;
//    private $originalPricePercent = 0.1;
//    const ORDER_TYPE = array(
//        1 => array('id' => 1, "name" => '默认1', 'field' => "id"),
//        2 => array('id' => 2, "name" => '新品2', 'field' => "id"),
//        3 => array('id' => 3, "name" => '销量3', 'field' => "user_buy_total"),
//        4 => array('id' => 4, "name" => '价格4', 'field' => 'lowest_price'),
//    );
    //$includeGoods:是否包含该产品下的商品信息
    //upPvUv : 更新PV UV 值，必须uid 存在 才行
    function getOneDetail($id, $includePCAP = 0, $uid = 0)
    {
        if (!$id) {
            return out_pc(8073);
        }

        $goods = GoodsModel::db()->getById($id);
        if (!$goods) {
            return out_pc(1027);
        }

        $pcap = null;
        //处理 该商品下的所有商品
        if ($includePCAP) {
            $GoodsLinkCategoryAttr = GoodsLinkCategoryAttrModel::db()->getAll(" gid = $id");
            if($GoodsLinkCategoryAttr){
                $pcapDbData = $this->initPCAPDataFromDb($GoodsLinkCategoryAttr);
                foreach ($GoodsLinkCategoryAttr as $k=>$v){
                    $pca_name_row =  $this->searchPcap($pcapDbData['pca'], $v['pca_id']);
                    $pcap_name_row =  $this->searchPcap($pcapDbData['pcap'], $v['pcap_id']);

                    $GoodsLinkCategoryAttr[$k]['pca_name'] = $pca_name_row['name'];
                    $GoodsLinkCategoryAttr[$k]['pcap_name'] = $pcap_name_row['name'];
                    $GoodsLinkCategoryAttr[$k]['pcap_img'] = $pcap_name_row['img'];
                }
                $pcap = $GoodsLinkCategoryAttr;
            }else{

            }

        }
        $goods['pcap'] = $pcap;
        $product = $this->formatRow($goods,$uid);
        return out_pc(200, $product);
    }
    //格式化价格
    function formatPrice($product){
        $product['original_price'] = ProductService::formatDataPrice(2, $product, 'original_price');
        $product['sale_price'] = ProductService::formatDataPrice(2, $product, 'lowest_price');
        $product['haulage'] = ProductService::formatDataPrice(2, $product, 'haulage');

        return $product;
    }
    //格式化一行
    function formatRow($goods , $uid = 0 ){
        //价格 由分 转换 元
        $goods = $this->formatPrice($goods);
        return $goods;
    }


    function initPCAPDataFromDb($GoodsLinkCategoryAttr)
    {
        $pca_ids = null;
        $pcap_ids = null;
        foreach ($GoodsLinkCategoryAttr as $k=>$v){
            $pca_ids [] = $v['pca_id'];
            $pcap_ids[] = $v['pcap_id'];
        }

        $pca_ids_str = implode(",", $pca_ids);
        $pcap_ids_str = implode(",", $pcap_ids);
        $pca = ProductCategoryAttrModel::db()->getAll(" id in ( $pca_ids_str ) ");
        $pcap = ProductCategoryAttrParaModel::db()->getAll(" id in ( $pcap_ids_str ) ");

        foreach ($pcap as $k=>$v){
            if(arrKeyIssetAndExist($v,'img')){
                $pcap[$k]['img'] = get_category_attr_para_url($v['img']);
            }
        }

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
}