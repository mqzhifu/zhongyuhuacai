<?php
class GoodsCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());

        $this->display("/product/goods_list.html");
    }


    function makeQrcode(){
//        var_dump(1);
        include PLUGIN .DS."phpqrcode".DS."qrlib.php";
        $url = "http://www.baidu.com";
        QRcode::png($url,false,"L",10);
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
                'status'=>_g('status'),
                'sale_price'=>_g('sale_price'),
                'original_price'=>_g('original_price'),
                'haulage'=>_g('haulage'),
                'admin_id'=>$this->_adminid,
                'a_time'=>time(),
                "sort"=>_g('sort'),
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

        $cnt = OrderModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'house_id',
                'type',
                'price',
                'deposit_price',
                'tenancy_pay_mode',
                'master_pay_mode',
                'contract_attachment',
                'contract_start_time',
                'contract_end_time',
                "a_time",
                'admin_id',
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
            $data = OrderModel::db()->getAll($where . $order .$limit);


//                 'id',
//                'id',
//                'house_id',
//                'type',
//                'price',
//                'deposit_price',
//                'tenancy_pay_mode',
//                'master_pay_mode',
//                'contract_attachment',
//                'contract_start_time',
//                'contract_end_time',
//                "a_time",
//                'admin_id',
//                '',

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['house_id'],
                    $v['type'],
                    $v['price'],
                    $v['deposit_price'],
                    $v['tenancy_pay_mode'],
                    $v['master_pay_mode'],
                    $v['contract_attachment'],
                    get_default_date($v['contract_start_time']),
                    get_default_date($v['contract_end_time']),
                    get_default_date($v['a_time']),
                    $v['admin_id'],
                    "",
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


}