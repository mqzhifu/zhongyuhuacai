<?php

/**
 * @Author: xuren
 * @Date:   2019-05-08 10:25:45
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-15 11:15:04
 */
class MakeXYXCntByDayBat{
	function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        $day = date("Y-m-d");
        $nowDayTime = strtotime($day);
        // $yesterday = date("Y-m-d", strtotime($day." -1 day"));
        // $day = "2019-05-22";
        // $day = "2019-04-15";
        $startDayTime = strtotime("2019-07-02");
        echo "MakeXYXCntByDayBat---start----".date("Y-m-d H:i:s")."\n";
        for($dayTime = $startDayTime; $dayTime <= $nowDayTime; $dayTime += 86400){
            $this->importDataNew($dayTime,false);
        }    
        echo "MakeXYXCntByDayBat---end----".date("Y-m-d H:i:s")."\n";
    }

    private function importDataNew($addTime, $isdebug){
        if($isdebug){
            echo "debug--start{  ";
        }
        // 一天前得当天0点时间
        $timePoint = strtotime(date("Y-m-d 00:00:00", $addTime - 86400));
        $pointDate = date("Y-m-d", $timePoint);
        $where = " a_time=$timePoint ";
        echo "$pointDate ";
        $count = XYXCntByDayModel::db()->getCount($where);
        if($count){
            echo "导入失败，数据已存在！\n";
            return false;
        }else{

            $startTime = $timePoint;
            $endTime = $startTime+86399;
            echo "查询数据……";
            $count = PlayedGamesModel::getCount($timePoint);
            if(!$count){
                echo "无数据！\n";
                return false;
            }
            // 数据拆分
            $splitNum = 1;
            for($i = 1; $i < 1000; $i++){
                $selectLimit = $count/$i;
                if(ceil($selectLimit) < 90000){
                    break;
                }
                $splitNum++;
            }
            $yu = $count - floor($selectLimit)*$splitNum;
            $limitArr = [];
            $limitstart = 0;
            for($i = 0; $i < $splitNum; $i++){
                if($i < $yu){
                    $limitArr[] = [$limitstart,ceil($selectLimit)];
                    $limitstart += ceil($selectLimit);
                }else{
                    $limitArr[$i] = [$limitstart, floor($selectLimit)];
                    $limitstart += floor($selectLimit);
                }
            }
            echo "分为 $splitNum 阶段取数据\n";
            $add = [];
            for($i=1; $i<=count($limitArr); $i++) {
                echo "阶段$i:";
                $data = PlayedGamesModel::getDataByDayTimeAndLimit($timePoint, $limitArr[$i-1][0], $limitArr[$i-1][1]);
                if($data){
                    echo count($data)."条\n";
                    echo memory_get_usage() . "    取数据后占用内存\n";

                    foreach ($data as &$v) {

                        $a2 = date("Y-m-d",$v['a_time']);
                        $a2time = strtotime($a2);
                        $e2 = date("Y-m-d",$v['e_time']);
                        $e2time = strtotime($e2);
                        
                        
                        // 需要更新的条目
                        if($v['e_time']){
                            if($v['type'] == 1){
                                $ym1 = date("Ym", $e2time);
                                $date1 = date("Y-m-d", $e2time);
                                if(!isset($add[$ym1])){
                                    $add[$ym1] = [];
                                }
                                if(!isset($add[$ym1][$date1])){
                                    $add[$ym1][$date1] = [];

                                }
                                if(!isset($add[$ym1][$date1][$v['game_id']])){
                                    $add[$ym1][$date1][$v['game_id']]['total_time'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['active_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['new_reg_user'] = 0;

                                    $add[$ym1][$date1][$v['game_id']]['click_num'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] = 0;
                                }
                                $add[$ym1][$date1][$v['game_id']]['total_time'] += $v['e_time'] - $v['a_time'];
                                $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                                $add[$ym1][$date1][$v['game_id']]['click_num'] += 1;
                                if($v['os'] == 'android'){
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] += 1;
                                }
                                if($v['os'] == 'ios'){
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] += 1;
                                }
                                
                            }
                            if($v['type'] == 2){
                                $ym1 = date("Ym", $e2time);
                                $date1 = date("Y-m-d", $e2time);
                                if(!isset($add[$ym1])){
                                    $add[$ym1] = [];
                                }
                                if(!isset($add[$ym1][$date1])){
                                    $add[$ym1][$date1] = [];

                                }
                                if(!isset($add[$ym1][$date1][$v['game_id']])){
                                    $add[$ym1][$date1][$v['game_id']]['total_time'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['active_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['new_reg_user'] = 0;

                                    $add[$ym1][$date1][$v['game_id']]['click_num'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] = 0;
                                }
                                $add[$ym1][$date1][$v['game_id']]['total_time'] += $v['e_time'] - $e2time;
                                $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];

                                $add[$ym1][$date1][$v['game_id']]['click_num'] += 1;
                                if($v['os'] == 'android'){
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] += 1;
                                }
                                if($v['os'] == 'ios'){
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] += 1;
                                }
                            }
                            if($v['type'] == 3){
                                $ym1 = date("Ym", $a2time);
                                $date1 = date("Y-m-d", $a2time);
                                if(!isset($add[$ym1])){
                                    $add[$ym1] = [];
                                }
                                if(!isset($add[$ym1][$date1])){
                                    $add[$ym1][$date1] = [];

                                }
                                if(!isset($add[$ym1][$date1][$v['game_id']])){
                                    $add[$ym1][$date1][$v['game_id']]['total_time'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['active_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['new_reg_user'] = 0;

                                    $add[$ym1][$date1][$v['game_id']]['click_num'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] = 0;
                                }
                                $add[$ym1][$date1][$v['game_id']]['total_time'] += strtotime(date("Y-m-d", $v['a_time'] + 86400)) - $v['a_time'];
                                $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];

                                $add[$ym1][$date1][$v['game_id']]['click_num'] += 1;
                                if($v['os'] == 'android'){
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] += 1;
                                }
                                if($v['os'] == 'ios'){
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] += 1;
                                }
                            }
                            if($v['type'] == 4){
                                $ym1 = date("Ym", $addTime);
                                $date1 = date("Y-m-d H:00", $addTime);
                                if(!isset($add[$ym1])){
                                    $add[$ym1] = [];
                                }
                                if(!isset($add[$ym1][$date1])){
                                    $add[$ym1][$date1] = [];

                                }
                                if(!isset($add[$ym1][$date1][$v['game_id']])){
                                    $add[$ym1][$date1][$v['game_id']]['total_time'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['active_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['new_reg_user'] = 0;

                                    $add[$ym1][$date1][$v['game_id']]['click_num'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'] = [];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] = 0;
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] = 0;
                                }
                                $add[$ym1][$date1][$v['game_id']]['total_time'] += 86400;
                                $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];

                                $add[$ym1][$date1][$v['game_id']]['click_num'] += 1;
                                if($v['os'] == 'android'){
                                    $add[$ym1][$date1][$v['game_id']]['android_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['android_click'] += 1;
                                }
                                if($v['os'] == 'ios'){
                                    $add[$ym1][$date1][$v['game_id']]['ios_user_num'][] = $v['uid'];
                                    $add[$ym1][$date1][$v['game_id']]['ios_click'] += 1;
                                }
                            }
                            
                        }

                        unset($v);

                    }
                    echo memory_get_usage() . "    组合后当前占用内存\n";
                    unset($data);
                    echo memory_get_usage() . "    释放掉data占用内存\n";
                    
                }else{
                    echo "无数据\n";
                }
            }
            echo "整合新注册用户……\n";
            foreach ($add as $ym => $data) {
                $addData = [];
                foreach ($data as $date => $gidDataMap) {
                    
                    $gidsStr = implode(",", array_keys($gidDataMap));
                    $startTime = strtotime($date);
                    $endTime = $startTime+86399;
                    $sql2 = "select count(uid) as new_reg_user,game_id from played_game_user where ( a_time between $startTime and $endTime ) and game_id in ( $gidsStr ) group by game_id";
                    $newRegData = PlayedGameUserModel::db()->getAllBySQL($sql2);
                    foreach ($gidDataMap as $gid => $dataArr) {
                        $new_reg_user = 0;
                        foreach ($newRegData as $v) {
                            if($v['game_id'] == $gid){
                                $new_reg_user = $v['new_reg_user'];
                            }
                        }
                        $d = [];
                        
                        $arr = array_flip($dataArr['active_user_num']);
                        $arr = array_flip($arr);
                        $arr = array_values($arr);
                        
                        $d['game_id'] = $gid;
                        $d['a_time'] = strtotime($date);
                        $d['total_time'] = $dataArr['total_time'];
                        $d['active_user_num'] = count($arr);
                        $d['new_reg_user'] = $new_reg_user;

                        $d['click_num'] = $dataArr['click_num'];
                        $d['ios_click'] = $dataArr['ios_click'];
                        $d['android_click'] = $dataArr['android_click'];

                        $arr2 = array_flip($dataArr['ios_user_num']);
                        $arr2 = array_flip($arr2);
                        $arr2 = array_values($arr2);
                        $d['ios_user_num'] = count($arr2);

                        $arr3 = array_flip($dataArr['android_user_num']);
                        $arr3 = array_flip($arr3);
                        $arr3 = array_values($arr3);
                        $d['android_user_num'] = count($arr3);

                        $addData[] = $d; 
                    }
                    
                }
                $res2 = XYXCntByDayModel::db()->addAll($addData);
                if($res2){
                    echo "add succ\n";
                }else{
                    echo "add error\n";
                }
            }
            // $data = PlayedGamesModel::getDataByDayTime($timePoint);
            
        }

        if($isdebug){
            echo "  }debug--end\n";
        }
    }

}