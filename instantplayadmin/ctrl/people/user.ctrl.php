<?php
class UserCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("typeOptions",UserModel::getTypeOptions());
        $this->assign("sexOptions", UserModel::getSexOptions());

        $this->display("/people/user_list.html");
    }

    function delOne(){
        $id = _g("uid");

        $where =" uid = $id limit 1000";

//        UserLogModel::db()->delete($where);
//        OrderModel::db()->delete($where);
//        MsgModel::db()->delete("from_uid = $id or to_uid = $id");
//        UserCollectionModel::db()->delete($where);
//        UserFeedbackModel::db()->delete($where);
//        UserProductLikedModel::db()->delete($where);
//        UserCommentModel::db()->delete($where);
//        VerifiercodeModel::db()->delete($where);
//
//
//        UserModel::db()->delById($id);
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        //获取搜索条件
        $where = $this->getDataListTableWhere();
        //计算 总数据数 DB中总记录数
        $iTotalRecords = UserModel::db()->getCount($where);
        if ($iTotalRecords){
            //按照某个字段 排序
            $order_sort = _g("order");
            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'uname',
                'nickname',
                'sex',
                'order_num',
                'mobile',
                'email',
                'birthday',
                'a_time',
                'type',
                'consume_total',
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
            $data = UserModel::db()->getAll($where . $order . $limit);



            foreach($data as $k=>$v){
                $avatar = get_avatar_url($v['avatar']);
                $userLiveplaceDesc = UserModel::getLivePlaceDesc($v['id']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uname'],
                    $v['nickname'],
                    $userLiveplaceDesc,
                    UserModel::getSexDescByKey($v['sex']),
                    $v['order_num'],
                    $v['mobile'],
                    $v['email'],
                    get_default_date($v['birthday']),
                    get_default_date($v['a_time']),
                    '<img height="30" width="30" src="'.$avatar.'" />',
                    UserModel::getTypeDescByKey($v['type']),
                    $v['wx_open_id'],
                    $v['consume_total'],
                    '<a href="/people/no/user/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'.
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
//                'third_uid'=>_g('third_uid'),
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

            $this->ok("成功",$this->backUrl);

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
        $uname = _g("uname");
        $nickname = _g('nickname');
        $sex = _g('sex');
        $mobile = _g('mobile');

        $email = _g("email");
        $type = _g("type");

        $birthday_from = _g('birthday_from');
        $birthday_to = _g('birthday_to');

        $from = _g('from');
        $to = _g('to');

        $consume_total = _g('consume_total');
        $order_num = _g('order_num');

        if($consume_total)
            $where .=" and consume_total = '$consume_total' ";

        if($order_num)
            $where .=" and order_num = '$order_num' ";


        if($id)
            $where .=" and id = '$id' ";

        if($uname)
            $where .=" and uname like '%$uname%' ";

        if($nickname)
            $where .=" and nickname like '%$nickname%' ";

        if($sex)
            $where .=" and sex =$sex ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($email)
            $where .=" and recommend ='$email' ";

        if($type)
            $where .=" and mobile = '$type' ";


//        if($from = _g("from")){
//            $from .= ":00";
//            $where .= " and add_time >= '".strtotime($from)."'";
//        }
//
//        if($to = _g("to")){
//            $to .= ":59";
//            $where .= " and add_time <= '".strtotime($to)."'";
//        }


        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($birthday_from)
            $where .=" and birthday >=  $birthday_from";

        if($birthday_to)
            $where .=" and birthday <=  $birthday_to";

        return $where;
    }


}