<?php

/**
 * @Author: xuren
 * @Date:   2019-04-29 14:21:09
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 20:07:32
 */
class MakeXYXCntByHour{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        // $day = date("Ymd",strtotime(date("Ymd"))- 24 * 60 * 60 );
        // $ym = date("Ym",strtotime(date("Ymd"))- 24 * 60 * 60);

        // $addTime = strtotime("2019-05-06 18:02:03");

        echo "脚本执行时间：".date("Y-m-d H:i:s", time())."\n";
        // 需要添加的数据time
        // $tomorrow = strtotime("tomorrow");
        // $dayFirstHourTime = strtotime(date("Y-m-d 00:i:s", $addTime));
        // for($i = $dayFirstHourTime; $i < $tomorrow; $i += 3600) {
        //     // echo date("Y-m-d H:i:s", $i)."\n";
        //     $this->importData2($i);
        // }
        if(arrKeyIssetAndExist($attr,'datetime')){
            $date = $attr['datetime'];
            $addTime = strtotime($date)+86400;//复杂是为了尽量少修改写好的函数
        }else{
            $addTime = time();
        }
		$this->importData3($addTime);        

    }



    private function time2oclock($time){
    	return strtotime(date("Y-m-d H:00", $time));
    }

    private function importData2($time){

        $publishDate = "2019-06-01";
        $publishTime = strtotime($publishDate);

        $dataTime = strtotime(date("Y-m-d H:00", $time - 86400));
        if($publishTime > $dataTime){
            echo "导入失败，表不存在";
            return false;
        }
        $ym = date("Ym", $dataTime);
        echo date("Y-m-d H:i:s", $dataTime)." ";
        $r = XYXCntByHourModel::HourDataExists($dataTime, $ym);
        if($r){
            echo(" 导入失败，数据已存在!\n");
            return false;
        }
        echo "查询数据…… ";
        $data = PlayedGamesModel::gerETimeDataByHourTime($dataTime);
        if(!$data){
            echo(" no data2\n");
            return false;
        }
        echo count($data)."条\n";
        // 需要新添加的数据
        // 
        $add = [];

        // $addTotalTime = [];
        // $addActive = [];
        // $addNewReg = [];
        foreach ($data as $v) {
            $e = date("H:00", $v['e_time']);
            $a = date("H:00", $v['a_time']);

            $a2 = date("Y-m-d H:00",$v['a_time']);
            $a2time = strtotime($a2);
            $e2 = date("Y-m-d H:00",$v['e_time']);
            $e2time = strtotime($e2);
            
            
            // 需要更新的条目
            if($v['e_time']){
                if($a == $e){
                    // add
                    $ym1 = date("Ym", $e2time);
                    $date1 = date("Y-m-d H:00", $e2time);
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
                        // $add[$ym1][$date1][$v['game_id']]['etime_active_user'] = [];
                    }
                    $add[$ym1][$date1][$v['game_id']]['total_time'] += $v['e_time'] - $v['a_time'];
                    $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                    // $sql2 = "select count(uid) as new_reg_user,game_id from played_game_user where ( a_time between $startTime and $endTime ) and game_id in ( $gidsStr ) group by game_id";
                    // $newRegData = PlayedGameUserModel::db()->getAllBySQL($sql2);
                    // $add[$ym1][] = [
                    //     "game_id"=>$v['game_id'],
                    //     "total_time"=>$v['e_time'] - $v['a_time'],
                    //     "a_time"=>$e2time,
                    // ];
                }else{
                    // echo $a2."\n";
                    // echo $e2;
                    // exit;
                    // add
                    $ym2 = date("Ym", $e2time);
                    $date2 = date("Y-m-d H:00", $e2time);
                    if(!isset($add[$ym2])){
                        $add[$ym2] = [];
                    }
                    if(!isset($add[$ym2][$date2])){
                        $add[$ym2][$date2] = [];

                    }
                    if(!isset($add[$ym2][$date2][$v['game_id']])){
                        $add[$ym2][$date2][$v['game_id']]['total_time'] = 0;
                        $add[$ym2][$date2][$v['game_id']]['active_user_num'] = [];
                        $add[$ym2][$date2][$v['game_id']]['new_reg_user'] = 0;
                        // $add[$ym2][$date2][$v['game_id']]['etime_active_user'] = [];
                    }
                    $add[$ym2][$date2][$v['game_id']]['total_time'] += $v['e_time'] - $e2time;
                    $add[$ym2][$date2][$v['game_id']]['active_user_num'][] = $v['uid'];
                    // $add[$ym2][] = [
                    //     "game_id"=>$v['game_id'],
                    //     "total_time"=>$v['e_time'] - $e2time,
                    //     "a_time"=>$e2time,
                    // ];



                    // update
                    // 1.先查询gameid a_time的数据 不存在添加,存在进行更新
                    // $a2time;
                    // $a2time+3600-$v['a_time'];
                    $where = " a_time=$a2time and game_id=".$v['game_id']." limit 1";
                    $ym3 = date("Ym", $a2time);
                    $date3 = date("Y-m-d H:00", $a2time);
                    if($publishTime > strtotime($date3)){
                        echo "未上线前的数据不导入\n";
                    }else{
                        $res = XYXCntByHourModel::db()->getRow($where, "xyx_cnt_hour_".$ym3);
                        if($res){

                            $tmpData = [
                                "total_time"=>$res['total_time']+$a2time+3600-$v['a_time'],
                                "active_user_num"=>$res['active_user_num']+1,
                                // "active_uids"=>$res['active_uids'] == "" ? $v['uid'] : $res['active_uids'].",".$v['uid']
                            ];
                            
                            $res22 = XYXCntByHourModel::db()->update($tmpData, $where, "xyx_cnt_hour_".$ym3);
                            if($res22){
                                echo "update".date("Y-m-d H:i:s", $a2time)."succ\n";
                            }else{
                                echo "update ".date("Y-m-d H:i:s", $a2time)."error\n";
                            }

                        }else{

                            if(!isset($add[$ym3])){
                                $add[$ym3] = [];
                            }
                            if(!isset($add[$ym3][$date3])){
                                $add[$ym3][$date3] = [];

                            }
                            if(!isset($add[$ym3][$date3][$v['game_id']])){
                                $add[$ym3][$date3][$v['game_id']]['total_time'] = 0;
                                $add[$ym3][$date3][$v['game_id']]['active_user_num'] = [];
                                $add[$ym3][$date3][$v['game_id']]['new_reg_user'] = 0;
                                // $add[$ym3][$date3][$v['game_id']]['etime_active_user'] = [];
                            }
                            $add[$ym3][$date3][$v['game_id']]['total_time'] += $a2time+3600-$v['a_time'];
                            $add[$ym3][$date3][$v['game_id']]['active_user_num'][] = $v['uid'];
                            // $add[$ym3][] = [
                            //     "game_id"=>$v['game_id'],
                            //     "total_time"=>$a2time+3600-$v['a_time'],
                            //     "a_time"=>$a2time,
                            // ];
                        }
                    }
                    




                    if(date("H:00", $v['e_time']-3600) != $v['a_time']){
                        // update 
                        for($i = $a2time+3600; $i<$e2time; $i+=3600){
                            
                            // 1.先查询game_id a_time数据,补存在则添加,存在则进行更新
                            // $i;// a_time
                            // 3600;
                            $ym4 = date("Ym", $i);
                            $date4 = date("Y-m-d H:00", $i);
                            if($publishTime > strtotime($date3)){
                                echo "未上线前的数据不导入\n";
                            }else{
                                $where2 = " a_time=$i and game_id=".$v['game_id']." limit 1";
                                $res = XYXCntByHourModel::db()->getRow($where2, "xyx_cnt_hour_".$ym4);
                                if($res){
                                    $tmpData = [
                                        "total_time"=>$res['total_time']+3600,
                                        "active_user_num"=>$res['active_user_num']+1,
                                        // "active_uids"=>$res['active_uids'] == "" ? $v['uid'] : $res['active_uids'].",".$v['uid']
                                    ];
                                    
                                    $res33 = XYXCntByHourModel::db()->update($tmpData, $where2, "xyx_cnt_hour_".$ym4);
                                    if($res33){
                                        echo "update".date("Y-m-d H:i:s", $i)."succ\n";
                                    }else{
                                        echo "update".date("Y-m-d H:i:s", $i)."error\n";
                                    }
                                }else{
                                    if(!isset($add[$ym4])){
                                        $add[$ym4] = [];
                                    }
                                    if(!isset($add[$ym4][$date4])){
                                        $add[$ym4][$date4] = [];

                                    }
                                    if(!isset($add[$ym4][$date4][$v['game_id']])){
                                        $add[$ym4][$date4][$v['game_id']]['total_time'] = 0;
                                        $add[$ym4][$date4][$v['game_id']]['active_user_num'] = [];
                                        $add[$ym4][$date4][$v['game_id']]['new_reg_user'] = 0;
                                        // $add[$ym4][$date4][$v['game_id']]['etime_active_user'] = [];
                                    }
                                    
                                    $add[$ym4][$date4][$v['game_id']]['total_time'] += 3600;
                                    $add[$ym4][$date4][$v['game_id']]['active_user_num'][] = $v['uid'];
                                    
                                    // $add[$ym4][] = [
                                    //     "game_id"=>$v['game_id'],
                                    //     "total_time"=>3600,
                                    //     "a_time"=>$i,
                                    // ];
                                }
                            }
                            
                        }
                    }

                }

                $ymn = date("Ym", $e2time);
                $daten = date("Y-m-d H:00", $e2time);
                // $add[$ymn][$daten][$v['game_id']]['etime_active_user'][] = $v['uid'];
            }


        }

        // 整理数据
        // echo json_encode($add);
        // exit;
        foreach ($add as $ym => $data) {
            $addData = [];
            foreach ($data as $date => $gidDataMap) {
                
                $gidsStr = implode(",", array_keys($gidDataMap));
                $startTime = strtotime($date);
                $endTime = $startTime+3599;
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
                    // $d['active_uids'] = implode(",",$dataArr['active_user_num']);
                    // $sql2 = "select count(uid) as new_reg_user from played_game_user where ( a_time between $startTime and $endTime ) and game_id=".$gid;
                    // $newRegData = PlayedGameUserModel::db()->getRowBySQL($sql2);
                    $arr = array_flip($dataArr['active_user_num']);
                    $arr = array_flip($arr);
                    $arr = array_values($arr);
                    
                    $d['game_id'] = $gid;
                    // $d['active_user_num'] = $v['active_num'];
                    // $d['new_reg_user'] = $new_reg_user;
                    $d['a_time'] = strtotime($date);
                    $d['total_time'] = $dataArr['total_time'];
                    $d['active_user_num'] = count($arr);
                    $d['new_reg_user'] = $new_reg_user;


                    // if(!empty($dataArr['etime_active_user'])){
                    //     $arr2 = array_flip($dataArr['etime_active_user']);
                    //     $arr2 = array_flip($arr2);
                    //     $arr2 = array_values($arr2);
                    //     $d['etime_active_user'] = implode(",", $arr2);
                    // }else{
                    //     $d['etime_active_user'] = "";
                    // }
                    $addData[] = $d; 
                }
                
            }
            $res2 = XYXCntByHourModel::addAll($addData, "xyx_cnt_hour_".$ym);
            if($res2){
                echo "add succ\n";
            }else{
                echo "add error\n";
            }
        }

        echo "end\n";
    }

    private function importData3($time){

        $publishDate = "2019-06-01";
        $publishTime = strtotime($publishDate);

        $dataTime = strtotime(date("Y-m-d H:00", $time - 86400));
        echo date("Y-m-d H:i:s", $dataTime)." ";
        if($publishTime > $dataTime){
            echo "导入失败，表不存在";
            return false;
        }
        $ym = date("Ym", $dataTime);
        $r = XYXCntByHourModel::HourDataExists($dataTime, $ym);
        if($r){
            echo(" 导入失败，数据已存在!\n");
            return false;
        }
        echo "查询数据…… ";
        $data = PlayedGamesModel::getDataByHourTime2($dataTime);
        if(!$data){
            echo(" no data2\n");
            return false;
        }
        echo count($data)."条\n";
        // 需要新添加的数据
        // 
        $add = [];

        // $addTotalTime = [];
        // $addActive = [];
        // $addNewReg = [];
        foreach ($data as $v) {
            $e = date("H:00", $v['e_time']);
            $a = date("H:00", $v['a_time']);

            $a2 = date("Y-m-d H:00",$v['a_time']);
            $a2time = strtotime($a2);
            $e2 = date("Y-m-d H:00",$v['e_time']);
            $e2time = strtotime($e2);
            
            
            // 需要更新的条目
            if($v['e_time']){
                
                if($v['type'] == 1){
                    $ym1 = date("Ym", $e2time);
                    $date1 = date("Y-m-d H:00", $e2time);
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
                    }
                    $add[$ym1][$date1][$v['game_id']]['total_time'] += $v['e_time'] - $v['a_time'];
                    $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                }
                if($v['type'] == 2){
                    $ym1 = date("Ym", $e2time);
                    $date1 = date("Y-m-d H:00", $e2time);
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
                    }
                    $add[$ym1][$date1][$v['game_id']]['total_time'] += $v['e_time'] - $e2time;
                    $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                }
                if($v['type'] == 3){
                    $ym1 = date("Ym", $a2time);
                    $date1 = date("Y-m-d H:00", $a2time);
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
                    }
                    $add[$ym1][$date1][$v['game_id']]['total_time'] += strtotime(date("Y-m-d H:00", $v['a_time'] + 3600)) - $v['a_time'];
                    $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                }
                if($v['type'] == 4){
                    $ym1 = date("Ym", $dataTime);
                    $date1 = date("Y-m-d H:00", $dataTime);
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
                    }
                    $add[$ym1][$date1][$v['game_id']]['total_time'] += 3600;
                    $add[$ym1][$date1][$v['game_id']]['active_user_num'][] = $v['uid'];
                }
                
            }


        }

        echo "整理新注册用户……\n";
        // 整理数据
        // echo json_encode($add);
        // exit;
        foreach ($add as $ym => $data) {
            $addData = [];
            foreach ($data as $date => $gidDataMap) {
                
                $gidsStr = implode(",", array_keys($gidDataMap));
                $startTime = strtotime($date);
                $endTime = $startTime+3599;
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

                    $addData[] = $d; 
                }
                
            }
            $res2 = XYXCntByHourModel::addAll($addData, "xyx_cnt_hour_".$ym);
            if($res2){
                echo "add succ\n";
            }else{
                echo "add error\n";
            }
        }

        echo "end\n";
    }

}