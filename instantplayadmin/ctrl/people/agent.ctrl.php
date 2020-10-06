<?php
class AgentCtrl extends BaseCtrl{
    public $agentService =  null;
    public $orderService = null;
    function __construct($request)
    {
        parent::__construct($request);
        $this->agentService = new AgentService();
        $this->orderService = new OrderService();
    }

    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $type = _g("type");
        if($type == 2){
            exit("暂未开发");
        }

        $this->assign("areaProvinceModelOptionsHtml", AreaProvinceModel::getSelectOptionsHtml());

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->assign("statusSelectOptionHtml",AgentModel::getStatusSelectOptionHtml());
        $this->display("/people/agent_list.html");
    }

    function getCountyData(){
        $id = _g("countyId");
        $data = AreaTownModel::db()->getAll("county_code=$id");
        echo json_encode($data);
    }

    function delOne(){
        $id = _g("id");
        AgentModel::db()->delById($id);
    }

    function detail(){
        $aid = _g("id");
        $agent = AgentModel::db()->getById($aid);

        //添加时间
        $agent['dt'] = get_default_date($agent['a_time']);
        //状态 描述
        $agent['status_desc'] = AgentModel::STATUS[$agent['status']];

        $agent['pic_url'] = get_agent_url($agent['pic']);
        $orders = $this->agentService->getAllOrderList($aid);
        if($orders){
            foreach ($orders as $k=>$v) {
//                $row['fee_percent'] = $v['price'] * $agent['fee_percent'];
                $orders[$k]['dt'] = get_default_date($v['a_time']);
                $orders[$k]['status_desc'] = OrderModel::STATUS_DESC[$v['status']];
            }
        }

        //代理 分享产品 总次数
        $share = ShareProductModel::db()->getAll(" agent_id = $aid");
        $shareCnt = 0;
        if($share){
            $shareCnt = count($share);
        }
        $this->assign("shareCnt",$shareCnt);
        //分享的产品连接，有多少个用户下单
        $shareOrderCnt = 0;
        //分享的产品连接，有多少个用户下单并完成
        $shareOrderFinishCnt = 0;
//        var_dump($orders);exit;
        if($orders){
            $shareOrderCnt = count($orders);
            foreach ($orders as $k=>$v){
                if($v['status'] == OrderModel::STATUS_FINISH){
                    $shareOrderFinishCnt++;
                }
            }
        }
        $this->assign("shareOrderCnt",$shareOrderCnt);
        $this->assign("shareOrderFinishCnt",$shareOrderFinishCnt);

        //可提现金额信息
        $allowWithDrawFeeInfo = $this->agentService->getFee($aid);
        //有几个子代理
        $subAgentCnt = 0;
        $SubAgentList = $this->agentService->getSubAgentList($aid);
        if($SubAgentList){
            $subAgentCnt = count($SubAgentList);
            foreach ($SubAgentList as $k=>$v){
                $SubAgentList[$k]['dt'] = get_default_date($v['a_time']);
            }
        }

        $auditAdminName = "";
        $auditTime = "";
        if($agent['audit_admin_id']){
            $auditAdmin = AdminUserModel::db()->getById($agent['audit_admin_id']);
            $auditAdminName = $auditAdmin['uname'];
        }

        $auditTime =    get_default_date($agent['audit_time']);


        $this->assign("auditAdminName",$auditAdminName);
        $this->assign("auditTime",$auditTime);

        $this->assign("SubAgentList",$SubAgentList);
        $this->assign("subAgentCnt",$subAgentCnt);

        $this->assign("fee",$allowWithDrawFeeInfo['fee']);

        $userLivePlaceDesc = UserModel::getAgentLivePlaceDesc($aid);
        $this->assign("userLivePlaceDesc",$userLivePlaceDesc);

        $this->assign("agent",$agent);
        $this->assign("orders",$orders);
//        $this->assign("userLog",$userLog);

        $this->display("/people/agent_detail.html");
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        $where = $this->getDataListTableWhere();
        $cnt = AgentModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'title',
                'real_name',
                'status',
                'province_code',
                'address',
                'sex',
                '',
                'mobile',
                'fee_percent',
                'a_time',
                '',
                '',
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
            $data = AgentModel::db()->getAll($where . $order . $limit);


            $agentService = new AgentService();

            $userService = new UserService();

            foreach($data as $k=>$v){
                $addressStr = $agentService->getAreaStr($v['id']);
                $subAgent = $agentService->getSubAgentList($v['id']);
                $subAgentNum = 0;
                if($subAgent){
                    $subAgentNum = count($subAgent);
                }
                $auditBnt = "";
                if($v['status'] == AgentService::STATUS_WAIT ){
                    $auditBnt = '<button class="btn btn-xs default red upstatus margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 审核</button>';
                }

                $userName = $userService->getUserNameByUid($v['uid']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    $v['real_name'],

                    AgentModel::STATUS[$v['status']],
//                    AreaProvinceModel::db()->getOneByOneField('code',$v['province_code'])['short_name'],
//                    AreaCityModel::db()->getOneByOneField('code',$v['city_code'])['short_name'],
//                    AreaCountyModel::db()->getOneByOneField('code',$v['county_code'])['short_name'],
//                    AreaTownModel::db()->getOneByOneField('code',$v['towns_code'])['short_name'],
                    $addressStr,
                    $v['address'],
                    UserModel::getSexDescByKey($v['sex']),
                    '<img height="30" width="30" src="'.get_agent_url($v['pic']).'" />',
                    $v['mobile'],
                    $v['fee_percent'],
                    get_default_date($v['a_time']),
                    $userName,
                    $v['invite_code'],
                    $subAgentNum,
                    '<a target="_blank" href="/people/no/agent/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'.
                    $auditBnt . "&nbsp;"
//                    '<a href="" class="btn yellow btn-xs margin-bottom-5 editone" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 编辑 </a>',
//                    '<button class="btn btn-xs default yellow delone" data-id="'.$v['id'].'" ><i class="fa fa-trash-o"></i>  删除</button>',
                );

                $records["data"][] = $row;
            }
        }



        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function upstatus(){
        $aid = _g("id");
        $agent = AgentModel::db()->getById($aid);
        if(!$agent){
            exit(" aid not in db.");
        }
        if(_g('opt')){
            $status = _g("status");
            if(!$status){
                out_ajax(7000);
            }

            $data = array('status'=>$status,'u_time'=>time(),'audit_admin_id'=>$this->_adminid,'audit_time'=>time());
            $memo = _g("memo");
            if($memo){
                $data['memo'] = $memo;
            }

            $rs = AgentModel::db()->upById($aid,$data);
//            $rs = "ok";
            out_ajax(200,"ok-" .$rs);
        }
        $statusDesc = AgentModel::STATUS;
        $statusDescRadioHtml = "";
        foreach ($statusDesc as $k=>$v) {
            $statusDescRadioHtml .= "<input name='status' type='radio' value={$k} />".$v;
        }

        $data = array(
            'statusDescRadioHtml'=>$statusDescRadioHtml,
            "id"=>$aid,
        );
//        $this->assign("agent",$agent);
//        $this->assign("statusDescRadioHtml",$statusDescRadioHtml);

        $html = $this->_st->compile("/people/agent_upstatus.html",$data);
        $html = file_get_contents($html);
        echo_json($html);
    }

    function add(){
        if(_g('opt')){
            $data =array(
                'title'=> _g('title'),
                'real_name'=> _g('realname'),
                'id_card_num'=> _g('id_card_num'),
                'mobile'=> _g('mobile'),
                'sex'=> _g('sex'),
                'status'=>AgentService::STATUS_WAIT,
                'type'=>AgentModel::ROLE_LEVEL_ONE,
                'address'=> _g('address'),
                'province_code'=> _g('province'),
                'city_code'=> _g('city'),
                'county_code'=> _g('county'),
                'town_code'=> _g('town'),
                'fee_percent'=> _g('fee_percent'),
                'sub_fee_percent'=>_g("sub_fee_percent"),
                'a_time'=>time(),
//                'villages'=> _g('villages'),
            );

            if(!$data['title']){
                $this->notice("店面名称 不能为空 ");
            }

            if(!$data['real_name']){
                $this->notice("真实姓名 不能为空 ");
            }

            if(!$data['id_card_num']){
                $this->notice("身份证号 不能为空 ");
            }

            if(!$data['sex']){
                $this->notice("性别 不能为空 ");
            }

            if(!$data['mobile']){
                $this->notice("手机号 不能为空 ");
            }

            if(!FilterLib::regex($data['mobile'],"phone")){
                $this->notice("手机号格式错误 ");
            }

            if(!$data['fee_percent']){
                $this->notice("佣金比例 不能为空 ");
            }

            $data['fee_percent'] = (int)$data['fee_percent'];
            if(!$data['fee_percent']){
                $this->notice("佣金比例 只允许正整数 ");
            }

            if(!$data['address']){
                $this->notice("详细地址 不能为空 ");
            }

            if(!$data['sub_fee_percent']){
                $this->notice("二级佣金比例 不能为空 ");
            }
            $data['sub_fee_percent'] = (int)$data['sub_fee_percent'];
            if(!$data['sub_fee_percent']){
                $this->notice("二级佣金比例 只允许正整数 ");
            }

            $uploadService = new UploadService();
            $uploadRs = $uploadService->agent('pic');
            if($uploadRs['code'] != 200){
                $this->notice(" uploadService->product error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            $newId = AgentModel::add($data);
            $invite_code = intToStr($newId);
            AgentModel::db()->upById($newId,array("invite_code"=>$invite_code));

            $this->ok($newId,$this->_backListUrl);
        }

        $sexOption = UserModel::getSexOptions();

        $this->assign("sexOption",$sexOption);

        $province = AreaProvinceModel::getSelectOptionsHtml();
        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());

        $this->assign("provinceOption",$province);
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);

//        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
//        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/people/agent_add_hook.html");

        $this->addHookJS("/layout/place.js.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/people/agent_add.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $title = _g('title');//店铺名称
        $real_name = _g('realname');//真实姓名
        $status = _g("status");
        $province = _g('province');
        $address = _g('address');
        $sex = _g("sex");
        $mobile = _g('mobile');
        $fee_percent = _g('fee_percent');
        $from = _g("from");
        $to = _g("to");
        $invite_code = _g('invite_code');

        if($id)
            $where .=" and id = '$id' ";

        if($title)
            $where .=" and title like '%$title%' ";

        if($real_name)
            $where .=" and real_name like '%$real_name%' ";

        if($address)
            $where .=" and address like '%$address%' ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($fee_percent)
            $where .=" and fee_percent = '$fee_percent' ";

        if($sex)
            $where .=" and sex = '$sex' ";

        if($status)
            $where .=" and status = '$status' ";

        if($province)
            $where .=" and province_code = '$province' ";

        if($invite_code)
            $where .=" and invite_code = '$invite_code' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        $username = _g('username');
        if($username){
            $userService =  new UserService();
            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
        }

        return $where;
    }


}