<?php
class UserCollectCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

//        $this->assign("typeOptions",UserModel::getTypeOptions());
//        $this->assign("sexOptions", UserModel::getSexOptions());

        $this->display("/useraction/user_collect_list.html");
    }

    function delOne(){

    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        //获取搜索条件
        $where = $this->getDataListTableWhere();
        //计算 总数据数 DB中总记录数
        $iTotalRecords = UserCollectionModel::db()->getCount($where);
        if ($iTotalRecords){
            //按照某个字段 排序
            $order_sort = _g("order");
            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'pid',
                'gid',
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

            $limit = " limit $iDisplayStart,$end";
            $data = UserCollectionModel::db()->getAll($where . $order . $limit);



            foreach($data as $k=>$v){
                $product = ProductModel::db()->getById($v['pid']);
                $productName = $product['title'];
                $user = UserModel::db()->getById($v['uid']);
                $userName = $user['nickname'];
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $productName,
//                    $v['gid'],
                    $userName,
                    get_default_date($v['a_time']),
                    ''
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
                'pid'=> _g('pid'),
                'gid'=> _g('gid'),
                'uid'=> _g('uid'),
                'a_time'=>time(),
            );

            $newId = UserLikedModel::db()->add($data);
            $this->ok($newId,"",$this->_backListUrl);
        }

        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());



//        $this->assign("provinceOption",AreaProvinceModel::getSelectOptionsHtml());
//        $this->assign("cityJs",$cityJs);
//        $this->assign("countyJs",$countryJs);
//
//        $this->assign("sexOption",UserModel::getSexOptions());
//        $this->assign("typeOption",UserModel::getTypeOptions());
//        $this->assign("statusOpen",UserModel::STATUS_DESC);
//
        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/useraction/user_liked_add_hook.html");
//        $this->addHookJS("/layout/place.js.html");
//        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/useraction/user_liked_add.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $product_name = _g("product_name");
        $username = _g('username');
        $from = _g('from');
        $to = _g('to');

        $productService = new ProductService();
        if($product_name){
            $where .= $productService->searchUidsByKeywordUseDbWhere($username);
        }

        $userService =  new UserService();
        if($username){
            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
        }

        if($id)
            $where .=" and id = '$id' ";

        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}