<?php
class OrderCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->assign("getStatusOptions",OrderModel::getStatusOptions());
        //付款类型
        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        //租赁/出卖
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());
        //房主/租户
        $this->assign("getCateTypeOptions",OrderModel::getCateTypeOptions());
        //结算类型
        $this->assign("getFinishTypeOptions",OrderModel::getFinishTypeOptions());


        $this->display("/house/order_list.html");
    }
    //删除一个订单 - 状态必须为：未生成付款记录
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

    function calcAll($order){
        //结束时间  - 开始时间  = 合同周期 (秒)
        $distance_second = $order['contract_end_time'] - $order['contract_start_time'];
        $distance = $order['contract_end_time'] - $order['contract_start_time'] + 1;//最后一天是 23:59:59，有1秒误差


        //计算  合同同期内 共计 月数  余 多少天
        $loopBreak = 0;
        $every_monty_start_time = $order['contract_start_time'];
        $monthTotal = 0;//周期内一共几个月
        $modDays = 0;//周期内，除掉月之后，余多少天
//        var_dump(date("Y-m-d H:i:s",$order['contract_start_time']));
//        var_dump(date("Y-m-d H:i:s",$order['contract_end_time']));
//        echo "<Br/><br/>";
        while(1){
            if($loopBreak){
                break;
            }
            //将开始时间加上:一个月，即取下个月的<这个时间> ，但是多了一天
            $monthEnd = strtotime("+1 month",$every_monty_start_time) ;
            $monthEnd = $monthEnd - 1;

//            var_dump(date("Y-m-d H:i:s",$every_monty_start_time));
//            var_dump(date("Y-m-d H:i:s",$monthEnd));
//            echo "<br>";
            if ($monthEnd ==  $order['contract_end_time']){
                $loopBreak = 1;
                $monthTotal++;
            }elseif($monthEnd > $order['contract_end_time']){
                $loopBreak = 1;
                $modDays = round (  ($order['contract_end_time'] + 1 -  $every_monty_start_time) / getOneDayTurnSecond() ) + 1;
            }else{
                $monthTotal++;
                $every_monty_start_time = $monthEnd + 1;
            }
        }
//        var_dump($modDays);
//        var_dump($monthTotal);
//        var_dump(111);exit;



        //合同周期 内，共计：天数
        $days = $distance / getOneDayTurnSecond();
        if($order['category'] == OrderModel::CATE_USER){
            $otherPrice =   $order['deposit_price'] + $order['water_price'] + $order['elec_price'] + $order['garbage_price'] + $order['repair_fund_price'];
        }else{
            $otherPrice =   "-".$order['vacancy_price'];
//            $otherPrice = 0;
        }
        $houseLeasePrice = $order['price']  - $otherPrice ;
        //每天的金额
        $everyDayPrice = round($houseLeasePrice / $days);
        //合同周期内：   总月数 X 每月单价 + 每天价格 X 余多少天 = 真实的房屋租约价格
        $houseLeaseTotalPrice = $monthTotal * $order['price_unit'] + $modDays * $everyDayPrice;

        $finalPrice = $houseLeaseTotalPrice + $otherPrice;

        $data = array(
            "distanceSecond"=>$distance_second,//合同周期时间：秒
            "distanceDays"=>$days,//合同周期时间：天
            "everyDayPrice"=>$everyDayPrice,//每天多少天
            'houseLeasePrice'=>$houseLeasePrice,//房屋的总价，即：去掉 <水电等 或 首次空置> 的金额
            "houseLeaseTotalPrice"=>$houseLeaseTotalPrice,//根据合同周期内 月数 余天数 计算的  房屋应收金额
            "monthTotal"=>$monthTotal,//合同周期内：包含多少个月
            "monthModDay"=>$modDays,//合同周期内：去掉<月>，余 多少天
            'otherPrice'=>$otherPrice,
            'finalPrice'=>$finalPrice,
        );
        return $data;
    }
    function makeCalcPayListPreData($order){
        $calcPayList = array();
        if($order['category'] == OrderModel::CATE_USER){
            $pre_s_time = $order['contract_start_time'] + 1;
            $pre_e_time = $order['contract_start_time'] + 28 *  getOneDayTurnSecond() + 1;
            $advance_time = $pre_s_time + $order['advance_day'] * getOneDayTurnSecond() ;
            $distance = ($pre_e_time - $pre_s_time) /  getOneDayTurnSecond();
            $onePayList = array(
                "price"=>$order['water_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['water_price'],
                "price_desc"=>"水费",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
            $onePayList = array(
                "price"=>$order['repair_fund_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['repair_fund_price'],
                "price_desc"=>"维修基金",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
            $onePayList = array(
                "price"=>$order['elec_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['elec_price'],
                "price_desc"=>"电费",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
            $onePayList = array(
                "price"=>$order['garbage_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['garbage_price'],
                "price_desc"=>"垃圾费",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
            $onePayList = array(
                "price"=>$order['deposit_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['deposit_price'],
                "price_desc"=>"押金",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
        }else{
            $pre_s_time = $order['contract_start_time'] + 1;
            $pre_e_time = $order['contract_start_time'] + 28 *  getOneDayTurnSecond() + 1;
            $advance_time = $pre_s_time + $order['advance_day'] * getOneDayTurnSecond() ;
            $distance = ($pre_e_time - $pre_s_time) /  getOneDayTurnSecond();
            $onePayList = array(
                "price"=>$order['vacancy_price'],
                'distance'=>$distance,
                "s_time"=> $pre_s_time, 'e_time'=> $pre_e_time,
                "final_price"=>$order['vacancy_price'],
                "price_desc"=>"首次付款空置金额",
                "status"=>OrderPayListModel::STATUS_WAIT,
                'advance_time'=>$advance_time,
                'type'=>OrderModel::FINANCE_INCOME,
            );
            $calcPayList[] = $onePayList;
        }
        return $calcPayList;
    }
    //结算
    function finish(){
        $oid = _g("oid");
        if(!$oid){
            $this->notice("oid is null");
        }

        $order = OrderModel::getById($oid);
        if(!$order){
            $this->notice("oid not in db");
        }

        if($order['status'] != OrderModel::FINANCE_EXPENSE){
            $this->notice("订单状态错误，只有为：已确认生成订单支付列表 状态 ，才可以结算");
        }
        $payListByDb = OrderPayListModel::db()->getAll("oid = {$oid}");
        if(!$payListByDb){
            $this->notice("pay list is null");
        }

        $force = _g("force");
        if(!$force){
            foreach ($payListByDb as $k=>$v){
                if($payListByDb['status'] != OrderPayListModel::STATUS_OK){
                    $this->notice("有一条记录状态错误：未处理，必须是所有记录的状态为：完结，才可以正常结算");
                }
            }

            $data = array("finish_type"=>OrderModel::FINISH_NORMAL);
            $rs = OrderModel::upStatus($oid, OrderModel::STATUS_FINISH,$data);
        }else{
            //如果是房主强行 结算订单，会连动牵扯到租户的合同~
            if ($order['category'] == OrderModel::CATE_MASTER) {
                $hasUserOrder = OrderModel::db()->getRow(" house_id = {$order['house_id']} and category =  " . OrderModel::CATE_USER . " and status in ( " . OrderModel::STATUS_WAIT . " , ". OrderModel::STATUS_OK." )");
                if($hasUserOrder){
                    $this->notice("系统检测出，该房源还有未完结的<用户订单>，请先完结<用户订单>，再强行结算<房主订单>");
                }
            }

            foreach ($payListByDb as $k=>$v){
                if($payListByDb['status'] != OrderPayListModel::STATUS_OK){
                    $data = array("finish_type"=>OrderModel::FINISH_FORCE);
                    OrderPayListModel::upStatus($v['id'],OrderPayListModel::STATUS_OK,$data);
                }
            }

            $data = array("finish_type"=>OrderModel::FINISH_FORCE);
            $rs = OrderModel::upStatus($oid, OrderModel::STATUS_FINISH,$data);
        }

        //如果房主 用户 订单都完结，证明该房屋状态 要变更为：可出租状态
        if ($order['category'] == OrderModel::CATE_USER) {
            $rs = HouseModel::upStatus($order['house_id'], HouseModel::STATUS_WAIT);
        } else {
            //如果是房主：结算订单，即：该房源上的用户订单肯定已结束完成了~那：状态改为  等待招租
            $rs = HouseModel::upStatus($order['house_id'], HouseModel::STATUS_INIT);
        }
    }

    function createPayRecord(){
        $oid = _g("oid");
        if(!$oid){
            $this->notice("oid is null");
        }

        $order = OrderModel::getById($oid);
        if(!$order){
            $this->notice("oid not in db");
        }

        $calcData = $this->calcAll($order);
        $payList = $this->getCreatePayList($order,$calcData);
        foreach ($payList as $k => $v) {
            unset($v['id']);
            unset($v['start_time_dt']);
            unset($v['end_time_dt']);
            unset($v['warn_trigger_time_dt']);
            unset($v['distance_day']);
            unset($v['status_desc']);
            unset($v['category_desc']);
            unset($v['advance_time_dt']);
            unset($v['type_desc']);
            unset($v['category_desc']);

            OrderPayListModel::db()->add($v);
        }

        $rs = OrderModel::upStatus($oid, OrderModel::STATUS_OK);
        $this->ok("生成记录成功!");
    }
    //一个订单，生成-付款记录
//    function createPayRecord(){
    function detail(){
        $oid = _g("oid");
        if(!$oid){
            $this->notice("oid is null");
        }

        $order = OrderModel::getById($oid);
        if(!$order){
            $this->notice("oid not in db");
        }

        $house = HouseModel::db()->getById($order['house_id']);
        $master = MasterModel::db()->getById($house['master_id']);
        //开始计算 - 详细的支付清单列表
        $calcData = $this->calcAll($order);

        $action = _g("action");
        if (!$action){
            $this->notice("action is null");
        }

        if ($order['category'] == OrderModel::CATE_USER) {
            $user = UserModel::db()->getById($order['uid']);
        }
        if ($action == 'createPayRecord') {
            //一个订单，只能生成一次付款列表
            $orderPayListExist = OrderPayListModel::db()->getRow(" oid = {$order['id']} and category = {$order['category']}");
            if($orderPayListExist){
                $this->notice("该订单已生成过支付记录，请不要重复操作");
            }

            $payList = $this->getCreatePayList($order,$calcData);
        }else{
            $payListByDb = OrderPayListModel::db()->getAll("oid = {$oid}");
            if(!$payListByDb){
                $this->notice("pay list is null");
            }

            $payListPre = [];
            foreach ($payListByDb as $k=>$v){
                $row = $v;
                $row['s_time'] = $v['start_time'];
                $row['e_time'] = $v['end_time'];
                $row['distance'] = $v['end_time'] - $v['start_time'];
                $payListPre[] = $row;
            }

            $payList = $this->makePayListData($payListPre,$order);
        }

        $this->assign("action",$action);

        $this->assign("order",$order);
        $this->assign("house",$house);
        $this->assign("master",$master);

        $this->assign("calcData",$calcData );
        $this->assign("num",count($payList));


        $this->assign("payList",$payList);
        $this->assign("user",$user);

        $this->display("/house/order_detail.html");
    }

    function makePayListData($calcPayList,$order){
        $payList = [];
        foreach ($calcPayList as $k=>$v){
//            $warn_trigger_time = $v['e_time'] - $order['warn_trigger_time'];
            $data = array(
                'id'=>$k+1,
                "house_id"=>$order['house_id'],
                "oid"=>$order['id'],
                'type' =>$v['type'],
                'type_desc' =>OrderModel::FINANCE_DESC[$v['type']],
                'price'=>$v['price'],
                'final_price'=>$v['final_price'],
                "start_time"=>$v['s_time'],
                "end_time"=>$v['e_time'],
                "start_time_dt"=>get_default_date($v['s_time']),
                "end_time_dt"=>get_default_date($v['e_time']),
//                "warn_trigger_time_dt"=>get_default_date($warn_trigger_time),
                "price_desc"=>$v['price_desc'],
                'status'=>$v['status'],
                "distance_day"=>$v['distance'],
                'pay_third_no'=>"",
                'pay_type'=> 0,
                'pay_time'=>0,
                'a_time'=>time(),
                'advance_time'=>$v['advance_time'],
                'advance_time_dt'=>get_default_date($v['advance_time']),

//                "warn_trigger_time"=>$warn_trigger_time,
                'category'=>$order['category'],
//                'category_desc'=>OrderModel::CATE_DESC[$v['category']],
                'status_desc'=>OrderModel::STATUS_DESC[$v['status']],
            );

            $payList[] = $data;
        }
        return $payList;
    }
    function getCreatePayList($order,$calcData){
        $s_time = $order['contract_start_time'];
        $e_time = $order['contract_end_time'];//最后一天是 23:59:59，有1秒误差
//        echo "s_time:".date("Y-m-d H:i:s",$s_time)." , e_time: ".date("Y-m-d H:i:s",$e_time)." <br/>";
        $calcPayList = $this->makeCalcPayListPreData($order);
        $price_unit = $order['price_unit'];//付款周期 单价
        $isBreak = 0;
        $loopTimes = 1;
//        $totalPrice = 0;
//        $otherPrice = 0;

        //生成付款列表-记录
        while (1) {
//            $oneLoopFinalPrice = 0;
//            $oneLoopDesc = "";
//            $otherPrice = 0;
//            if ($loopTimes == 1){
//                if ($order['category'] == OrderModel::CATE_MASTER){
//                    $otherPrice = "-" . $order['vacancy_price'];
//                    $oneLoopDesc = "扣除-首月空置金额:".$order['vacancy_price'];
//                }else{
//                    $otherPrice = $order['water_price'] + $order['elec_price'] + $order['garbage_price'] + $order['property_heat_type'];
//                    $oneLoopDesc = "水费:".$order['water_price'] . "<br/>电费".$order['elec_price'] . "<br/>垃圾费".$order['garbage_price'] . "<br/>维修基金".$order['repair_fund_price'] . "<br/>";
//                }
//            }
            //每次-付款的周期，最后转换成最小单位：月
            $monthUnit = OrderModel::PAY_TYPE_TURN_MONTH[$order['pay_mode']];
            //合同开始时间 + 每次-付款的周期（N个月） = 一次付款周期的记录
            $unitTime = strtotime("+$monthUnit month", $s_time);
            //但 实际上是多计算了一天,因为是用开始时间（00:00:00） + 一个月，等于多加了一秒
            //也就是，如：开始时间 是  2020-01-02（00:00:00）    结果时间 应该是 2021-01-01 23:59:59
            $unitTime = $unitTime - 1;
            $distanceMonthUnit = 0;//一个周期的(N个月)
            $price = $monthUnit * $price_unit;
            if ($unitTime > $e_time) {
                //这种就是特殊情况 ，按说，合同 都应该是正常的周期，一天都不应该差
                //实际情况是：多出来那么几天

                //先计算出，多出来一共几天，再把这几天 * 单价
                $distanceMonthUnit = ($e_time - $s_time) / getOneDayTurnSecond();
                $unitTime = $e_time;
//                $calcPayList[] = array(
//                    'price'=>$distanceMonthUnit * $everyDayPrice,
//                );
                $price = $distanceMonthUnit * $calcData['everyDayPrice'];
                $isBreak = 1;
            } elseif ($unitTime == $e_time) {//这种是最好的情况，时间刚刚好，没有余数
                $distanceMonthUnit = ($unitTime - $s_time + 1) / getOneDayTurnSecond();
//                $calcPayList[] = array(
//                    'price'=>$monthUnit * $price_unit,
                $isBreak = 1;
            }
            $distanceMonthUnit = round($distanceMonthUnit);
            $price = round($price);
//            $oneLoopFinalPrice = $price + $otherPrice;
//            $totalPrice += $oneLoopFinalPrice;

            $advanceTime = $unitTime - $order['advance_day'] * getOneDayTurnSecond();
            if ($advanceTime <= 0) {
                $advanceTime = $unitTime;
            }
            if ($order['category'] == OrderModel::CATE_MASTER) {
                $type = OrderModel::FINANCE_EXPENSE;
            } else {
                $type = OrderModel::FINANCE_INCOME;
            }
            $row = array(
                "s_time" => $s_time,
                'e_time' => $unitTime,
                "status" => OrderPayListModel::STATUS_WAIT,
                "distance" => $distanceMonthUnit,
                "price" => $price,

                "final_price" => $price,
                "price_desc" => "房屋租金",
//                "final_price"=>$oneLoopFinalPrice,
//                "price_desc"=>$oneLoopDesc,
                'advance_time' => $advanceTime,
                'type' => $type,
            );


            if ($isBreak) {
                $calcPayList[] = $row;
                break;
            }

            $distanceMonthUnit = ($unitTime - $s_time + 1) / getOneDayTurnSecond();
            $row['distance'] = $distanceMonthUnit;
            $s_time = $unitTime + 1;

            $calcPayList[] = $row;
            $loopTimes++;
        }

        //如果是<用户>，还得生成  -  押金付款记录
        if ($order['category'] == OrderModel::CATE_USER) {
//            $user = UserModel::db()->getById($order['uid']);
//            if(arrKeyIssetAndExist($order,'deposit_price')){
            $s_time = $order['contract_end_time'] + 1;
            $e_time = $order['contract_end_time'] + 28 * getOneDayTurnSecond() + 1;
            $advance_time = $s_time + $order['advance_day'] * getOneDayTurnSecond();
            $distance = ($e_time - $s_time) / getOneDayTurnSecond();
            $depositPayList = array(
                'distance' => $distance,
                "price" => $order['deposit_price'], 'distance' => $distance,
                "s_time" => $s_time, 'e_time' => $e_time,
                "final_price" => $order['deposit_price'],
                "price_desc" => "押金",
                "status" => OrderPayListModel::STATUS_WAIT,
                'advance_time' => $advance_time,
                'type' => OrderModel::FINANCE_EXPENSE,
            );
            $calcPayList[] = $depositPayList;
//                $tmpOrder = $order;
//                $tmpOrder['type'] = OrderModel::FINANCE_EXPENSE;
//                $depositPayList = $this->makePayListData(array($depositPayList),$tmpOrder);
//                $payList[] = $depositPayList[0];
//            }
        }
        $payList = $this->makePayListData($calcPayList, $order);
        return $payList;
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
                'time_cycle'=>OrderModel::Time_Cycle_IN,
                'uid'=>0,
                'saler_id'  => _g("saler_id"),
                "contract_no"=>_g('contract_no'),//合同编号
                'contract_start_time'=> strtotime( _g('contract_start_time')),//合同开始时间,unix time
                'contract_end_time'=> strtotime( _g('contract_end_time')),//合同结束时间,unix time
                'price'=>_g('price'),//合同金额
                "pay_mode"=>_g('pay_mode'),//付款类型
                'price_unit'=>_g("price_unit"),//月付金额
                'admin_id'=>$this->_adminid,
                'a_time'=>time(),
                "warn_trigger_time"=>_g('warn_trigger_time',0),//报警-提醒时间
                "status"=>OrderModel::STATUS_WAIT,
                "advance_day"=>_g("advance_day"),//提前提醒天数
                'category'=>_g('category'),//房主/用户
                'type'=>_g('type'),
                //用户相关
                'deposit_price'=>_g('deposit_price',0),//押金
                'water_price'=>_g('water_price',0),//水费
                'elec_price'=>_g('elec_price',0),//电费
                'garbage_price'=>_g('garbage_price',0),//垃圾费
                'repair_fund_price'=>_g('repair_fund_price',0),//维修基金
                //房主相关
                "master_breach_price"=>_g("master_breach_price",0),//违约金
                'vacancy_price'=>_g('vacancy_price',0),//首月空置钱
//                'time_cycle'=>_g('time_cycle',0),//内置/外置
                'property_heat_type'=>_g('property_heat_type',0),//物业取暖
            );

            if($house['status'] != HouseModel::STATUS_WAIT){
                $this->notice("该房源状态为：".HouseModel::STATUS[HouseModel::STATUS_USED].",不能添加订单");
            }

            $existOrder = OrderModel::db()->getRow(" house_id = {$hid} and category = {$data['category']} and status =  ".OrderModel::STATUS_WAIT);
            if($existOrder){
                $msg = "该房源，已存在<".OrderModel::CATE_DESC[$data['category']].">订单，不要重复添加，您可删除无用数据，再来操作!";
                $this->notice($msg);
            }

            if(!$data['saler_id']){
                $this->notice("业务员ID不能为空");
            }

            $saler = AdminUserModel::db()->getById($data['saler_id']);
            if(!$saler){
                $this->notice("业务员ID错误，不存在DB中");
            }

            if(!$data['type'] || $data['type'] < 0){
                $this->notice("类型，不能为空");
            }

            if($data['type'] != OrderModel::TYPE_TENANCY){
                $this->notice("抱歉：目前仅支持 <租赁>模式");
            }

            if(!$data['category'] ){
                $this->notice("用户类型不能为空!");
            }

            $data['price'] = (int)$data['price'];
            if(! $data['price'] || $data['price'] <= 0){
                $this->notice("合同金额，不能为空且 必须为正整数");
            }

            if(!$data['contract_start_time'] ){
                $this->notice("合同开始时间 不能为空");
            }

            if(!$data['advance_day'] || $data['advance_day'] < 0){
                $this->notice("每次付款，提前天数，不能为空");
            }

            if(!$data['contract_end_time'] ){
                $this->notice("合同结束时间 不能为空");
            }

            if(!$data['contract_no'] ){
                $this->notice("合同编号 不能为空");
            }

            $contractNoExist = OrderModel::db()->getRow(" contract_no = '{$data['contract_no']}' ");
            if ($contractNoExist){
                $this->notice("合同编号,重复!!!");
            }

            if($data['contract_start_time'] >= $data['contract_end_time']){
                $this->notice("合同开始时间不能 >= 结束时间");
            }
            //默认 选择日期 :只有日期，没有时间 ，但是合同的时间得有：具体时间，以用户选择的：结束日期 +  23:59:59 秒
            $data['contract_end_time'] += getOneDayTurnSecond() - 1;
            $this->checkContractTimePeriodPay($data['contract_start_time'],$data['contract_end_time'] , $data['pay_mode']);

            if(!is_numeric($data['pay_mode'])){
                $this->notice("付款方式不能为空");
            }

            if(!$data['price_unit'] ){
                $this->notice("月付金额 不能为空");
            }

            if($data['price_unit'] > $data['price']){
                $this->notice("月付金额 不能大于 合同 总金额");
            }

            if($data['category'] == OrderModel::CATE_USER){//租户
                $data['deposit_price'] = (int)$data['deposit_price'];
                if(! $data['deposit_price'] || $data['deposit_price'] <= 0){
                    $this->notice("押金，不能为空且 必须为正整数");
                }

                if(!arrKeyIssetAndExist($data,'water_price')){
                    $this->notice("水费不能为空");
                }

                if(!arrKeyIssetAndExist($data,'elec_price')){
                    $this->notice("电费不能为空");
                }

                if(!arrKeyIssetAndExist($data,'garbage_price')){
                    $this->notice("垃圾费不能为空");
                }

                if(!arrKeyIssetAndExist($data,'repair_fund_price')){
                    $this->notice("维修基金不能为空");
                }

                $userData = array(
                    "name"=>_g("uname"),
                    "mobile"=>_g("user_mobile"),
                    'a_time'=>time(),
                );

                if(!$userData){
                    $this->notice("用户名不能为空");
                }

                if(!$userData){
                    $this->notice("用户手机号不能为空");
                }

                if(!FilterLib::regex($userData['mobile'],"phone")){
                    $this->notice("用户-手机号格式错误 ");
                }

                if($house['status'] != OrderModel::STATUS_WAIT){
                    $houseStatusMsg  ="房源状态错误，必须得先：创建<房主>订单，且状态为：已确认，且已经生成了支付列表，且，房源状态变更为:".OrderModel::STATUS_DESC[OrderModel::STATUS_WAIT];
                    $houseStatusMsg .="才能再有<租房订单>,因为生成用户订单时才会变更房源状态!!!";

                    $this->notice();
                }

                $existMasterOrder = OrderModel::db()->getRow(" house_id = {$hid} and category = ".OrderModel::CATE_MASTER ." and status =  ".OrderModel::STATUS_OK);
                if(!$existMasterOrder){
                    $this->notice("房源必须得先：创建<房主>订单，且状态为：已确认，且已经生成了支付列表，才能再有<租房订单>,因为生成用户订单时才会变更房源状态!!!");
                }
                //租户,这里要特殊处理下，因为得减掉押金
                $totalPrice = $data['price'];
                $totalPrice = $totalPrice - $data['deposit_price'];
                if($data['price_unit'] > $totalPrice){
                    $this->notice("年/半年/季/月付单元金额 不能大于 合同 总金额(除掉押金){$data['price_unit']}>{$totalPrice}");
                }
                if($data['contract_end_time'] > $existMasterOrder['contract_end_time']){
                    $msg = "与房主签订的合同，结果时间为:".date("Y-m-d H:i:s",$existMasterOrder['contract_end_time']) .",租房的合同：结束时间不能大于这个";
                    $this->notice($msg);
                }
            }else{
                $data['uid'] = 0;
                if(!arrKeyIssetAndExist($data,'vacancy_price')){
                    $this->notice("首次付款空置费 不能为空");
                }

                if(!arrKeyIssetAndExist($data,'property_heat_type')){
                    $this->notice("物业/取暖费类型 不能为空");
                }

            }

            $calcData = $this->calcAll($data);
            $showMsg = "合同总金额:".$data['price']."<Br/>";
            $showMsg .="合同周期，开始时间:".date("Y-m-d H:i:s",$data['contract_start_time']) .", 结束时间:".date("Y-m-d H:i:s",$data['contract_end_time']);
            $showMsg .= "合同周期共计:".$calcData['distanceDays'] . "天，周期内总月份：".$calcData['monthTotal'] . "个月,余:". $calcData['monthModDay']."天.<br/>";
            $showMsg .= "合同总金额-其它金额 =  {$data['price']}-{$calcData['otherPrice']}={$calcData['houseLeasePrice']}.<br/>";
            $showMsg .= "最终应收付金额:{$calcData['finalPrice']}<br/>";
            $monthPrice = $calcData['monthTotal'] * $data['price_unit'];
            $showMsg .= "每天价格=".$calcData['everyDayPrice'] .",{$calcData['monthTotal']} * {$data['price_unit']}=".$monthPrice . " + {$calcData['monthModDay']} * {$calcData['everyDayPrice']} = {$calcData['houseLeaseTotalPrice']}";
            if($calcData['finalPrice'] != $data['price']){
                $this->notice("合同金额计算错误<br/>$showMsg");
            }

            //合同附件
//            contract_attachment
            if (isset($_FILES['contract_attachment']['size']) && $_FILES['contract_attachment']['size']){
                $uploadService = new UploadService();
                $uploadRs = $uploadService->contract('contract_attachment');
                if($uploadRs['code'] != 200){
                    exit(" uploadService->contract_attachment error ".json_encode($uploadRs));
                }
                $data['contract_attachment'] = $uploadRs['msg'];
            }

            if($data['category'] == OrderModel::CATE_USER) {//租户
                $newUserId = UserModel::db()->add($userData);
                $data["uid"] = $newUserId;

                //变更房源状态为：出租中
                $houseData = array("uid"=>$newUserId);
                HouseModel::upStatus($hid,HouseModel::STATUS_USED,$houseData);
            }else{
                //变更房源状态为：等待出租
                $houseData = array("status"=>HouseModel::STATUS_WAIT,'u_time'=>time());
                HouseModel::db()->upById($hid,$houseData);
            }

            $newId = OrderModel::db()->add($data);
            $this->ok($newId . "<br/>".$showMsg,$this->_backListUrl);
        }

        $this->assign("house",$house);

        $this->assign("getPayTypeOptions",OrderModel::getPayTypeOptions());
        $this->assign("getTypeOptions",OrderModel::getTypeOptions());
        $this->assign("getCateTypeOptions",OrderModel::getCateTypeOptions());

        $this->addHookJS("/house/order_add_hook.html");
        $this->display("/house/order_add.html");
    }

    function checkContractTimePeriodPay($s_time,$e_time,$payType){
        $distance = $e_time - $s_time + 1;//最后一天是 23:59:59，有1秒误差
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
//        $contractDistance = $data['contract_end_time'] - $data['contract_start_time'];
        $contractDistanceDay = $distance /  getOneDayTurnSecond();
        if ( $contractDistanceDay <= 31 ){
            $this->notice("合租周期不能小于31天");
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
                $adminUserName = AdminUserModel::getFieldById( $v['admin_id'],'nickname');
                //附件
                $contract_attachment = "";
                if($v['contract_attachment']){
                    $url = get_contract_url($v['contract_attachment']);
                    $contract_attachment = "<a href='$url' target='_blank'>".'<i class="fa fa-file-text"></i></a>';
                }

                $delBnt= "";
                $createPayListBnt = "";
                $finishBnt = "";
                if($v['status'] == OrderModel::STATUS_WAIT){
//                    $createPayListBnt = '<a href="/house/no/order/createPayRecord/oid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 生成收/付款 </a>';
                    $createPayListBnt = '<a href="/house/no/order/detail/action=createPayRecord&oid='.$v['id'].'" class="btn purple btn-xs btn blue btn-xs margin-bottom-5"><i class="fa fa-plus"></i>   </i> 生成收/付款 </a>';
                    $delBnt = '<a  class="btn red btn-xs margin-bottom-5 delone"  data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i>删除 </a>';
                }
                if($v['status'] == OrderModel::STATUS_OK){
                    $finishBnt = '<a href="/house/no/order/detail/action=finish&oid='.$v['id'].'" class="btn blue btn-xs margin-bottom-5 delone"  data-id="'.$v['id'].'"><i class="fa fa-file-text-o"></i> 结算 </a>';
//                    $finishBnt = '<a href="/house/no/order/finish/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5 delone"  data-id="'.$v['id'].'"><i class="fa fa-file-text-o"></i> 结算 </a>';
                }
                $hasPay = 0;
                if($v['status'] == OrderModel::STATUS_OK || $v['status'] == OrderModel::STATUS_FINISH){
                    $hasPay = OrderModel::totalPayListByTypeStatus($v['id'],OrderPayListModel::STATUS_OK);
                    $hasPay = $hasPay['total'];
                    if(!$hasPay){
                        $hasPay = 0;
                    }
                }

                $finishTypeDesc= "---";
                if($v['finish_type']){
                    $finishTypeDesc = OrderModel::FINISH_DESC[$v['finish_type']];
                }

                $userName = "无";
                if($v['uid']){
                    $user = UserModel::db()->getById($v['uid']);
                    if($user){
                        $userName = $user['name']."-".$v['uid'];
                    }
                }

                if($v['category'] == OrderModel::CATE_MASTER){
                    $otherPrice = "首月空置:".$v['vacancy_price'];
                }else{
                    $otherPrice = "水费:".$v['water_price']."<br/>"."电费:".$v['elec_price']."<br/>"."垃圾费:".$v['garbage_price']."<br/>"."维修基金:".$v['repair_fund_price']."<br/>"."押金:".$v['deposit_price'];
                }
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['house_id'],
                    OrderModel::TYPE_DESC[$v['type']],
                    OrderModel::STATUS_DESC[$v['status']],
                    $finishTypeDesc,
                    $v['price'],
                    $v['price_unit'],
                    $v['advance_day'],
                    $hasPay,
                    OrderModel::PAY_TYPE_DESC[$v['pay_mode']],
                    OrderModel::CATE_DESC[$v['category']],
                    $userName,
                    $otherPrice,
                    get_default_date($v['contract_start_time']),
                    get_default_date($v['contract_end_time']),
                    get_default_date($v['a_time']),
                    $adminUserName,
                    $createPayListBnt . $delBnt . " ".$finishBnt,
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