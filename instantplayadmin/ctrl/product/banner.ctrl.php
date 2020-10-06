<?php
class BannerCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("getStatusOptionHtml",BannerModel::getStatusOptionHtml());
//        $this->assign("sexOptions", BannerModel::getSexOptions());

        $this->display("/product/banner_list.html");
    }

    function upstatus(){
        $status = _g("type");
        $id = _g("id");
        BannerModel::db()->upById($id,array('status'=>$status));
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
                'title',
                'pid',
                '',
                'status',
                'sort',
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
            $data = BannerModel::db()->getAll($where . $order . $limit);





            foreach($data as $k=>$v){
                $statusBnt = "上架";
                $type = BannerModel::STATUS_TRUE;
                $statusCssColor = "green";
                if($v['status'] == BannerModel::STATUS_TRUE){
                    $statusBnt = "下架";
                    $type = BannerModel::STATUS_FALSE;
                    $statusCssColor = 'red';
                }


                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    ProductModel::db()->getOneFieldValueById($v['pid'],'title'),
                    "<img width=50 height=50 src='".get_banner_url($v['pic'])."' />",
                    BannerModel::STATUS_DESC[ $v['status']],
                    $v['sort'],
                    get_default_date($v['a_time']),
                    '<button class="btn btn-xs default '.$statusCssColor.' btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'" data-type="'.$type.'"><i class="fa fa-link"></i>'.$statusBnt.'</button>'
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
                'pid'=> _g('pid'),
                'title'=> _g('title'),
                'status'=> _g('status'),
                'sort'=> _g('sort'),
                'a_time'=>time(),
            );

            if(!$data['pid']){
                $this->notice("跳转产品pid 不能为空");
            }

            $product = ProductModel::db()->getById($data['pid']);
            if(!$product){
                $this->notice("跳转产品pid 错误，不存在");
            }

            if(!$data['title']){
                $this->notice("标题 不能为空");
            }

            if(!$data['status']){
                $this->notice("状态 不能为空");
            }


            $uploadService = new UploadService();
            $uploadRs = $uploadService->banner('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->banner error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            $newId = BannerModel::db()->add($data);

            $this->ok("成功",$this->_backListUrl);

        }

        $this->assign("getStatusOptionHtml",BannerModel::getStatusOptionHtml());

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
        $title = _g("title");
        $status = _g('status');
        $from = _g('from');
        $to = _g('to');

        if($id)
            $where .=" and id = '$id' ";

        if($title)
            $where .=" and title like '%$title%' ";

        if($status)
            $where .=" and status =$status ";

        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}