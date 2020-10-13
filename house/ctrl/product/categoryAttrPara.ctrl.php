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
                $this->notice(" uploadService->avatar error ".json_encode($uploadRs));
            }

            $data = array(
                'name'=>$name,
                'pca_id'=>$pcaId,
                'img'=>$uploadRs['msg'],
            );

            $newId = ProductCategoryAttrParaModel::db()->add($data);
            $this->ok("成功",$this->_backListUrl);
        }

        $this->assign("category",$category);
        $this->assign("categoryAttr",$categoryAttr);



        $this->addHookJS("/product/category_attr_para_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/product/category_attr_para_add.html");
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
                'id',
                'name',
                'pca_id',
                '',
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

            $data = ProductCategoryAttrParaModel::db()->getAll($where .  $order. " limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $pcaName = "";
                if($v['pca_id']){
                    $pca = ProductCategoryAttrModel::db()->getById($v['pca_id']);
                    $pcaName = $pca['name'];
                }

                $img = get_category_attr_para_url($v['img']);
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $pcaName ."({$v['pca_id']})",
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
        $name = _g("name");
        $pcp_id = _g("pcp_id");
        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($name)
            $where .=" and name like '%$name%' ";

        if($pcp_id)
            $where .=" and pcp_id = '$pcp_id' ";

        return $where;
    }


}