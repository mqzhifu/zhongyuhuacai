<?php
class RefundCtrl extends BaseCtrl{

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

        $statusOP = constArrCoverOptionsHtml(OrderService::REFUND_STATS);
        $typeOP = constArrCoverOptionsHtml(OrderService::REFUND_TYPE_DESC);
        $reasonOP = constArrCoverOptionsHtml(OrderService::REFUND_REASON_DESC);



        $this->assign("statusOP",$statusOP);
        $this->assign("typeOP",$typeOP);
        $this->assign("reasonOP",$reasonOP);


        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->display("/finance/refund_list.html");
    }
    //退款
    function refund(){
        $id = _g("id");
        if(!$id)
            $this->notice("id null");

        $info = OrderRefundModel::db()->getById($id);
        if(!$info){
            $this->notice("id not in db");
        }

        if($info['status'] != OrderService::REFUND_STATS_APPLY){
            $this->notice("只有 记录为 ：申请退款状态 ，才能进此页面");
        }

        if(_g("opt")) {
            $status = _g("status");
            $memo = _g("memo");
            if (!$status)
                $this->notice("status null");

            $service = new OrderService();
            $payService = new PayService();
//            $order = OrderModel::db()->getById($info['oid']);
            if($status == 2){//通过
                $wxPayRefundBack = $payService->wxPayRefund($info['oid']);
                if($wxPayRefundBack['code'] != 200){
                    $this->notice("微信接口请求退款异常,".$wxPayRefundBack['msg']);
                }
                //更新订单状态为  退款完成
                $service->upStatus($info['oid'],OrderModel::STATUS_REFUND_FINISH,array('refund_memo'=>$memo));
            }else{//拒绝
                //回滚订单状态
                $service->refundCancelAndRollbackStatus($info['oid'],$info['lock_order_status']);
            }
            OrderRefundModel::db()->upById($id,array('memo'=>$memo,'audit_time'=>time(),'audit_admin_id'=>$this->_adminid,'status'=>$status));
            $this->ok("成功");
        }

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/finance/order_refund_hook.html");
        $this->display("/finance/order_refund.html");
    }
    //帮助用户在后台申请退款
    function apply(){
        $id = _g("id");
        if(!$id)
            $this->notice("id null");

        $info = OrderModel::db()->getById($id);
        if(!$info){
            $this->notice("id not in db");
        }

        if($info['status'] != OrderModel::STATUS_PAYED){
            $this->notice("状态错误：只有状态为：已支付，才可进入此页面!!!");
        }

        $rs = $this->orderService->applyRefund($id,$info['uid'],1,"管理员后台帮忙申请",1,"","");
        var_dump($rs);
        if($rs['code'] == 200){
            $this->ok("成功");
        }else{
            $this->notice("失败:".$rs['msg']);
        }
    }

    function add(){
        if(_g("opt")){

            $gidsNums =_g("gidsNums");
            $couponId = _g("couponId");
            $memo = _g("memo");
            $uid = _g("uid");
            $share_uid = _g("share_uid");
            $userSelAddressId = _g("userSelAddressId");


            $order = $this->orderService->doing($uid,$gidsNums,$couponId,$memo,$share_uid,$userSelAddressId);
            $this->ok("成功");
        }

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/finance/order_add_hook.html");
        $this->display("/finance/order_add.html");
    }

    function editShip(){
        $id = _g("id");
        if(!$id){
            exit("id 为空");
        }
        $order = OrderModel::db()->getById($id);
        if(!$order){
            exit("id 不在 db中");
        }
        if(_g('opt')){
            $no = _g("no");
            $shipType = _g("ship_type");

            $data = array(
                'ship_time'=>time(),
                'express_no'=>$no,
                'ship_type'=>$shipType,
            );

            $upRs = OrderModel::db()->upById($id,$data);

            var_dump($upRs);
            var_dump($data);exit;
        }
        $shipTypeDesc = OrderService::SHIP_TYPE_DESC;
        $shipTypeDescHtml = "";
        foreach ($shipTypeDesc as $k=>$v) {
            $shipTypeDescHtml .= "<option name='$k'>$v</option>";
        }

        $data = array(
            'shipTypeDescHtml'=>$shipTypeDescHtml,
            'id'=>$id,
        );


        $html = $this->_st->compile("/finance/order_edit_ship.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = OrderRefundModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'oid',
                'status',
                'type',
                'reason',
                'content',
                'mobile',
                'price',
                '',
                'uid',
                'a_time',
                ''
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

            $data = OrderRefundModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
//                $payType = "--";
//                if($v['pay_type']){
//                    $payType = OrderModel::PAY_TYPE_DESC[$v['pay_type']];
//                }
//
//                $refundBnt = "";
//                if($v['status'] == OrderModel::STATUS_REFUND){
////                    $refundBnt = '<button class="btn btn-xs default green refund btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-link"></i>退款审批</button>';
//                    $refundBnt =  '<a href="/finance/no/order/refund/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 退款审批 </a>';
//                }
//
//                $shareUserName = "";
//                if(arrKeyIssetAndExist($v,'share_uid')){
//                    $shareUserName = UserModel::db()->getOneFieldValueById($v['share_uid'],'nickname',"--");
//                }

                $img = "";
                if($v['pic']){
                    $img = "<img src='".get_refund_url( $v['pic'])."' width=50 height=50 />";
                }

                $refundBnt = "";
                if($v['status'] == OrderService::REFUND_STATS_APPLY  ){
                    $refundBnt =  '<a href="/finance/no/refund/refund/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 退款审批 </a>';
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['oid'],
                   OrderService::REFUND_STATS[$v['status']],
                    OrderService::REFUND_TYPE_DESC[$v['type']],
                    OrderService::REFUND_REASON_DESC[ $v['reason']],
//                    $v['memo'],
//                    OrderModel::STATUS_DESC[$v['status']],
//                    $shareUserName,
                    $v['content'],
//                    $v['title'],
//                    get_default_date($v['pay_time']),
                    $v['mobile'],
                   fenToYuan( $v['price']),
                    $img,
                    UserModel::db()->getOneFieldValueById($v['uid'],'nickname',"--"),
                    get_default_date($v['a_time']),
                    $refundBnt,
//                    '<a target="_blank"  href="/finance/no/order/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
//                    '<a target="_blank"  href="/finance/no/order/edit/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 编辑 </a>';
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


        $oid = _g("oid");
        $status = _g("status");
        $type = _g('type');
        $reason = _g('reason');
        $content = _g('content');
        $mobile = _g('mobile');

        $from = _g("from");
        $to = _g("to");

        $price_from = _g("price_from");
        $price_to = _g("price_to");

        $username = _g("username");
        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($oid)
            $where .=" and oid = '$oid' ";

        if($status)
            $where .=" and status = '$status' ";

        if($type)
            $where .=" and type = '$type' ";

        if($content)
            $where .=" and content like '%$content%' ";

        if($reason)
            $where .=" and reason = '$reason' ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($price_from)
            $where .=" and price >=  ".($price_from);

        if($price_to)
            $where .=" and a_time <= ".($price_to);

        if($username){
            $userService = new UserService();
            $where .= $userService->searchUidsByKeywordUseDbWhere($username);

        }

        return $where;
    }


}