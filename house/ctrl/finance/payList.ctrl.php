<?php
class PayListCtrl extends BaseCtrl{

    public $orderService = null;

    function __construct($request)
    {
        parent::__construct($request);
        $this->orderService = new OrderService();
    }

    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("statusOptions", OrderPayListModel::getStatusOptions());
        $this->assign("payTypeOptions", OrderPayListModel::getPayTypeOptions());
        $this->assign("getFinanceDescOption", OrderModel::getFinanceDescOption());
        $this->assign("getCateTypeOptions", OrderModel::getCateTypeOptions());

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->display("/finance/order_list.html");
    }

//    function add(){
//        if(_g("opt")){
//
//            $gidsNums =_g("gidsNums");
////            $couponId = _g("couponId");
//            $memo = _g("memo");
//            $uid = _g("uid");
//            $share_uid = _g("share_uid");
//            $userSelAddressId = _g("userSelAddressId");
//
//
//            $rs = $this->orderService->doing($uid,$gidsNums,0 , $memo,$share_uid , $userSelAddressId);
//            if($rs['code'] == 200){
//                $this->ok("成功");
//            }else{
//                $this->notice($rs['code'] . "-".$rs['msg']);
//            }
//
//        }
//
//        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
//        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');
//
//        $this->addHookJS("/finance/order_add_hook.html");
//        $this->display("/finance/order_add.html");
//    }
    function payListRecord(){
        $id = _g("id");
        if(!$id){
            exit("id 为空");
        }
        $OrderPay = OrderPayListModel::db()->getById($id);
        if(!$OrderPay){
            exit("id 不在 db中");
        }
        $list = OrderPayListRecordModel::db()->getAll(" pay_list_id = $id");
        $tableBody = "<tr><td>无记录</td></tr>";
        if($list){
            foreach ($list as $k=>$v){
                $tableBody .= "<tr>";
                $tableBody .= "<td>{$v['id']}</td>";
                $tableBody .= "<td>{$v['price']}</td>";
                $tableBody .= "<td>{$v['memo']}</td>";
                $tableBody .= "<td>{$v['admin_id']}</td>";
                $tableBody .= "<td>".get_default_date($v['a_time'])."</td>";
                $tableBody .= "<td><span class='label label-sm label-success'>删除</span></td>";
                $tableBody .= "</tr>";
            }
        }
        $data = array(
            'tableBody'=>$tableBody
        );

        $html = $this->_st->compile("/finance/pay_list_record_win.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function addPayListRecord(){
        $id = _g("id");
        if(!$id){
            exit("id 为空");
        }
        $OrderPay = OrderPayListModel::db()->getById($id);
        if(!$OrderPay){
            exit("id 不在 db中");
        }
//        $order = OrderModel::db()->getById($record['oid']);
//        if(!$order){
//            exit("pay_list oid not in db");
//        }
        if(_g('opt')){

//            $no = _g("pay_third_no");
//            $payType = _g("pay_type");
//            $real_price = _g("real_price");
            $price = _g("price");
            $memo = _g("memo");
//            if(!$no){
//                out_ajax(600,"三方流水号为空");
//            }
//
//            if(!$payType){
//                out_ajax(601,"支付类型为空");
//            }
//
//            if(!$real_price){
//                out_ajax(601,"实际收支为空");
//            }

            if(!$price){
                out_ajax(601,"金额不能为空");
            }

            if ($price > $OrderPay['final_price']){
                out_ajax(602,"不能大于实际金额 ".$OrderPay['final_price']);
            }

            $data = array(
                'price'=>$price,
                'memo'=>$memo,
                'a_time'=>time(),
                'house_id'=>$OrderPay['house_id'],
                'oid'=>$OrderPay['oid'],
                'type'=>$OrderPay['type'],
                'category'=>$OrderPay['category'],
                'admin_id'=>$this->_adminid,
                'pay_list_id'=>$OrderPay['id'],

            );
            OrderPayListRecordModel::db()->add($data);
//            $upRs = OrderPayListModel::db()->upById($id,$data);
//            $list = OrderPayListModel::db()->getRow("oid = {$record['oid']} and status = ".OrderPayListModel::STATUS_OK);
//            if(!$list){//证明所有的支付记录均已完成，可以更新<上级>状态了
//                OrderModel::upStatus($real_price['oid'],OrderModel::STATUS_FINISH);
//            }
            out_ajax(200);
        }

        $data = array(
            'getPayTypeOptions'=>OrderPayListModel::getPayTypeOptions(),
            'id'=>$id,
            "oid"=>$OrderPay['oid'],
            "house_id"=>$OrderPay['house_id'],
            'price'=>$OrderPay['price'],
            'final_price'=>$OrderPay['final_price'],
        );

        $arr = array("a"=>1);
        $html = $this->_st->compile("/finance/add_pay_list_record.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function upStatus(){
        $id = _g("id");
        if(!$id){
            exit("id 为空");
        }
        $record = OrderPayListModel::db()->getById($id);
        if(!$record){
            exit("id 不在 db中");
        }
//        $order = OrderModel::db()->getById($record['oid']);
//        if(!$order){
//            exit("pay_list oid not in db");
//        }
        if(_g('opt')){

//            $no = _g("pay_third_no");
//            $payType = _g("pay_type");
//            $real_price = _g("real_price");
//            if(!$no){
//                out_ajax(600,"三方流水号为空");
//            }
//
//            if(!$payType){
//                out_ajax(601,"支付类型为空");
//            }
//
//            if(!$real_price){
//                out_ajax(601,"实际收支为空");
//            }
            $memo = _g("memo");
            $price = _g("price");
            if(!$price){
                out_ajax(601,"金额不能为空");
            }

            if ($price > $record['final_price']){
                out_ajax(602,"不能大于实际金额 ".$record['final_price']);
            }

            $data = array(
//                'pay_type'=>$payType,
//                'pay_third_no'=>$no,
//                'a_time'=>time(),
                'real_price'=>$price,
                'memo'=>$memo,
                'status'=>OrderPayListModel::STATUS_OK,
            );
            $upRs = OrderPayListModel::db()->upById($id,$data);

            $list = OrderPayListModel::db()->getRow("oid = {$record['oid']} and status = ".OrderPayListModel::STATUS_OK);
            if(!$list){//证明所有的支付记录均已完成，可以更新<上级>状态了
                OrderModel::upStatus($record['oid'],OrderModel::STATUS_FINISH);
            }
            out_ajax(200);
        }

        $data = array(
            'getPayTypeOptions'=>OrderPayListModel::getPayTypeOptions(),
            'id'=>$id,
            "oid"=>$record['oid'],
            "house_id"=>$record['house_id'],
            "final_price"=>$record['final_price'],
        );

        $html = $this->_st->compile("/finance/pay_list_up_status.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = OrderPayListModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'house_id',
                'oid',
                'type',
                'price',
                'start_time',
                'end_time',
                'pay_third_no',
                'status',
                'pay_type',
                'pay_time',
                'final_price',
                'advance_time',
                'warn_trigger_time',
                'a_time',
                'category',
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

            $data = OrderPayListModel::db()->getAll($where . $order);
            foreach($data as $k=>$v){
                $payType = "--";
                if($v['pay_type']){
                    $payType = OrderPayListModel::PAY_TYPE_DESC[$v['pay_type']];
                }
                $upStatusBnt = "";
                $addPayListRecordBnt = "";
                $payListRecordBnt = "";
                if($v['status'] == OrderPayListModel::STATUS_WAIT){
                    $upStatusBnt =  '<a class="btn red btn-xs margin-bottom-5 upStatus" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 完结 </a>';
                    $addPayListRecordBnt = '<a class="btn blue btn-xs margin-bottom-5 addPayListRecord" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 添加收付款记录 </a>';
                    $payListRecordBnt =  '<a class="btn yellow btn-xs margin-bottom-5 payListRecord" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 查看收付款记录 </a>';
                }
//                $shareUserName = "";
//                if(arrKeyIssetAndExist($v,'share_uid')){
//                    $shareUserName = UserModel::db()->getOneFieldValueById($v['share_uid'],'nickname',"--");
//                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['house_id'],
                    $v['oid'],
                    OrderModel::FINANCE_DESC[$v['type']],
                    OrderModel::CATE_DESC[$v['category']],
                    $v['price'],
                    $v['final_price'],
                    get_default_date($v['start_time']),
                    get_default_date($v['end_time']),
//                    $v['pay_third_no'],
//                    $payType,
//                    get_default_date($v['warn_trigger_time']),
//                    OrderModel::STATUS_DESC[$v['status']],
//                    UserModel::db()->getOneFieldValueById($v['uid'],'nickname',"--"),
//                    $shareUserName,
//                    $v['agent_id']."-".$v['address_agent'],
                    OrderPayListModel::STATUS_DESC[$v['status']],
                    get_default_date($v['pay_time']),
                    get_default_date($v['advance_time']),
                    get_default_date($v['a_time']),

                    $upStatusBnt ."<br/>".$addPayListRecordBnt ."<br/>".$payListRecordBnt,
//                    '<a target="_blank"  href="/finance/no/order/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
//                    '<a target="_blank"  href="/finance/no/order/edit/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 编辑 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_ONE.'&oids='.$v['id'].'&uid=1" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 一级代理提现 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_TWO.'&oids='.$v['id'].'&uid=2" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 二级代理提现 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_FACTORY.'&oids='.$v['id'].'&fid=3" class="btn yellow btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 工厂提现 </a>'.
//                    '<button class="btn btn-xs default red editship margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 快递信息</button>'. "&nbsp;",
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


    function edit(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $order = OrderModel::db()->getById($id);


        if(_g("opt")){
            $data = array(
                'express_no'=>_g("express_no"),
            );

            OrderModel::db()->upById($id,$data);
            $this->ok("成功");
        }


        $this->assign("order",$order);

        $this->addHookJS("/finance/order_edit_hook.html");
        $this->display("/finance/order_edit.html");


    }

    function detail(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $order = OrderModel::db()->getById($id);
        if(!$order){
            $this->notice("id  not in db");
        }
        $orderService =  new OrderService();
        //产品/商品列表
        $order['goods_list'] = $orderService->getOneDetail($id)['msg'];
        //添加时间
        $order['dt'] = get_default_date($order['a_time']);
        //支付时间
        $order['pay_time_dt'] = get_default_date($order['pay_time']);
        $order['u_time_dt'] = get_default_date($order['u_time']);
        //签收时间
        $order['signin_time_dt'] = get_default_date($order['signin_time']);
        //发货时间
        $order['expire_time_dt'] = get_default_date($order['expire_time']);
        $order['status_desc'] = OrderModel::STATUS_DESC[$order['status']];
        //订单包括总商品数
        $order['goods_total_num'] = count(explode(",",$order['gids']));


        $order['agent_one_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['agent_one_withdraw']];
        $order['agent_two_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['agent_two_withdraw']];
        $order['factory_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['factory_withdraw']];

        $address = array("area"=>"","detail"=>"");
        if(arrKeyIssetAndExist($order,'address_id')){
            $addressService =  new UserAddressService();
            $addressRow = $addressService->getById($order['address_id']);
            if($addressRow['code'] != 200){
                $this->notice($addressRow['msg']);
            }
            $addressRow = $addressRow['msg'];
            $address['area'] = $addressRow['province_cn'] . "-" .   $addressRow['city_cn'] . "-" .  $addressRow['county_cn']  . "-" .  $addressRow['town_cn'];
            $address['detail'] = $addressRow['address'];
        }

        $agent = null;
        $agentFather = null;
        $shareUser = null;

        if(arrKeyIssetAndExist($order,'share_uid')){
            $shareUser = UserModel::db()->getById($order['share_uid']);
        }

        if(arrKeyIssetAndExist($order,'agent_id')){
            $agent = AgentModel::db()->getById($order['agent_id']);
            if($agent['type'] == AgentModel::ROLE_LEVEL_TWO){
                $agentFather = AgentModel::db()->getById($agent['invite_agent_uid']);
            }
        }

        $this->assign("address",$address);

        $this->assign("shareUser",$shareUser);
        $this->assign("agentFather",$agentFather);
        $this->assign("agent",$agent);
        $this->assign("orderDetail",$order);

        $this->display("/finance/order_detail.html");


//        $category = ProductCategoryModel::db()->getById($product['category_id']);
//        $product['category_name'] = $category['name'];
//
//        $admin = AdminUserModel::db()->getById($product['admin_id']);
//        $product['admin_name'] = $admin['uname'];
//
//        $product['status_desc'] = ProductModel::STATUS[$product['status']];
//
//        $product['desc_attr_arr'] = "";
//        if(arrKeyIssetAndExist($product,'desc_attr')){
//            $product['desc_attr_arr'] = json_decode($product['desc_attr'],true);
//        }
//
//        $factory = FactoryModel::db()->getById($product['factory_uid']);
//
//        $product['factory'] = $factory['title'];
//        if(arrKeyIssetAndExist($product,'pic')){
//            $pics = explode(",",$product['pic']);
//            foreach ($pics as $k=>$v) {
//                $product['pics'][] = get_product_url($v);
//            }
//        }
//
//        $goodsList = GoodsModel::getListByPid($id);
//        $product['goods_num'] = 0;
//        if($goodsList){
//            $product['goods_num'] = count($goodsList);
//        }
//
//        $attributeArr = ProductModel::attrParaParserToName($product['attribute']);

//        $this->assign("goodsList",$goodsList);
//        $this->assign("product",$product);

    }

    function getDataListTableWhere(){
        $where = 1;


        $no = _g("no");
        $total_price_from = _g("total_price_from");
        $total_price_to = _g("total_price_to");
        $payType = _g('payType');
        $status = _g("status");


        $username = _g('username');
        $share_username = _g('share_username');
        $address = _g('address');

        $from = _g("from");
        $to = _g("to");

        $pay_from = _g("pay_from");
        $pay_to = _g("pay_to");


        $num = _g("num");
        $haulage = _g("haulage");


        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($no)
            $where .=" and no = '$no' ";

        if($total_price_from)
            $where .=" and total_price >= '$total_price_from' ";

        if($total_price_to)
            $where .=" and total_price <= '$total_price_to' ";

        if($status)
            $where .=" and status = '$status' ";

        if($payType)
            $where .=" and pay_type = '$payType' ";

        if($share_username){
            $userService = new UserService();
            $where .= $userService->searchUidsByKeywordUseDbWhere($share_username);
        }

        if($address)
            $where .=" and address like '%$address%' ";

        if($num)
            $where .=" and num = '$num' ";

        if($haulage)
            $where .=" and haulage = '$haulage' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($pay_from)
            $where .=" and a_time >=  ".strtotime($from);

        if($pay_to)
            $where .=" and a_time <= ".strtotime($to);

        if($username){
            $userService = new UserService();
            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
        }

        return $where;
    }


}