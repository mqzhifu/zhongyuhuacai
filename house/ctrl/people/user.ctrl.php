<?php
class UserCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("areaProvinceModelOptionsHtml", AreaProvinceModel::getSelectOptionsHtml());
        $this->assign("typeOptions",UserModel::getTypeOptions());
        $this->assign("sexOptions", UserModel::getSexOptions());
        $this->assign("innerTypeOptions", UserModel::getInnerTypeOptions());

        $this->display("/people/user_list.html");
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
                'name',
                'mobile',
                'live_address',
                'sex',
                'bank_name',
                'bank_account_name',
                'bank_account_no',
                'wx',
                'ali',
                'a_time',//归属代理
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
            $data = UserModel::db()->getAll($where . $order . $limit);


            foreach($data as $k=>$v){
//                $avatar = get_avatar_url($v['avatar']);
//                $userLiveplaceDesc = UserModel::getLivePlaceDesc($v['id']);
//                $bindAgentName = "--";
//                $bindAgentRs = $agentService->getOneByUid($v['id']);
//                if($bindAgentRs && $bindAgentRs['msg']){
//                    $bindAgentName  = $bindAgentRs['msg']['real_name'];
//                }

                $adminUserName = AdminUserModel::getFieldById( $v['admin_id'],'nickname');
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $v['mobile'],
                    $v['live_address'],
                    UserModel::getSexDescByKey($v['sex']),
//                    $v['bank_name'],
//                    $v['bank_account_name'],
//                    $v['bank_account_no'],
                    $v['wx'],
                    $v['ali'],
                    $adminUserName,
                    get_default_date($v['a_time']),
                    '<a href="/people/no/user/detail/id='.$v['id'].'" target="_blank" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-file-o"></i> 详情 </a>'
//                    '<a href="" target="_blank" class="btn yellow btn-xs margin-bottom-5 editone" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 编辑 </a>',
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
            $name = _g("name");
            $live_address = _g('live_address');
            $sex = _g('sex');
            $mobile = _g('mobile');
            $wx = _g('wx');
            $ali = _g("ali");
//            $bank_account_no = _g('bank_account_no');
//            $bank_name = _g('bank_name');
//            $bank_account_name = _g('bank_account_name');
            $data =array(
                'name'=> _g('name'),
                'live_address'=> _g('live_address'),
                'sex'=> _g('sex'),
//                'bank_account_no'=> _g('bank_account_no'),
//                'bank_name'=>_g('bank_name'),
//                'bank_account_name'=>_g('bank_account_name'),
                'wx'=> _g('wx'),
                'ali'=> _g('ali'),
                'memo'=> _g("memo"),
                'a_time'=>time(),
                'admin_id'=>$this->uid,
            );
//            if(_g("a_time")){
//                $data['a_time'] = strtotime( _g("a_time"));
//            }
//            $email = _g('email');
//            if(!FilterLib::regex($mobile,"email")){
//                $this->notice("邮箱格式错误 ");
//            }
//            $data['email'] = $email ;
//
//            $uploadService = new UploadService();
//            $uploadRs = $uploadService->avatar('pic');
//            if($uploadRs['code'] != 200){
//                exit(" uploadService->avatar error ".json_encode($uploadRs));
//            }
//
//            $data['avatar'] = $uploadRs['msg'];

            $mobile =  _g('mobile');
            if(!FilterLib::regex($mobile,"phone")){
                $this->notice("手机号格式错误 ");
            }

            $data['mobile'] = $mobile ;
            $newId = UserModel::db()->add($data);
            $this->ok("成功-$newId");

        }

//        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
//        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());



//        $this->assign("provinceOption",AreaProvinceModel::getSelectOptionsHtml());
//        $this->assign("cityJs",$cityJs);
//        $this->assign("countyJs",$countryJs);

        $this->assign("sexOption",UserModel::getSexOptions());
//        $this->assign("typeOption",UserModel::getTypeOptions());
//        $this->assign("statusOpen",UserModel::STATUS_DESC);

        $this->addHookJS("/people/user_add_hook.html");
//        $this->addHookJS("/layout/place.js.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/people/user_add.html");
    }

    function detail(){
        $uid = _g("id");
        if(!$uid){
            exit("uid null");
        }
        $user = UserModel::db()->getById($uid);
        if(!$user){
            exit("uid not in db");
        }
        $user['dt'] = get_default_date($user['a_time']);
        $user['status_desc'] = UserModel::STATUS_DESC[$user['status']];
        $user['avatar_url'] = get_avatar_url($user['avatar']);
        $user['birthday_dt'] =  get_default_date($user['birthday']);
        $user['type_desc'] = UserModel::getTypeDescByKey($user['type']);
        $user['inner_type_desc'] = UserModel::INNER_TYPE_DESC[$user['inner_type']];
        $orders = OrderModel::getListByUid($uid);
        $userLog = UserLogModel::getListByUid($uid);
        if($userLog){
            foreach ($userLog as $k=>$v){
                $userLog[$k]['dt'] = get_default_date($v['a_time']);
            }
        }


        $ordersConsumeTotalPrice = 0;
        $ordersTotalNum = 0;

        $ordersPayConsumeTotalPrice = 0;
        $ordersPayTotalNum = 0;

        if($orders){
            $payArrStatus = array(
                OrderModel::STATUS_PAYED ,
                OrderModel::STATUS_TRANSPORT,
                OrderModel::STATUS_SIGN_IN ,
                OrderModel::STATUS_FINISH ,
                OrderModel::STATUS_REFUND_REJECT)
            ;
            $ordersTotalNum = count($orders);
            foreach ($orders as $k=>$v){
                $ordersConsumeTotalPrice += $v['total_price'];
                $ordersConsumeTotalPrice = fenToYuan($ordersConsumeTotalPrice);

                if(in_array($v['status'],$payArrStatus)){
                    $ordersPayTotalNum++;
                    $ordersPayConsumeTotalPrice += $v['total_price'];
                }

                $orders[$k]['status_desc'] = OrderModel::STATUS_DESC[$v['status']];
                $orders[$k]['dt'] = get_default_date($v['a_time']);
                $orders[$k]['total_yuan_price'] = fenToYuan( $v['total_price']);
            }
        }

        $ordersPayConsumeTotalPrice = fenToYuan($ordersPayConsumeTotalPrice);

        $this->assign("ordersTotal",$ordersTotalNum);
        $this->assign("ordersConsumeTotalPrice",$ordersConsumeTotalPrice);


        $this->assign("ordersPayConsumeTotalPrice",$ordersPayConsumeTotalPrice);
        $this->assign("ordersPayTotalNum",$ordersPayTotalNum);


        $UpService = new UpService();
        $upTotal = $UpService->getUserCnt($user['id']);

        $CollectService = new CollectService();
        $collectTotal = $CollectService->getUserCnt($user['id']);

        $CommentService = new CommentService();
        $commentTotal = $CommentService->getUserCnt($user['id']);

        $productService = new ProductService();
        $viewProductTotalCnt = $productService->getUserViewProductTotalCnt($user['id']);

        $this->assign("upTotal",$upTotal);
        $this->assign("collectTotal",$collectTotal);
        $this->assign("commentTotal",$commentTotal);
        $this->assign("viewProductTotalCnt",$viewProductTotalCnt);

        $lastActiveRecord = $this->userService->getLastActiveRecordTime($user['id']);
        $user['last_active_record_dt'] = $lastActiveRecord;
        $user['active_day_cnt'] = $this->userService->getActiveDayCnt($user['id']);

        $this->assign("user",$user);
        $this->assign("orders",$orders);
        $this->assign("userLog",$userLog);

        $this->display("/people/user_detail.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $name = _g("name");
        $live_address = _g('live_address');
        $sex = _g('sex');
        $mobile = _g('mobile');
//        $bank_account_no = _g('bank_account_no');
//        $bank_name = _g('bank_name');
//        $bank_account_name = _g('bank_account_name');
        $wx = _g('wx');
        $ali = _g("ali");
        $admin = _g("admin");
        $from = _g('from');
        $to = _g('to');


//        $email = _g("email");
//        if($email)
//            $where .=" and recommend ='$email' ";


        if($id)
            $where .=" and id = '$id' ";

        if($name)
            $where .=" and name like '%$name%' ";

        if($live_address)
            $where .=" and live_address like '%$live_address%' ";

        if($wx)
            $where .=" and wx like '%$wx%' ";

//        if($admin)
//            $where .=" and wx like '%$wx%' ";

        if($ali)
            $where .=" and ali like '%$ali%' ";

        if($sex)
            $where .=" and sex =$sex ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

//        if($bank_account_no)
//            $where .=" and bank_account_no like '%$bank_account_no%' ";
//
//        if($bank_name)
//            $where .=" and bank_name like '%$bank_name%' ";
//
//        if($bank_account_name)
//            $where .=" and bank_account_name like '%$bank_account_name%' ";

        if($from){
            $from .= ":00";
            $where .=" and a_time >=  '".strtotime($from) . "'";
        }

        if($to){
            $to .= ":59";
            $where .=" and a_time <= '".strtotime($to) . "'";
        }

        return $where;
    }


}