<?php
class HouseCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $statusSelectOptionHtml = HouseModel::getStatusSelectOptionHtml();
        $this->assign("statusSelectOptionHtml",$statusSelectOptionHtml);
        //装修
        $getFitmentSelectOptionHtml = HouseModel::getFitmentSelectOptionHtml();
        $this->assign("getFitmentSelectOptionHtml",$getFitmentSelectOptionHtml);
        //朝向
        $getDirectionSelectOptionHtml = HouseModel::getDirectionSelectOptionHtml();
        $this->assign("getDirectionSelectOptionHtml",$getDirectionSelectOptionHtml);

        $this->display("/house/house_list.html");
    }
    //详情里，获取一个订单list
    function getOrderListByHouseId($hid){
        $orderList = OrderModel::db()->getAll(" house_id = $hid");
        if (!$orderList)
            return null;

        foreach ($orderList as $k=>$v){
            $adminUserName = AdminUserModel::getFieldById( $v['admin_id'],'uname');

            $row =$v;
            $row['type_desc'] =       OrderModel::TYPE_DESC[$v['type']];
            $row['status_desc'] =        OrderModel::STATUS_DESC[$v['status']];
            $row['price'] =        $v['price'];
            $row['deposit_price'] =        $v['deposit_price'];
            $row['pay_mode_desc'] =        OrderModel::PAY_TYPE_DESC[$v['pay_mode']];
            $row['category_desc'] =        OrderModel::CATE_DESC[$v['category']];
            $row['uid'] =        $v['uid'];
            $row['contract_start_time_dt'] =        get_default_date($v['contract_start_time']);
            $row['contract_end_time_dt'] =        get_default_date($v['contract_end_time']);
            $row['dt'] =        get_default_date($v['a_time']);
            $row['admin_name'] =       $adminUserName;
            $orderList[$k] = $row;
        }
        return $orderList;
    }
    //房源详情
    function detail(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $house = HouseModel::db()->getById($id);
        if (!$house){
            $this->notice("id not in db");
        }
        $house['dt'] = get_default_date($house['a_time']);
        $house['udt'] = get_default_date($house['u_time']);

        $admin = AdminUserModel::db()->getById($house['admin_id']);
        $house['admin_name'] = $admin['uname'];
        $house['saler_name'] = get_admin_name($house['saler_id']);

        $house['build_fitment_desc'] = HouseModel::DIRECTION_DESC[$house['build_fitment']];
        $house['build_direction_desc'] = HouseModel::FITMENT_DESC[$house['build_direction']];

        $house['status_desc'] = HouseModel::STATUS[$house['status']];

        $user = null;
        if($house['uid']){
            $user = UserModel::db()->getById($house['uid']);
        }

        $picsList = array();
        if(arrKeyIssetAndExist($house,"pics")){
            $pics = explode(",",$house['pics']);
            foreach ($pics as $k=>$v){
                $picsList[] = get_house_url($v);
            }
        }

        $master = MasterModel::db()->getById($house['master_id']);
        $orderList = $this->getOrderListByHouseId($house['id']);

        $userOrder = OrderModel::getHouseCategoryRowByInStatus($id,OrderModel::CATE_USER,OrderModel::STATUS_WAIT . "," .OrderModel::STATUS_OK);
        $masterOrder = OrderModel::getHouseCategoryRowByInStatus($id,OrderModel::CATE_MASTER,OrderModel::STATUS_WAIT . "," .OrderModel::STATUS_OK);

        $userRoi = 0;
        if($userOrder){
            $userOrderInfo = "已生成";
            if($userOrder['status'] == OrderModel::STATUS_WAIT){
                $userOrderInfo .= "，但未生成支付记录";
            }else{
                $userOrderInfo .= "，已未生成支付记录";
            }
        }else{
            $userOrderInfo = "未生成";
        }

        $masterRoi = 0;
        if($masterOrder){
            $masterOrderInfo = "已生成";
            if($masterOrder['status'] == OrderModel::STATUS_WAIT){
                $masterOrderInfo .= "，但未生成支付记录";
            }else{
                $masterOrderInfo .= "，已未生成支付记录";
            }
        }else{
            $masterOrderInfo = "未生成";
        }

        $roi = $userRoi + $masterRoi;
        $this->assign("roi",$roi);
        $this->assign("userRoi",$userRoi);
        $this->assign("masterRoi",$masterRoi);

        $this->assign("userOrderInfo",$userOrderInfo);
        $this->assign("masterOrderInfo",$masterOrderInfo);
        $this->assign("user",$user);
        $this->assign("master",$master);
        $this->assign("house",$house);
        $this->assign("orderList",$orderList);
        $this->assign("picsList",$picsList);


        $this->display("/house/house_detail.html");
    }
    //添加一条房源
    function add(){
        if(_g('opt')){

            $data['saler_id']  = _g("saler_id");
            $data['status'] = HouseModel::STATUS_INIT;
            $data['pics'] = _g("pics");
            $data['desc']  = _g("desc");
            $data['community'] = _g("community");
            //房屋信息
            $data['build_no'] = _g("build_no");
            $data['build_unit'] = _g("build_unit");
            $data['build_detail_no'] = _g("build_detail_no");
            $data['build_floor'] = _g("build_floor");
            $data['build_area'] = _g("build_area");
            $data['build_direction'] = _g("build_direction",0);
            $data['build_room_num'] = _g("build_room_num",0);
            $data['build_fitment'] = _g("build_fitment",0);
            $data['a_time'] = time();
            $data['admin_id']  = $this->_adminid;
            $data['u_time'] = time();
//            $data['province_code'] = _g("province");
//            $data['city_code'] = _g("city");
//            $data['county_code'] = _g("county");
//            $data['town_code'] = _g("town");

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
//            if(!$data['master_id']){
//                $this->notice("master_id 为空 ");
//            }

            if(!$data['saler_id']){
                $this->notice(SALE_MAN."ID不能为空");
            }
            $saler = AdminUserModel::db()->getById($data['saler_id']);
            if(!$saler){
                $this->notice(SALE_MAN."ID错误，不存在DB中");
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

            //mysql8 验证有点难，这种INT 类型，不给默认值会出错
            if(!$data['build_fitment']){
                $data['build_fitment'] = HouseModel::FITMENT_UNKNOW;
            }
            if(!$data['build_direction']){
                $data['build_direction'] = HouseModel::DIRECTION_UNKNOW;
            }

            $data['province_code'] = 130000;
            $data['city_code'] = 131000;
            $data['county_code'] = 131082;
            $data['town_code'] = 131082450;

            if (!$data['build_direction']){
                $data['build_direction'] = 1;
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
            $data['pics'] = $pic;
            //房主信息--------start
            $dataMaster =array(
                'name'=> _g('master_name'),
                'bank_account_no'=> _g('master_bank_account'),
                'bank_name'=>_g('master_bank'),
                'bank_account_name'=>_g('master_bank_account_name'),
                'a_time'=>time(),
                'admin_id'=>$this->_adminid,
            );

            $mobile =  _g('master_mobile');
            if(!FilterLib::regex($mobile,"phone")){
                $this->notice(MASTER."-手机号格式错误 ");
            }

            $dataMaster['mobile'] = $mobile ;
            $newUserId = MasterModel::db()->add($dataMaster);
            $data['master_id'] = $newUserId;
//            $this->ok("成功-$newId");
            //房主信息--------start


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
                'u_time',
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
                $pic = "";
                if(arrKeyIssetAndExist($v,'pic')){
                    $pics = explode(",",$v['pic']);
                    $pic = get_house_url($pics[0]);
                }

                $adminUserName = AdminUserModel::getFieldById( $v['admin_id'],'nickname');
                $masterName = MasterModel::getFieldById( $v['master_id'],'name');
                $userName = "";
                if(arrKeyIssetAndExist($v,'uid')){
                    $userName = UserModel::db()->getById( $v['uid'])['name'];
                }

                $HouseService =  new HouseService();
                $v = $HouseService->formatRow($v);

                $addOrderBnt = "";
                if($v['status'] == HouseModel::STATUS_WAIT || $v['status'] == HouseModel::STATUS_INIT){
                    $addOrderBnt =
                        '<a href="/house/no/order/add/hid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 添加订单 </a>';
                }
                $closeBnt = "";
                if($v['status'] == HouseModel::STATUS_INIT){
                $closeBnt =
                    '<button class="btn red closeHouse btn-xs margin-bottom-5" data-id="'.$v['id'].'" data-status="'.HouseModel::STATUS_CLOSE.'"><i class="fa fa-minus-square"></i>'."关闭".'</button>';
                }
               $pics= "";
                if($v['pics']){
                    $p = explode(",",$v['pics']);
                    $pics = '<img height="30" width="30" src="'.get_house_url($p[0]).'" />';
                }

                $build_fitment = "";
                if($v['build_fitment']){
                    $build_fitment = HouseModel::FITMENT_DESC[$v['build_fitment']];
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
                    get_admin_name($v['saler_id']),
                    $v['community'],
                    $v['build_floor'],

                    HouseModel::DIRECTION_DESC[$v['build_direction']],

                    $v['build_area'],
                    $v['build_room_num'],
                    $build_fitment,

                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    $addOrderBnt. " ".$closeBnt.
                    '<a href="/house/no/house/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>',
                    );

                $records["data"][] = $row;
            }
        }

        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function upStatus(){
        $id = _g("id");
        $status = _g("status");
        if(!$id)
            out_ajax(500,"id is null");

        $house = HouseModel::db()->getById($id);
        if(!$house)
            out_ajax(500,"id not in db");

        if(!$status)
            out_ajax(500,"status is null");

        if($house['status'] == $status){
            out_ajax(500);
        }

        if($status == HouseModel::STATUS_CLOSE){
            if($house['status'] != HouseModel::STATUS_INIT){
                out_ajax(500,"状态不为：STATUS_INIT");
            }
            $masterOrder = OrderModel::db()->getRow("house_id = $id and status != ".OrderModel::STATUS_FINISH ." and category = ".OrderModel::CATE_MASTER );
            if($masterOrder){
                out_ajax(500,"还有未完结的：订单~");
            }

//            $userOrder = OrderModel::db()->getRow("house_id = $id and status = ".OrderModel::STATUS_OK ." and category = ".OrderModel::CATE_USER );
//            if($userOrder){
//                out_ajax(500,"还有未完结的：用户 订单~");
//            }

            HouseModel::upStatus($id,$status);
        }
    }

    function getDataListTableWhere(){
        $where = 1;

        $id = _g("id");
        $master = _g("master");
        $user = _g('user');
        $status = _g('status');
//        $community = _g('community');
        $build_floor_from = _g('build_floor_from');
        $build_floor_to = _g('build_floor_to');

        $build_direction = _g("build_direction");
        //面积
        $build_area_from = _g("build_area_from");
        $build_area_to = _g('build_area_to');
        //几居室
        $build_room_num = _g('build_room_num');
        //装修
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

//        if($community)
//            $where .=" and community like '%$community%' ";

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