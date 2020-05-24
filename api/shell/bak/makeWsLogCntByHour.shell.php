<?php
//将分散的 ws 日志，汇总到一张表里面
class MakeWsLogCntByHour{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        $addTime = time();
        // $day = date("Ymd",strtotime(date("Ymd"))- 24 * 60 * 60 );
        // $ym = date("Ym",strtotime(date("Ymd"))- 24 * 60 * 60);

        // $addTime = strtotime("2019-04-29 18:01:59");

        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";
        // 需要添加的数据time
        
        $this->importData2($addTime);
        // // 需要插入年月
        // $ym = date("Ym", $dataTime);
        // // 需要添加的数据年月日
        // $day = date("Ymd", $dataTime);
        // // 排重
        // $item = WsCntByHourModel::getLatestItem($ym);

        // // $t1 = $this->time2oclock(isset($item['a_time'])?$item['a_time']:0);
        // // $t2 = $this->time2oclock($dataTime);
        
        // $hour = date("H:00", $dataTime);
        // $item2 = WsLogModel::getLastestItemByDayAndHour($day, $hour);
        // if(!$item2){
        // 	exit("no data1\n");
        // }

        // if(empty($item) || $dataTime != $item['a_time']){
        // 	echo "查询 $day $hour 数据\n";
        // 	$data = WsLogModel::getDataByDayAndHour($day,$hour);
        // 	if(!$data){
        // 		exit("no data2");
        // 	}

        // 	$dataCount = count($data);
        // 	echo "共计 $dataCount 条数据! \n";
        // 	$active_user = 0;
        // 	$new_reg_user = 0;
        // 	$total_time = 0;
        // 	foreach ($data as $v) {
        // 		$active_user ++;
        // 		$total_time += $v['total'];
        // 		if($this->time2oclock($v['reg_time']) == $this->time2oclock($dataTime)){
        // 			$new_reg_user ++;
        // 		}
        // 	}

        // 	echo "计算结果：\n active_user:$active_user\n new_reg_user:$new_reg_user\n total_time:$total_time\n";
        // 	$insert = array(
        //         'a_time'=>$dataTime,
        //         'active_user'=>$active_user,
        //         'new_reg_user'=>$new_reg_user,
        //         'total_time'=>$total_time,
        //     );

        //     $res2 = WsCntByHourModel::add($insert, "ws_cnt_hour_".$ym);
        //     if($res2){
        //     	echo "导入成功\n";
        //     }else{
        //     	echo "数据库插入失败\n";
        //     }
        // }else{
        // 	exit("导入失败，数据已存在\n");
        // }

    }

    private function importData($addTime){
        $dataTime = strtotime(date("Y-m-d H:00", $addTime - 86400));
        // 需要插入年月
        $ym = date("Ym", $dataTime);
        // 需要添加的数据年月日
        $day = date("Ymd", $dataTime);
        

        // $t1 = $this->time2oclock(isset($item['a_time'])?$item['a_time']:0);
        // $t2 = $this->time2oclock($dataTime);
        
        $hour = date("H:00", $dataTime);
        $selectDataTime = $day." ".$hour." ";

        $exist = WsCntByHourModel::HourDataExists($dataTime, $ym);
        if($exist){
            echo($selectDataTime." 导入失败，数据已存在!\n");
            return false;
        }
        

        echo "查询 $day $hour 数据\n";
        $data = WsLogModel::getDataByDayAndHour($day,$hour);
        if(!$data){
            echo($selectDataTime." no data2\n");
            return false;
        }

        $dataCount = count($data);
        echo "共计 $dataCount 条数据! \n";
        $active_user = 0;
        $new_reg_user = 0;
        $total_time = 0;
        foreach ($data as $v) {
            $active_user ++;
            $total_time += $v['total'];
            if($this->time2oclock($v['reg_time']) == $this->time2oclock($dataTime)){
                $new_reg_user ++;
            }
        }

        echo "计算结果：\n active_user:$active_user\n new_reg_user:$new_reg_user\n total_time:$total_time\n";
        $insert = array(
            'a_time'=>$dataTime,
            'active_user'=>$active_user,
            'new_reg_user'=>$new_reg_user,
            'total_time'=>$total_time,
        );

        $res2 = WsCntByHourModel::add($insert, "ws_cnt_hour_".$ym);
        if($res2){
            echo "导入成功\n";
            return true;
        }else{
            echo "数据库插入失败\n";
            return false;
        }
    }

    private function importData2($time){
        $dataTime = strtotime(date("Y-m-d H:00", $time - 86400));
        $ym = date("Ym", $dataTime);
        // 需要添加的数据年月日
        $day = date("Ymd", $dataTime);
        $r = WsCntByHourModel::HourDataExists($dataTime, $ym);
        if($r){
            echo(" 导入失败，数据已存在!\n");
            return false;
        }
        echo "查询数据…… ";
        $data = WsLogModel::gerETimeDataByHourTime($day, $dataTime);
        if(!$data){
            echo(" no data2\n");
            return false;
        }
        echo count($data)."条\n";
        // 需要新添加的数据
        // 
        $add = [];
        $update = [];
        foreach ($data as $v) {
            $e = date("H:00", $v['e_time']);
            $a = date("H:00", $v['a_time']);

            $a2 = date("Y-m-d H:00",$v['a_time']);
            $a2time = strtotime($a2);
            $e2 = date("Y-m-d H:00",$v['e_time']);
            $e2time = strtotime($e2);
            
            
            // 需要更新的条目
            if($v['e_time']){
                $this->time2oclock($v['reg_time']);
                $e2time;
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
                    if(!isset($add[$ym1][$date1][$v['uid']])){
                        $add[$ym1][$date1][$v['uid']] = [];
                        $add[$ym1][$date1][$v['uid']]['total_time'] = 0;
                        $add[$ym1][$date1][$v['uid']]['active_user_num'] = 0;
                        $add[$ym1][$date1][$v['uid']]['new_reg_user'] = 0;
                        // $add[$ym1][$date1][$v['uid']]['etime_active_user'] = [];
                    }
                    if($this->time2oclock($v['reg_time']) == $e2time){
                        $add[$ym1][$date1][$v['uid']]['new_reg_user'] = 1;
                    }
                    $add[$ym1][$date1][$v['uid']]['total_time'] += $v['e_time'] - $v['a_time'];
                    $add[$ym1][$date1][$v['uid']]['active_user_num'] = 1;
                    
                }else{
                    
                    $ym2 = date("Ym", $e2time);
                    $date2 = date("Y-m-d H:00", $e2time);
                    if(!isset($add[$ym2])){
                        $add[$ym2] = [];
                    }
                    if(!isset($add[$ym2][$date2])){
                        $add[$ym2][$date2] = [];
                        
                    }
                    if(!isset($add[$ym2][$date2][$v['uid']])){
                        $add[$ym2][$date2][$v['uid']] = [];
                        $add[$ym2][$date2][$v['uid']]['total_time'] = 0;
                        $add[$ym2][$date2][$v['uid']]['active_user_num'] = 0;
                        $add[$ym2][$date2][$v['uid']]['new_reg_user'] = 0;
                        // $add[$ym2][$date2][$v['uid']]['etime_active_user'] = [];
                    }
                    if($this->time2oclock($v['reg_time']) == $e2time){
                        $add[$ym2][$date2][$v['uid']]['new_reg_user'] = 1;
                    }
                    $add[$ym2][$date2][$v['uid']]['total_time'] += $v['e_time'] - $e2time;
                    $add[$ym2][$date2][$v['uid']]['active_user_num'] = 1;
                    
                    $where = " a_time=$a2time limit 1";
                    $ym3 = date("Ym", $a2time);
                    $date3 = date("Y-m-d H:00", $a2time);
                    $res = WsCntByHourModel::db()->getRow($where, "ws_cnt_hour_".$ym3);
                    if($res){

                        $tmpData = [
                            "total_time"=>$res['total_time']+$a2time+3600-$v['a_time'],
                            "active_user"=>$res['active_user'] + 1,
                            "etime_active_user"=>$res['etime_active_user']=="" ? $v['uid'] : $res['etime_active_user'].",".$v['uid']
                        ];
                        if($this->time2oclock($v['reg_time']) == $e2time){
                            $tmpData['new_reg_user'] = $res['new_reg_user'] + 1;
                        }
                        $res22 = WsCntByHourModel::db()->update($tmpData, $where, "ws_cnt_hour_".$ym3);
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
                        if(!isset($add[$ym3][$date3][$v['uid']])){
                            $add[$ym3][$date3][$v['uid']] = [];
                            $add[$ym3][$date3][$v['uid']]['total_time'] = 0;
                            $add[$ym3][$date3][$v['uid']]['active_user_num'] = 0;
                            $add[$ym3][$date3][$v['uid']]['new_reg_user'] = 0;
                            // $add[$ym3][$date3][$v['uid']]['etime_active_user'] = [];
                        }
                        if($this->time2oclock($v['reg_time']) == $e2time){
                            $add[$ym3][$date3][$v['uid']]['new_reg_user'] = 1;
                        }
                        $add[$ym3][$date3][$v['uid']]['total_time'] += $a2time+3600-$v['a_time'];
                        $add[$ym3][$date3][$v['uid']]['active_user_num'] = 1;
                        
                    }




                    if(date("H:00", $v['e_time']-3600) != $v['a_time']){
                        // update 
                        for($i = $a2time+3600; $i<$e2time; $i+=3600){
                            
                            
                            $ym4 = date("Ym", $i);
                            $date4 = date("Y-m-d H:00", $i);
                            $where2 = " a_time=$i limit 1";
                            $res = WsCntByHourModel::db()->getRow($where2, "ws_cnt_hour_".$ym4);
                            if($res){
                                $tmpData = [
                                    "total_time"=>$res['total_time']+3600,
                                    "active_user"=>$res['active_user'] + 1,
                                    "etime_active_user"=>$res['etime_active_user']=="" ? $v['uid'] : $res['etime_active_user'].",".$v['uid']
                                ];
                                if($this->time2oclock($v['reg_time']) == $i){
                                    $tmpData['new_reg_user'] = $res['new_reg_user'] + 1;
                                }
                                
                                $res33 = WsCntByHourModel::db()->update($tmpData, $where2, "ws_cnt_hour_".$ym4);
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
                                if(!isset($add[$ym4][$date4][$v['uid']])){
                                    $add[$ym4][$date4][$v['uid']] = [];
                                    $add[$ym4][$date4][$v['uid']]['total_time'] = 0;
                                    $add[$ym4][$date4][$v['uid']]['active_user_num'] = 0;
                                    $add[$ym4][$date4][$v['uid']]['new_reg_user'] = 0;
                                    // $add[$ym4][$date4][$v['uid']]['etime_active_user'] = [];
                                }
                                if($this->time2oclock($v['reg_time']) == $i){
                                    $add[$ym4][$date4][$v['uid']]['new_reg_user'] = 1;
                                }
                                
                                $add[$ym4][$date4][$v['uid']]['total_time'] += 3600;
                                $add[$ym4][$date4][$v['uid']]['active_user_num'] = 1;
                                
                                
                            }
                        }
                    }

                }

                // $ymn = date("Ym", $e2time);
                // $daten = date("Y-m-d H:00", $e2time);
                // $add[$ymn][$daten][$v['uid']]['etime_active_user'][] = $v['uid'];
            }


        }

        // 整理数据
        // echo json_encode($add);
        // exit;
        foreach ($add as $ym => $data) {
            $addData = [];
            foreach ($data as $date => $uidDataMap) {
                
                $item = [
                    "total_time"=>0,
                    "active_user"=>0,
                    "new_reg_user"=>0,
                    "etime_active_user"=>"",
                    "a_time"=>strtotime($date)
                ];
                $n = 0;
                foreach ($uidDataMap as $uid => $dataArr) {
                    # code...
                    // $arr = array_flip($dataArr['active_user_num']);
                    // $arr = array_flip($arr);
                    // $arr = array_values($arr);
                    // $d = [];
                    // $d['a_time'] = strtotime($date);
                    // $d['total_time'] = $dataArr['total_time'];
                    // $d['active_user'] = count($arr);
                    // $d['new_reg_user'] = $dataArr['new_reg_user'];

                    // if(!empty($dataArr['etime_active_user'])){
                    //     $arr2 = array_flip($dataArr['etime_active_user']);
                    //     $arr2 = array_flip($arr2);
                    //     $arr2 = array_values($arr2);
                    //     $d['etime_active_user'] = implode(",", $arr2);
                    // }else{
                    //     $d['etime_active_user'] = "";
                    // }
                    // $addData[] = $d; 
                    $item['total_time'] += $dataArr['total_time'];
                    $item['active_user'] += 1;
                    $item['new_reg_user'] += $dataArr['new_reg_user'];
                    $str = "";
                    if($n>0){
                        $str .= ",";
                    }
                    $str .= $uid;
                    $item['etime_active_user'] .= $str;
                    $n++;
                }
                $addData[] = $item;
                
                
            }
            $res2 = WsCntByHourModel::addAll($addData, "ws_cnt_hour_".$ym);
            if($res2){
                echo "add succ\n";
            }else{
                echo "add error\n";
            }
        }

        // foreach ($update as $ym => $data) {
        //     $addData = [];
        //     foreach ($data as $date => $uidDataMap) {
                
        //         $item = [
        //             "total_time"=>0,
        //             "active_user"=>0,
        //             "new_reg_user"=>0,
        //             "etime_active_user"=>"",
        //             "a_time"=>strtotime($date)
        //         ];
        //         $n = 0;
        //         foreach ($uidDataMap as $uid => $dataArr) {
        //             # code...
        //             // $arr = array_flip($dataArr['active_user_num']);
        //             // $arr = array_flip($arr);
        //             // $arr = array_values($arr);
        //             // $d = [];
        //             // $d['a_time'] = strtotime($date);
        //             // $d['total_time'] = $dataArr['total_time'];
        //             // $d['active_user'] = count($arr);
        //             // $d['new_reg_user'] = $dataArr['new_reg_user'];

        //             // if(!empty($dataArr['etime_active_user'])){
        //             //     $arr2 = array_flip($dataArr['etime_active_user']);
        //             //     $arr2 = array_flip($arr2);
        //             //     $arr2 = array_values($arr2);
        //             //     $d['etime_active_user'] = implode(",", $arr2);
        //             // }else{
        //             //     $d['etime_active_user'] = "";
        //             // }
        //             // $addData[] = $d; 
        //             $item['total_time'] += $dataArr['total_time'];
        //             $item['active_user'] += 1;
        //             $item['new_reg_user'] += $dataArr['new_reg_user'];
        //             $str = "";
        //             if($n>0){
        //                 $str .= ",";
        //             }
        //             $str .= $uid;
        //             $item['etime_active_user'] .= $str;
        //             $n++;
        //         }
        //         $addData[] = $item;
                
                
        //     }
        //     $res2 = WsCntByHourModel::addAll($addData, "ws_cnt_hour_".$ym);
        //     if($res2){
        //         echo "add succ\n";
        //     }else{
        //         echo "add error\n";
        //     }
        // }
        echo "end\n";
    }

    private function time2oclock($time){
    	return strtotime(date("Y-m-d H:00", $time));
    }

}



