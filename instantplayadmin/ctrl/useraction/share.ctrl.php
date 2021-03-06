<?php
class ShareCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

//        $this->assign("typeOptions",UserModel::getTypeOptions());
//        $this->assign("sexOptions", UserModel::getSexOptions());

        $this->display("/useraction/share_comment_list.html");
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);
        //获取搜索条件
        $where = $this->getDataListTableWhere();
        //计算 总数据数 DB中总记录数
        $iTotalRecords = ShareModel::db()->getCount($where);
        if ($iTotalRecords){
            //按照某个字段 排序
            $order_sort = _g("order");
            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'uid',
                'pid',
                'source',
                'goto_page_path',
                'agent_id',
                'a_time',
//                'type',
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
            $data = ShareModel::db()->getAll($where . $order . $limit);

            $userService = new UserService();

            foreach($data as $k=>$v){
//                $avatar = get_avatar_url($v['avatar']);
//                $userLiveplaceDesc = UserModel::getLivePlaceDesc($v['id']);
//                $user  = UserModel::db()->getById($v['uid']);
//                $username = "未知";
//                if($user){
//                    $username = $user['nickname'];
//                }

                $username = $userService->getUserNameByUid($v['uid']);

                $agentName = "未知";
                if($v['agent_id']){
                    $agent  = AgentModel::db()->getById($v['agent_id']);
                    if($agent){
                        $agentName = $agent['title'];
                    }
                }



                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $username,
                    $v['pid'],
                    $v['source'],
                    $v['goto_page_path'],
                    $agentName,
//                    $v['type'],
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
                'pid'=> _g('pid'),
                'gid'=> _g('gid'),
                'uid'=> _g('uid'),
                'title'=> _g('title'),
                'content'=> _g('content'),
                'a_time'=>time(),
            );

            $uploadService = new UploadService();
            $uploadRs = $uploadService->comment('pic');
            if($uploadRs['code'] == 200){
                $data['pic'] = $uploadRs['msg'];
//                exit(" uploadService->avatar error ".json_encode($uploadRs));
            }

            $newId = UserCommentModel::db()->add($data);
            $this->ok($newId,$this->_addUrl,$this->_backListUrl);

        }

//        $this->assign("sexOption",UserModel::getSexOptions());

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/useraction/user_comment_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/useraction/user_comment_add.html");
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $username = _g("username");
        $product_name = _g("product_name");

        $from = _g('from');
        $to = _g('to');

        $source = _g('source');
        $agent_name = _g('agent_name');

        $productService = new ProductService();
        if($product_name){
            $where .= $productService->searchUidsByKeywordUseDbWhere($product_name);
        }

        $userService =  new UserService();
        if($username){
            $where .= $userService->searchUidsByKeywordUseDbWhere($username);
        }

        if($agent_name){
            $AgentService = new AgentService();
            $where .= $AgentService->searchUidsByKeywordUseDbWhere($agent_name,"agent_id");
        }

        if($id)
            $where .=" and id = '$id' ";

        if($source)
            $where .=" and source like '%$source%' ";


        if($from){
            $where .=" and a_time >=  ".strtotime($from);
        }

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}