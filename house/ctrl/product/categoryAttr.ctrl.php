<?php
class CategoryAttrCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $getSelectOptionHtml = ProductCategoryModel::getSelectOptionHtml();
        $this->assign("getSelectOptionHtml",$getSelectOptionHtml);


        $this->assign("getIsNoSelectOptionHtml",ProductModel::getIsNoSelectOptionHtml());

        $this->display("product/category_attr_list.html");
    }


    function add(){
        $cid  = _g("cid");
        if(!$cid){
            $this->notice("cid is null");
        }

        $category = ProductCategoryModel::db()->getById($cid);
        if(!$category ){
            $this->notice("cid not in db");
        }

        $CategoryAttrExist = ProductCategoryAttrModel::db()->getRow(" pc_id = $cid  ");
        $this->assign("CategoryAttrExist",$CategoryAttrExist);

        if(_g("opt")){
            $name = _g("name");
            $isNo = _g("is_no");
            if(!$name){
                $this->notice("name is null");
            }

            $exist = ProductCategoryAttrModel::db()->getRow(" pc_id = $cid and name = '$name'");
            if($exist){
                $this->notice("重复:".$name);
            }
            //一个产品分类 ，添加<空属性>，必须之前没有任何参数
            $CategoryAttrExist = ProductCategoryAttrModel::db()->getRow(" pc_id = $cid  ");

            if($isNo){
                if($isNo == ProductModel::CATE_ATTR_NULL_TRUE){
                    if($CategoryAttrExist){
                        $this->notice("一个产品分类 ，如果添加的是<空属性>，该产品分类之前不能有任何参数");
                    }
                }
            }else{
                $isNo = ProductModel::CATE_ATTR_NULL_FALSE;
            }



            $data = array(
                'name'=>$name,
                'pc_id'=>$cid,
                'is_no'=>$isNo,
            );

            $newId = ProductCategoryAttrModel::db()->add($data);
            $this->ok("成功",$this->_backListUrl);
        }

        $this->assign("category",$category);


        $this->assign("getIsNoSelectOptionHtml",ProductModel::getIsNoSelectOptionHtml());

        $this->addHookJS("/product/category_attr_add_hook.html");
        $this->display("/product/category_attr_add.html");
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);

        $where = $this->getDataListTableWhere();



        $cnt = CategoryAttrModel::db()->getCount($where);
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                "name",
                "pc_id",
                "is_no",
                "",
                ""
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

            $data = CategoryAttrModel::db()->getAll($where .  $order. " limit $iDisplayStart,$end");




            foreach($data as $k=>$v){
                $isNo  = "";
                if($v['is_no']){
                    $isNo = ProductModel::CATE_ATTR_NULL_DESC[ $v['is_no']]  ;
                }

                $addBnt = "";
                if($v['is_no'] == 2){
                    $addBnt = '<a href="/product/no/categoryAttrPara/add/pca_id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5"><i class="fa fa-edit"></i> 添加参数 </a>';
                }
                $para = CategoryAttrModel::getProductRelationByAidHtml($v['id']);
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    CategoryModel::db()->getById($v['pc_id'])['name'],
                    $v['name'],
                    $isNo,
                    $para,
                    $addBnt,
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

        $pc_id = _g("pc_id");
        $name = _g("name");
        $is_no = _g("is_no");
        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($pc_id)
            $where .=" and pc_id = '$pc_id' ";

        if($name)
            $where .=" and name like '%$name%' ";

        if($is_no)
            $where .=" and is_no = '$is_no' ";

        return $where;
    }


}