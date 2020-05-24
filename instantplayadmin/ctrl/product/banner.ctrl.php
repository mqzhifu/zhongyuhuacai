<?php
class BannerCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

//        $this->assign("typeOptions",BannerModel::getTypeOptions());
//        $this->assign("sexOptions", BannerModel::getSexOptions());

        $this->display("/product/banner_list.html");
    }

    function delOne(){
        $id = _g("uid");
        BannerModel::db()->delById($id);
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        //获取搜索条件
        $where = $this->getDataListTableWhere();
        //计算 总数据数 DB中总记录数
        $iTotalRecords = BannerModel::db()->getCount($where);
        if ($iTotalRecords){
            //按照某个字段 排序
            $order_sort = _g("order");
            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
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
            $data = BannerModel::db()->getAll($where . $order . $limit);



            foreach($data as $k=>$v){
//                $avatar = get_avatar_url($v['avatar']);
//                $userLiveplaceDesc = BannerModel::getLivePlaceDesc($v['id']);

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    ProductModel::db()->getOneFieldValueById($v['pid'],'title'),
                    "<img width=50 height=50 src='".get_banner_url($v['pic'])."' />",
                    get_default_date($v['a_time']),
                    '<button class="btn btn-xs default yellow delone" data-id="'.$v['id'].'" ><i class="fa fa-scissors"></i>  删除</button>',
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
                'pid'=> _g('pid'),
                'a_time'=>time(),
                'title'=> _g('title'),
            );

            $uploadService = new UploadService();
            $uploadRs = $uploadService->banner('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->banner error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            $newId = BannerModel::db()->add($data);

            var_dump($newId);exit;

        }

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/product/banner_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/product/banner_add.html");
    }

    function detail(){
        $uid = _g("id");
        $user = BannerModel::db()->getById($uid);
        $user['dt'] = get_default_date($user['a_time']);
        $user['status_desc'] = BannerModel::STATUS_DESC[$user['status']];


        $user['avatar_url'] = get_avatar_url($user['avatar']);
        $user['birthday_dt'] =  get_default_date($user['birthday']);
        $user['type_desc'] = BannerModel::getTypeDescByKey($user['type']);
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