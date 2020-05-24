<?php

/**
 * @Author: xuren
 * @Date:   2019-04-22 17:36:42
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-05 17:33:06
 */
class TimeCtrl extends BaseCtrl{

    function byHour(){
        $this->display("xyx_app_cnt/time/byhour.html");
    }

    function getDataByHour(){
        $selectDay = _g("day");

        if(!$selectDay){
            $selectDay = date("Y-m-d");
        }

        // 上线日期
        $launchDay = "2019-05-1";
        $launchTime = strtotime($launchDay);

        $day = date("Y-m-d", strtotime($selectDay));

        $yesterday = date("Y-m-d", strtotime($selectDay." -1 day"));
        $sevenDay = date("Y-m-d", strtotime($selectDay." -7 day"));
        $thirtyDay = date("Y-m-d", strtotime($selectDay." -30 day"));

        $res = [];
        $res1 = [];
        $res7 = [];
        $res30 = [];
        if(strtotime($selectDay) >= $launchTime){
            $res = WsCntByHourModel::getDataByDay($day);
        }
        if(strtotime($selectDay." -1 day") >= $launchTime){
            $res1 = WsCntByHourModel::getDataByDay($yesterday);
        }
        if(strtotime($selectDay." -7 day") >= $launchTime){
            $res7 = WsCntByHourModel::getDataByDay($sevenDay);
        }
        if(strtotime($selectDay." -30 day") >= $launchTime){
            $res30 = WsCntByHourModel::getDataByDay($thirtyDay);
        }



        $today = strtotime("today");
        $tomorrow = strtotime("tomorrow");
        // 时间和总和的映射
        $timeMap = [];
        $timeMap1 = [];
        $timeMap7 = [];
        $timeMap30 = [];

        for($i = $today; $i < $tomorrow; $i += 3600){
            $timeMap[date("H:00", $i)] = 0;
            $timeMap1[date("H:00", $i)] = 0;
            $timeMap7[date("H:00", $i)] = 0;
            $timeMap30[date("H:00", $i)] = 0;

        }

        foreach ($res as $v) {
            $timeMap[date("H:00", $v['a_time'])] = $v;
        }
        foreach ($res1 as $v) {
            $timeMap1[date("H:00", $v['a_time'])] = $v;
        }
        foreach ($res7 as $v) {
            $timeMap7[date("H:00", $v['a_time'])] = $v;
        }
        foreach ($res30 as $v) {
            $timeMap30[date("H:00", $v['a_time'])] = $v;
        }


        // 格式数据
        $newData = [];
        foreach ($timeMap as $key => $value) {
            $newData[] = [$key,$value['active_user']>0 ? $value['total_time']/$value['active_user'] : 0];
        }

        $newData1 = [];
        foreach ($timeMap1 as $key => $value) {
            $newData1[] = [$key,$value['active_user']>0 ? $value['total_time']/$value['active_user'] : 0];
        }

        $newData7 = [];
        foreach ($timeMap7 as $key => $value) {
            $newData7[] = [$key,$value['active_user']>0 ? $value['total_time']/$value['active_user'] : 0];
        }

        $newData30 = [];
        foreach ($timeMap30 as $key => $value) {
            $newData30[] = [$key,$value['active_user']>0 ? $value['total_time']/$value['active_user'] : 0];
        }

        $obj = new LineObj();
        $obj->data = $newData;
        $obj->label = "今天";
        $obj1 = new LineObj();
        $obj1->data = $newData1;
        $obj1->label = "昨天";
        $obj7 = new LineObj();
        $obj7->data = $newData7;
        $obj7->label = "7天前";
        $obj30 = new LineObj();
        $obj30->data = $newData30;
        $obj30->label = "30天前";
        $returnData = [];
        $returnData[] = $obj;
        $returnData[] = $obj1;
        $returnData[] = $obj7;
        $returnData[] = $obj30;

        $newReturnData['line'] = $returnData;

        // table数据
        $tableData = [];
        foreach ($timeMap as $key => $value) {
            $tableData[] = [$key, isset($value['new_reg_user']) ? $value['new_reg_user'] : 0, isset($value['active_user']) ? $value['active_user'] : 0, $this->secToTime($value['active_user']>0 ? $value['total_time']/$value['active_user'] : 0)];
        }
        $newReturnData['table'] = $tableData;
        $this->outputJson(200, 'succ', $newReturnData);
    }


    function byDay(){
        $this->display("xyx_app_cnt/time/byday.html");
    }

    function getDataByDay(){
        $from = _g("from");
        $to = _g("to");

        if(!$to){
            $to = date("Y-m-d");
        }
        if(strtotime($to) > time()){
            $to = date("Y-m-d", time());
        }

        $oneDayInterval = 24*60*60;

        $fromTime = strtotime($from);
        $toTime = strtotime($to);// 一天结尾时间

        $fromTime = strtotime(date("Y-m-1", $toTime));
        $toTime = strtotime(date("Y-m-1", $toTime)." +1 month");
        $dayMap = [];
        $activeMap = [];
        $uidMap = [];
        $newUserMap = [];
        for($i = $fromTime; $i < $toTime; $i+= 86400){
            $dayMap[date("d", $i)] = 0;
            $activeMap[date("d", $i)] = 0;
            $uidMap[date("d", $i)] = [];
            $newUserMap[date("d", $i)] = 0;
        }

        $ym = date("Ym",$fromTime);
        $res = WsCntByHourModel::getDataByYM($ym);



        $data = [];
        foreach ($res as $v) {
            $dateTmp = date("d", $v['a_time']);
            $dayMap[$dateTmp] += $v['total_time'];
            $activeMap[$dateTmp] += $v['active_user'];
            if(empty($uidMap[$dateTmp])){
                if($v['etime_active_user'] != ""){
                    $uidMap[$dateTmp] = explode(",",$v['etime_active_user']);
                }
            }else{
                if($v['etime_active_user'] != ""){
                    $uidMap[$dateTmp] = array_merge($uidMap[$dateTmp],explode(",",$v['etime_active_user']));
                }
            }
            $newUserMap[$dateTmp] += $v['new_reg_user'];

        }

        // 格式化数据
        $data = [];
        $tableData = [];
        foreach ($dayMap as $key => $value) {
            $arr = array_flip($uidMap[$key]);
            $arr = array_flip($arr);
            $arr = array_values($arr);
            $activeUserNum = count($arr);
            $data[] = [$key, $activeUserNum>0 ? $dayMap[$key]/$activeUserNum : 0];
            $tableData[] = [$key, $newUserMap[$key], $activeUserNum, $this->secToTime($activeUserNum>0 ? $dayMap[$key]/$activeUserNum : 0)];
        }
        $lineObj = new LineObj();
        $lineObj->data = $data;
        $lineObj->label = "使用时长";

        $returnData = [];
        $returnData[] = $lineObj;

        $newReturnData = [];
        $newReturnData['line'] = $returnData;

        // $sql2 = "select count(uid) as activeNum,a_time from ".$table." group by a_time";
        // $res2 = WsCntModel::db()->getAllBySql($sql2);

        // $tableData = [];

        $newReturnData['table'] = $tableData;

        $this->outputJson(200, "succ", $newReturnData);
    }

    function singleUser(){
        $this->addCss("/assets/open/css/game-detail.css?1");
        if(_g("getSingleUserData")){
            $this->getSingleUserData();
        }
        $this->display("xyx_app_cnt/time/single_user.html");
    }

    function getSingleUserData(){

        // $records = array();
        //       $records["data"] = array();

        // $uid = _g("uid");
        // $from = _g("from");
        // $to = _g("to");

        // if(!$uid){
        // 	$this->outputJson(1,'请填写UID');
        // }
        // $uid = intval($uid);
        // if(!$to){
        // 	$to = date("Y-m-d");
        // }

        // $oneDayInterval = 24*60*60;

        // $fromTime = strtotime($from);
        // $toTime = strtotime($to);

        // $ym = date("Ym",$toTime);
        // $table = WsCntModel::getTableByDay($ym);
        // $sql = "select sum(total_time) as y,a_time,uid from ".$table." where uid=".$uid." group by a_time";
        // $res = WsCntModel::db()->getAllBySql($sql);

        // foreach ($res as $v) {
        // 	$records["data"][] = array(
        //            date("Y-m-d", $v['a_time'] - $oneDayInterval),
        //            $v['uid'],
        //            $v['y'],
        //            "<a>详情</a>",
        //        );
        // }

        //       echo json_encode($records);
        //       exit;



        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        if(!_g("uid")){
            $iTotalRecords = 0;
        }else{
            $oneDayInterval = 24*60*60;
            $where = $this->getWhere();

            $from = _g("from");

            if(!$from){
                $fromTime = strtotime(date("Y-m-d",time()))+86400;
            }else{
                $fromTime = strtotime(date("Y-m-d",strtotime($from)))+86400;
            }

            $to = _g("to");
            if(!$to){
                $to = date("Y-m-d");
            }
            $toTime = strtotime($to)+86400;
            $ym = date("Ym",$toTime);
            $table = WsCntModel::getTableByDay($ym);

            $sql = "select id from ".$table." where $where group by a_time";
            $cntSql = WsCntModel::db()->getAllBySQL($sql);
            $cnt = (0 == count($cntSql))?0:count($cntSql);
            // var_dump($cntSql);
            // exit;
            // if(arrKeyIssetAndExist($cntSql,'cnt')){
            //     $cnt = $cntSql['cnt'];
            // }

            $iTotalRecords = $cnt;//DB中总记录数
        }


        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'a_time',
                'uid',
                'y',
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



            $sql = "select sum(total_time) as y,a_time,uid from ".$table." where $where group by a_time order by $order limit $iDisplayStart,$iDisplayLength ";
            $data = WsCntModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $date = date("Y-m-d", $v['a_time'] - $oneDayInterval);// 实际数据时间
                if($fromTime <= $v['a_time']){
                    $records["data"][] = array(
                        $date,
                        "",
                        $this->secToTime($v['y']),
                        '<a class="btn btn-xs default red delone" href="javascript:void(0);" onclick="getDetail('.($v['a_time'] - $oneDayInterval).','.$v["uid"].')"><i class="fa fa-file-text"></i>'.'查看'.'</a>',
                    );
                }


            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;

    }

    function dayDetailByUser(){
        $uid = _g("uid");
        $selectDayTime = _g("selectDayTime");
        if(!$uid){
            $this->outputJson(1, 'uid不存在');
        }
        if(!$selectDayTime){
            $this->outputJson(2, '未传入时间');
        }

        $selectDay = date("Y-m-d",$selectDayTime);


        // 上线日期
        $launchDay = "2019-05-1";
        $launchTime = strtotime($launchDay);

        $day = date("Ymd", strtotime($selectDay));

        $res = [];
        if(strtotime($selectDay) >= $launchTime){
            $res = WsLogModel::getPartDataByDay($day);
        }

        $today = strtotime("today");
        $tomorrow = strtotime("tomorrow");
        // 时间和总和的映射
        $timeMap = [];
        // 时间和uid个数映射
        for($i = $today; $i < $tomorrow; $i += 3600){
            $timeMap[date("H:00", $i)] = 0;
        }
        foreach ($res as $v) {
            $a = date("H:00", $v['a_time']);
            $e = date("H:00",$v['e_time']);
            $a2 = date("Y-m-d H:00",$v['a_time']);
            $a2time = strtotime($a2);
            $e2 = date("Y-m-d H:00",$v['e_time']);
            $e2time = strtotime($e2);
            $atime = strtotime($a);
            $etime = strtotime($e);

            if($v['uid'] == $uid && $v['e_time']){
                if($a == $e){
                    $timeMap[$a] += $v['e_time'] - $v['a_time'];
                }else{

                    $timeMap[$a] += $a2time + 3600 - $v['a_time'];
                    $timeMap[$e] += $v['e_time'] - $e2time;

                    if(date("H:00", $v['e_time'] - 3600) != $a){
                        foreach ($timeMap as $key => $value) {
                            if(strtotime($key) > $atime && strtotime($key) < $etime){
                                $timeMap[$key] += 3600;
                            }
                        }
                    }
                }
            }
        }


        $tableData = [];
        foreach ($timeMap as $key => $value) {
            $tableData[] = [$key, $uid, $this->secToTime($value)];
        }

        $this->outputJson(200, 'succ', $tableData);

    }

    function getWhere(){
        $where = " 1 ";
        if($uid = _g("uid")){
            $where .= " and uid=$uid";
        }


        if($from = _g("from")){
            $from = strtotime(date("Y-m-d",strtotime($from)))+86400;

        }else{
            $from = strtotime(date("Y-m-d",time()))+86400;
        }
        $where .= " and a_time >= '".$from."'";

        if($to = _g("to")){
            $to = strtotime(date("Y-m-d",strtotime($to)))+86400;

        }else{
            $to = strtotime(date("Y-m-d",time()))+86400;
        }
        $where .= " and a_time <= '".($to+86399)."'";
        return $where;
    }

    function uniqueArr($arr){
        $arr = array_flip($arr);
        $arr = array_flip($arr);
        $arr = array_values($arr);
        return $arr;
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