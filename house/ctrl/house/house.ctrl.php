<?php
class HouseCtrl extends BaseCtrl{
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

        $this->display("/house/house_list.html");
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

        $this->display("/house/house_detail.html");
    }

    function add(){
        if(_g('opt')){
            $data['status'] = HouseModel::STATUS_WAIT;
            $data['master_id'] = _g('master_id');
//            $data['uid']= _g("uid");
            $data['pics'] = _g("pics");
            $data['desc']  = _g("desc");
            $data['community'] = _g("community");
//            $data['province_code'] = _g("province");
//            $data['city_code'] = _g("city");
//            $data['county_code'] = _g("county");
//            $data['town_code'] = _g("town");
            //房屋信息
            $data['build_no'] = _g("build_no");
            $data['build_unit'] = _g("build_unit");
            $data['build_detail_no'] = _g("build_detail_no");
            $data['build_floor'] = _g("build_floor");
            $data['build_direction'] = _g("build_direction");
            $data['build_area'] = _g("build_area");
            $data['build_room_num'] = _g("build_room_num");
            $data['build_fitment'] = _g("build_fitment");
            $data['a_time'] = time();
            $data['admin_id']  = $this->_adminid;

//            if(ProductModel::db()->getOneByOneField("title",_g('title'))){
//                $this->notice("标题重复:"._g('title'));
//            }
//            if(!$data['uid']){
//                $this->notice("uid is null ");
//            }
//            if(!$data['province_code']){
//                $this->notice("province_code is null ");
//            }
//
//            if(!$data['city_code']){
//                $this->notice("city_code is null ");
//            }
//
//            if(!$data['county_code']){
//                $this->notice("county_code is null ");
//            }
//
//            if(!$data['town_code']){
//                $this->notice("town_code is null ");
//            }
            if(!$data['master_id']){
                $this->notice("master_id 为空 ");
            }
            $this->checkRequestDataInt($data['master_id'],"master_id 只允许为正整数 ");

            $master = MasterModel::db()->getById($data['master_id']);
            if(!$master){
                $this->notice("master_id 错误，不在DB中 ");
            }

            if(!$data['community']){
                $this->notice("community is null ");
            }

            if(!$data['build_no']){
                $this->notice("build_no is null ");
            }
//            $this->checkRequestDataInt($data['build_no'],"楼号 只允许为正整数 ");

            if(!$data['build_unit']){
                $this->notice("build_unit is null ");
            }
//            $this->checkRequestDataInt($data['build_unit'],"单元号 只允许为正整数 ");

            if(!$data['build_detail_no']){
                $this->notice("build_detail_no is null ");
            }
            $this->checkRequestDataInt($data['build_detail_no'],"门牌号只允许为正整数 ");

            if(!$data['build_floor']){
                $this->notice("build_floor is null ");
            }
//            $this->checkRequestDataInt($data['build_floor'],"楼层号 只允许为正整数 ");

            if(!$data['build_area']){
                $this->notice("build_area is null ");
            }

            $data['province_code'] = 130000;
            $data['city_code'] = 131000;
            $data['county_code'] = 131082;
            $data['town_code'] = 131082450;

            $pics = _g("pics");
            $pic = "";
            if($pics){
                $pic = "";
                foreach ($pics as $k=>$v){
                    $pic .= $v . ",";
                }
                $pic = substr($pic,0,strlen($pic)-1);
            }
            $data['pics'] = $pic;

            $newId = HouseModel::db()->add($data);

            $this->ok("成功-$newId",$this->_backListUrl);
        }

        $getFitmentSelectOptionHtml = HouseModel::getFitmentSelectOptionHtml();
        $this->assign("getFitmentSelectOptionHtml",$getFitmentSelectOptionHtml);

        $getDirectionSelectOptionHtml = HouseModel::getDirectionSelectOptionHtml();
        $this->assign("getDirectionSelectOptionHtml",$getDirectionSelectOptionHtml);


        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());

        $this->assign("provinceOption",AreaProvinceModel::getSelectOptionsHtml());
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);

        $this->addJs("/assets/global/plugins/dropzone/dropzone.js");
        $this->addCss("/assets/global/plugins/dropzone/css/dropzone.css" );

        $this->addHookJS("/house/house_add_hook.html");
        $this->addHookJS("/layout/place.js.html");
        $this->addHookJS("/layout/file_upload.js.html");
        $this->display("/house/house_add.html");
    }

    function checkRequestDataInt($value,$msg = ''){
        $value = (int)$value;
        if(!$value){
            $this->notice($msg);
        }
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
//                $statusBnt = "上架";
//                $type = 2;
//                $statusCssColor = "green";
//                if($v['status'] == HouseModel::STATUS_ON){
//                    $statusBnt = "下架";
//                    $type = 1;
//                    $statusCssColor = 'red';
//                }


                $pic = "";
                if(arrKeyIssetAndExist($v,'pic')){
                    $pics = explode(",",$v['pic']);
                    $pic = get_house_url($pics[0]);
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
                $masterName = MasterModel::getFieldById( $v['master_id'],'name');
                $userName = "";
                if(arrKeyIssetAndExist($v,'uid')){
                    $userName = UserModel::db()->getById( $v['uid'])['name'];
                }

                $HouseService =  new HouseService();
                $v = $HouseService->formatRow($v);

                $addOrderBnt = "";
                if($v['status'] == HouseModel::STATUS_WAIT){
                    $addOrderBnt = '<a href="/house/no/order/add/hid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 添加订单 </a>';
                }
                $pics= "";
                if($v['pics']){
                    $p = explode(",",$v['pics']);
                    $pics = '<img height="30" width="30" src="'.get_house_url($p[0]).'" />';
                }
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $masterName,
                    $userName,
                    HouseModel::STATUS[$v['status']],
                    $pics,
//                    $v['province_code'] ."-".$v['city_code'].$v['county_code'].$v['town_code'],
                    $v['province_cn'] ." ".$v['city_cn']." ".$v['county_cn']." ".$v['town_cn'],
                    $v['community'],
                    $v['build_floor'],

                    HouseModel::DIRECTION_DESC[$v['build_direction']],

                    $v['build_area'],
                    $v['build_room_num'],
                    HouseModel::FITMENT_DESC[$v['build_fitment']],

                    get_default_date($v['a_time']),
                    $addOrderBnt,
//                    '<a href="/product/no/product/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>',
//                    '<button class="btn btn-xs default '.$statusCssColor.' btn blue upstatus btn-xs margin-bottom-5" data-id="'.$v['id'].'" data-type="'.$type.'"><i class="fa fa-link"></i>'.$statusBnt.'</button>'.
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

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $master = _g("master");
        $user = _g('user');
        $status = _g('status');
        $community = _g('community');
        $build_floor_from = _g('build_floor_from');
        $build_floor_to = _g('build_floor_to');

        $build_direction = _g("build_direction");
        $build_area_from = _g("build_area_from");
        $build_area_to = _g('build_area_to');

        $build_room_num = _g('build_room_num');
        $build_fitment = _g('build_fitment');

        $from = _g('from');
        $to = _g('to');

        if($id)
            $where .=" and id = '$id' ";

        if($master)
            $where .= MasterModel:: getSearchWhereByKeyword($master,'master_id');

        if($user)
            $where .= UserModel::getSearchWhereByKeyword($user,'uid');

        if($status)
            $where .=" and status = '$status' ";

        if($community)
            $where .=" and community like '%$community%' ";

        if($build_floor_from)
            $where .=" and build_floor >= '$build_floor_from' ";

        if($build_floor_to)
            $where .=" and build_floor <= '$build_floor_to' ";

        if($build_direction)
            $where .=" and build_direction = $build_direction ";

        if($from){
            $from .= ":00";
            $where .= " and add_time >= '".strtotime($from)."'";
        }

        if($to){
            $to .= ":59";
            $where .= " and add_time <= '".strtotime($to)."'";
        }

        if($build_area_from)
            $where .=" and build_area >=  '$build_area_from'";

        if($build_area_to)
            $where .=" and build_area <=  '$build_area_to";

        if($build_room_num)
            $where .=" and build_room_num = $build_room_num";

        if($build_fitment)
            $where .=" and build_fitment = $build_fitment";

        return $where;
    }

    function multipleUploadOneImg(){
        $uploadService = new UploadService();
        $uploadRs = $uploadService->house('pic');
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
}