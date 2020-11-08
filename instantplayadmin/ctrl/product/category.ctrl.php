<?php
class CategoryCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $showIndexOptionsHtml = "";
        foreach (ProductService::CATEGORY_SHOW_INDEX_DESC as $k=>$v){
            $showIndexOptionsHtml .= "<option value={$k}>$v</option>";
        }

        $showSearchOptionsHtml = "";
        foreach (ProductService::CATEGORY_SHOW_SEARCH_DESC as $k=>$v){
            $showSearchOptionsHtml .= "<option value={$k}>$v</option>";
        }

        $this->assign("showIndexOptionsHtml",$showIndexOptionsHtml);
        $this->assign("showSearchOptionsHtml",$showSearchOptionsHtml);

        $this->display("product/category_list.html");
    }


    function add(){
        if(_g("opt")){
            $name = _g("name");
            $is_show_index = _g("is_show_index");
            $is_show_search = _g("is_show_search");


            if(!$name){
                $this->notice("名称为空");
            }

            if(!$is_show_index){
                $this->notice("是否显示首页为空");
            }

            if(!$is_show_search){
                $this->notice("是否显示到全部商品页");
            }

            $exist = ProductCategoryModel::db()->getOneByOneField("name",$name);
            if($exist){
                $this->notice("重复:".$name);
            }

            $data = array(
                'name'=>$name,
                'is_show_index'=>$is_show_index,
                'is_show_search'=>$is_show_search,
            );
            $newId = ProductCategoryModel::db()->add($data);

//            $newId = 10;//用于测试

            $uploadService = new UploadService();
            $uploadRs = $uploadService->category('pic',$newId);
            if($uploadRs['code'] != 200){
                $this->notice(" uploadService->banner error ".json_encode($uploadRs));
            }
            $data['pic'] = $uploadRs['msg'];
            ProductCategoryModel::db()->upById($newId,$data);


            $this->ok($newId,$this->_backListUrl);
        }

        $showIndexOptionsHtml = "";
        foreach (ProductService::CATEGORY_SHOW_INDEX_DESC as $k=>$v){
            $showIndexOptionsHtml .= "<option value={$k}>$v</option>";
        }

        $showSearchOptionsHtml = "";
        foreach (ProductService::CATEGORY_SHOW_SEARCH_DESC as $k=>$v){
            $showSearchOptionsHtml .= "<option value={$k}>$v</option>";
        }

        $this->assign("showSearchOptionsHtml",$showSearchOptionsHtml);
        $this->assign("showIndexOptionsHtml",$showIndexOptionsHtml);

        $this->addHookJS("/product/category_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/product/category_add.html");
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = _g("draw");

        $where = $this->getDataListTableWhere();

        $cnt = CategoryModel::db()->getCount($where);
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                "name",
                "",
                "is_show_index",
                "is_show_search",
                "",
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

            $data = CategoryModel::db()->getAll($where .  $order. " limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $attr = CategoryModel::getProductRelationByCidHtml($v['id']);
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    "<img width=20 height=20 src='".get_category_url($v['pic'])."' />",
                    ProductService::CATEGORY_SHOW_INDEX_DESC[$v['is_show_index']],
                    ProductService::CATEGORY_SHOW_SEARCH_DESC[$v['is_show_search']],
                    $attr,
                    '<a href="/product/no/categoryAttr/add/cid='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-edit"></i> 添加属性 </a>',
                );

                $records["data"][] = $row;
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getDataListTableWhere(){
        $where = 1;


        $name = _g("name");
        $is_show_index = _g("is_show_index");
        $is_show_search = _g("is_show_search");
        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($is_show_index)
            $where .=" and is_show_index = '$is_show_index' ";

        if($is_show_search)
            $where .=" and is_show_search = '$is_show_search' ";

        if($name){
            $where .=" and name like '%$name%' ";
        }

        return $where;
    }


}