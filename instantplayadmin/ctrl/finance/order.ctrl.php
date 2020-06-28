<?php
class OrderCtrl extends BaseCtrl{

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

        $this->assign("statusOptions", OrderModel::getStatusOptions());
        $this->assign("payTypeOptions", OrderModel::getPayTypeOptions());

        $this->display("/finance/order_list.html");
    }
    //退款
    function refund(){
        $id = _g("id");
        if(!$id)
            $this->notice("id null");

        $order = OrderModel::db()->getById($id);
        if(!$order){
            $this->notice("id not in db");
        }

//        if($order['status'] != OrderModel::STATUS_REFUND){
//            $this->notice("只有 用户 申请退款 ，才能进此页面");
//        }

        if(_g("opt")) {
            $status = _g("status");
            $memo = _g("memo");
            if (!$status)
                $this->notice("status null");

            $service = new OrderService();
            $payService = new PayService();
            if($status == 1){
                $wxPayRefundBack = $payService->wxPayRefund($id);
                var_dump($wxPayRefundBack);exit;
                if($wxPayRefundBack['code'] != 200){
                    $this->notice("微信接口请求退款异常,".$wxPayRefundBack['msg']);
                }
                $service->upStatus($id,OrderModel::STATUS_REFUND_FINISH,array('refund_memo'=>$memo));
                var_dump($wxPayRefundBack);exit;
            }else{
                $service->upStatus($id,OrderModel::STATUS_REFUND_REJECT,array('refund_memo'=>$memo));
            }

            $this->ok("成功");
        }

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/finance/order_refund_hook.html");
        $this->display("/finance/order_refund.html");
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

    function getWhere(){
        $where = " 1 ";
        if($mobile = _g("mobile"))
            $where .= " and mobile = '$mobile'";

        if($message = _g("message"))
            $where .= " and mobile like '%$message%'";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " and add_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and add_time <= '".strtotime($to)."'";
        }


        return $where;
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
                '',
                '',
                '',
                '',
                'add_time',
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

            $data = OrderModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $payType = "--";
                if($v['pay_type']){
                    $payType = OrderModel::PAY_TYPE_DESC[$v['pay_type']];
                }

                $refundBnt = "";
                if($v['status'] == OrderModel::STATUS_REFUND){
//                    $refundBnt = '<button class="btn btn-xs default green refund btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-link"></i>退款审批</button>';
                    $refundBnt =  '<a href="/finance/no/order/refund/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 退款审批 </a>';
                }


                $shareUserName = "";
                if(arrKeyIssetAndExist($v,'share_uid')){
                    $shareUserName = UserModel::db()->getOneFieldValueById($v['share_uid'],'nickname',"--");
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['no'],
                    $v['pids'],
//                    ProductModel::db()->getOneFieldValueById($v['pid'],'title'),
                    $v['gids'],
                    $v['total_price'],
                    $payType,
                    OrderModel::STATUS_DESC[$v['status']],
                    UserModel::db()->getOneFieldValueById($v['uid'],'nickname',"--"),
                    $shareUserName,
                    $v['agent_id']."-".$v['address_agent'],
                    get_default_date($v['a_time']),
                    get_default_date($v['pay_time']),
                    $v['nums'],
                    $v['haulage'],
                    $refundBnt.
                    '<a target="_blank"  href="/finance/no/order/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
                    '<a target="_blank"  href="/finance/no/order/edit/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 编辑 </a>'.
                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_ONE.'&oids='.$v['id'].'&uid=1" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 一级代理提现 </a>'.
                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_TWO.'&oids='.$v['id'].'&uid=2" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 二级代理提现 </a>'.
                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_FACTORY.'&oids='.$v['id'].'&fid=3" class="btn yellow btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 工厂提现 </a>',
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
            $addressRow = $addressService->getRowById($order['address_id']);
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
        $openid = _g("openid");
        $sex = _g("sex");
        $status = _g("status");

        $nickname = _g('name');
//        $nickname_byoid = _g('nickname_byoid');
//        $content = _g('content');
//        $is_online = _g('is_online');
//        $uname = _g('uname');

        $from = _g("from");
        $to = _g("to");

//        $trigger_time_from = _g("trigger_time_from");
//        $trigger_time_to = _g("trigger_time_to");


//        $uptime_from = _g("uptime_from");
//        $uptime_to = _g("uptime_to");


        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($openid)
            $where .=" and openid = '$openid' ";

        if($sex)
            $where .=" and sex = '$sex' ";

        if($status)
            $where .=" and status = '$status' ";

        if($nickname)
            $where .=" and nickname = '$nickname' ";

//        if($nickname_byoid){
//            $user = wxUserModel::db()->getRow(" nickname='$nickname_byoid'");
//            if(!$user){
//                $where .= " and 0 ";
//            }else{
//                $where .=  " and openid = '{$user['openid']}' ";
//            }
//        }

//        if($content)
//            $where .= " and content like '%$content%'";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

//        if($trigger_time_from)
//            $where .=" and trigger_time_from >=  ".strtotime($trigger_time_from);
//
//        if($trigger_time_to)
//            $where .=" and trigger_time_to <= ".strtotime($trigger_time_to);
//
//        if($uptime_from)
//            $where .=" and up_time >=  ".strtotime($uptime_from);
//
//        if($uptime_to)
//            $where .=" and up_time <= ".strtotime($uptime_to);



//        if($is_online)
//            $where .=" and is_online = '$is_online' ";


//        if($uname)
//            $where .=" and uname = '$uname' ";

        return $where;
    }


}