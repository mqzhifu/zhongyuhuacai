<?php
class UserLogCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

//        $this->assign("typeOptions",UserModel::getTypeOptions());
//        $this->assign("sexOptions", UserModel::getSexOptions());

        $this->display("/useraction/user_log_list.html");
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        //获取搜索条件
        $where = $this->getDataListTableWhere();
        //计算 总数据数 DB中总记录数
        $iTotalRecords = UserLogModel::db()->getCount($where);
        if ($iTotalRecords){
            //按照某个字段 排序
            $order_sort = _g("order");
            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'ctrl',
                'ac',
                'uid',
                'trace_id',
                'request_id',
                'request',
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

            $limit = " limit $iDisplayStart,$end";
            $data = UserLogModel::db()->getAll($where . $order . $limit);



            foreach($data as $k=>$v){
//                $avatar = get_avatar_url($v['avatar']);
//                $userLiveplaceDesc = UserModel::getLivePlaceDesc($v['id']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['ctrl'],
                    $v['ac'],
                    $v['uid'],
                    $v['trace_id'],
                    $v['request_id'],
                    $v['request'],
                    get_default_date($v['a_time']),
                    '',
//                    '<a href="/people/no/user/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'.
//                    '<button class="btn btn-xs default yellow delone" data-id="'.$v['id'].'" ><i class="fa fa-scissors"></i>  删除</button>',
                );

                $records["data"][] = $row;
            }
        }

        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }



    function add(){
        if(_g('opt')){
            $data =array(
                'uname'=> _g('uname'),
                'realname'=> _g('realname'),
                'nickname'=> _g('nickname'),
                'mobile'=> _g('mobile'),
                'sex'=> _g('sex'),
                'email'=> _g('email'),
                'birthday'=> _g('birthday'),
                'status'=>_g('status'),
                'type'=>_g('type'),
                'third_uid'=>_g('third_uid'),
                'a_time'=>time(),
                'city_code'=> _g('city'),
                'county_code'=> _g('county'),
                'town_code'=> _g('street'),
                'province_code'=> _g('province'),
            );

            $uploadService = new UploadService();
            $uploadRs = $uploadService->avatar('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->avatar error ".json_encode($uploadRs));
            }

            $data['avatar'] = $uploadRs['msg'];

            $newId = UserModel::db()->add($data);

            var_dump($newId);exit;

        }

        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());



        $this->assign("provinceOption",AreaProvinceModel::getSelectOptionsHtml());
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);

        $this->assign("sexOption",UserModel::getSexOptions());
        $this->assign("typeOption",UserModel::getTypeOptions());
        $this->assign("statusOpen",UserModel::STATUS_DESC);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/people/user_add_hook.html");
        $this->addHookJS("/layout/place.js.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/people/user_add.html");
    }

    function detail(){
        $uid = _g("id");
        $user = UserModel::db()->getById($uid);
        $user['dt'] = get_default_date($user['a_time']);
        $user['status_desc'] = UserModel::STATUS_DESC[$user['status']];


        $user['avatar_url'] = get_avatar_url($user['avatar']);
        $user['birthday_dt'] =  get_default_date($user['birthday']);
        $user['type_desc'] = UserModel::getTypeDescByKey($user['type']);
        $orders = OrderModel::getListByUid($uid);
        $userLog = UserLogModel::getListByUid($uid);

        $this->assign("user",$user);
        $this->assign("orders",$orders);
        $this->assign("userLog",$userLog);

        $this->display("/people/user_detail.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $ctrl = _g("para_ctrl");
        $ac = _g('para_ac');
        $uid = _g('uid');
        $trace_id = _g('trace_id');
        $request_id = _g("request_id");

        $from = _g('from');
        $to = _g('to');

        if($id)
            $where .=" and id = '$id' ";

        if($ctrl)
            $where .=" and ctrl = '$ctrl' ";

        if($ac)
            $where .=" and ac = '$ac' ";

        if($uid)
            $where .=" and uid =$uid ";

        if($trace_id)
            $where .=" and trace_id = '$trace_id' ";

        if($request_id)
            $where .=" and request_id ='$request_id' ";

        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}