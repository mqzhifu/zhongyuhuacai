<?php

class StatisticsCtrl extends BaseCtrl 
{

    function index()
    {
        $this->checkGame();
        // $gameid = _g("gameid");

        // $gameVisitType = isset($_POST['gameVisitType']) ? _g("gameVisitType") : 'active';

        // //昨日概况
        // $yesterdayData = $this->countsGamesService->getYesterdayData($gameid);
        // $this->assign("yesterdayData", $yesterdayData);

        // //近30天访问趋势
        // $gameVisitData["data"] = [];
        // $gameVisitData = $this->countsGamesService->getGameVisitData($gameid, $gameVisitType);
        // $this->assign("gameVisitData", $gameVisitData["data"]);

        // //近30天收入趋势
        // $adIncomeData = $this->advertiseService->income($gameid);
        // $this->assign("adIncomeData", $adIncomeData);
        $this->assign("visitSelection30", PurchaseCntDayModel::get30VisitSelection());
        $this->assign("incomeSelection30", PurchaseCntDayModel::get30IncomeSelection());

        // 访问分析
        $this->assign("visitLineDesc", PurchaseCntDayModel::getVisitLineDesc());
        $this->addJs('assets/open/scripts/echarts.min.js');
        $this->addJs('assets/open/scripts/laydate/laydate.js');
        $this->addJs('assets/open/scripts/canvas.js');
        $this->addJs('assets/open/scripts/map/china.js');
        $this->display('game/gameStatistics.html', 'new', 'isLogin');
    }

    // 昨日概况
    public function getYesterdaySummary(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        // 累计注册用户数
        // xyx_cnt_hour_2019xxx
        $yesterdayTime = strtotime("yesterday");
        $oneDayBeforeYesterdayTime = $yesterdayTime - 86400;
        $oneWeekbeforeYesterdayTime = $yesterdayTime - 86400*7;
        $thirtyDaybeforeYesterdayTime = $yesterdayTime - 86400*30;

        $launchTime = strtotime("2019-05-01");
        $res1 = [];
        $res2 = [];
        $res3 = [];
        $res4 = [];

        $acuPeople1 = 0;
        $acuPeople2 = 0;
        $acuPeople3 = 0;
        $acuPeople4 = 0; 
        if($yesterdayTime >= $launchTime){
            $res1 = XYXCntByDayModel::getDataByGameIdAndDay($game_id, $yesterdayTime) ;
            $d1 = $yesterdayTime + 86399;
            $acuPeople1 = PlayedGameUserModel::db()->getCount("game_id=$game_id and a_time <=$d1");
            // $res1 = XYXCntByHourModel::getCountDataByGameIdAndDay($game_id, $yesterdayTime);
        }
        if($oneDayBeforeYesterdayTime >= $launchTime){
            $res2 = XYXCntByDayModel::getDataByGameIdAndDay($game_id, $oneDayBeforeYesterdayTime);
            $d2 = $oneDayBeforeYesterdayTime + 86399;
            $acuPeople2 = PlayedGameUserModel::db()->getCount("game_id=$game_id and a_time <=$d2");
            // $res2 = XYXCntByHourModel::getCountDataByGameIdAndDay($game_id, $oneDayBeforeYesterdayTime);
        }
        if($oneWeekbeforeYesterdayTime >= $launchTime){
            $res3 = XYXCntByDayModel::getDataByGameIdAndDay($game_id, $oneWeekbeforeYesterdayTime);
            $d3 = $oneWeekbeforeYesterdayTime + 86399;
            $acuPeople3 = PlayedGameUserModel::db()->getCount("game_id=$game_id and a_time <=$d3");
            // $res3 = XYXCntByHourModel::getCountDataByGameIdAndDay($game_id, $oneWeekbeforeYesterdayTime);
        }
        if($thirtyDaybeforeYesterdayTime >= $launchTime){
            $res4 = XYXCntByDayModel::getDataByGameIdAndDay($game_id, $thirtyDaybeforeYesterdayTime);
            $d4 = $thirtyDaybeforeYesterdayTime + 86399;
            $acuPeople4 = PlayedGameUserModel::db()->getCount("game_id=$game_id and a_time <=$d4");
            // $res4 = XYXCntByHourModel::getCountDataByGameIdAndDay($game_id, $thirtyDaybeforeYesterdayTime);
        }
        $returnData = [[0,'0.00','0.00','0.00'],[0,'0.00','0.00','0.00'],[0,'0.00','0.00','0.00'],[0,'0.00','0.00','0.00']];

        $unum1 = 0;
        $unum2 = 0;
        $unum3 = 0;
        $unum4 = 0;
        $num1 = 0;
        $num2 = 0;
        $num3 = 0;
        $num4 = 0;
        $time1 = 0;
        $time2 = 0;
        $time3 = 0;
        $time4 = 0;
        $returnData[0][0] = $acuPeople1==0 ? 0 : $acuPeople1;
        $returnData[0][1] = number_format(($acuPeople2==0 ? ($acuPeople1==0 ? 0 : 1) : ($acuPeople1-$acuPeople2)/$acuPeople2)*100, 2);
        $returnData[0][2] = number_format(($acuPeople3==0 ? ($acuPeople2==0 ? 0 : 1) : ($acuPeople2-$acuPeople3)/$acuPeople3)*100, 2);
        $returnData[0][3] = number_format(($acuPeople4==0 ? ($acuPeople3==0 ? 0 : 1) : ($acuPeople3-$acuPeople4)/$acuPeople4)*100, 2);
        if($res1){
            $unum1 = isset($res1['active_user_num']) ? $res1['active_user_num'] : 0;
            $num1 = isset($res1['new_reg_user']) ? $res1['new_reg_user'] : 0;
            $time1 = $res1['active_user_num']==0 ? 0 : $res1['total_time']/$res1['active_user_num'];
            $returnData[1][0] = $unum1;
            $returnData[2][0] = $num1;
            $returnData[3][0] = $time1;
        }
        if($res2){
            $unum2 = $res2['active_user_num'];
            $num2 = $res2['new_reg_user'];
            $time2 = $res2['active_user_num']==0 ? 0 : $res2['total_time']/$res2['active_user_num'];
            $returnData[1][1] = number_format(($unum2==0 ? ($unum1==0 ? 0 : 1) : ($unum1-$unum2)/$unum2)*100, 2);
            $returnData[2][1] = number_format(($num2==0 ? ($num1==0 ? 0 : 1) : ($num1-$num2)/$num2)*100, 2);
            $returnData[3][1] = number_format(($time2==0 ? ($time1==0 ? 0 : 1) : ($time1-$time2)/$time2)*100, 2);
        }
        if($res3){
            $unum3 = $res3['active_user_num'];
            $num3 = $res3['new_reg_user'];
            $time3 = $res3['active_user_num']==0 ? 0 : $res3['total_time']/$res3['active_user_num'];
            $returnData[1][2] = number_format(($unum3==0 ? ($unum2==0 ? 0 : 1) : ($unum2-$unum3)/$unum3)*100, 2);
            $returnData[2][2] = number_format(($num3==0 ? ($num2==0 ? 0 : 1) : ($num2-$num3)/$num3)*100, 2);
            $returnData[3][2] = number_format(($time3==0 ? ($time2==0 ? 0 : 1) : ($time2-$time3)/$time3)*100, 2);
        }
        if($res4){
            $unum4 = $res4['active_user_num'];
            $num4 = $res4['new_reg_user'];
            $time4 = $res4['active_user_num']==0 ? 0 : $res4['total_time']/$res4['active_user_num'];
            $returnData[1][3] = number_format(($unum4==0 ? ($unum3==0 ? 0 : 1) : ($unum3-$unum4)/$unum4)*100, 2);
            $returnData[2][3] = number_format(($num4==0 ? ($num3==0 ? 0 : 1) : ($num3-$num4)/$num4)*100, 2);
            $returnData[3][3] = number_format(($time4==0 ? ($time3==0 ? 0 : 1) : ($time3-$time4)/$time4)*100, 2);
        }
        // 获取累计注册用户数
        // 活跃用户数
        // $unum1 = $res1['active_user_num'];
        // $unum2 = $res2['active_user_num'];
        // $unum3 = $res3['active_user_num'];
        // $unum4 = $res4['active_user_num'];

        // $returnData[1] = [
        //     $unum1,
        //     $unum1==0 ? 1 : $unum2/$unum1,
        //     $unum2==0 ? 1 : $unum3/$unum2,
        //     $unum3==0 ? 1 : $unum4/$unum3,
        // ];
        // 新增注册用户数
        // $num1 = $res1['new_reg_user'];
        // $num2 = $res2['new_reg_user'];
        // $num3 = $res3['new_reg_user'];
        // $num4 = $res4['new_reg_user'];
        // $returnData[2] = [
        //     $num1,
        //     $num1==0 ? 1 : $num2/$num1,
        //     $num2==0 ? 1 : $num3/$num2,
        //     $num3==0 ? 1 : $num4/$num3,
        // ];
        // 人均停留时长
        // $time1 = $res1['total_time']/$res1['active_user_num'];
        // $time2 = $res2['total_time']/$res2['active_user_num'];
        // $time3 = $res3['total_time']/$res3['active_user_num'];
        // $time4 = $res4['total_time']/$res4['active_user_num'];
        // $returnData[3] = [
        //     $time1,
        //     $time1==0 ? 1 : $time2/$time1,
        //     $time2==0 ? 1 : $time3/$time2,
        //     $time3==0 ? 1 : $time4/$time3,
        // ];

        $this->outputJson(200, 'succ', $returnData);

    }

    // 概况 近30天访问趋势
    public function getRecentThirtyDayVisitData(){
        $type = _g("type");
        $info = $this->checkGame();
        $game_id = $info['id'];

        $nowTime = time();
        $endTime = strtotime(date("Y-m-d", $nowTime-86400))+86399;
        $startTime = strtotime(date("Y-m-d", $nowTime-86400))-86400*30;

        $this->filtDate($startTime, $endTime);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $startTime), date("Y-m-d", $endTime));
        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case PurchaseCntDayModel::$type_active_user_num :// 活跃用户数
                $sql = "select a_time,active_user_num from xyx_cnt_day where a_time between $startTime and $endTime and game_id=$game_id ";
                $data = XYXCntByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['active_user_num'];
                }
                break;
            case PurchaseCntDayModel::$type_new_reg_user_num :// 新增注册用户数
                $sql = "select a_time,new_reg_user from xyx_cnt_day where a_time between $startTime and $endTime and game_id=$game_id ";
                $data = XYXCntByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['new_reg_user'];
                }
                break;
            case PurchaseCntDayModel::$type_pay_user_num :// 付费用户数
                $sql = "select a_time,pay_users from purchase_cnt_day where a_time between $startTime and $endTime and game_id=$game_id ";
                $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['pay_users'];
                }
                break;
            case PurchaseCntDayModel::$type_first_pay_user_num :// 首次付费用户数
                $sql = "select a_time,first_pay_users from purchase_cnt_day where a_time between $startTime and $endTime and game_id=$game_id ";
                $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['first_pay_users'];
                }
                break;
            default:
                # code...
                break;
        }
        $line = new LineObj();
        $line->name = PurchaseCntDayModel::get30VisitSelection()[$type];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);


    }

    // 概况 近30天收入趋势
    public function getRecentThirtyDayIncomeData(){
        $type = _g("type");
        $info = $this->checkGame();
        $game_id = $info['id'];

        $nowTime = time();
        $endTime = strtotime(date("Y-m-d", $nowTime-86400))+86399;
        $startTime = strtotime(date("Y-m-d", $nowTime-86400))-86400*30;

        $this->filtDate($startTime, $endTime);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $startTime), date("Y-m-d", $endTime));

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case PurchaseCntDayModel::$type_single_day_income :// 当日总收入
                $sql = "select money from purchase_cnt_day where a_time between $startTime and $endTime and game_id=$game_id ";
                $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['money'];
                }
                break;
            case PurchaseCntDayModel::$type_single_day_android_income :// 单日安卓收入
                $sql = "select money from purchase_cnt_day where a_time between $startTime and $endTime and game_id=$game_id and os_type='android'";
                $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['money'];
                }
                break;
            case PurchaseCntDayModel::$type_single_day_ios_income :// 单日ios收入
                $sql = "select money from purchase_cnt_day where a_time between $startTime and $endTime and game_id=$game_id and os_type='ios'";
                $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['money'];
                }
                break;
            default:
                break;
        }

        $line = new LineObj();
        $line->name = PurchaseCntDayModel::get30IncomeSelection()[$type];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);
    }

    // 实时统计
    public function realtime(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $dateType = _g("dateType");
        $from = _g("from");// 需要进行合法验证

        $to = _g("to");
        
        $granularity = _g("granularity");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d H:00",$nowTime));
        switch ($dateType) {
            case 1 :
                $to = $nowTime - 1;
                $from = $nowTime - 86400;
                break;
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(1,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $res = PlayedGamesModel::db()->getAllBySQL("select a_time from played_games where ( a_time between $from and $to ) and game_id=$game_id ");
        switch ($granularity) {
            case 1:// 小时粒度
                $hourArr = $adService->getAllHour($from,  $to);
                $map = [];
                foreach ($hourArr as $value) {
                    $map[$value] = 0;
                }
                foreach ($res as $v) {
                    $map[date("Y-m-d H:00", $v['a_time'])]++;
                }
                break;
            case 2:// 五分钟粒度
                $fiveMinuteArr = $adService->getAllFiveMinute($from,  $to);
                $map = [];
                foreach ($fiveMinuteArr as $value) {
                    $map[$value] = 0;
                }
                foreach ($res as $v) {
                    $map[date("Y-m-d H:i", $v['a_time'])]++;
                }
                break;
            default:
                $this->outputJson(2, '未知粒度类型');
                break;
        }
        
        $line = new LineObj();
        $line->name = "";
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);

    }

    // 访问分析 折线数据
    public function getVisitLineData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $type = _g("type");
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d",$nowTime));
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));

        $xyxRes = XYXCntByDayModel::db()->getAll(" a_time between $from and $to and game_id=$game_id");

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case PurchaseCntDayModel::$type_accumulative_reg_num :// 累计注册用户数
                // 历史新注册人数
                $historyNewRegNum = PlayedGameUserModel::db()->getCount(" a_time<$from and game_id=$game_id ");
                foreach ($xyxRes as $v) {
                    // $historyNewRegNum += $v['new_reg_user'];
                    $map[date("Y-m-d", $v['a_time'])] += $v['new_reg_user'];
                }
                foreach ($map as $key => $value) {
                    $historyNewRegNum += $value;
                    $map[$key] = $historyNewRegNum;
                }
                break;
            case PurchaseCntDayModel::$type_active_user_num :// 活跃用户数
                foreach ($xyxRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['active_user_num'];
                }
                break;
            case PurchaseCntDayModel::$type_visit_times :// 访问次数
                foreach ($xyxRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['click_num'];
                }
                break;
            case PurchaseCntDayModel::$type_new_reg_user_num :// 新增注册用户数
                foreach ($xyxRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['new_reg_user'];
                }
                break;
            case PurchaseCntDayModel::$type_avg_play_time :// 人均停留时长
                foreach ($xyxRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['active_user_num']==0 ? number_format(0,2) :number_format($v['total_time']/$v['active_user_num'],2);
                }
                break;
            case PurchaseCntDayModel::$type_share_times :// 分享次数
                $shareRes = ShareGameCntModel::db()->getAll(" a_time between $from and $to and game_id=$game_id");
                foreach ($shareRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['num'];
                }
                break;
            case PurchaseCntDayModel::$type_share_user_num :// 分享用户数
                $shareRes = ShareGameCntModel::db()->getAll(" a_time between $from and $to and game_id=$game_id");
                foreach ($shareRes as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['people'];
                }
                break;
            default:
                # code...
                break;
        }

        $line = new LineObj();
        $line->name = PurchaseCntDayModel::getVisitLineDesc()[$type];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);
    }

    // 访问分析 表格数据
    public function getVisitTableData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d",$nowTime));
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                 if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));

        // 历史新注册人数
        $historyNewRegNum = PlayedGameUserModel::db()->getCount(" a_time<$from and game_id=$game_id ");


        $xyxRes = XYXCntByDayModel::db()->getAll(" a_time between $from and $to and game_id=$game_id");
        $shareRes = ShareGameCntModel::db()->getAll(" a_time between $from and $to and game_id=$game_id");

        $returnData = [];

        foreach ($dayArr as $day) {
            $accumulate_user_num = 0;
            $active_user_num = 0;
            $click_num = 0;
            $new_reg_user = 0;
            $avg_time = 0;
            $share_num = 0;
            $share_user_num = 0;
            foreach ($xyxRes as $v1) {
                if(date("Y-m-d", $v1['a_time']) == $day){
                    $active_user_num = $v1['active_user_num'];
                    $click_num = $v1['click_num'];
                    $new_reg_user = $v1['new_reg_user'];
                    $avg_time = $v1['active_user_num']==0 ? number_format(0,2) :number_format($v1['total_time']/$v1['active_user_num'],2);
                    $historyNewRegNum += $v1['new_reg_user'];
                    $accumulate_user_num = $historyNewRegNum;
                }
            }

            foreach ($shareRes as $v2) {
                if(date("Y-m-d", $v2['a_time']) == $day){
                    $share_num = $v2['num'];
                    $share_user_num = $v2['people'];
                }
                
            }

            $returnData[] = [
                $day,
                $accumulate_user_num,
                $active_user_num,
                $click_num,
                $new_reg_user,
                $avg_time,
                $share_num,
                $share_user_num
            ];
        }
        $this->outputJson(200, 'succ', $returnData);

    }

    // 访问分析 饼状图
    public function getVisitPieData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d",$nowTime));
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400+86399;
                $from = $nowTime - 86400*30;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400+86399;
                $from = $nowTime - 86400*7;
                break;
            case 4:// 昨天
                $to = $nowTime - 86400+86399;
                $from = $nowTime - 86400;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        // $adService = new AdvertiseService();
        // $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        // $map = [];
        // foreach ($dayArr as $value) {
        //     $map[$value] = 0;
        // }

        // played_game_user ,要不要聚合
        // $sql = "select b.src from (select uid from played_game_user where a_time between $from and $to and game_id=$game_id) a left join played_games b on a.uid=b.uid ";
        $sql = "select src from played_game_user where a_time between $from and $to and game_id=$game_id";
        $data = PlayedGameUserModel::db()->getAllBySQL($sql);
        $map = [];
        foreach ($data as $v) {
            if(!isset($map[$v['src']])){
                $map[$v['src']] = 1;
            }else{
                $map[$v['src']]++;
            }
        }
        $srcDesc = PlayedGamesModel::getSrcDesc();
        $returnData = [];
        foreach ($map as $key => $value) {
            $pieObj = new PieObj();
            $pieObj->name = $srcDesc[$key];
            $pieObj->value = $value;
            $returnData[] = $pieObj;
        }

        $this->outputJson(200, 'succ', $returnData);
    }

    // 访问分析 新增用户留存折线
    public function getVisitRetentionLineData(){
        $type = _g("type");
        $info = $this->checkGame();
        $game_id = $info['id'];
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d",$nowTime));
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));

        $data = RetentionCntByDayModel::db()->getAll(" day_time between $from and $to and game_id=$game_id ");

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case 1:
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['retention_rate'];
                }
                break;
            case 3:
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['retention3_rate'];
                }
                break;
            case 7:
                foreach ($data as $v) {
                    $map[date("Y-m-d", $v['a_time'])] += $v['retention7_rate'];
                }
                break;
            default:
                # code...
                break;
        }

        $line = new LineObj();
        $line->name = "111";
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);
    }
    // 访问分析 新增用户留存表格
    public function getVisitRetentionTableData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        $nowTime = strtotime(date("Y-m-d",$nowTime));
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2+86399;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayTimeArr = $adService->getAllDayTime($from, $to);
        $dayTimeArr = array_reverse($dayTimeArr);
        $data = RetentionCntByDayModel::db()->getAll(" day_time between $from and $to and game_id=$game_id ");

        $map = [];
        foreach ($dayTimeArr as $time) {
            $map[$time] = [date("Y-m-d", $time), "0.00", "0.00", "0.00"];
        }
        
        foreach ($data as $v) {
            $map[$v['day_time']] = [
                date("Y-m-d", $v['day_time']),
                $v['retention_rate'],
                $v['retention3_rate'],
                $v['retention7_rate']
            ];
        }

        $returnData = [];
        $returnData = array_values($map);
        $this->outputJson(200, 'succ', $returnData);

    }

    // 收入分析折线图数据 有缺陷ostype
    public function getPurchaseLineData(){
        $info = $this->checkGame();
        $type = _g("type");
        $dateType = _g("dateType");
        $osType = _g("osType");
        $from = _g("from");
        $to = _g("to");
        $game_id = $info['id'];

        $nowTime = time();
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d",$from), date("Y-m-d", $to));

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case PurchaseCntDayModel::$type_single_day_income :
                $res = PurchaseCntDayModel::getSingleDayIncome($game_id, $from, $to, $osType);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['money'];
                }
                break;
            case PurchaseCntDayModel::$type_accumulative_income :
                $sql = "select a_time,money from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['money'];
                }

                $sql2 = "select sum(money) as money from purchase_cnt_day where a_time<$from and game_id=$game_id and os_type=$osType ";
                $row = PurchaseCntDayModel::db()->getRowBySQL($sql2);
                $history = $row['money'];
                foreach ($map as $key => $value) {
                    $history += $value;
                    $map[$key] = $history;
                }
                break;
            case PurchaseCntDayModel::$type_pay_user_num :
                $sql = "select pay_users from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['pay_users'];
                }
                break;
            case PurchaseCntDayModel::$type_first_pay_user_num :
                $sql = "select first_pay_users from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['first_pay_users'];
                }
                break;
            case PurchaseCntDayModel::$type_permeate_rate :// 渗透率
                $sql = "select pay_users from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['pay_users'];
                }
                // 小游戏日活跃与用户
                $sql2 = "select android_user_num,ios_user_num from xyx_cnt_day where a_time between $from and $to and game_id=$game_id ";
                $res2 = XYXCntByDayModel::db()->getAllBySQL($sql2);

                $str = "";
                if($osType == "ios"){
                    $str = "android_user_num";
                }else if($osType == "andriod"){
                    $str = "ios_user_num";
                }
                foreach ($res2 as $v2) {
                    $map[date("Y-m-d", $v2['a_time'])] = isset($v2[$str])&&$v2[$str]!=0 ? $map[date("Y-m-d", $v2['a_time'])]/$v2[$str] : 0;
                }
                // 查询每天下游戏活跃用户数 无法区分android和ios
                break;
            case PurchaseCntDayModel::$type_avg_pay_user_income :
                $sql = "select money,pay_users from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['money']==0 ? 0 : $v['pay_users']/$v['money'];
                }
                
                break;
            case PurchaseCntDayModel::$type_avg_user_income :
                $sql = "select money from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])]+= $v['money'];
                }
                // 小游戏日活跃与用户
                $sql2 = "select android_user_num,ios_user_num from xyx_cnt_day where a_time between $from and $to and game_id=$game_id ";
                $res2 = XYXCntByDayModel::db()->getAllBySQL($sql2);

                $str = "";
                if($osType == "ios"){
                    $str = "android_user_num";
                }else if($osType == "andriod"){
                    $str = "ios_user_num";
                }
                foreach ($res2 as $v2) {
                    $map[date("Y-m-d", $v2['a_time'])] = isset($v2[$str])&&$v2[$str]!=0 ? $map[date("Y-m-d", $v2['a_time'])]/$v2[$str] : 0;
                }
                // 查询每天下游戏活跃用户数 无法区分android和ios
                break;
            case PurchaseCntDayModel::$type_first_pay_percent :
                $sql = "select pay_users,first_pay_users from purchase_cnt_day where (a_time between $from and $to ) and os_type=$osType and game_id=$game_id ";
                $res = PurchaseCntDayModel::db()->getAllBySQL($sql);
                foreach ($res as $v) {
                    $map[date("Y-m-d", $v['a_time'])] = $v['pay_users']==0 ? 0 : $v['first_pay_users']/$v['pay_users'];
                }
                break;
            default:
                # code...
                break;
        }


        $line = new LineObj();
        $line->name = PurchaseCntDayModel::getTypeDesc()[$type];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";
        $this->outputJson(200, 'succ', $line);

    }

    // 收入分析图表数据
    public function getPurchaseTableData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        // 获取时间
        $dateType = _g("dateType");
        $from = _g("from");
        $to = _g("to");

        $nowTime = time();
        switch ($dateType) {
            case PurchaseCntDayModel::$date_type_30 :
                $to = $nowTime - 86400*2;
                $from = $nowTime - 86400*32;
                break;
            case PurchaseCntDayModel::$date_type_7 :
                $to = $nowTime - 86400*2;
                $from = $nowTime - 86400*9;
                break;
            case PurchaseCntDayModel::$date_type_custom :
            default :
                if(!$from || !$to){
                    $this->outputJson(1,'no from or to');
                }
                if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
                    $this->outputJson(2,'日期格式不正确');
                }
                $to = strtotime($to);
                $from = strtotime($from);
                if(($to-$from) > 86400*60){
                    $from = $to-86400*60;
                }
                break;
        }

        $this->filtDate($from, $to);

        // 获取历史收入
        $sql2 = "select sum(money) as money from purchase_cnt_day where a_time<$from and game_id=$game_id group by os_type";
        $arr = PurchaseCntDayModel::db()->getAllBySQL($sql2);
        $iosHistoryTotalMoney = 0;
        $androidHistoryTotalMoney = 0;
        $allHistoryTotalMoney = 0;
        foreach ($arr as $v) {
            if($v['os_type'] == 'ios'){
                $iosHistoryTotalMoney = $v['money'];
            }
            if($v['os_type'] == 'android'){
                $androidHistoryTotalMoney = $v['money'];
            }
            $allHistoryTotalMoney += $v['money'];
        }

        // 获取数据
        $sql = "select * from purchase_cnt_day where a_time between $from and $to and game_id=$game_id ";
        $data = PurchaseCntDayModel::db()->getAllBySQL($sql);
        $ios = [0,0,0,0,0,0,0];
        $android = [0,0,0,0,0,0,0];
        $all = [0,0,0,0,0,0,0];
        foreach ($data as $v) {
            if($v['os'] == 'ios'){
                $ios[0] += $v['money'];
                $ios[1] += $v['pay_users'];
                $ios[2] += $v['first_pay_users'];
            }else{
                $android[0] += $v['money'];
                $android[1] += $v['pay_users'];
                $android[2] += $v['first_pay_users'];
            }
            $all[0] += $v['money'];
            $all[1] += $v['pay_users'];
            $all[2] += $v['first_pay_users'];
        }

        $ios[3] = 0;// 无法计算
        $ios[4] = $ios[1]==0 ? 0 : $ios[2]/$ios[1];
        $ios[5] = 0;//很难区分ios和android
        $ios[6] = $ios[0]+$iosHistoryTotalMoney;
        $android[3] = 0;
        $android[4] = $android[1]==0 ? 0 : $android[2]/$android[1];
        $android[5] = 0;
        $android[6] = $android[0]+$androidHistoryTotalMoney;
        $all[3] = 0;
        $all[4] = $all[1]==0 ? 0 : $all[2]/$all[1];
        $all[5] = 0;
        $all[6] = $all[0]+$allHistoryTotalMoney;

        // 获取小游戏时间段得数据
        // XYXCntByDayModel::db()->getAllBySQL();
        $returnData = [];
        $returnData[] = ['单日总收入',$all[0],$android[0],$ios[0]];
        $returnData[] = ['付费用户数',$all[1],$android[1],$ios[1]];
        $returnData[] = ['首次付费用户数',$all[2],$android[2],$ios[2]];
        $returnData[] = ['平均用户数',$all[3],$android[3],$ios[3]];
        $returnData[] = ['首付占比',$all[4],$android[4],$ios[4]];
        $returnData[] = ['渗透率',$all[5],$android[5],$ios[5]];
        $returnData[] = ['累计总收入',$all[6],$android[6],$ios[6]];
        $this->outputJson(200, 'succ', $returnData);


    }

    // 收入分析订单数据
    public function getOrderData(){
        $length = 10;
        $page = _g("page");
        $start = ($page-1) * $length;

        $info = $this->checkGame();
        $game_id = $info['id'];
        // 获取时间
        $dateType = _g("dateType");
        // $from = strtotime(_g("from"));
        // $to = strtotime(_g("to"));

        $nowTime = date("Y-m-d",time());
        switch ($dateType) {
            case 1 :
                $to = $nowTime+86399;
                $from = $nowTime;
                break;
            case 2 :
                $to = $nowTime+86399;
                $from = $nowTime - 86400*30;
                break;
            case 3 :
                $to = $nowTime+86399;
                $from = $nowTime - 86400*60;
                break;
            default :
                $to = $nowTime+86399;
                $from = $nowTime;
                break;
        }

        $this->filtDate($from, $to);

        $sql = "select goods_name,in_trade_no,out_trade_no,uid,done_time,money from (select goods_id,in_trade_no,out_trade_no,uid,done_time,money from games_goods_order where done_time between  $from and $to and game_id=$game_id and status=2) as a inner join open_props_price as o on a.goods_id=o.id limit $start,$length";
        $res = GamesGoodsOrderModel::db()->getAllBySQL($sql);
        foreach ($res as &$v) {
            $v['done_time'] = date("Y-m-d H:i:s", $v['done_time']);
        }
        $this->outputJson(200, "succ", ["totalPage"=>count($res)/$length, "list"=>$res]);

    }

    // 用户画像 性别与年龄分布
    // public function getSexAndAgeData(){
    //     $type = _g("type");
    //     $dateType = _g("dateType");

    //     $nowTime = time();
    //     $nowTime = strtotime(date("Y-m-d",$nowTime));
    //     switch ($dateType) {
    //         case 1 :// 近一个月
    //             $to = $nowTime - 86400+86399;
    //             $from = $nowTime - 86400*31;
    //             break;
    //         case 2 :// 近7天
    //             $to = $nowTime - 86400+86399;
    //             $from = $nowTime - 86400*8;
    //             break;
    //         case 3 :// 昨天
    //         default :
    //             $to = $nowTime - 86400+86399;
    //             $from = $nowTime - 86400;
    //             break;
    //     }

    // }


    
    /**
     * 导出成excel 输出到网页
     * first和data必须对应
     * @param  [type] $first 数据head
     * @param  [type] $data  数据数组
     * @return [type]        [description]
     */
    function export_data_as_excel($first, $data){
        include PLUGIN . "/phpexcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $num = 65;
        $x = 0;
        foreach($first as $k2=>$v2){
            $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x)."1" , $v2);
            $x++;
        }

        $line_num = 1;
        foreach($data as $k=>$line){
            $line_num ++;
            $x = 0;
            foreach($line as $k2=>$v2){
                if($x == 1){
                    $first = substr($v2,0,2);
                    if($first == 86){
                        $v2 = substr($v2,2);
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x).$line_num , $v2);
                $x++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    function export(){

        $info = $this->checkGame();
        $game_id = $info['id'];
        // 获取时间
        $dateType = _g("dateType");
        // $from = strtotime(_g("from"));
        // $to = strtotime(_g("to"));

        $nowTime = date("Y-m-d",time());
        switch ($dateType) {
            case 1 :
                $to = $nowTime+86399;
                $from = $nowTime;
                break;
            case 2 :
                $to = $nowTime+86399;
                $from = $nowTime - 86400*30;
                break;
            case 3 :
                $to = $nowTime+86399;
                $from = $nowTime - 86400*60;
                break;
            default :
                $to = $nowTime+86399;
                $from = $nowTime;
                break;
        }

        $this->filtDate($from, $to);

        $sql = "select goods_name,in_trade_no,out_trade_no,uid,done_time,money from (select goods_id,in_trade_no,out_trade_no,uid,done_time,money from games_goods_order where done_time between  $from and $to and game_id=$game_id and status=2) as a inner join open_props_price as o on a.goods_id=o.id";
        $res = GamesGoodsOrderModel::db()->getAllBySQL($sql);
        foreach ($res as &$v) {
            $v['done_time'] = date("Y-m-d H:i:s", $v['done_time']);
        }


        if(!$res)
            exit('数据为空，不需要导出');

        $uid = $this->_sess->getValue('id');
        $str .= count($res);
        // admin_db_log_writer($str,$uid,'export_excel');

        if(count($res) >= 30000){
            exit("数据已超过3000条，会影响服务器性能，请筛选条件分批下载");
        }


        $first = array(
            '订单号',
            '创建时间',
            '金额',
            '平台用户id',
            '第三方订单号',
            '描述'
        );

        $newdatas = [];
        foreach ($res as $item) {
            $newdata = [];
            $newdata[] = $item['in_trade_no'];
            $newdata[] = $item['done_time'];
            $newdata[] = $item['money'];
            $newdata[] = $item['uid'];
            $newdata[] = $item['out_trade_no'];
            $newdata[] = $item['goods_name'];
            $newdatas[] = $newdata;
        }
        
        $this->export_data_as_excel($first, $newdatas);
        
    }

    // 限制6-1
    private function filtDate(&$from, &$to){
        $time61 = strtotime("2019-07-01");
        if($from < $time61){
            $from = $time61;
        }
        if($to < $time61){
            $to = $time61;
        }
    }

}

class LineObj{
    public $year;
    public $name;
    public $data_num;
    public $data_date;
}

class PieObj{
    public $value;
    public $name;
}