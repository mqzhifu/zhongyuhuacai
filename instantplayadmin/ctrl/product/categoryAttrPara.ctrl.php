<?php
class CategoryAttrParaCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("product/category_attr_para_list.html");
    }


    function add(){
        $pcaId  = _g("pca_id");
        if(!$pcaId){
            $this->notice("pcaId is null");
        }

        $categoryAttr = ProductCategoryAttrModel::db()->getById($pcaId);
        if(!$categoryAttr ){
            $this->notice("pcaId not in db");
        }

        $category = ProductCategoryModel::db()->getById($categoryAttr['pc_id']);

        if(_g("opt")){
            $name = _g("name");
            if(!$name){
                $this->notice("name is null");
            }

            $exist = ProductCategoryAttrParaModel::db()->getRow(" pca_id = $pcaId and name = '$name'");
            if($exist){
                $this->notice("重复:".$name);
            }


            $uploadService = new UploadService();
            $uploadRs = $uploadService->categoryAttrPara('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->avatar error ".json_encode($uploadRs));
            }

            $data = array(
                'name'=>$name,
                'pca_id'=>$pcaId,
                'img'=>$uploadRs['msg'],
            );

            $newId = ProductCategoryAttrParaModel::db()->add($data);
            var_dump($newId);exit;
        }

        $this->assign("category",$category);
        $this->assign("categoryAttr",$categoryAttr);



        $this->addHookJS("/product/category_attr_para_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/product/category_attr_para_add.html");
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


    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = ProductCategoryAttrParaModel::db()->getCount($where);
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id'
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

            $data = ProductCategoryAttrParaModel::db()->getAll($where .  $order. " limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $img = get_category_attr_para_url($v['img']);
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $v['pca_id'],
                    '<img height="30" width="30" src="'.$img.'" />',
                    "",
                );

                $records["data"][] = $row;
            }
        }

        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
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