<?php
class AgentCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $type = _g("type");
        if($type == 2){
            exit("暂未开发");
        }
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
        $uid = _g("id");
        $agent = AgentModel::db()->getById($uid);
        $agent['dt'] = get_default_date($agent['a_time']);
        $agent['status_desc'] = AgentModel::STATUS[$agent['status']];

        $agent['pic_url'] = get_agent_url($agent['pic']);
//        $agent['birthday_dt'] =  get_default_date($agent['birthday']);
//        $agent['type_desc'] = UserModel::getTypeDescByKey($agent['type']);
        $orders = OrderModel::getListByAgentId($uid);
//        $userLog = UserLogModel::getListByUid($uid);

        if($orders){
            foreach ($orders as $k=>$v) {
                $row = $v;
                $row['fee_percent'] = $v['price'] * $agent['fee_percent'];
                $orders[$k] = $row;
            }

        }

        $userLivePlaceDesc = UserModel::getAgentLivePlaceDesc($uid);
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
                '',
                '',
                'status',
                'province_code',
                'city_code',
                'county_code',
                'town_code',
                '',
                'sex',
                '',
                '',
                'fee_percent',
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

            $limit = " limit $iDisplayStart,$end";
            $data = AgentModel::db()->getAll($where . $order . $limit);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    $v['real_name'],
                    AgentModel::STATUS[$v['status']],
                    AreaProvinceModel::db()->getOneByOneField('code',$v['province_code'])['short_name'],
                    AreaCityModel::db()->getOneByOneField('code',$v['city_code'])['short_name'],
                    AreaCountyModel::db()->getOneByOneField('code',$v['county_code'])['short_name'],
                    AreaTownModel::db()->getOneByOneField('code',$v['towns_code'])['short_name'],
                    $v['villages'],
                    UserModel::getSexDescByKey($v['type']),
                    '<img height="30" width="30" src="'.get_agent_url($v['pic']).'" />',
                    $v['mobile'],
                    $v['fee_percent'],
                    get_default_date($v['a_time']),
                    '<a href="/people/no/agent/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'.
                    '<button class="btn btn-xs default red upstatus margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 审核</button>'. "&nbsp;".
                    '<a href="" class="btn yellow btn-xs margin-bottom-5 editone" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 编辑 </a>',
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

        if(_g('opt')){


            var_dump($_REQUEST);exit;
            exit;
        }
        $statusDesc = AgentModel::STATUS;
        $statusDescRadioHtml = "";
        foreach ($statusDesc as $k=>$v) {
            $statusDescRadioHtml .= "<input name='status' type='radio' value={$k} />".$v;
        }

        $data = array(
            'statusDescRadioHtml'=>$statusDescRadioHtml,
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
                'fee_percent'=> _g('fee_percent'),
                'status'=>1,

                'type'=>AgentModel::ROLE_LEVEL_ONE,
                'address'=> _g('address'),
                'province_code'=> _g('province'),
                'city_code'=> _g('city'),
                'county_code'=> _g('county'),
                'towns_code'=> _g('town'),
                'villages'=> _g('villages'),

                'a_time'=>time(),
            );

            $uploadService = new UploadService();
            $uploadRs = $uploadService->agent('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->product error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            $newId = AgentModel::add($data);

            var_dump($newId);exit;

        }

        $sexOption = UserModel::getSexOptions();

        $this->assign("sexOption",$sexOption);

        $province = AreaProvinceModel::getSelectOptionsHtml();
        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());

        $this->assign("provinceOption",$province);
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/people/agent_add_hook.html");

        $this->addHookJS("/layout/place.js.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/people/agent_add.html");
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