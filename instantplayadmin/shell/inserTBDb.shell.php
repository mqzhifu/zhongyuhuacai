<?php
class inserTBDb{
    public $taobao_username = "mqzhifu";
    public $taobao_password = "mqzhifu";
    public $host = "https://detail.1688.com/";


    public $tbCategoryAttrId = 37;
    public $tbCategoryId = 8;//分类:1688抓取，空属性值ID

    public function __construct($c){
        $this->commands = $c;
    }

    function run(){
        $startTime = time();
        $products = ProductTbModel::db()->getAll();


        //共3步:
        //1：先把产品的分类属性，都定位到，并入库
        //2：生成产品信息
        //3：生成商品信息

//        $this->insertProductCategoryAttrPara($products);
        $this->insertProduct($products);

        $endTime = time();

        $total = $endTime - $startTime;
        out("total time:".$total);

    }

    function insertProduct($products){
        ProductModel::db()->delete(" id > 1 limit 1000 ");
        GoodsModel::db()->delete(" id > 1 limit 4000 ");
        ProductLinkCategoryAttrModel::db()->delete(" pid > 1 limit 1000 ");

        $newProductData = ProductModel::getField();
        $newProductData['category_id'] = $this->tbCategoryId;
        $newProductData['a_time'] = time();
        $newProductData['factory_uid'] = FACTORY_UID_DEFAULT;//默认工厂ID
        $newProductData['spider_source_type'] = ProductModel::SPIDER_TYPE_1688;//获取 来源
        $newProductData['status'] = ProductModel::STATUS_OFF;
        $newProductData['category_attr_null'] = ProductModel::CATE_ATTR_NULL_FALSE;



        $newGoodsData = GoodsModel::getField();
        $newGoodsData['a_time'] = time();
        $newGoodsData['u_time'] = time();
        $newGoodsData['status'] = GoodsModel::STATUS_OFF;
        $newGoodsData['pay_type'] = OrderModel::PAY_WX_H5_NATIVE;

        out("start insert Product Goods");
        foreach ($products as $k=>$v) {
            out(" productTb num: $k ");
            $data = $newProductData;
            $data['title'] = $v['title'];
            $data['subtitle'] = $v['title'];
            $data['desc'] = $v['desc'];
            $data['desc_attr'] = $v['attr'];
            $data['pic'] = $v['box_img'];
            $data['spider_source_pid'] = $v['offerid'];

            $price = json_decode($v['price'],true);
            //只是单卖，没有任何商品属性，如：一瓶、一件
            if(!arrKeyIssetAndExist($v,'category_attr') ||!arrKeyIssetAndExist($v,'category_attr_para') ){
                $this->insertNoAttrProduct($price[0]['price'],$data,$newGoodsData);
            }else{
                //判断下  价格信息，如果价格信息均是数字组成的数组，那就证明是所有属性组合都是一个价格
                $isScalar = 0;
                foreach ($price as $k2=>$v2) {
                    if(is_array($v2)){
                        $isScalar = 1;
                    }
                    break;
                }

                if($isScalar){
                    $this->insertOnePriceProduct($price[0][1],$v['category_attr'],$data,$newGoodsData);
                }else{
                    $this->insertComplexProduct($price,$v['category_attr'],$data,$newGoodsData);
                }
            }
        }

        out("end");
    }
    //最复杂的情况，也就是，一个产品，有多种组合的商品
    function insertComplexProduct($price,$category_attr,$productData,$newGoodsData){
        $space = "    ";
        out("case 3: is complex");

        $categoryArr = explode(",",$category_attr);
        $categoryArrFinal = null;
        //先把一级 属性 获取到
        foreach ($categoryArr as $k2=>$v2) {
            $name = trim($v2);
            if(!$name){
                continue;
            }
            $categoryAttrDb = ProductCategoryAttrModel::getRowByNameByCategoryId($name,$this->tbCategoryId);
//            $categoryAttrDb = ProductCategoryAttrModel::db()->getRow(" name = '$name' and pc_id = {$this->tbCategoryId}");
            $categoryArrFinal[] = $categoryAttrDb['id'];
        }


        $newGoodsDataInsertBat =[];
        $productCategoryAttrParaIds = [];

        $tmp = $newGoodsData;
        foreach ($price as $k2=>$finalPrice) {
            $goodsCateAttrPara = [];
            out($space .$k2 . " " .$finalPrice);
            $attrPara = explode(">",$k2);
            if(count($attrPara)>=3){
                exit("attr para >= 3");
            }

            if(count($attrPara) == 1){
//                $attrId = $categoryArrFinal[0];

                $categoryAttrPara = ProductCategoryAttrParaModel::getRowByNameByCategoryAttrId($attrPara[0],$categoryArrFinal[0]);
//                $categoryAttrPara = ProductCategoryAttrParaModel::db()->getRow(" pca_id = ".$attrId . "  and name = '{$attrPara[0]}'");
                if(!$categoryAttrPara){
                    exit(" pca_id err");
                }
                $productCategoryAttrParaIds[] =$categoryArrFinal[0] ."_".$categoryAttrPara['id'];
                $goodsCateAttrPara[] =  $categoryArrFinal[0] ."_".$categoryAttrPara['id'];
            }else{
//                $attrId1 = $categoryArrFinal[0];
//                $attrId2 = $categoryArrFinal[1];

                $categoryAttrPara1 = ProductCategoryAttrParaModel::getRowByNameByCategoryAttrId($attrPara[0],$categoryArrFinal[0]);
//                $categoryAttrPara1 = ProductCategoryAttrParaModel::db()->getRow(" pca_id = ".$attrId1 . "  and name = '{$attrPara[0]}'");
                if(!$categoryAttrPara1){
                    exit(" pca_id 1 err");
                }

                $categoryAttrPara2 = ProductCategoryAttrParaModel::getRowByNameByCategoryAttrId($attrPara[1],$categoryArrFinal[1]);
//                $categoryAttrPara2 = ProductCategoryAttrParaModel::db()->getRow(" pca_id = ".$attrId2 . "  and name = '{$attrPara[1]}'");
                if(!$categoryAttrPara2){
                    exit(" pca_id 2 err");
                }

                $productCategoryAttrParaIds[] = $categoryArrFinal[0] ."_".$categoryAttrPara1['id'];
                $productCategoryAttrParaIds[] = $categoryArrFinal[1] ."_".$categoryAttrPara2['id'];

                $goodsCateAttrPara[] =  $categoryArrFinal[0] ."_".$categoryAttrPara1['id'];
                $goodsCateAttrPara[] = $categoryArrFinal[1] ."_".$categoryAttrPara2['id'];
            }

            out("goodsCateAttrPara : ".json_encode($goodsCateAttrPara));

            $newGoodsDataInsert = $tmp;
            $newGoodsDataInsert['sale_price'] = $finalPrice;
            $newGoodsDataInsert['original_price'] = $finalPrice;
            $newGoodsDataInsert['tmp_product_attr_ids'] = $goodsCateAttrPara;

            $newGoodsDataInsertBat[] = $newGoodsDataInsert;
        }

        $newPid = ProductModel::addOne($productData,0,$productCategoryAttrParaIds);
        $newProduct = ProductModel::db()->getById($newPid);

        out($space."create new product ,id : $newPid ");

        foreach ($newGoodsDataInsertBat as $k2=>$v2) {
            $goodsPcap = [];
//            var_dump($v2['tmp_product_attr_ids']);
            foreach ($v2['tmp_product_attr_ids'] as $k3=>$v3) {
                $row = explode("_",$v3 );
                $goodsPcap[$row[0]] = $row[1];
            }
            unset($v2['tmp_product_attr_ids']);

            $newGoodsId = GoodsModel::addOne($v2,$newProduct,$goodsPcap , 0);
            out(" create goods, id:$newGoodsId");
        }
        ProductModel::upTotal($newPid);
    }

    function insertOnePriceProduct($price,$category_attr,$productData,$newGoodsData){
        $space = "    ";
        out($space ."case 2 ,is scalar , price : $price");
        //所有属性组合都是一个价格
        //[[2,2.8],[100,2.5],[200000,1.8]]

        $finalPrice = $price;//最第一个，也就是，最高的价格
        //取出<分类为：1688抓取>的所有产品的分类
        $categoryArr = explode(",",$category_attr);

        $newProductAttribute = $this->calcSingleProductGoodsPrice($categoryArr,$this->tbCategoryId,'getIds');
        $newPid = ProductModel::addOne($productData,0,$newProductAttribute);
        $newProduct = ProductModel::db()->getById($newPid);

        out($space."create new product ,id : $newPid ");

        foreach ($newProductAttribute as $k3=>$v3) {
            $row = explode("_",$v3);
            $goodsPcap = array($row[0]=>$row[1]);

            $goods = $newGoodsData;
            $goods['sale_price'] = $finalPrice;
            $goods['original_price'] = $finalPrice;

            $newGoodsId = GoodsModel::addOne($goods,$newProduct,$goodsPcap , 0);

            out($space." add goods $v3:".$newGoodsId);
        }

        ProductModel::upTotal($newPid);
    }
    //这种其实就只有一种商品(最简单的)
    //[{"begin":"4","end":"35","price":"7.00"}
    function insertNoAttrProduct($price,$productData,$newGoodsData){
        $space = "    ";
        out($space ."case 1 , no attr , price : $price");

        $newPid = ProductModel::addOne($productData,$this->tbCategoryAttrId);
        $newProduct = array('category_attr_null'=>1,'id'=>$newPid);
        out($space."create new product ,id : $newPid ");

        $goods = $newGoodsData;
        $goods['sale_price'] = $price;
        $goods['original_price'] = $price;
        $GoodsNewId = GoodsModel::addOne($goods,$newProduct,null);
        out(" create goods, id:$GoodsNewId");
    }

    function calcSingleProductGoodsPrice($categoryArr,$tbCategoryId){
        $categoryArrParaIds = [];
        foreach ($categoryArr as $k2=>$v2) {
            $attr = trim($v2);
            if(!$attr){
                //待处理
                exit("calcSingleProductGoodsPrice is null");
            }
            $categoryAttrDb = ProductCategoryAttrModel::getRowByNameByCategoryId($attr,$this->tbCategoryId);
//            $categoryAttrDb = ProductCategoryAttrModel::db()->getRow(" name = '$attr' and pc_id = $tbCategoryId");
            if(!$categoryAttrDb){
                exit(" db has this attr");
            }

            $categoryAttrPara = ProductCategoryAttrParaModel::db()->getAll(" pca_id = ".$categoryAttrDb['id']);
            if(!$categoryAttrPara){
                exit(" pca_id err");
            }

            foreach ($categoryAttrPara as $k=>$v) {
                $categoryArrParaIds[] = $categoryAttrDb['id'] ."_" .$v['id'];
            }
            return $categoryArrParaIds;
        }
        return $categoryArrParaIds;
    }
    //先把，产品的属性及属性参数，插入DB中
    function insertProductCategoryAttrPara($products){
        ProductCategoryAttrModel::db()->delete(" id > 37 limit 1000 ");
        ProductCategoryAttrParaModel::db()->delete(" id > 100 limit 4000 ");
        out("start process categoryAttrCategory");
        foreach ($products as $k=>$v) {
            out("offerId:".$v['offerid']);
            if(!arrKeyIssetAndExist($v,'category_attr')){
                out("this product no have category_attr");
                continue;
            }

            $categoryArr = explode(",",$v['category_attr']);
            foreach ($categoryArr as $k2=>$v2) {
                $attr = trim($v2);
                if(!$attr){
                    continue;
                }
                out("new attr name: $attr");
                $categoryAttrDb = ProductCategoryAttrModel::getRowByNameByCategoryId($attr,$this->tbCategoryId);
//                $categoryAttrDb = ProductCategoryAttrModel::db()->getRow(" name = '$attr' and pc_id = {$this->tbCategoryId}");
                if($categoryAttrDb){
                    out(" db has this attr");
                    continue;
                }

                $data = array("name"=>$attr,'is_no'=>ProductCategoryAttrModel::NO_ATTR_FALSE,'pc_id'=>$this->tbCategoryId);
                $newCategoryAttrId = ProductCategoryAttrModel::db()->add($data);
                out(" insert db id : $newCategoryAttrId");
            }

            if(!arrKeyIssetAndExist($v,'category_attr_para')){
                out(" no have category_attr_para ");
                continue;
            }

            $categoryAttrPara = json_decode($v['category_attr_para'],true);
            out("start process category_attr_para");
            foreach ($categoryAttrPara as $k3=>$v3) {
                $attrName = trim($k3);
                $categoryAttrDb = ProductCategoryAttrModel::getRowByNameByCategoryId($attrName,$this->tbCategoryId);
//                $attrDb = ProductCategoryAttrModel::db()->getRow(" name = '$attrName' and pc_id = {$this->tbCategoryId}");
                out(" attr:".$attrName . " pca_id:".$categoryAttrDb['id']);
                foreach ($v3 as $k4=>$v4) {
                    $data = array('name'=>$v4['name'],'pca_id'=>$categoryAttrDb['id']);
                    if(arrKeyIssetAndExist($v4,'img_url')){
                        $data['img'] = $v4['img_url'];
                    }
                    $newId = ProductCategoryAttrParaModel::db()->add($data);
                    out("  para:".$v4['name'] . " newId:$newId");
                }
            }
        }
    }
}