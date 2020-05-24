<?php
class AdvertiseService{
    function getRandomAdvertise ($type = 1 ,$limit = 1){
        $max = AdvertiseModel::db()->getCount( 1);
        $rand = rand(1,$max);

        if(!$max){
            return out_pc(8234);
        }
        $list = AdvertiseModel::db()->getRow(" id = $rand and type = ".$type . " limit $limit");
        if(!$list){
            return false;
        }

        return out_pc(200,$list);
    }

    /**
     * 收入图表Json信息
     */
    public function income ($gameId, $day = 30)  {

        $endDate = date("Y-m-d", time());
        $startDate = date("Y-m-d", strtotime("$endDate -$day week"));

        //获取两个时间区间的所有天
        // include_once("../app/open/ctrl/advertise.ctrl.php");
        $allDay = $this->getAllDay($startDate, $endDate);

        //查询时间区间的所有数据
        $where = " game_id = $gameId and stat_datetime between '". $startDate ."' and '". $endDate ."'";
//        if(!empty($type)) {
//            $where .= "and advertise_type = $type";
//        }
        $order = " order by stat_datetime desc";

        $advertiseChartList = OpenAdvertiseModel::getAdvertiseList($where, $order);

        //整理返回值 为下边整合数据用
        $list = '';
        foreach ($advertiseChartList as $k => $v) {
            if(!isset($list[$v['stat_datetime']])) {
                $list[$v['stat_datetime']] = $v;
            } else {
                $list[$v['stat_datetime']]['show'] += $v['show'];              //曝光量
                $list[$v['stat_datetime']]['click'] += $v['click'];            //点击量
                $list[$v['stat_datetime']]['click_rate'] += $v['click_rate'];  //点记率
                $list[$v['stat_datetime']]['cost'] += $v['cost'];            //收入
            }
        }

        //遍历所有的天 从结果集中插入指标数据
        foreach ($allDay as $k => $v) {
            if(isset($list[$v]) && $list[$v]['stat_datetime'] == $v) {
                $showArray[] = $list[$v]['show'];              //曝光量
                $clickArray[] = $list[$v]['click'];            //点击量
                $clickRateArray[] = $list[$v]['click_rate'];  //点记率
                $incomeArray[] = $list[$v]['cost'];            //收入
            } else {
                $showArray[] = 0;              //曝光量
                $clickArray[] = 0;             //点击量
                $clickRateArray[] = 0;         //点记率
                $incomeArray[] = 0;            //收入
            }
        }

        $res = [
            'date' => $allDay,
            'income' => $incomeArray,
            'income_count' => array_sum($incomeArray),
            'show' => $showArray,
            'show_count' => array_sum($showArray),
            'click' => $clickArray,
            'click_rate' => $clickRateArray,
            'result' => true,
        ];

        return $res;
    }

    public function getAllDay($startDate, $endDate) {
        //默认取一周的时间
        if(empty($startDate) || empty($endDate)) {
            $endDate = date("Y-m-d", time());
            $startDate = date("Y-m-d", strtotime("$endDate -1 week"));
        } else {
            $endDate = date("Y-m-d", strtotime($endDate));
            $startDate = date("Y-m-d", strtotime($startDate));
        }

        $dayTotal = ceil((strtotime($endDate) - strtotime($startDate)) / (3600 * 24)) + 1;

        $allDayArr = [$endDate];
        for($i = 1; $i < $dayTotal; $i++) {
            array_push($allDayArr, date("Y-m-d", strtotime("$endDate -$i day")));
        }
        $allDayArr = array_reverse($allDayArr);
        return $allDayArr;
    }

    public function getAllHour($startTime, $endTime) {
        //默认取一天的时间
        if(empty($startTime) || empty($endTime)) {
            $endTime = strtotime(date("Y-m-d H:00", time()));
            $startTime = $endTime - 86400;
        } else {
            $endTime = strtotime(date("Y-m-d H:00", $endTime));
            $startTime = strtotime(date("Y-m-d H:00", $startTime));
        }

        $allDayArr = [];
        for($i = $startTime; $i <= $endTime; $i+=3600) {
            array_push($allDayArr, date("Y-m-d H:00", $i));
        }

        return $allDayArr;
    }

    public function getAllFiveMinute($startTime, $endTime){
        //默认取一天的时间
        if(empty($startTime) || empty($endTime)) {
            $endTime = strtotime(date("Y-m-d H:i", time()));
            $startTime = $endTime - 3600;
        } else {
            $endTime = strtotime(date("Y-m-d H:i", $endTime));
            $startTime = strtotime(date("Y-m-d H:i", $startTime));
        }

        $allDayArr = [];
        for($i = $startTime; $i <= $endTime; $i+=300) {
            array_push($allDayArr, date("Y-m-d H:i", $i));
        }

        return $allDayArr;
    }

    public function getAllDayTime($startTime, $endTime){
        //默认取一周的时间
        if(empty($startTime) || empty($endTime)) {
            $endTime = time();
            $startTime = $endTime - 7 * 86400;
        } else {
            $endTime = $endTime;
            $startTime = $startTime;
        }

        $allDayArr = [];
        for($i = $startTime; $i <= $endTime; $i+=86400) {
            array_push($allDayArr, $i);
        }

        return $allDayArr;
    }

    function getAllMonth($startDate, $endDate) {
        //默认取一周的时间
        if(empty($startDate) || empty($endDate)) {
            $endDate = strtotime(date("Y-m", time()));
            $startDate = strtotime(date("Y-m", strtotime("$endDate -1 month")));
        } else {
            $endDate = strtotime(date("Y-m", strtotime($endDate)));
            $startDate = strtotime(date("Y-m", strtotime($startDate)));
        }


        $allDayArr = [];
        for($i = $startDate; $i <= $endDate; $i = strtotime(date("Y-m", $i)." +1 month")) {
            array_push($allDayArr, date("Y-m", $i));
        }

        return $allDayArr;
    }

    function getAllYearWeek($startDate, $endDate){
        if(empty($startDate) || empty($endDate)) {
            $endDate = strtotime(date("Y-m-d", time()));
            $startDate = strtotime(date("Y-m-d", strtotime("$endDate -1 month")));
        } else {
            $endDate = strtotime(date("Y-m-d", strtotime($endDate)));
            $startDate = strtotime(date("Y-m-d", strtotime($startDate)));
        }

        $startDate = strtotime("Sunday -6 day",$startDate);
        $endDate = strtotime("Sunday -6 day",$endDate);
        $allDayArr = [];
        for($i = $startDate; $i <= $endDate; $i = strtotime(date("Y-m-d", $i)." +1 week")) {
            array_push($allDayArr, date("Y.W", $i));
        }

        return $allDayArr;   
    }

    public function getAdIncomeListByDayRange($startDate, $endDate){
        $sql = "select * from open_advertise_income where stat_datetime between str_to_date('2019-03-03','%Y-%m-%d') and str_to_date('2019-03-04','%Y-%m-%d') and ad_slot_id in (select outer_ad_id from open_advertise oa right join ad_map am on oa.id=am.inner_ad_id);";

        $res = advertiseIncomeModel::db()->query($sql);
        return $res;
    }

    public function getAdIncomeListByCondition($startDate, $endDate, $OS=null, $ad_type=null, $game_id=null){
        if(!$startDate || !$endDate){
            return [];
        }
        $amWhere = "";
        $ouWhere = "";
        $oaWhere = "";
        if($OS){
            $ouWhere .= " where am.system = $OS ";
            if($ad_type){
                $ouWhere .= "and oa.advertise_type = $ad_type ";
                if($game_id){
                    $ouWhere .= "and oa.game_id = $game_id ";
                }
            }else{
                if($game_id){
                    $ouWhere .= "and oa.game_id = $game_id ";
                }
            }
        }else{
            if($ad_type){
                $ouWhere .= " where oa.advertise_type = $ad_type ";
                if($game_id){
                    $ouWhere .= "and oa.game_id = $game_id ";
                }
            }else{
                if($game_id){
                    $ouWhere .= " where oa.game_id = $game_id ";
                }
            }
        }
        $sql = "select * from open_advertise_income where stat_datetime between str_to_date('$startDate','%Y-%m-%d') and str_to_date('$endDate','%Y-%m-%d') and ad_slot_id in (select outer_ad_id from open_advertise oa right join ad_map am on oa.id=am.inner_ad_id $ouWhere);";
        $res = advertiseIncomeModel::db()->query($sql);
        return $res;
    }

}