<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class WithdrawCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $statusOptions = "";
        foreach ( WithdrawMoneyService::WITHDRAW_STATUS_DESC as $k=>$v){
            $statusOptions .= "<option value'{$k}'>$v</option>";
        }


        $levelOptions = "";
        foreach ( AgentModel::ROLE as $k=>$v){
            $levelOptions .= "<option value'{$k}'>$v</option>";
        }

        $this->assign("levelOptions",$levelOptions);
        $this->assign("statusOptions",$statusOptions);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->display("/finance/withdraw_list.html");
    }

    function add(){
        $oids = _g("oids");
        if(!$oids){
            exit("oids is null");
        }

        $role = _g("role");
        if(!$role){
            exit("role is null");
        }
        $oids = explode(",",$oids);
        $priceTotal = 0;
        $showHtml = "";
        $agent = null;
        foreach ($oids as $k=>$v) {
            $order = OrderModel::db()->getById($v);
            if($order['status'] != OrderModel::STATUS_FINISH){
                $this->notice($v. " 订单非完成状态，不允许 提现");
            }

            if($role == AgentModel::ROLE_FACTORY){
                if($order['factory_withdraw'] ==WithdrawMoneyService::WITHDRAW_ORDER_STATUS_OK){
                    $this->notice($v." 订单 已提取过了");
                }
            }else{
                if($order['agent_one_withdraw'] ==WithdrawMoneyService::WITHDRAW_ORDER_STATUS_OK){
                    $this->notice($v." 订单 已提取过了");
                }
            }

            if($role != AgentModel::ROLE_FACTORY){
                $agent = AgentModel::db()->getById($order['agent_id']);
                $fee_percent = $agent['fee_percent'] / 100;
                $price = $order['total_price'] * $fee_percent;
                $priceTotal += $price;
                $showHtml .= "$v($price)";
            }
        }

        if(_g('opt')){
            $data = array(
                'price'=>_g('price'),
                'orders_ids'=>_g('price'),
                'status'=>_g('price'),
                'type'=>_g('role'),
                'a_time'=>time(),
            );
            if(AgentModel::ROLE_FACTORY == $role){
                $data['admin_id'] = $this->_adminid;
            }else{
                $data['uid'] = -1;
            }

            $newId = WithdrawModel::db()->add($data);

            var_dump($newId);exit;
        }else{
//            $category = ProductCategoryModel::db()->getById($product['category_id']);
//
//            $statusSelectOptionHtml = ProductModel::getStatusSelectOptionHtml();
//            $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
//            $this->assign("categoryName",$category['name']);
//        $this->assign("categoryOptions", ProductCategoryModel::getSelectOptionHtml());

            $this->assign("priceTotal",$priceTotal);
            $this->assign("show",$showHtml);
            $this->assign("agent",$agent);
            $this->assign("role",AgentModel::ROLE[$role]);


            $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
            $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

//            $this->addHookJS("/finance/withdraw_add_hook.html");
            $this->display("/finance/withdraw_add.html");
        }

    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = WithdrawModel::db()->getCount($where);

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
                '',
                '',
                'a_time',
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

            $data = WithdrawModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $user  = AgentModel::db()->getById($v['agent_id']);
                $username = "未知";
                if($user){
                    $username = $user['title'];
                }

                $auditBnt = "";
                if($v['status'] != WithdrawMoneyService::WITHDRAW_STATUS_FINISH ){
                    $auditBnt = '<button class="btn btn-xs default red upstatus margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 审核</button>';
                }




                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $username,
                    $v['price'],
                    $v['orders_ids'],
                    get_default_date($v['a_time']),
                    WithdrawMoneyService::WITHDRAW_STATUS_DESC[$v['status']],
                    $v['memo'],
                    WithdrawMoneyService::TYPE_DESC[$v['type']],
                    get_default_date($v['audit_time']),
                    '<a target="_blank" href="/finance/no/withdraw/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'.
                    $auditBnt,
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
        $aid = _g("id");
        $info = WithdrawModel::db()->getById($aid);

        //添加时间
        $info['dt'] = get_default_date($info['a_time']);
        //状态 描述
        $info['status_desc'] = WithdrawMoneyService::WITHDRAW_STATUS_DESC [$info['status']];

        $auditAdminName = "";
        $auditTime = "";
        if($info['audit_admin_id']){
            $auditAdmin = AdminUserModel::db()->getById($info['audit_admin_id']);
            $auditAdminName = $auditAdmin['uname'];
        }

        $auditTime =    get_default_date($info['audit_time']);


        $this->assign("auditAdminName",$auditAdminName);
        $this->assign("auditTime",$auditTime);

        $this->assign("info",$info);

        $this->display("/finance/withdraw_detail.html");
    }

    function upstatus(){
        $aid = _g("id");
        $withdraw = WithdrawModel::db()->getById($aid);
        if(!$withdraw){
            exit(" id not in db.");
        }
        if(_g('opt')){
            $status = _g("status");
            if(!$status){
                out_ajax(7000);
            }

            if(!in_array($status,array_flip(WithdrawMoneyService::WITHDRAW_STATUS_DESC))){
                out_ajax(7001,"status is err ($status)");
            }

            $agent = AgentModel::db()->getById($withdraw['agent_id']);
            //更新订单状态
            $orderUpData = array();
            $orderStatus = 0;
            if($status == WithdrawMoneyService::WITHDRAW_STATUS_OK){
                $orderStatus = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_OK;
            }elseif($status == WithdrawMoneyService::WITHDRAW_STATUS_REJECT){
                $orderStatus = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_REJECT;
            }
            if($agent['type'] ==  AgentModel::ROLE_LEVEL_ONE){
                $orderUpData['agent_one_withdraw'] = $orderStatus;
            }elseif($agent['type'] ==  AgentModel::ROLE_LEVEL_TWO){
                $orderUpData['agent_two_withdraw'] = $orderStatus;
            }else{
                exit("agent type is err.");
            }

            $orderService = new OrderService();
            $orderService->upWithdrawStatus($withdraw['orders_ids'],$orderUpData);

            $data = array('status'=>$status,'u_time'=>time(),'audit_admin_id'=>$this->_adminid,'audit_time'=>time());
            $memo = _g("memo");
            if($memo){
                $data['memo'] = $memo;
            }

            $rs = WithdrawModel::db()->upById($aid,$data);

            out_ajax(200,$rs);
        }
        $statusDesc = WithdrawMoneyService::WITHDRAW_STATUS_DESC;
        $statusDescRadioHtml = "";
        foreach ($statusDesc as $k=>$v) {
            if($k == WithdrawMoneyService::WITHDRAW_STATUS_FINISH || $k == WithdrawMoneyService::WITHDRAW_STATUS_WAIT){
                //这两种状态不放开，没必要 给后台管理员操作
                continue;
            }
            $statusDescRadioHtml .= "<label><input name='status' type='radio' value={$k} />".$v . "</label>";
        }



        $data = array(
            'statusDescRadioHtml'=>$statusDescRadioHtml,
            "id"=>$aid,
            "oids"=>$withdraw['orders_ids'],
            "price"=>$withdraw['price'],
        );

        $html = $this->_st->compile("/finance/withdraw_upstatus.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function getDataListTableWhere(){
//        $openid = _g("openid");
//        $nickname = _g('name');
//        $nickname_byoid = _g('nickname_byoid');
//        $content = _g('content');
//        $is_online = _g('is_online');
//        $uname = _g('uname');
//        $trigger_time_from = _g("trigger_time_from");
//        $trigger_time_to = _g("trigger_time_to");
//        $uptime_from = _g("uptime_from");
//        $uptime_to = _g("uptime_to");

        $from = _g("from");
        $to = _g("to");
        $where = 1;
        $type = _g("type");
        $status = _g("status");
        $id = _g("id");
        $agentName = _g("agent_name");
        if($id)
            $where .=" and id = '$id' ";

        if($type)
            $where .=" and type = '$type' ";

        if($status)
            $where .=" and status = '$status' ";


        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($audit_time_from = _g("audit_time_from")){
            $audit_time_from .= ":00";
            $where .= " and audit_time >= '".strtotime($audit_time_from)."'";
        }

        if($audit_time_to = _g("audit_time_to")){
            $audit_time_to .= ":59";
            $where .= " and audit_time <= '".strtotime($audit_time_to)."'";
        }


        $AgentService = new AgentService();
        if($agentName){
            $where .= $AgentService->searchUidsByKeywordUseDbWhere($agentName);
        }

//        $userService =  new UserService();
//        if($username){
//            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
//        }

//
//        if($sex)
//            $where .=" and sex = '$sex' ";
//        if($nickname)
//            $where .=" and nickname = '$nickname' ";

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


        return $where;
    }


}