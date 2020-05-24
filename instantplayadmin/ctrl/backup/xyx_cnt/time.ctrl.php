<?php

/**
 * @Author: xuren
 * @Date:   2019-04-22 17:39:29
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-05 17:31:52
 */
class TimeCtrl extends BaseCtrl{

    function byHour(){
        $gameNameList = GamesModel::getOnlineGamesNameList();
        $this->assign("gameNameList", $gameNameList);
        $this->display("xyx_cnt/time/byhour.html");
    }

    function getDataByHour(){
        $gameid = _g("gameid");
        if(!$gameid){
            $this->outputJson(1,'gameid未填写');
        }
        $gameid = intval($gameid);

        $selectDay = _g("day");
        if(!$selectDay){
            $selectDay = date("Y-m-d");
        }

        $launchDay = "2019-05-1";
        $launchTime = strtotime($launchDay);

        $day = strtotime($selectDay);
        $yesterday = strtotime($selectDay." -1 day");
        $sevenDay = strtotime($selectDay." -7 day");
        $thirtyDay = strtotime($selectDay." -30 day");

        $resArr = [];

        if(strtotime($selectDay) >= $launchTime){
            $res = XYXCntByHourModel::getDataByGameIdAndDay($gameid, $day);
        }
        if(strtotime($selectDay." -1 day") >= $launchTime){
            $res1 = XYXCntByHourModel::getDataByGameIdAndDay($gameid, $yesterday);
        }
        if(strtotime($selectDay." -7 day") >= $launchTime){
            $res7 = XYXCntByHourModel::getDataByGameIdAndDay($gameid, $sevenDay);
        }
        if(strtotime($selectDay." -30 day") >= $launchTime){
            $res30 = XYXCntByHourModel::getDataByGameIdAndDay($gameid, $thirtyDay);
        }

        $resArr[] = $res;
        $resArr[] = $res1;
        $resArr[] = $res7;
        $resArr[] = $res30;

        $today = strtotime("today");
        $tomorrow = strtotime("tomorrow");
        // 时间和总和的映射
        $timeMapArr = [];
        $uidMapArr = [];
        $newRegMapArr = [];
        for($j = 0; $j < count($resArr); $j++) {
            for($i = $today; $i < $tomorrow; $i += 3600){
                $timeMapArr[$j][date("H:00", $i)] = 0;
                $uidMapArr[$j][date("H:00", $i)] = 0;
                $newRegMapArr[$j][date("H:00", $i)] = 0;
            }
        }

        for($j = 0; $j < count($resArr); $j++) {
            foreach ($resArr[$j] as $v) {
                $a = date("H:00", $v['a_time']);

                $a2 = date("Y-m-d H:00",$v['a_time']);

                $timeMapArr[$j][$a] += $v['total_time'];
                $uidMapArr[$j][$a] += $v['active_user_num'];
                $newRegMapArr[$j][$a] += $v['new_reg_user'];

            }
        }

        // 数据平均
        for($j = 0; $j < count($timeMapArr); $j++) {
            foreach ($timeMapArr[$j] as $key => $value) {

                $timeMapArr[$j][$key] = $uidMapArr[$j][$key]==0 ? 0 : $value/$uidMapArr[$j][$key];
            }
        }


        // 格式化数据
        $newDataArr = [];
        for($j = 0; $j < count($timeMapArr); $j++) {
            $newData = [];
            foreach ($timeMapArr[$j] as $key => $value) {
                $newData[] = [$key,$value];
            }
            $newDataArr[] = $newData;
        }


        $obj = new LineObj();
        $obj->data = $newDataArr[0];
        $obj->label = "今天";
        $obj1 = new LineObj();
        $obj1->data = $newDataArr[1];
        $obj1->label = "昨天";
        $obj7 = new LineObj();
        $obj7->data = $newDataArr[2];
        $obj7->label = "7天前";
        $obj30 = new LineObj();
        $obj30->data = $newDataArr[3];
        $obj30->label = "30天前";

        $returnData = [];
        $returnData[] = $obj;
        $returnData[] = $obj1;
        $returnData[] = $obj7;
        $returnData[] = $obj30;

        $newReturnData['line'] = $returnData;

        // table数据
        $tableData = [];
        foreach ($timeMapArr[0] as $key => $value) {
            $tableData[] = [$key, $newRegMapArr[0][$key], $uidMapArr[0][$key], $this->secToTime($value)];
        }
        $newReturnData['table'] = $tableData;
        $this->outputJson(200, 'succ', $newReturnData);
    }

    function byDay(){
        $gameNameList = GamesModel::getOnlineGamesNameList();
        $this->assign("gameNameList", $gameNameList);
        $this->display("xyx_cnt/time/byday.html");
    }

    function getDataByDay(){
        $from = _g("from");
        $to = _g("to");
        $gameid = _g("gameid");
        if(!$gameid){
            $this->outputJson(1,'gameid未填写');
        }
        $gameid = intval($gameid);

        if(!$from){
            $from = date("Y-m-d", time());
        }
        if(!$to){
            $to = date("Y-m-d");
        }

        $monthStart = date("Y-m-1", strtotime($to));
        $monthEnd = date("Y-m-1", strtotime($monthStart." +1 month"));
        $monthStartTime = strtotime($monthStart);
        $monthEndTime = strtotime($monthEnd);
        // $fromTime = strtotime($from);
        // $toTime = strtotime($to)+86399;// 一天结尾时间

        $dayMap = [];
        $uidMap = [];
        $newRegMap = [];
        for($i = $monthStartTime; $i < $monthEndTime; $i+= 86400){
            $dayMap[date("Y-m-d", $i)] = 0;
            $uidMap[date("Y-m-d", $i)] = 0;
            $newRegMap[date("Y-m-d", $i)] = 0;
        }
        // $ym = date("Ym", strtotime($to));
        // $res = XYXCntByHourModel::getDataByYMAndGameId($ym, $gameid);

        $res = XYXCntByDayModel::getDataByYmAndGameId($monthStartTime, $monthEndTime-1, $gameid);
        foreach ($res as $v) {
            $dayTime = date("Y-m-d", $v['a_time']);
            foreach ($dayMap as $k2 => $v2) {
                if($k2 == $dayTime){
                    $dayMap[$k2] += $v['total_time'];
                    $uidMap[$k2] += $v['active_user_num'];
                    $newRegMap[$k2] += $v['new_reg_user'];
                    // if(empty($uidMap[$k2])){
                    //     if($v['etime_active_user'] != ""){
                    //         $uidMap[$k2] = explode(",",$v['etime_active_user']);
                    //     }
                    // }else{
                    //     if($v['etime_active_user'] != ""){
                    //         $uidMap[$k2] = array_merge($uidMap[$k2],explode(",",$v['etime_active_user']));
                    //     }
                    // }

                    // $newRegMap[$k2] += $v['new_reg_user'];
                }
            }

        }


        // 数据平均
        foreach ($dayMap as $key => $value) {
            // $arr = array_flip($uidMap[$key]);
            // $arr = array_flip($arr);
            // $arr = array_values($arr);

            $dayMap[$key] = $uidMap[$key]==0 ? 0 : $value/$uidMap[$key];
        }

        $newData = [];
        foreach ($dayMap as $key => $value) {
            $newData[] = [$key,$value];
        }

        $lineObj = new LineObj();
        $lineObj->data = $newData;
        $lineObj->label = "游戏时长";

        $returnData = [];
        $returnData[] = $lineObj;



        $newReturnData['line'] = $returnData;

        // table数据
        $tableData = [];
        foreach ($dayMap as $key => $value) {
            // $arr = array_flip($uidMap[$key]);
            // $arr = array_flip($arr);
            // $arr = array_values($arr);
            $tableData[] = [$key, $newRegMap[$key], $uidMap[$key], $this->secToTime(intval($value))];
        }
        $newReturnData['table'] = $tableData;
        $this->outputJson(200, 'succ', $newReturnData);
    }

    function click(){
        $gameNameList = GamesModel::getOnlineGamesNameList();
        $this->assign("gameNameList", $gameNameList);
        if(_g("getClickData")){
            $this->getClickData();
        }
        $this->display("xyx_cnt/time/click.html");
    }

    // 时长汇总
    function total(){
        if(_g("getTotalData")){
            $this->getTotalData();
        }
        $this->display("xyx_cnt/time/total.html");
    }

    function getTotalData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $selectDay = _g("selectDay");
        // 当天
        if(!$selectDay){
            $selectDay = date("Y-m-d", time());
        }

        // 一天开始
        $startTime = strtotime($selectDay);
        // 一天结束
        $endTime = $startTime + 86399;
        $sql = "select game_id from played_games where a_time between $startTime and $endTime and e_time!= 0 group by game_id";

        $cntSql = GamesModel::db()->getAllBySQL($sql);

        $cnt = (0 == count($cntSql))?0:count($cntSql);


        $iTotalRecords = $cnt;//DB中总记录数
        if($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'game_id',
                '',
                'clickNum',
                '',
                '',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;


            $sql2 = "select a.game_id,g.name,a.total,a.clickNum,a.userNum from (select count(*) as cnt,game_id,sum(e_time-a_time) as total,count(a_time) as clickNum,count(distinct uid) as userNum from played_games where a_time between $startTime and $endTime and e_time!= 0 group by game_id order by $order limit $iDisplayStart,$iDisplayLength ) a left join games g on a.game_id=g.id ";
            $res = GamesModel::db()->getAllBySQL($sql2);

            foreach ($res as $v) {
                // $a_timesArr = explode(",", $v['a_times']);
                // $e_timesArr = explode(",", $v['e_times']);
                // $totalTime = 0;
                // foreach ($a_timesArr as $k=>$a_time) {
                // 	if($e_timesArr[$k]){
                // 		$totalTime += $e_timesArr[$k] - $a_time;
                // 	}
                // }
                $records["data"][] = array(
                    $selectDay,
                    $v['game_id'],
                    $v['name'],
                    $v['clickNum'],
                    $this->secToTime($v['userNum']>0 ? $v['total']/$v['userNum'] : 0),
                    "",
                );

            }

        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }


    function getClickData(){


        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $oneDayInterval = 24*60*60;
        $where = $this->getWhere();

        $from = _g("from");
        $to = _g("to");

        if(!$from){
            $from = date("Y-m-d", time());
        }
        if(!$to){
            $to = $to = date("Y-m-d", time());
        }

        if(!_g("gameid")){
            $iTotalRecords = 0;
        }else{
            $fromTime = strtotime(date("Y-m-d",strtotime($from)));
            $toTime = strtotime(date("Y-m-d",strtotime($to)))+86399;

            $dayMap = [];
            for($i = $fromTime; $i <= $toTime; $i+= 86400){
                $dayMap[date("Y-m-d", $i)] = 0;
            }


            $sql = "select count(*) as cnt from played_games where $where";


            $cntSql = PlayedGamesModel::db()->getRowBySQL($sql);
            $cnt = (0 == $cntSql['cnt'])?0:$cntSql['cnt'];
            // var_dump($cntSql);
            // exit;
            if(arrKeyIssetAndExist($cntSql,'cnt')){
                $cnt = $cntSql['cnt'];
            }

            $iTotalRecords = $cnt;//DB中总记录数
        }



        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'a_time',
                'game_id',
                '',
                '',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;



            $sql = "select uid,a_time,e_time,src from played_games where $where order by $order";


            $data = PlayedGamesModel::db()->getAllBySQL($sql);
            foreach ($data as $v) {
                if($v['a_time']){
                    $dateStr = date("Y-m-d", $v['a_time']);
                    foreach ($dayMap as $k2 => $v2) {
                        if($k2 == $dateStr){
                            $dayMap[$k2] ++;
                        }
                    }
                }

            }

            foreach ($dayMap as $key => $value) {
                $dayTime2 = strtotime($key);
                $records["data"][] = array(
                    $key,
                    "",
                    $value,
                    '<a class="btn btn-xs default red delone" href="javascript:void(0);" onclick="getDetail('.$dayTime2.','._g("gameid").')"><i class="fa fa-file-text"></i>'.'查看'.'</a>',
                );
            }

        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;

    }

    function dayDetailByGameId(){
        $selectTime =  _g("selectDayTime");
        $gameid = _g("gameid");

        $start = strtotime(date("Y-m-d",$selectTime));
        $end = strtotime(date("Y-m-d",$selectTime+86400));
        $where = " 1 ";
        if($gameid){
            $where .= " and game_id=$gameid";
        }
        if($selectTime){
            $where .= " and a_time between $start and $end";
        }


        $sql = "select uid,a_time,e_time,src from played_games where $where";


        $data = PlayedGamesModel::db()->getAllBySQL($sql);
        $map = PlayedGamesModel::getSrcDesc();
        $desc = PlayedGamesModel::getSrcDesc();
        foreach ($map as &$value) {
            $value = 0;
        }

        foreach ($data as $value) {
            $map[$value['src']]++;
        }

        $tableData = [];
        foreach ($map as $key => $value) {
            $tableData[] = [$desc[$key], $value];
        }

        $this->outputJson(200, 'succ', $tableData);

    }

    function getWhere(){
        $where = " 1 ";
        if($gameid = _g("gameid")){
            $where .= " and game_id=$gameid";
        }


        if($from = _g("from")){
            $from = strtotime(date("Y-m-d",strtotime($from)));

        }else{
            $from = date("Y-m-d", time());

        }
        $where .= " and a_time >= '".$from."'";

        if($to = _g("to")){
            $to = strtotime(date("Y-m-d",strtotime($to)));
        }else{
            $to = $to = date("Y-m-d", time());
        }
        $where .= " and a_time <= '".($to+86399)."'";
        return $where;
    }

    function s2str($time){
        $h = intval($time/3600);
        $m = intval($time%3600/60);
        $s = $time%60;

        return "$h : $m : $s ";
    }

    function secToTime($times){
        $result = '00:00:00';
        if ($times>0) {
            $hour = floor($times/3600);
            $hour = $hour>9 ? $hour :"0".$hour;
            $minute = floor(($times-3600 * $hour)/60);
            $minute = $minute>9 ? $minute :"0".$minute;
            $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
            $second = $second>9 ? $second :"0".$second;
            $result = $hour.':'.$minute.':'.$second;
        }
        return $result;
    }
}
class LineObj{
    public $data;
    public $label;
}