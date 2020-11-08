<?php
class FixProductPrice{
    private $userInfoTextPath = "";
    function __construct($c){


    }

    public function run($attr){
        set_time_limit(0);
//        if(PHP_OS == 'WINNT'){
//            exec('chcp 936');
//        }

//        $this->process("province");
//        $this->process("city");
//        $this->process("county");
        $productList = ProductModel::db()->getAll(" 1 ");
        if(!$productList){
            exit(" product list is null.");
        }

        foreach ($productList as $k=>$v){
            $goodsRow = GoodsModel::db()->getRow(" pid = {$v['id']} order by sale_price asc ",null,'sale_price' );
            if(!$goodsRow ){
                exit("goodsRow is null");
            }
            $upData = array('lowest_price'=>$goodsRow['sale_price']);
            echo "pid : {$v['id']} now price : {$v['sale_price']} , up price : {$v['sale_price']} \n";
            //ProductModel::db()->upById($v['id'],$upData);
        }
    }

}

function o($str){
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }
    echo $str."\n";
}