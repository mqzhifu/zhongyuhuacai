<?php
class OrderCtrl extends BaseCtrl{
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
        $this->notice("id null");

        $order = OrderModel::db()->getById($id);
        if(!$order){
            $this->notice("id not in db");
        }

        if($order['status'] != OrderModel::STATUS_REFUND){
            $this->notice("只有 用户 申请退款 ，才能进此页面");
        }
        if(_g("opt")) {
            $status = _g("status");
            $memo = _g("memo");
            if (!$status)
                $this->notice("status null");

            $service = new OrderService();
            $payService = new PayService();
            if($status == 1){
                $wxPayRefundBack = $payService->wxPayRefund($id);

                $service->upStatus($id,OrderModel::STATUS_REFUND_FINISH,array('refund_memo'=>$memo));
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
            $goods_id =_g("goods_id");
            $agent_uid = _g("agent_uid");
            $num = _g("num");
            $uid = _g("uid");

            if(!$goods_id)
                $this->notice("goods_id null");

            if(!$agent_uid)
                $this->notice("agent_uid null");

            if(!$num)
                $this->notice("num null");

            if(!$uid)
                $this->notice("uid null");

            $goods = GoodsModel::db()->getById($goods_id);
            $agent = AgentModel::db()->getById($agent_uid);
            $user = UserModel::db()->getById($uid);

            if(!$goods)
                $this->notice("goods_id not in db");

            if(!$agent)
                $this->notice("agent_uid not in db");

            if(!$user)
                $this->notice("uid not in db");

            $agentAddr = AgentModel::getAddrStrById($agent_uid);
            $data = array(
                'no'=>OrderModel::getNo(),
                'uid'=>$uid,
                'pid'=>$goods['pid'],
                'gid'=>$goods_id,
                'agent_uid'=>$agent_uid,
                'a_time'=> time(),
                'status'=>OrderModel::STATUS_WAIT_PAY,
                'pay_type'=>0,
                'pay_time'=>time(),
                'express_no'=>"",
                'address_agent'=>$agentAddr,
                'agent_withdraw_money_status'=> OrderModel::WITHDRAW_MONEY_AGENT_WAIT,
                'factory_withdraw_money_status'=>OrderModel::WITHDRAW_MONEY_FACTORY_WAIT,
                'num'=>$num,
            );
            $price = $goods['sale_price'] * $num;
            $data['price'] = $price;
            $data['pid'] = $goods['pid'];

            $newId = OrderModel::db()->add($data);
            $this->ok($newId,"",$this->_backListUrl);
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
                    AgentModel::db()->getOneFieldValueById($v['agent_uid'],'real_name','--'),
                    $v['address_agent'],
                    get_default_date($v['a_time']),
                    get_default_date($v['pay_time']),
                    $v['nums'],
                    $v['haulage'],
                    $refundBnt.
                    '<a href="/finance/no/order/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
                    '<a href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_ONE.'&oids='.$v['id'].'&uid=1" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 一级代理提现 </a>'.
                    '<a href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_TWO.'&oids='.$v['id'].'&uid=2" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 二级代理提现 </a>'.
                    '<a href="/finance/no/withdraw/add/role='.AgentModel::ROLE_FACTORY.'&oids='.$v['id'].'&fid=3" class="btn yellow btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 工厂提现 </a>',
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


    function detail(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }


        $orderInfo = OrderModel::db()->getById($id);
        $orderInfo['goods_total_num'] = count(explode(",",$orderInfo['gids']));

        $orderInfo['sigin_time_dt'] = 0;
        if(arrKeyIssetAndExist($orderInfo,'sigin_time')){
            $orderInfo['sigin_time_dt'] = get_default_date($orderInfo['sigin_time']);
        }

        $orderInfo['status_desc'] = OrderModel::STATUS_DESC[$orderInfo['status']];

        $orderService =  new OrderService();
        $orderInfo['goods_list'] = $orderService->getOneDetail($id)['msg'];

        $orderDetail = $orderInfo;//这里是个坑

//        $orderDetail = $orderService->getOneDetail($id);
//        $product = ProductModel::db()->getById($id);
        $orderDetail['dt'] = get_default_date($orderDetail['a_time']);
        $orderDetail['pay_time_dt'] = get_default_date($orderDetail['pay_time']);
        $orderDetail['u_time_dt'] = get_default_date($orderDetail['u_time']);
        $orderDetail['sigin_time_dt'] = get_default_date($orderDetail['sigin_time']);
        $orderDetail['expire_time_dt'] = get_default_date($orderDetail['expire_time']);


        $orderDetail['status_desc'] = OrderModel::STATUS_DESC[$orderDetail['status']];

//
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


        $this->assign("orderDetail",$orderDetail);
//        $this->assign("goodsList",$goodsList);
//        $this->assign("product",$product);

        $this->display("/finance/order_detail.html");
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