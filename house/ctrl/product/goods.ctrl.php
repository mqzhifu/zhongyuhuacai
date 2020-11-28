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

    function createPayRecord(){
        $oid = _g("oid");
        if(!$oid){
            $this->notice("oid is null");
        }

        $order = OrderModel::db()->getById($oid);
        if(!$order){
            $this->notice("oid not in db");
        }

        $distance = $order['contract_end_time'] - $order['contract_start_time'];
        if($distance < 31 * 24 * 60 * 60){
            $this->notice("合同的周期，不能小于31天，因为最付款单元为：月");
        }

        if($order['tenancy_pay_mode'] == OrderModel::PAY_TYPE_YEAR){
            if($distance < 365 * 24 * 60 * 60){
                $this->notice("年付，合同的周期，不能小于365天");
            }
        }elseif($order['tenancy_pay_mode'] == OrderModel::PAY_TYPE_HALF_YEAR){

        }elseif($order['tenancy_pay_mode'] == OrderModel::PAY_TYPE_QUARTER){

        }elseif($order['tenancy_pay_mode'] == OrderModel::PAY_TYPE_MONTH){

        }else{
            exit("pay_type err");
        }

    }

    function add(){
        $hid = _g("hid");
        if(!$hid){
            $this->notice("hid is null");
        }

        $house = HouseModel::db()->getById($hid);
        if(!$house){
            $this->notice("hid not in db");
        }

        if(_g("opt")){

            $data = array(
                'house_id'=>$hid,
                'deposit_price'=>_g('deposit_price'),
                'contract_start_time'=> strtotime( _g('contract_start_time')),
                'contract_end_time'=> strtotime( _g('contract_end_time')),
                'type'=>_g('type'),
                'price'=>_g('price'),
                'admin_id'=>$this->_adminid,
                'a_time'=>time(),
                "tenancy_pay_mode"=>_g('tenancy_pay_mode'),
                "master_pay_mode"=>_g('master_pay_mode'),
                "warn_trigger_time"=>_g('warn_trigger_time'),
            );


//            if(!$data['deposit_price'] ){
//                $this->notice("pid not in db");
//            }

            $data['deposit_price'] = (int)$data['deposit_price'];
            if(! $data['deposit_price'] || $data['deposit_price'] <= 0){
                $this->notice("押金，不能为空且 必须为正整数");
            }

            $data['price'] = (int)$data['price'];
            if(! $data['price'] || $data['price'] <= 0){
                $this->notice("合同金额，不能为空且 必须为正整数");
            }

            if(!$data['contract_start_time'] ){
                $this->notice("合同开始时间 不能为空");
            }

            if(!$data['contract_end_time'] ){
                $this->notice("合同结束时间 不能为空");
            }

            if($data['contract_start_time'] >= $data['contract_end_time']){
                $this->notice("合同开始时间不能 >= 结束时间");
            }

            $distance = $data['contract_end_time'] - $data['contract_start_time'];
            if($distance < 31 * 24 * 60 * 60){
                $this->notice("合同的周期，不能小于31天，因为最付款单元为：月");
            }

            if(!is_numeric($data['tenancy_pay_mode'])){
                $this->notice("租户付款方式不能为空");
            }

            if(!$data['type'] ){
                $this->notice("类型 不能为空");
            }

            if(!$data['master_pay_mode']){
                $this->notice("房主付款方式不能为空");
            }

            $data['warn_trigger_time'] = (int)$data['warn_trigger_time'];
            if(!$data['warn_trigger_time'] ){
                $this->notice("房主付款方式不能为空");
            }

            $uploadService = new UploadService();
            $uploadRs = $uploadService->contract('contract_attachment');
            if($uploadRs['code'] != 200){
                exit(" uploadService->contract_attachment error ".json_encode($uploadRs));
            }
            $data['contract_attachment'] = $uploadRs['msg'];

//            var_dump($data);exit;
            $newId = OrderModel::db()->add($data);
            $this->ok($newId,$this->_backListUrl);
        }

        $this->assign("house",$house);

        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());

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


            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['house_id'],
                    OrderModel::TYPE_DESC[$v['type']],
                    $v['price'],
                    $v['deposit_price'],
                    OrderModel::PAY_TYPE_DESC[$v['tenancy_pay_mode']],
                    OrderModel::PAY_TYPE_DESC[$v['master_pay_mode']],
                    $v['contract_attachment'],
                    get_default_date($v['contract_start_time']),
                    get_default_date($v['contract_end_time']),
                    get_default_date($v['a_time']),
                    $v['admin_id'],
                    '<a href="/product/no/goods/add/hid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 生成付款记录 </a>',
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