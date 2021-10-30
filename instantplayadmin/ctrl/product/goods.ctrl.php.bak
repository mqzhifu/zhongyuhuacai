<?php
class GoodsCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("statusSelectOptionHtml",GoodsModel::getStatusSelectOptionHtml());
        $this->display("/product/goods_list.html");
    }


    function makeQrcode(){
//        var_dump(1);
        include PLUGIN .DS."phpqrcode".DS."qrlib.php";
        $url = "http://www.baidu.com";
        QRcode::png($url,false,"L",10);
    }
    function upprice(){
        $aid = _g("id");
        $goods = GoodsModel::db()->getById($aid);
        if(!$goods){
            exit(" gid not in db.");
        }
        if(_g('opt')){
            $original_price = _g("original_price");
            if(!$original_price){
                out_ajax(5001);
            }

            $sale_price = _g("sale_price");
            if(!$sale_price){
                out_ajax(5002);
            }

            $sale_price = yuanToFen($sale_price);
            $original_price = yuanToFen($original_price);
            if(!$sale_price){
                out_ajax(5003);
            }

            if(!$original_price){
                out_ajax(5004);
            }

            $stock = _g("stock");
            if(!$stock){
                out_ajax(5005);
            }

            $stock = (int)$stock;
            if(!$stock){
                out_ajax(5006);
            }


            $data = array('original_price'=>$original_price,'u_time'=>time(),'sale_price'=>$sale_price,'stock'=>$stock);
            $rs = GoodsModel::db()->upById($aid,$data);
            out_ajax(200,"ok-" .$rs);


        }
//        $statusDesc = AgentModel::STATUS;
//        $statusDescRadioHtml = "";
//        foreach ($statusDesc as $k=>$v) {
//            $statusDescRadioHtml .= "<input name='status' type='radio' value={$k} />".$v;
//        }

        $data = array(
            'original_price'=>fenToYuan($goods['original_price']),
            'sale_price'=>fenToYuan($goods['sale_price']),
            'stock'=>$goods['stock'],
            "id"=>$aid,
        );
//        $this->assign("agent",$agent);
//        $this->assign("statusDescRadioHtml",$statusDescRadioHtml);

        $html = $this->_st->compile("/product/goods_upprice.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }
    function add(){
        $pid = _g("pid");
        if(!$pid){
            $this->notice("pid is null");
        }

        $product = ProductModel::db()->getById($pid);
        if(!$product){
            $this->notice("pid not in db");
        }

//        $productAttribute= json_decode($product['attribute'],true);
//        foreach ($productAttribute as $k=>$v) {
//            $productAttributeId = $k;
//        }
        if(_g("opt")){

            $data = array(
                'pid'=>$pid,
                'stock'=>_g('stock'),
                'type'=>0,
                'status'=>_g('status'),
                'sale_price'=>_g('sale_price'),
                'original_price'=>_g('original_price'),
                'haulage'=>_g('haulage'),
                'admin_id'=>$this->_adminid,
                'a_time'=>time(),
                "sort"=>_g('sort'),
                'third_id'=>"",

"pay_type"=>"",


//                'pay_type'=>implode(",",_g('payType')),
            );

            if(!$data['stock'] ){
                $this->notice("pid not in db");
            }

            $data['stock'] = (int)$data['stock'];
            if(! $data['stock']){
                $this->notice("库存数 必须为正整数");
            }

            if(!$data['status'] ){
                $this->notice("请选择 上下架 状态");
            }

            if(!$data['sale_price'] ){
                $this->notice("销售价为不能迷代");
            }

            if(!is_numeric($data['sale_price'])){
                $this->notice("销售价格只能是 整数+小数");
            }

            if(!$data['original_price'] ){
                $this->notice("原始价格 不能为空");
            }

            if(!is_numeric($data['original_price'])){
                $this->notice("原始价格只能是 整数+小数");
            }

            if($data['haulage'] ){
                if(!is_numeric($data['haulage'])){
                    $this->notice("运费价格只能是 整数+小数");
                }
            }else{
                $data['haulage'] = 0;
            }





//            $data['sale_price'] = yuanToFen($data['sale_price']);
//            $data['original_price'] = yuanToFen($data['sale_price']);
//            $data['haulage'] = yuanToFen($data['sale_price']);

            $attrPara = [];
            if($product['category_attr_null'] == ProductModel::CATE_ATTR_NULL_FALSE){
                $attrParaGroup = ProductLinkCategoryAttrModel::getAttrParaGroup($product['id']);
                $attrParaStr = "";
                foreach ($attrParaGroup as $k=>$v) {
                    $attrPara[$k] = _g("categoryAttrPara_".$k);
                    if( !$attrPara[$k] ){
                        $ProductCategoryAttr = ProductCategoryAttrModel::db()->getById($k);
                        $this->notice("类别属性({$ProductCategoryAttr['name']}-$k)，不能为空，请选择一个值！");
                    }
                    $attrParaStr .= $k ."-". $attrPara[$k] . ",";
                }
                $attrParaStr = substr($attrParaStr,0,strlen($attrParaStr)-1);
                if(!$attrParaStr){
                    $this->notice("类别属性 不能为空");
                }
                $exist = GoodsModel::db()->getRow(" product_attr_ids = '$attrParaStr'");
                if($exist){
                    $this->notice("该商品已存在 ，请不要重复添加:".$attrParaStr);
                }
            }else{
                $goods = GoodsModel::db()->getRow(" pid = $pid");
                if($goods){
                    $this->notice("<空属性>的产品，只能添加一个商品.");
                }

                $attrParaGroup = ProductLinkCategoryAttrModel::getAttrParaGroup($product['id']);
                foreach ($attrParaGroup as $k=>$v){
                    $attrPara[$k] = $v;
                }
            }

            $newId = GoodsModel::addOne($data,$product,$attrPara);
            $this->ok($newId,$this->_backListUrl);
        }

        $this->assign("product",$product);
        $this->assign("payType",OrderModel::PAY_TYPE_DESC);

        $productLinkCategoryAttr = ProductLinkCategoryAttrModel::getRelationFormatHtml($pid);
//        if(!$productLinkCategoryAttr){
//            $attr = ProductCategoryAttrModel::db()->getById($productAttributeId);
//        }
        $this->assign("productLinkCategoryAttr",json_encode($productLinkCategoryAttr));



        $paraMax = 0;
        foreach ($productLinkCategoryAttr as $k=>$v) {
            if(arrKeyIssetAndExist($v,'para')){
                if(count($v['para']) > $paraMax){
                    $paraMax = count($v['para']);
                }
            }
        }

        $this->assign("paraMax",$paraMax);


        $category = ProductCategoryModel::db()->getById($product['category_id']);

        $statusSelectOptionHtml = ProductModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
        $this->assign("categoryName",$category['name']);
//        $this->assign("categoryOptions", ProductCategoryModel::getSelectOptionHtml());

        $this->addHookJS("/product/goods_add_hook.html");
        $this->display("/product/goods_add.html");
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = GoodsModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'pid',
                'status',
                '',
                'stock',
                'sale_price',
                'original_price',
                '',
                'haulage',
                'order_total',
                'a_time',
                '',
            );
            $order = " order by ". $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $limit = " limit $iDisplayStart,$end";
            $data = GoodsModel::db()->getAll($where . $order .$limit);


            foreach($data as $k=>$v){
                $paraStr = "";
                if(arrKeyIssetAndExist($v,'product_attr_ids')){
                    $paraIds = explode(",",$v['product_attr_ids']);
                    foreach ($paraIds as $k2=>$v2) {
                        $tmp = explode("-",$v2);
                        $attrName = ProductCategoryAttrModel::db()->getOneFieldValueById($tmp[0],'name');
                        $paraName = ProductCategoryAttrParaModel::db()->getOneFieldValueById($tmp[1],'name');
                        $paraStr .= $attrName . " : ". $paraName . "<br/>";
                    }
                }

//                $payTypeArr = OrderModel::getSomePayTypeDesc($v['pay_type']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    ProductModel::db()->getOneFieldValueById($v['pid'],'title'),
//                    $v['type'],
                    GoodsModel::STATUS[$v['status']],
                    $paraStr,
                    $v['stock'],
                    fenToYuan($v['sale_price']),
                    fenToYuan($v['original_price']),
//                    json_encode($payTypeArr,JSON_UNESCAPED_UNICODE),
                    AdminUserModel::db()->getOneFieldValueById($v['admin_id'],'uname'),
//                    $v['is_search'],
                    $v['haulage'],
                    $v['order_total'],
                    get_default_date($v['a_time']),
                    '<button class="btn btn-xs default dark delone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-scissors"></i>  删除</button>'.
                    '<button class="btn btn-xs default red upprice margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 改价</button>'

                ,

                );

                $records["data"][] = $row;
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getDataListTableWhere(){
        $where = 1;
        $id = _g("id");
        $product_name = _g("product_name");
        $status = _g("status");

        $from = _g("from");
        $to = _g("to");

        $stock_from = _g("stock_from");
        $stock_to = _g("stock_to");

        $sale_price_from = _g("sale_price_from");
        $sale_price_to = _g("sale_price_to");

        $original_price_from = _g("original_price_from");
        $original_price_to = _g("original_price_to");

        $haulage_from = _g("haulage_from");
        $haulage_to = _g("haulage_to");

        $order_total_from = _g("order_total_from");
        $order_total_to = _g("order_total_to");


        if($id)
            $where .=" and id = '$id' ";

        $productService = new ProductService();
        if($product_name){
            $where .= $productService->searchUidsByKeywordUseDbWhere($product_name);
        }
        if($status)
            $where .=" and status = '$status' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($stock_from)
            $where .=" and stock_from >=  ".strtotime($stock_from);

        if($stock_to)
            $where .=" and stock_to <= ".strtotime($stock_to);


        if($sale_price_from)
            $where .=" and sale_price_from >=  ".strtotime($sale_price_from);

        if($sale_price_to)
            $where .=" and sale_price_to <= ".strtotime($sale_price_to);

        if($original_price_from)
            $where .=" and original_price_from >=  ".strtotime($original_price_from);

        if($original_price_to)
            $where .=" and original_price_to <= ".strtotime($original_price_to);

        if($haulage_from)
            $where .=" and haulage_from >=  ".strtotime($haulage_from);

        if($haulage_to)
            $where .=" and haulage_to <= ".strtotime($haulage_to);


        if($order_total_from)
            $where .=" and order_total_from >=  ".strtotime($order_total_from);

        if($order_total_to)
            $where .=" and order_total_to <= ".strtotime($order_total_to);


        return $where;
    }


    function delOne(){
        $id = _g("id");
        if(!$id){
            out_ajax(4002);
        }
        $agent = GoodsModel::db()->getById($id);
        if(!$agent){
            out_ajax(4003);
        }
        $orderGoods = OrderGoodsModel::db()->getRow(" gid = $id");
        if($orderGoods){
            out_ajax(5000);
        }
        GoodsModel::db()->delById($id);
        out_ajax(200);

    }

}