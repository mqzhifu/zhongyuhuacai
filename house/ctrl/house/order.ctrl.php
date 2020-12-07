<?php
class OrderCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());
        $this->assign("getCateTypeOptions",OrderModel::getCateTypeOptions());

        $this->display("/house/order_list.html");
    }
    function delone(){
        $id = _g("id");
        if(!$id){
            out_ajax(500,"id 为空");
        }
        $info = OrderModel::db()->getById($id);
        if(!$info){
            out_ajax(501,"id 不在DB中");
        }

        if($info['status'] == OrderModel::STATUS_OK){
            out_ajax(502,"状态错误，该订单已生成支付列表");
        }

        $rs = OrderModel::db()->delById($id);
        out_ajax(200,"ok");
    }

    function oldCreatePayRecord(){
//根据 付款 方式 计算出，一共需要收付款 次数
        $num = (int)$days / OrderModel::PAY_TYPE_TURN_DAY[$order['pay_mode']];
        //虽然能计算出，每次付款金额，但肯定很多情况会有 余数
        $mod = $days % OrderModel::PAY_TYPE_TURN_DAY[$order['pay_mode']];
        //每次付款的单价
        $price = $order['price'] / $num;
        if($order['category'] == OrderModel::CATE_MASTER){
            $type = OrderModel::FINANCE_EXPENSE;
            $typeDesc = "支出";
        }else{
            $type = OrderModel::FINANCE_INCOME;
            $typeDesc = "收入";
        }

        $payList = [];
        $s_time = $order['contract_start_time'];
        $e_time = 0;
        for($i=0;$i<$num;$i++){
            $finalPrice = $price;
            if(!$i ){//第一次付款如果是租房，得把押金一并给了
                if($order['category'] == OrderModel::CATE_USER){
                    $finalPrice = $price - $order['deposit_price'];
                }
            }
            $e_time = $s_time + OrderModel::PAY_TYPE_TURN_DAY[$order['pay_mode']] * 24 * 60 * 60;
            $warn_trigger_time = $e_time - $order['warn_trigger_time'];
            $data = array(
                'id'=>$i+1,
                "house_id"=>$order['house_id'],
                "oid"=>$order['id'],
                'type' =>$type,
                'price'=>$finalPrice,
                "start_time"=>$s_time,
                "end_time"=>$e_time,
                "start_time_dt"=>get_default_date($s_time),
                "end_time_dt"=>get_default_date($e_time),

                "status"=>OrderPayListModel::STATUS_WAIT,
                'pay_third_no'=>"",
                'pay_type'=> 0,
                'pay_time'=>0,
                'a_time'=>time(),
                "warn_trigger_time_dt"=>get_default_date($warn_trigger_time),
                "warn_trigger_time"=>$warn_trigger_time,
                'category'=>$order['category'],
            );
            $payList[] = $data;
        }
    }
    //一个订单，生成-付款记录
    function createPayRecord(){
        $oid = _g("oid");
        if(!$oid){
            $this->notice("oid is null");
        }

        $order = OrderModel::getById($oid);
        if(!$order){
            $this->notice("oid not in db");
        }

        $orderPayListExist = OrderPayListModel::db()->getRow(" oid = {$order['id']} and category = {$order['category']}");
        if($orderPayListExist){
            $this->notice("该订单已生成过支付记录，请不要重复操作");
        }

        $house = HouseModel::db()->getById($order['house_id']);
        $master = MasterModel::db()->getById($house['master_id']);
        $this->assign("house",$house);
        $this->assign("master",$master);

        if($order['category'] == OrderModel::CATE_MASTER){
            $type = OrderModel::FINANCE_EXPENSE;
            $typeDesc = "支出";
        }else{
            $type = OrderModel::FINANCE_INCOME;
            $typeDesc = "收入";
        }

        //检查 - 合同 开始时间  与  结束时间   是否正确
        $this->checkContractTimePeriodPay($order['contract_start_time'] , $order['contract_end_time'],$order['pay_mode']);
        //结束时间  - 开始时间  = 合同周期 (秒)
        $distance = $order['contract_end_time'] - $order['contract_start_time'];
        //合同周期 秒 => 天
        $days = $distance / $this->getOneDayTurnSecond();
        //房租金额 = 合同金额 - 押金
        $finalPrice = $order['price'];
        if($order['category'] == OrderModel::CATE_USER){
            $finalPrice = $finalPrice - $order['deposit_price'];
        }
        //每天的金额
        $everyDayPrice = $finalPrice / $days;

        $s_time = $order['contract_start_time'];
        $e_time = $order['contract_end_time'];
        echo "s_time:".date("Y-m-d H:i:s",$s_time)." , e_time: ".date("Y-m-d H:i:s",$e_time)." <br/>";
//        $num = 0;
        $calcPayList = [];
        while(1){
            $monthUnit = OrderModel::PAY_TYPE_TURN_MONTH[$order['pay_mode']];
            $unitTime =  strtotime("+$monthUnit month",$s_time) ;
            $unitTime = $unitTime - $this->getOneDayTurnSecond();
            if($unitTime > $e_time){
                $distanceMonthUnit = ( $e_time - $s_time  + $this->getOneDayTurnSecond() ) /$this->getOneDayTurnSecond() ;
                $calcPayList[] = array("s_time"=>$s_time,'e_time'=>$e_time , "distance"=>$distanceMonthUnit,'price'=>$distanceMonthUnit * $everyDayPrice);
//                $num++;
                break;
            }elseif($unitTime == $e_time){//这种是最好的情况，时间刚刚好，没有余数
                $distanceMonthUnit = ( $unitTime - $s_time + $this->getOneDayTurnSecond() ) /$this->getOneDayTurnSecond() ;
                $calcPayList[] = array("s_time"=>$s_time,'e_time'=>$unitTime , "distance"=>$distanceMonthUnit,'price'=>$monthUnit * $order['month_price']);
//                $num++;
                break;
            }
            $distanceMonthUnit = ( $unitTime - $s_time + $this->getOneDayTurnSecond() ) /$this->getOneDayTurnSecond() ;

            $calcPayList[] = array("s_time"=>$s_time,'e_time'=>$unitTime , "distance"=>$distanceMonthUnit,'price'=>$monthUnit * $order['month_price']);
//            $num++;
            $s_time = $unitTime + $this->getOneDayTurnSecond();
        }
        echo "<br/>";
        $payList = [];
        foreach ($calcPayList as $k=>$v){
            $warn_trigger_time = $e_time - $order['warn_trigger_time'];
            $data = array(
                'id'=>$k+1,
                "house_id"=>$order['house_id'],
                "oid"=>$order['id'],
                'type' =>$type,
                'price'=>$v['price'],
                "start_time"=>$v['s_time'],
                "end_time"=>$v['e_time'],
                "start_time_dt"=>get_default_date($v['s_time']),
                "end_time_dt"=>get_default_date($v['e_time']),

                "distance_day"=>$v['distance'],
                "status"=>OrderPayListModel::STATUS_WAIT,
                'pay_third_no'=>"",
                'pay_type'=> 0,
                'pay_time'=>0,
                'a_time'=>time(),
                "warn_trigger_time_dt"=>get_default_date($warn_trigger_time),
                "warn_trigger_time"=>$warn_trigger_time,
                'category'=>$order['category'],
            );
            $payList[] = $data;
        }
        if(_g("opt")){
            foreach ($payList as $k=>$v){
                unset($v['id']);
                unset($v['start_time_dt']);
                unset($v['end_time_dt']);
                unset($v['warn_trigger_time_dt']);

                OrderPayListModel::db()->add($v);
            }

            $this->ok("生成记录成功!");
        }

        $this->assign("typeDesc",$typeDesc);
        $this->assign("distance",$distance);
        $this->assign("days",$days);
        $this->assign("num",count($payList));
        $this->assign("mod","");
        $this->assign("finalPrice",$finalPrice);
        $this->assign("everyDayPrice",$everyDayPrice);

        $this->assign("payList",$payList);
        $this->assign("order",$order);

        $this->display("/house/create_pay_record.html");
    }

    function getOneDayTurnSecond(){
        return 24 * 60 * 60;
    }

    function testEcho(){
//        echo "<table>";
//        echo "<tr><td>收付款类型</td><td>$typeDesc</td></tr>";
//        echo "<tr><td>合同金额：</td><td>{$order['price']}</td></tr>";
//        echo "<tr><td>周期</td><td>$days(day)</td><td>{$order['contract_end_time'] }-{$order['contract_start_time']}=$distance=$days(day)</td></tr>";
//        echo "<tr><td>收付款类型</td><td>".OrderModel::PAY_TYPE_DESC[$order['pay_mode']]."</td><td>".OrderModel::PAY_TYPE_TURN_DAY[$order['pay_mode']]."</td></tr>";
//        echo "<tr><td>收付款次数</td><td>$num 次</td></tr>";
//        echo "<tr><td>每次收付款金额</td><td>$price </td><td> {$order['price']} / $num</td></tr>";
//        echo "<tr><td>最后余额</td><td>$mod</td></tr>";
//        echo "</table>";
//        exit;
    }

    function add(){
        $hid = _g("hid");
        if(!$hid){
            $this->notice("hid is null");
        }

        $house = HouseModel::db()->getById($hid);
        if(!$house){
            $this->notice("hid not in db");
        }

        if(_g("opt")){

            $data = array(
                'house_id'=>$hid,
                'deposit_price'=>_g('deposit_price'),
                'contract_start_time'=> strtotime( _g('contract_start_time')),
                'contract_end_time'=> strtotime( _g('contract_end_time')),
                'type'=>_g('type'),
                'category'=>_g('category'),
                'uid'=>_g('uid'),
                'price'=>_g('price'),
                'month_price'=>_g("month_price"),
                'admin_id'=>$this->_adminid,
                'a_time'=>time(),
                "pay_mode"=>_g('pay_mode'),
                "warn_trigger_time"=>_g('warn_trigger_time'),

                "status"=>OrderModel::STATUS_WAIT,
//                "tenancy_pay_mode"=>_g('tenancy_pay_mode'),
//                "master_pay_mode"=>_g('master_pay_mode'),
            );

            if($data['category'] == OrderModel::CATE_USER){//租户
                $data['deposit_price'] = (int)$data['deposit_price'];
                if(! $data['deposit_price'] || $data['deposit_price'] <= 0){
                    $this->notice("押金，不能为空且 必须为正整数");
                }

                if(!arrKeyIssetAndExist($data,'uid')){
                    $this->notice("uid不能为空");
                }
                $user = UserModel::db()->getById($data['uid']);
                if(!$user){
                    $this->notice("uid错误，不在DB中");
                }

                $existMasterOrder = OrderModel::db()->getRow(" house_id = {$hid} and category = ".OrderModel::CATE_MASTER);
                if(!$existMasterOrder){
                    $this->notice("房源必须得先有：房主订单，才能再有<租房订单>!!!");
                }
            }else{
                $data['deposit_price'] = 0;
                $data['uid'] = 0;
            }

            if($house['status'] == HouseModel::STATUS_USED){
                $this->notice("该房源状态为：".HouseModel::STATUS[HouseModel::STATUS_USED].",不能添加订单");
            }

            $existOrder = OrderModel::db()->getRow(" house_id = {$hid} and category = {$data['category']} and status =  ".OrderModel::STATUS_WAIT);
            if($existOrder){
                $msg = "该房源，已存在<".OrderModel::CATE_DESC[$data['category']].">订单，不要重复添加，您可删除无用数据，再来操作!";
                $this->notice($msg);
            }

            $data['price'] = (int)$data['price'];
            if(! $data['price'] || $data['price'] <= 0){
                $this->notice("合同金额，不能为空且 必须为正整数");
            }

            if(!$data['contract_start_time'] ){
                $this->notice("合同开始时间 不能为空");
            }

            if(!$data['contract_end_time'] ){
                $this->notice("合同结束时间 不能为空");
            }

            if($data['contract_start_time'] >= $data['contract_end_time']){
                $this->notice("合同开始时间不能 >= 结束时间");
            }

//            if(!is_numeric($data['tenancy_pay_mode'])){
//                $this->notice("租户付款方式不能为空");
//            }
//            if(!$data['master_pay_mode']){
//                $this->notice("房主付款方式不能为空");
//            }
//            if(!$data['uid'] ){
//                $this->notice("用户ID不能为空!");
//            }

            if(!is_numeric($data['pay_mode'])){
                $this->notice("付款方式不能为空");
            }
            $this->checkContractTimePeriodPay($data['contract_start_time'],$data['contract_end_time'] , $data['pay_mode']);

            if(!$data['month_price'] ){
                $this->notice("月租金 不能为空");
            }

            if(!$data['type'] ){
                $this->notice("类型 不能为空");
            }

            if(!$data['category'] ){
                $this->notice("用户类型不能为空!");
            }

            if($data['category'] == OrderModel::CATE_MASTER){
//                $uinfo = MasterModel::db()->getById($data['uid']);
            }else{
                $uinfo = UserModel::db()->getById($data['uid']);
                if(!$uinfo){
                    $this->notice("用户ID不在DB中!");
                }
            }

            $data['warn_trigger_time'] = (int)$data['warn_trigger_time'];
            if(!$data['warn_trigger_time'] ){
                $this->notice("付款提醒时间");
            }

            $uploadService = new UploadService();
            $uploadRs = $uploadService->contract('contract_attachment');
            if($uploadRs['code'] != 200){
                exit(" uploadService->contract_attachment error ".json_encode($uploadRs));
            }
            $data['contract_attachment'] = $uploadRs['msg'];

            $newId = OrderModel::db()->add($data);
            $this->ok($newId,$this->_backListUrl);
        }

        $this->assign("house",$house);

        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());
        $this->assign("getCateTypeOptions",OrderModel::getCateTypeOptions());

        $this->addHookJS("/house/order_add_hook.html");
        $this->display("/house/order_add.html");
    }
    //检查 合同的 时间周期  与付款方式
    function checkContractTimePeriodPay($s_time,$e_time,$payType){
        $distance = $e_time - $s_time;
        if($payType == OrderModel::PAY_TYPE_YEAR){
            if($distance < OrderModel::PAY_TYPE_TURN_DAY[$payType] * 24 * 60 * 60){
                $this->notice("整年付，合同的周期，不能小于365天");
            }
        }elseif($payType == OrderModel::PAY_TYPE_HALF_YEAR){
            if($distance < OrderModel::PAY_TYPE_TURN_DAY[$payType] * 24 * 60 * 60){
                $this->notice("半年付，合同的周期，不能小于183天");
            }
        }elseif($payType == OrderModel::PAY_TYPE_QUARTER){
            if($distance < OrderModel::PAY_TYPE_TURN_DAY[$payType] * 24 * 60 * 60){
                $this->notice("季付，合同的周期，不能小于90天");
            }
        }elseif($payType == OrderModel::PAY_TYPE_MONTH){
            if($distance < OrderModel::PAY_TYPE_TURN_DAY[$payType] * 24 * 60 * 60){
                $this->notice("月付，合同的周期，不能小于31天");
            }
        }else{
            $this->notice("pay_type err");
        }

        return 1;
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = OrderModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";

            $sort = array(
                'id',
                'id',
                'house_id',
                'type',
                'status',
                'price',
                'deposit_price',
                'pay_mode',
                'category',
                'uid',
                'contract_attachment',
                'contract_start_time',
                'contract_end_time',
                "a_time",
                'admin_id',
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

            $limit = " limit $iDisplayStart,$end";
            $data = OrderModel::db()->getAll($where . $order .$limit);

            foreach($data as $k=>$v){
                $contract_attachment = "";
                if($v['contract_attachment']){
                    $url = get_contract_url($v['contract_attachment']);
                    $contract_attachment = "<a href='$url' target='_blank'>".'<i class="fa fa-file-text"></i></a>';
                }

                $delBnt= "";
                $createPayListBnt = "";
                if($v['status'] == OrderModel::STATUS_WAIT){
                    $createPayListBnt = '<a href="/house/no/order/createPayRecord/oid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 生成收/付款 </a>';
                    $delBnt = '<a  class="btn red btn-xs margin-bottom-5 delone"  data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i>删除 </a>';
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['house_id'],
                    OrderModel::TYPE_DESC[$v['type']],
                    $v['status'],
                    $v['price'],
                    $v['deposit_price'],
                    OrderModel::PAY_TYPE_DESC[$v['pay_mode']],
                    OrderModel::CATE_DESC[$v['category']],
                    $v['uid'],
                    $contract_attachment,
                    get_default_date($v['contract_start_time']),
                    get_default_date($v['contract_end_time']),
                    get_default_date($v['a_time']),
                    $v['admin_id'],
                    $createPayListBnt . $delBnt,
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
        $id = _g("id");
        $product_name = _g("product_name");
        $status = _g("status");

        $from = _g("from");
        $to = _g("to");

        $stock_from = _g("stock_from");
        $stock_to = _g("stock_to");

        $sale_price_from = _g("sale_price_from");
        $sale_price_to = _g("sale_price_to");

        $original_price_from = _g("original_price_from");
        $original_price_to = _g("original_price_to");

        $haulage_from = _g("haulage_from");
        $haulage_to = _g("haulage_to");

        $order_total_from = _g("order_total_from");
        $order_total_to = _g("order_total_to");


        if($id)
            $where .=" and id = '$id' ";

        $productService = new ProductService();
        if($product_name){
            $where .= $productService->searchUidsByKeywordUseDbWhere($product_name);
        }
        if($status)
            $where .=" and status = '$status' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($stock_from)
            $where .=" and stock_from >=  ".strtotime($stock_from);

        if($stock_to)
            $where .=" and stock_to <= ".strtotime($stock_to);


        if($sale_price_from)
            $where .=" and sale_price_from >=  ".strtotime($sale_price_from);

        if($sale_price_to)
            $where .=" and sale_price_to <= ".strtotime($sale_price_to);

        if($original_price_from)
            $where .=" and original_price_from >=  ".strtotime($original_price_from);

        if($original_price_to)
            $where .=" and original_price_to <= ".strtotime($original_price_to);

        if($haulage_from)
            $where .=" and haulage_from >=  ".strtotime($haulage_from);

        if($haulage_to)
            $where .=" and haulage_to <= ".strtotime($haulage_to);


        if($order_total_from)
            $where .=" and order_total_from >=  ".strtotime($order_total_from);

        if($order_total_to)
            $where .=" and order_total_to <= ".strtotime($order_total_to);


        return $where;
    }


}