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
                'mobile',
                'email',
                'birthday',
                'a_time',
                'type',
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
//                    $v['order_num'],
                    $v['mobile'],
                    UserModel::INNER_TYPE_DESC[ $v['inner_type']],
                    get_default_date($v['birthday']),
                    get_default_date($v['a_time']),
                    '<img height="30" width="30" src="'.$avatar.'" />',
                    UserModel::getTypeDescByKey($v['type']),
//                    $v['wx_open_id'],
//                    fenToYuan($v['consume_total']),
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
            $data =array(
                'uname'=> _g('uname'),
                'realname'=> _g('realname'),
                'nickname'=> _g('nickname'),
                'sex'=> _g('sex'),
                'birthday'=> strtotime( _g('birthday')),
                'status'=>UserModel::STATUS_NORMAL,
                'inner_type'=>UserModel::INNER_TYPE_HUMAN,
                'type'=>_g('type'),
                'wx_open_id'=>_g('third_uid'),
                'city_code'=> _g('city'),
                'county_code'=> _g('county'),
                'town_code'=> _g('town'),
                'province_code'=> _g('province'),
                'id_card_no'=> _g('id_card_no'),
                'a_time'=>time(),
            );
            if(_g("a_time")){
                $data['a_time'] = strtotime( _g("a_time"));
            }

            $mobile =  _g('mobile');
            $email = _g('email');

            if(!FilterLib::regex($mobile,"phone")){
                $this->notice("手机号格式错误 ");
            }

//            if(!FilterLib::regex($mobile,"email")){
//                $this->notice("邮箱格式错误 ");
//            }

            $data['mobile'] = $mobile ;
            $data['email'] = $email ;

            $uploadService = new UploadService();
            $uploadRs = $uploadService->avatar('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->avatar error ".json_encode($uploadRs));
            }

            $data['avatar'] = $uploadRs['msg'];


            $newId = UserModel::db()->add($data);

            $this->ok("成功-$newId");

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
            $payArrStatus = array(OrderModel::STATUS_PAYED ,OrderModel::STATUS_TRANSPORT,OrderModel::STATUS_SIGN_IN ,OrderModel::STATUS_FINISH ,OrderModel::STATUS_REFUND_REJECT);
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

//        $consume_total_from = _g('consume_total_from');
//        $consume_total_to = _g('consume_total_to');
//        $order_num_from = _g('order_num_from');
//        $order_num_to = _g('order_num_to');
        $province = _g('province');
        $inner_type = _g('inner_type');



        if($inner_type)
            $where .=" and inner_type = '$inner_type' ";

//        if($consume_total_from){
//            $where .=" and consume_total >= '$consume_total_from' ";
//        }
//
//        if($consume_total_to){
//            $where .=" and consume_total <= '$consume_total_to' ";
//        }
//
//        if($order_num_from){
//            $where .=" and order_num >= '$order_num_from' ";
//        }
//
//        if($order_num_to){
//            $where .=" and order_num <= '$order_num_to' ";
//        }

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

        if($province){
            $where .=" and province_code = '$province' ";
        }

        if($from){
            $from .= ":00";
            $where .=" and a_time >=  '".strtotime($from) . "'";
        }

        if($to){
            $to .= ":59";
            $where .=" and a_time <= '".strtotime($to) . "'";
        }


        if($birthday_from){
            $birthday_from .= ":00";
            $where .=" and birthday >=  '".strtotime($birthday_from) . "'";
        }


        if($birthday_to){
            $birthday_to .= ":59";
            $where .=" and birthday <= '".strtotime($birthday_to)."'";
        }

        return $where;
    }


}