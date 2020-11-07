<?php
class ProductCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }


        $statusSelectOptionHtml = HouseModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);


        $getFitmentSelectOptionHtml = HouseModel::getFitmentSelectOptionHtml();
        $this->assign("getFitmentSelectOptionHtml",$getFitmentSelectOptionHtml);

        $getDirectionSelectOptionHtml = HouseModel::getDirectionSelectOptionHtml();
        $this->assign("getDirectionSelectOptionHtml",$getDirectionSelectOptionHtml);



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

        $factory = FactoryModel::db()->getById($product['factory_uid']);

        $product['factory'] = $factory['title'];
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

//            if(ProductModel::db()->getOneByOneField("title",_g('title'))){
//                $this->notice("标题重复:"._g('title'));
//            }

            $data['sort'] = _g('sort');
            $data['title']= _g("title");
            $data['desc'] = _g("desc");
            $data['brand'] = _g("brand");
            $data['notice']  = _g("notice");
            $data['category_id'] = _g("category_id");
            $data['status'] = _g("status");
            $data['factory_uid'] = _g("factory_uid");
            $data['recommend'] = _g("recommend");
            $data['recommend_detail'] = _g("recommend_detail");

            $data['a_time'] = time();
            $data['admin_id']  = $this->_adminid;
            $data['pv'] =0;
            $data['uv'] =0;
            $data['lowest_price'] = 0;
            $data['goods_total'] = 0;
            $data['user_buy_total'] = 0;
            $data['user_up_total'] = 0;
            $data['user_collect_total'] = 0;
            $data['user_comment_total'] = 0;

            $payType = _g('payType');
            if(!$payType){
                $this->notice("请选择支付的类型渠道 ");
            }

            $data['pay_type'] = implode(",",$payType);

            //两个参数，只会有一个是存在的,categoryAttrNull:为特殊参数，空属性
            $categoryAttrPara  = _g("categoryAttrPara");
            $categoryAttrNull = _g("categoryAttrNull");

            if(!$data['title'])
                $this->notice("title is null ");

            if(!$categoryAttrPara && !$categoryAttrNull){
                $this->notice("categoryAttrPara is null ");
            }

            $pics = _g("pics");
            $pic = "";
            if($pics){
                $pic = "";
                foreach ($pics as $k=>$v){
                    $pic .= $v . ",";
                }
                $pic = substr($pic,0,strlen($pic)-1);
            }
            $data['pic'] = $pic;

//            $uploadService = new UploadService();
//            $uploadRs = $uploadService->product('pic');
//            if($uploadRs['code'] != 200){
//                exit(" uploadService->product error ".json_encode($uploadRs));
//            }


//            $data['pic'] = $uploadRs['msg'];

            $productService = new ProductService();
            $productService->addOne($data,$categoryAttrNull,$categoryAttrPara);

            $this->ok("成功",$this->_backListUrl);
        }

        $this->assign("payType",OrderModel::PAY_TYPE_DESC);

        $this->assign("getRecommendOptionHtml",ProductModel::getRecommendOptionHtml());
        $this->assign("getRecommendDetailOptionHtml",ProductModel::getRecommendDetailOptionHtml());

        $factory = FactoryModel::db()->getById(FACTORY_UID_DEFAULT);
        $this->assign("factory",$factory);

        $statusSelectOptionHtml = ProductModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
        $this->assign("categoryOptions", ProductCategoryModel::getSelectOptionHtml());

        $this->addJs("/assets/global/plugins/dropzone/dropzone.js");
        $this->addCss("/assets/global/plugins/dropzone/css/dropzone.css" );

        $this->addHookJS("product/product_add_hook.html");
        $this->addHookJS("/layout/file_upload.js.html");
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

    function makeQrcode(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $product = ProductModel::db()->getById($id);
        if(!$product){
            $this->notice("id not in db");
        }

        $tmpPath = "$id.jpg";
        $path = get_wx_little_product_path($tmpPath);
        if(file_exists($path)){
            $url = get_wx_product_qr_code_url($tmpPath);
            var_dump($url);exit;
        }

        $lib = new WxLittleLib();
        $binaryImg = $lib->getProductQrCode($id);
        var_dump($binaryImg);
        if(!$binaryImg){
            out_ajax(8367);
        }

        $imService = new UploadService();
        $imService->saveProductQrCode($binaryImg,$id);

        $url = get_wx_product_qr_code_url($tmpPath);
        out_ajax(200,$url);

    }

    function getList(){
        //初始化返回数据格式
        $records = array('data'=>[],'draw'=>$_REQUEST['draw']);

        $where = $this->getDataListTableWhere();
        $cnt = HouseModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'master_id',
                'uid',
                'status',
                'pics',
                'province_code',
//                'city_code',
//                'county_code',
//                'town_code',
                'community',
                'build_floor',
                'build_direction',
                'build_area',
                'build_room_num',
                'build_fitment',
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
            $data = HouseModel::db()->getAll($where . $order . $limit);

            foreach($data as $k=>$v){
                $statusBnt = "上架";
                $type = 2;
                $statusCssColor = "green";
                if($v['status'] == HouseModel::STATUS_ON){
                    $statusBnt = "下架";
                    $type = 1;
                    $statusCssColor = 'red';
                }


                $pic = "";
                if(arrKeyIssetAndExist($v,'pic')){
                    $pics = explode(",",$v['pic']);
                    $pic = get_product_url($pics[0]);
                }

//                $attributeArr = ProductModel::attrParaParserToName($v['attribute']);
//
//                if($v['recommend'] == ProductModel::RECOMMEND_FALSE){
//                    $recommendBnt =   '<button class="btn btn-xs default red recommendone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-share-alt"></i>  设为推荐首页</button>';
//                }else{
//                    $recommendBnt =   '<button class="btn btn-xs default blue-hoki recommendone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-share-alt"></i>  取消推荐首页</button>';
//                }
//
//                if($v['recommend_detail'] == ProductModel::RECOMMEND_DETAIL_TRUE){
//                    $recommendDetailBnt =   '<button class="btn btn-xs default yellow recommendDetailOne margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-share-alt"></i>  设为推荐产品</button>';
//                }else{
//                    $recommendDetailBnt =   '<button class="btn btn-xs default grey-cascade recommendDetailOne margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-share-alt"></i>  取消推荐产品</button>';
//                }


//                $sort = array(
//                    'id',
//                    'id',
//                    'master_id',
//                    'uid',
//                    'status',
//                    'pics',
//                    'province_code',
//                    'city_code',
//                    'county_code',
//                    'town_code',
//                    'community',
//                    'build_floor',
//                    'build_direction',
//                    'build_area',
//                    'build_room_num',
//                    'build_fitment',
//                    'add_time',
//                );

                $adminUserName = AdminUserModel::getFieldById( $v['admin_id'],'nickname');
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['master_id'],
                    $v['uid'],
                    $v['status'],
//                    '<img height="30" width="30" src="'.$pic.'" />',
//                    $adminUserName,
                    $v['province_code'] .$v['city_code'].$v['county_code'].$v['town_code'],
                    $v['pv'],
                    $v['uv'],
                    $v['community'],
                    $v['build_floor'],
                    $v['build_direction'],
                    $v['build_area'],
                    $v['build_room_num'],
                    $v['build_fitment'],

                    get_default_date($v['a_time']),
                    '<a href="/product/no/product/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>',
//                    '<button class="btn btn-xs default '.$statusCssColor.' btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'" data-type="'.$type.'"><i class="fa fa-link"></i>'.$statusBnt.'</button>'.
//                    '<a href="/product/no/goods/add/pid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 添加商品 </a>'.
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
        $product = ProductModel::db()->getById($id);
        if(!$product){
            return false;
        }

        if($product['recommend'] == ProductModel::RECOMMEND_TRUE){
            ProductModel::db()->upById($id,array('recommend'=>ProductModel::RECOMMEND_FALSE));
        }else{
            ProductModel::db()->upById($id,array('recommend'=>ProductModel::RECOMMEND_TRUE));
        }
    }

    function recommendDetailOne(){
        $id = _g("id");
        $product = ProductModel::db()->getById($id);
        if(!$product){
            return false;
        }

        if($product['recommend'] == ProductModel::RECOMMEND_DETAIL_TRUE){
            ProductModel::db()->upById($id,array('recommend_detail'=>ProductModel::RECOMMEND_FALSE));
        }else{
            ProductModel::db()->upById($id,array('recommend_detail'=>ProductModel::RECOMMEND_TRUE));
        }
    }




    function delOne(){
        $id = _g("id");

        ProductModel::db()->delById($id);
        GoodsModel::db()->delete(" pid = $id limit 1000");
        OrderModel::db()->delete("goods_id = $id");
    }

    function getWhere(){
        $where = " 1 ";


        $status = _g("status");
        $title = _g("title");
        $category_id = _g("category_id");
        $pv_from = _g("pv_from");
        $pv_to = _g("pv_to");
        $uv_from = _g("uv_from");
        $uv_to = _g("uv_to");
        $sort = _g("sort");
        $recommend = _g("recommend");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($status)
            $where .= " and status = $status";

        if($title)
            $where .= " and content like '%$title%'";

        if($category_id)
            $where .= " and category_id = '$category_id'";

        if($pv_from)
            $where .= " and pv_from >= $pv_from";

        if($pv_to )
            $where .= " and pv_to <= $pv_to";


        if( $uv_from  )
            $where .= " and uv_from >= $uv_from";

        if( $uv_to  )
            $where .= " and uv_to <= $uv_to";

        if($recommend)
            $where .= " and recommend = '$recommend'";

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
        $lowest_price_from = _g('lowest_price_from');
        $lowest_price_to = _g('lowest_price_to');
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

        $sort = _g("sort");

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

        if($lowest_price_from)
            $where .=" and lowest_price >=  $lowest_price_from";

        if($lowest_price_to)
            $where .=" and lowest_price <=  $lowest_price_to";

        return $where;
    }

    function multipleUploadOneImg(){
//        if(!$id){
//            exit("id is null");
//        }
//
//        $product = ProductModel::db()->getById($id);
//        if(!$product){
//            exit("pid not in db.");
//        }


        $uploadService = new UploadService();
        $uploadRs = $uploadService->product('pic');
        if($uploadRs['code'] != 200){
            exit(" uploadService->product error ".json_encode($uploadRs));
        }

        echo out_ajax(200,$uploadRs['msg']);

//        $newPicSrc = "";
//        if(arrKeyIssetAndExist($product,'pic')){
//            $newPicSrc = $product['pic'] . "," .$uploadRs['msg'];
//        }else{
//            $newPicSrc = $uploadRs['msg'];
//        }
//        ProductModel::db()->upById($id,array("pic"=>$newPicSrc));
    }

    function multipleDelOneImg($id,$imgSrc){
        if(!$id){
            exit("id is null");
        }

        $product = ProductModel::db()->getById($id);
        if(!$product){
            exit("pid not in db.");
        }

        if(arrKeyIssetAndExist($product,'pic')){
            if (strpos($product['pic'], $imgSrc) !== false ) {
                $newPicSrc = "";
                if(strpos($product['pic'], $imgSrc . ",") !== false){
                    $newPicSrc = str_replace($imgSrc . ",","",$product['pic']);
                }else{
                    $newPicSrc = str_replace($imgSrc,"",$product['pic']);
                }

                ProductModel::db()->upById($id,array("pic"=>$newPicSrc));
            }else{
                exit("not found..");
            }

        }else{
            exit(" product pic is null,no need del");
        }
    }


    function testDropImgUpload(){
//<script src="../../assets/global/plugins/dropzone/dropzone.js"></script>


//        $this->addJs('/assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js');
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/load-image.min.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js");
//        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js");

        $this->addJs("/assets/global/plugins/dropzone/dropzone.js");
        $this->addCss("/assets/global/plugins/dropzone/css/dropzone.css" );


        $this->display("/product/test_drop_img_upload.html");
    }

    function testMultipleImgUpload(){
//        <link href="../../assets/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css" rel="stylesheet"/>
//<link href="../../assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
//<link href="../../assets/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css" rel="stylesheet"/>

        $this->addCss('/assets/global/plugins/jquery-file-upload/blueimp-gallery/blueimp-gallery.min.css');
        $this->addCss('/assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css');
        $this->addCss('/assets/global/plugins/jquery-file-upload/css/jquery.fileupload-ui.css');

        //        <script src="../../assets/global/plugins/fancybox/source/jquery.fancybox.pack.js"></script>

//        <script src="../../assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/vendor/load-image.min.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js"></script>
//        <script src="../../assets/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js"></script>



        $this->addJs('/assets/global/plugins/fancybox/source/jquery.fancybox.pack.js');

        $this->addJs('/assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js');
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/tmpl.min.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/load-image.min.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/vendor/canvas-to-blob.min.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/blueimp-gallery/jquery.blueimp-gallery.min.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.iframe-transport.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-process.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-image.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-audio.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-video.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-validate.js");
        $this->addJs("/assets/global/plugins/jquery-file-upload/js/jquery.fileupload-ui.js");
//        $this->addJs("/assets/admin/pages/scripts/form-fileupload.js");

        $this->addHookJS("product/test_multiple_img_upload_hook.html");
        $this->display("/product/test_multiple_img_upload.html");
    }

}