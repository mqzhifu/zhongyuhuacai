<?php
class AddressCtrl extends BaseCtrl{

    public $orderService = null;

    function __construct($request)
    {
        parent::__construct($request);
        $this->orderService = new OrderService();
    }

    static function getIsDefaultOptions(){
        $html = "";
        foreach ( UserAddressService::IS_DEFAULT_DESC as $k=>$v) {
            $html .= "<option value={$k}>{$v}</option>";
        }
        return $html;
    }

    function index(){
        if(_g("getlist")){
            $this->getList();
        }


        $this->assign("getIsDefaultOptions", self::getIsDefaultOptions());
        $this->display("/useraction/address_list.html");
    }

    function add(){
        if(_g("opt")){

            $name = _g("name");

            $province_code = _g("province");
            $county_code = _g("county");
            $city_code = _g('city');
            $town_code = _g("town");

            $is_default = _g("is_default");
            $mobile = _g('mobile');
            $address = _g('address');

            $uid =_g("uid");

            if(!$province_code){
                $this->notice("省  不能为空");
            }
            if(!$county_code){
                $this->notice("县 不能为空");
            }

            if(!$city_code){
                $this->notice("市 不能为空");
            }

            if(!$is_default){
                $this->notice("默认地址 不能为空");
            }

            if(!$mobile){
                $this->notice("手机号 不能为空");
            }

            if(!$address){
                $this->notice("详细地址 不能为空");
            }

            if(!$uid){
                $this->notice("uid 不能为空");
            }

            $user = UserModel::db()->getById($uid);
            if(!$user){
                $this->notice("uid 错误，用户不存在，UID不在DB中");
            }

            $data = array(
                'province_code'=>$province_code,
                'county_code'=>$county_code,
                'city_code'=>$city_code,
                'town_code'=>$town_code,
                'is_default'=>$is_default,
                'mobile'=>$mobile,
                'address'=>$address,
                'uid'=>$uid,
                'name'=>$name,
            );
            $userAddressService = new UserAddressService();
            $rs = $userAddressService->addOne($uid,$data);
            if($rs['code'] == 200){
                $this->ok("成功");
            }else{
                $this->notice($rs['code']."-".$rs['msg']);
            }
        }

        $province = AreaProvinceModel::getSelectOptionsHtml();
        $cityJs = json_encode(AreaCityModel::getJsSelectOptions());
        $countryJs = json_encode(AreaCountyModel::getJsSelectOptions());

        $this->assign("provinceOption",$province);
        $this->assign("cityJs",$cityJs);
        $this->assign("countyJs",$countryJs);


        $this->assign("getIsDefaultOptions", self::getIsDefaultOptions());

        $this->addHookJS("/useraction/address_add_hook.html");
        $this->addHookJS("/layout/place.js.html");
        $this->display("/useraction/address_add.html");
    }

//    function editShip(){
//        $id = _g("id");
//        if(!$id){
//            exit("id 为空");
//        }
//        $order = OrderModel::db()->getById($id);
//        if(!$order){
//            exit("id 不在 db中");
//        }
//        if(_g('opt')){
//            $no = _g("no");
//            $shipType = _g("ship_type");
//
//            $data = array(
//                'ship_time'=>time(),
//                'express_no'=>$no,
//                'ship_type'=>$shipType,
//            );
//
//            $upRs = OrderModel::db()->upById($id,$data);
//
//            var_dump($upRs);
//            var_dump($data);exit;
//        }
//        $shipTypeDesc = OrderService::SHIP_TYPE_DESC;
//        $shipTypeDescHtml = "";
//        foreach ($shipTypeDesc as $k=>$v) {
//            $shipTypeDescHtml .= "<option name='$k'>$v</option>";
//        }
//
//        $data = array(
//            'shipTypeDescHtml'=>$shipTypeDescHtml,
//            'id'=>$id,
//        );
//
//
//        $html = $this->_st->compile("/finance/order_edit_ship.html",$data);
//        $html = file_get_contents($html);
//        echo_json($html);
//    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = UserAddressModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'uid',
                'province_code',
                'city_code',
                'county_code',
                'town_code',
                'is_default',
                'mobile',
                'address',
                'add_time',
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

            $data = UserAddressModel::db()->getAll($where . $order);

            $UserAddressService  = new UserAddressService();
            foreach($data as $k=>$v){
//                $refundBnt = "";
                $username = "";
                if(arrKeyIssetAndExist($v,'uid')){
                    $username = UserModel::db()->getOneFieldValueById($v['uid'],'nickname',"--");
                }

                $formatRow = $UserAddressService->formatRow($v);

                $isDefault = "--";
                if($v['is_default']){
                    $isDefault = UserAddressService::IS_DEFAULT_DESC[$v['is_default']];
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $username,
                    $formatRow['province_cn'] . "|".  $v['province_code'],
                    $formatRow['city_cn'] . "|".$v['city_code'],
                    $formatRow['county_cn'] . "|".$v['county_code'],
                    $formatRow['town_cn'] . "|".$v['town_code'],
                    $isDefault,
                    $v['mobile'],
                    $v['address'],
                    get_default_date($v['a_time']),

                    '',
//                    $refundBnt.
//                    '<a target="_blank"  href="/finance/no/order/detail/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 详情 </a>'.
//                    '<a target="_blank"  href="/finance/no/order/edit/id='.$v['id'].'" class="btn green btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 编辑 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_ONE.'&oids='.$v['id'].'&uid=1" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 一级代理提现 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_LEVEL_TWO.'&oids='.$v['id'].'&uid=2" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 二级代理提现 </a>'.
//                    '<a target="_blank"  href="/finance/no/withdraw/add/role='.AgentModel::ROLE_FACTORY.'&oids='.$v['id'].'&fid=3" class="btn yellow btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-file-o"></i> 工厂提现 </a>'.
//                    '<button class="btn btn-xs default red editship margin-bottom-5"  data-id="'.$v['id'].'" ><i class="fa fa-female"></i> 快递信息</button>'. "&nbsp;",
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


    function edit(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $order = OrderModel::db()->getById($id);


        if(_g("opt")){
            $data = array(
                'express_no'=>_g("express_no"),
            );

            OrderModel::db()->upById($id,$data);
            $this->ok("成功");
        }


        $this->assign("order",$order);

        $this->addHookJS("/finance/order_edit_hook.html");
        $this->display("/finance/order_edit.html");


    }

    function detail(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $order = OrderModel::db()->getById($id);
        if(!$order){
            $this->notice("id  not in db");
        }
        $orderService =  new OrderService();
        //产品/商品列表
        $order['goods_list'] = $orderService->getOneDetail($id)['msg'];
        //添加时间
        $order['dt'] = get_default_date($order['a_time']);
        //支付时间
        $order['pay_time_dt'] = get_default_date($order['pay_time']);
        $order['u_time_dt'] = get_default_date($order['u_time']);
        //签收时间
        $order['signin_time_dt'] = get_default_date($order['signin_time']);
        //发货时间
        $order['expire_time_dt'] = get_default_date($order['expire_time']);
        $order['status_desc'] = OrderModel::STATUS_DESC[$order['status']];
        //订单包括总商品数
        $order['goods_total_num'] = count(explode(",",$order['gids']));


        $order['agent_one_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['agent_one_withdraw']];
        $order['agent_two_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['agent_two_withdraw']];
        $order['factory_withdraw_desc'] = WithdrawMoneyService::WITHDRAW_ORDER_STATUS_DESC[$order['factory_withdraw']];

        $address = array("area"=>"","detail"=>"");
        if(arrKeyIssetAndExist($order,'address_id')){
            $addressService =  new UserAddressService();
            $addressRow = $addressService->getById($order['address_id']);
            if($addressRow['code'] != 200){
                $this->notice($addressRow['msg']);
            }
            $addressRow = $addressRow['msg'];
            $address['area'] = $addressRow['province_cn'] . "-" .   $addressRow['city_cn'] . "-" .  $addressRow['county_cn']  . "-" .  $addressRow['town_cn'];
            $address['detail'] = $addressRow['address'];
        }

        $agent = null;
        $agentFather = null;
        $shareUser = null;

        if(arrKeyIssetAndExist($order,'share_uid')){
            $shareUser = UserModel::db()->getById($order['share_uid']);
        }

        if(arrKeyIssetAndExist($order,'agent_id')){
            $agent = AgentModel::db()->getById($order['agent_id']);
            if($agent['type'] == AgentModel::ROLE_LEVEL_TWO){
                $agentFather = AgentModel::db()->getById($agent['invite_agent_uid']);
            }
        }

        $this->assign("address",$address);

        $this->assign("shareUser",$shareUser);
        $this->assign("agentFather",$agentFather);
        $this->assign("agent",$agent);
        $this->assign("orderDetail",$order);

        $this->display("/finance/order_detail.html");


//        $category = ProductCategoryModel::db()->getById($product['category_id']);
//        $product['category_name'] = $category['name'];
//
//        $admin = AdminUserModel::db()->getById($product['admin_id']);
//        $product['admin_name'] = $admin['uname'];
//
//        $product['status_desc'] = ProductModel::STATUS[$product['status']];
//
//        $product['desc_attr_arr'] = "";
//        if(arrKeyIssetAndExist($product,'desc_attr')){
//            $product['desc_attr_arr'] = json_decode($product['desc_attr'],true);
//        }
//
//        $factory = FactoryModel::db()->getById($product['factory_uid']);
//
//        $product['factory'] = $factory['title'];
//        if(arrKeyIssetAndExist($product,'pic')){
//            $pics = explode(",",$product['pic']);
//            foreach ($pics as $k=>$v) {
//                $product['pics'][] = get_product_url($v);
//            }
//        }
//
//        $goodsList = GoodsModel::getListByPid($id);
//        $product['goods_num'] = 0;
//        if($goodsList){
//            $product['goods_num'] = count($goodsList);
//        }
//
//        $attributeArr = ProductModel::attrParaParserToName($product['attribute']);

//        $this->assign("goodsList",$goodsList);
//        $this->assign("product",$product);

    }

    function getDataListTableWhere(){
        $where = 1;


        $province_code = _g("province_code");
        $county_code = _g("county_code");
        $city_code = _g('city_code');
        $town_code = _g("town_code");

        $is_default = _g("is_default");
        $mobile = _g('mobile');
        $address = _g('address');

        $from = _g("from");
        $to = _g("to");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($is_default)
            $where .=" and is_default = '$is_default' ";

        if($province_code)
            $where .=" and province_code = '$province_code' ";

        if($county_code)
            $where .=" and county_code = '$county_code' ";

        if($city_code)
            $where .=" and city_code = '$city_code' ";

        if($town_code)
            $where .=" and town_code = '$town_code' ";

        if($mobile)
            $where .=" and mobile = '$mobile' ";

        if($address)
            $where .=" and address like '%$address%' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

//        if($share_username){
//            $userService = new UserService();
//            $where .= $userService->searchUidsByKeywordUseDbWhere($share_username);
//        }

        return $where;
    }


}