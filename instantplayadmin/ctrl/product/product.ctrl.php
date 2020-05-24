<?php
class ProductCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }


        $statusSelectOptionHtml = ProductModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
        $this->assign("categoryOptions", ProductCategoryModel::getSelectOptionHtml());
        $this->assign("recommendOptions", ProductModel::getRecommendOptionHtml());
        $this->display("/product/product_list.html");
    }

    function detail(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $product = ProductModel::db()->getById($id);
        $product['dt'] = get_default_date($product['a_time']);

        $category = ProductCategoryModel::db()->getById($product['category_id']);
        $product['category_name'] = $category['name'];

        $admin = AdminUserModel::db()->getById($product['admin_id']);
        $product['admin_name'] = $admin['uname'];

        $product['status_desc'] = ProductModel::STATUS[$product['status']];

        $product['desc_attr_arr'] = "";
        if(arrKeyIssetAndExist($product,'desc_attr')){
            $product['desc_attr_arr'] = json_decode($product['desc_attr'],true);
        }

        if(arrKeyIssetAndExist($product,'pic')){
            $pics = explode(",",$product['pic']);
            foreach ($pics as $k=>$v) {
                $product['pics'][] = get_product_url($v);
            }
        }

        $goodsList = GoodsModel::getListByPid($id);
        $product['goods_num'] = 0;
        if($goodsList){
            $product['goods_num'] = count($goodsList);
        }

        $attributeArr = ProductModel::attrParaParserToName($product['attribute']);


        $this->assign("attributeArr",$attributeArr);
        $this->assign("goodsList",$goodsList);
        $this->assign("product",$product);

        $this->display("/product/product_detail.html");
    }

    function add(){
        if(_g('opt')){

            if(ProductModel::db()->getOneByOneField("title",_g('title'))){
                $this->notice("标题重复:"._g('title'));
            }

            $data['sort'] = _g('sort');
            $data['title']= _g("title");
            $data['desc'] = _g("desc");
            $data['brand'] = _g("brand");
            $data['notice']  = _g("notice");
            $data['category_id'] = _g("category_id");
            $data['status'] = _g("status");
            $data['a_time'] = time();
            $data['admin_id']  = $this->_adminid;
            $data['pv'] =0;
            $data['uv'] =0;
            $data['lowest_price'] = 0;
            $data['factory_uid'] = _g("factory_uid");

            //两个参数，只会有一个是存在的,categoryAttrNull:为特殊参数，空属性
            $categoryAttrPara  = _g("categoryAttrPara");
            $categoryAttrNull = _g("categoryAttrNull");

            if(!$data['title'])
                $this->notice("title is null ");

            if(!$categoryAttrPara && !$categoryAttrNull){
                $this->notice("categoryAttrPara is null ");
            }




            $uploadService = new UploadService();
            $uploadRs = $uploadService->product('pic');
            if($uploadRs['code'] != 200){
                exit(" uploadService->product error ".json_encode($uploadRs));
            }

            $data['pic'] = $uploadRs['msg'];

            ProductModel::addOne($data,$categoryAttrNull,$categoryAttrPara);

            exit("成功");
        }

        $factory = FactoryModel::db()->getById(FACTORY_UID_DEFAULT);
        $this->assign("factory",$factory);

        $statusSelectOptionHtml = ProductModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
        $this->assign("categoryOptions", ProductCategoryModel::getSelectOptionHtml());

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("product/product_add_hook.html");
        $this->display("/product/product_add.html");
    }

    function getProductCategoryRelation(){
        if(!arrKeyIssetAndExist($this->_request,'categoryId')){
            echo false;
            exit;
        }
        $list = CategoryModel::getProductRelationByCid($this->_request['categoryId']);
        $paraMax = 0;
        foreach ($list as $k=>$v) {
            if(arrKeyIssetAndExist($v,'para')){
                if(count($v['para']) > $paraMax){
                    $paraMax = count($v['para']);
                }
            }
        }

        $arr = array('list'=>$list,'paraMax'=>$paraMax);
        echo json_encode($arr);
        exit;
    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);

        $where = $this->getDataListTableWhere();
        $cnt = ProductModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
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
            $data = ProductModel::db()->getAll($where . $order . $limit);

            foreach($data as $k=>$v){
                $statusBnt = "上架";
                $type = 2;
                $statusCssColor = "green";
                if($v['status'] == ProductModel::STATUS_ON){
                    $statusBnt = "下架";
                    $type = 1;
                    $statusCssColor = 'red';
                }


//                $desc = $v['desc'];
//                if(mb_strlen($desc) >=128){
//                    $desc .= mb_substr($desc,0,128);
//                }

                $pic = "";
                if(arrKeyIssetAndExist($v,'pic')){
                    $pics = explode(",",$v['pic']);
                    $pic = get_product_url($pics[0]);
                }

                $attributeArr = ProductModel::attrParaParserToName($v['attribute']);

                $recommendBnt = "";
                if($v['recommend'] == ProductModel::RECOMMEND_FALSE){
                    $recommendBnt =   '<button class="btn btn-xs default blue-hoki recommendone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-scissors"></i>  设为推荐</button>';
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
//                    $v['subtitle'],
                    $v['goods_total'],
                    $v['lowest_price'],
                    $v['brand'],
//                    json_encode($attributeArr,JSON_UNESCAPED_UNICODE),
                    count($attributeArr),
                    '<img height="30" width="30" src="'.$pic.'" />',
                    ProductCategoryModel::getNameById($v['category_id']),
//                    $v['status'],
                    ProductModel::getStatusDescById( $v['status']),
//                    $v['lables'],
//                    $v['is_search'],
                    $v['admin_id'],
                    $v['factory_uid'],
                    $v['pv'],
                    $v['uv'],
                   "<input data-id='{$v['id']}'  value='{$v['sort']}' type='input' onblur='upSort(this)' />",
                    ProductModel::RECOMMEND[$v['recommend']],
                    get_default_date($v['a_time']),
                    '<a href="/product/no/product/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
                    '<button class="btn btn-xs default '.$statusCssColor.' btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'" data-type="'.$type.'"><i class="fa fa-link"></i>'.$statusBnt.'</button>'.
                    '<a href="/product/no/goods/add/pid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 添加商品 </a>'.
//                    '<button class="btn btn-xs default dark delone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-scissors"></i>  删除</button>'.
                    '<a href="" class="btn yellow btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 编辑 </a>'.
                   $recommendBnt

                );

                $records["data"][] = $row;
            }
        }

        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function recommendOne(){
        $id = _g("id");
        ProductModel::db()->upById($id,array('recommend'=>ProductModel::RECOMMEND_TRUE));
    }

    function delOne(){
        $id = _g("id");

        ProductModel::db()->delById($id);
        GoodsModel::db()->delete(" pid = $id limit 1000");
        OrderModel::db()->delete("goods_id = $id");
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

    function upSort(){
        $sort = _g("sort");
        $id = _g("id");
        ProductModel::db()->upById($id,array('sort'=>$sort));
    }


    function upstatus(){
        $status = _g("type");
        $id = _g("id");
        ProductModel::db()->upById($id,array('status'=>$status));
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $title = _g("title");
        $category_id = _g('category_id');
        $status = _g('status');
        $recommend = _g('recommend');

        $from = _g("atime_from");
        $to = _g("atime_to");

        $pv_from = _g('pv_from');
        $pv_to = _g('pv_to');

        $uv_from = _g('uv_from');
        $uv_to = _g('uv_to');

        $goods_total_from = _g('goods_total_from');
        $goods_total_to = _g('goods_total_to');

        if($id)
            $where .=" and id = '$id' ";

        if($title)
            $where .=" and title like '%$title%' ";

        if($category_id)
            $where .=" and category_id =$category_id ";

        if($status)
            $where .=" and status = '$status' ";

        if($recommend)
            $where .=" and recommend =$recommend ";


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

        if($uv_from)
            $where .=" and uv >=  $uv_from";

        if($uv_to)
            $where .=" and uv <=  $uv_from";

        if($pv_from)
            $where .=" and pv >=  $pv_from";

        if($pv_to)
            $where .=" and pv <=  $pv_to";


        if($goods_total_from)
            $where .=" and goods_total >=  $goods_total_from";

        if($goods_total_to)
            $where .=" and goods_total <=  $goods_total_from";

        return $where;
    }


}