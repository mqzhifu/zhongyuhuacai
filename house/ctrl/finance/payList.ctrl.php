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

        $this->display("/finance/pay_list.html");
    }

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
        if(_g('opt')){
            $price = _g("price");
            $memo = _g("memo");

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
            out_ajax(200,"ok");
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
//                    $addPayListRecordBnt = '<a class="btn blue btn-xs margin-bottom-5 addPayListRecord" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 添加收付款记录 </a>';
//                    $payListRecordBnt =  '<a class="btn yellow btn-xs margin-bottom-5 payListRecord" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 查看收付款记录 </a>';
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
                    $v['real_price'],
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
//                    get_default_date($v['pay_time']),
                    $v['price_desc'],
                    get_default_date($v['advance_time']),
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
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


    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $house_id = _g("house_id");
        $oid = _g("oid");
        $type = _g("type");
        $category = _g('category');
        $price_from = _g("price_from");
        $price_to = _g("price_to");
        $real_price_from = _g("real_price_from");
        $real_price_to = _g("real_price_to");
        $status = _g("status");
        $from = _g("advance_time_from");
        $to = _g("advance_time_to");
        $price_memo = _g("price_memo");

        if($id)
            $where .=" and id = '$id' ";

        if($house_id)
            $where .=" and house_id = '$house_id' ";

        if($oid)
            $where .=" and oid = '$oid' ";

        if($type)
            $where .=" and type = '$type' ";

        if($category)
            $where .=" and category = '$category' ";


        if($price_from)
            $where .=" and price >= '$price_from' ";

        if($price_to)
            $where .=" and price <= '$price_to' ";

        if($real_price_from)
            $where .=" and real_price >= '$real_price_from' ";

        if($real_price_to)
            $where .=" and real_price <= '$real_price_to' ";

        if($status)
            $where .=" and status = '$status' ";

        if($price_memo)
            $where .=" and price_memo like '%$price_memo%' ";


//        if($payType)
//            $where .=" and pay_type = '$payType' ";
//        if($share_username){
//            $userService = new UserService();
//            $where .= $userService->searchUidsByKeywordUseDbWhere($share_username);
//        }
//

//
//        if($from)
//            $where .=" and a_time >=  ".strtotime($from);
//
//        if($to)
//            $where .=" and a_time <= ".strtotime($to);

//        if($pay_from)
//            $where .=" and a_time >=  ".strtotime($from);
//
//        if($pay_to)
//            $where .=" and a_time <= ".strtotime($to);
//
//        if($username){
//            $userService = new UserService();
//            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
//        }

        return $where;
    }


}