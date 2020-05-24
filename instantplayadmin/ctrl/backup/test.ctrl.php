<?php
class TestCtrl extends BaseCtrl{

	public function testGenerate(){
		$this->aggregateByDay("2019-06-13");
	}
	private function aggregateByDay($day){
        // 判重
        $count = InnerAdDetailsByDayModel::db()->getCount("stat_datetime='".$day."'");
        if($count){
            echo $day."数据已存在\n";
            LogLib::appWriteFileHash("========aggregateAD======$day 数据已存在");
            return false;
        }
    	// 穿山甲广告
    	$sql = "select b.uid,b.inner_ad_id,b.game_id,sum(a.click) click,sum(a.cost) cost,sum(a.`show`) `show` from (select ad_slot_id,cost,click,`show` from open_advertise_income where stat_datetime='".$day."') a inner join (select am.inner_ad_id,am.outer_ad_id,oa.game_id,oa.uid from open_advertise oa left join ad_map am on oa.id=am.inner_ad_id where oa.status!=".OpenAdvertiseModel::$status_del." and am.status=1 group by am.outer_ad_id) b on a.ad_slot_id=b.outer_ad_id group by b.inner_ad_id";
        $total = advertiseIncomeModel::db()->getAllBySQL($sql);
        if(empty($total)){
            echo $day."穿山甲无可聚合数据\n";
            LogLib::appWriteFileHash("========aggregateAD======$day 无可聚合数据");
            return false;
        }
        $yesterday = date("Y-m-d", strtotime($day." -1 day"));
        // 获取历史暗扣比例
        $gameIdsStr = implode(',', array_unique(array_column($total, 'game_id')));
        $historyData = InnerAdDetailsByDayModel::db()->getAllBySQL("select click_cut_p,cost_cut_p,show_cut_p,game_id from inner_ad_details_byday where stat_datetime='".$yesterday."' and game_id in ($gameIdsStr) group by game_id");
        $historyMap = [];
        foreach ($historyData as $v) {
            $historyMap[$v['game_id']] = $v;
        }
        // 组装今日暗扣比例
        $addData = [];
        $addTime = time();
        foreach ($total as $value) {
            $click_percent = 0;
            $cost_percent = 0;
            $show_percent = 0;
            if(isset($historyMap[$value['game_id']])){
                if(isset($historyMap[$value['game_id']]['click_cut_p'])){
                    $click_percent = $historyMap[$value['game_id']]['click_cut_p'];
                }

                if(isset($historyMap[$value['game_id']]['cost_cut_p'])){
                    $cost_percent = $historyMap[$value['game_id']]['cost_cut_p'];
                }

                if(isset($historyMap[$value['game_id']]['show_cut_p'])){
                    $show_percent = $historyMap[$value['game_id']]['show_cut_p'];
                }
                
            }
        	$add = [
                'uid'=>$value['uid'],
        		'inner_ad_id'=>$value['inner_ad_id'],
        		'game_id'=>$value['game_id'],
        		'cost'=>$value['cost'],
        		'click'=>$value['click'],
        		'show'=>$value['show'],
        		'cut_cost'=>$value['cost']*(1-$cost_percent),
        		'cut_click'=>$value['click']*(1-$click_percent),
        		'cut_show'=>$value['show']*(1-$show_percent),
                'click_cut_p'=>$click_percent,
                'cost_cut_p'=>$cost_percent,
                'show_cut_p'=>$show_percent,
        		'stat_datetime'=>$day,
        		'a_time'=>$addTime
        	];
        	$addData[] = $add;
        }

        if($addData){
            $res = InnerAdDetailsByDayModel::db()->addAll($addData);
            if($res){
                echo $day."聚合成功".count($addData)."数据";
                LogLib::appWriteFileHash("========aggregateAD======$day 聚合成功".count($addData)."数据");
                return true;
            }
        }
        
    }

    public function xyxday(){

        $addTime = time();

        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";
        $addTime = 1560528012;
        $this->importData($addTime);        

    }
    private function importData($addTime){
        // 一天前得当天0点时间
        $timePoint = strtotime(date("Y-m-d", $addTime - 86400));
        $pointDate = date("Y-m-d", $timePoint);
        $where = " a_time=$timePoint ";
        $count = XYXCntByDayModel::db()->getCount($where);
        if($count){
            echo "导入失败，数据已存在！\n";
            return false;
        }else{

            $startTime = $timePoint;
            $endTime = $startTime+86399;
            // $sql = "select game_id,sum(e_time-a_time) as total_time,count(distinct uid) as active_user_num,count(uid) as clickNum from played_games where e_time!=0 and a_time between $startTime and $endTime group by game_id";
            
            $sql = "select a.game_id,sum(a.e_time-a.a_time) as total_time,count(distinct a.uid) as active_user_num,count(a.uid) as clickNum,count( IF(b.os ='ios' ,b.os, null)) as ios_click,count( IF(b.os ='android' ,b.os, null)) as android_click,count( DISTINCT IF(b.os ='ios' ,a.uid, null)) as ios_user_num,count( DISTINCT IF(b.os ='android' ,a.uid, null)) as android_user_num from (select uid,a_time,e_time,game_id from played_games where e_time!=0 and a_time between $startTime and $endTime) a left join (select  uid,os from login group by uid) b on a.uid=b.uid group by a.game_id"; 
            $data = PlayedGamesModel::db()->getAllBySQL($sql);
            if($data){
                $count = count($data);
                echo "查询".$pointDate." ".$count."条数据\n";

                $gidsStr = implode(",", array_column($data, "game_id"));
                $sql2 = "select count(uid) as new_reg_user,game_id from played_game_user where ( a_time between $startTime and $endTime ) and game_id in ( $gidsStr ) group by game_id";
                $newRegData = PlayedGameUserModel::db()->getAllBySQL($sql2);

                $addData = [];
                foreach ($data as $v) {
                    
                    $d = [];
                    $d['game_id'] = $v['game_id'];
                    $d['new_reg_user'] = 0;
                    $d['total_time'] = $v['total_time'];
                    $d['active_user_num'] = $v['active_user_num'];
                    $d['click_num'] = $v['clickNum'];
                    $d['a_time'] = $timePoint;
                    $d['ios_click'] = $v['ios_click'];
                    $d['android_click'] = $v['android_click'];
                    $d['ios_user_num'] = $v['ios_user_num'];
                    $d['android_user_num'] = $v['android_user_num'];
                    foreach ($newRegData as $v2) {
                        if($v2['game_id'] == $v['game_id']){
                            $d['new_reg_user'] = $v2['new_reg_user'];
                        }
                    }

                    $addData[] = $d;
                }

                $rs = XYXCntByDayModel::db()->addAll($addData);
                if($rs){
                    echo "add succ(".$count."/".count($addData).")\n";
                }else{
                    echo "add error\n";
                }


            }else{
                echo "无数据\n";
                return false;
            }
        }
    }

    public function xyxhour(){
        $addTime = time();

        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";
        $addTime = 1560656108;
        $this->importData2($addTime);   
    }
    public function xyxhourday(){
        $dayFirstHourTime = strtotime(date("Y-m-d 00:i:s", 1560528012));
        $tomorrow = strtotime(date("Y-m-d 00:i:s", 1560528012+86400));
        for($i = $dayFirstHourTime; $i < $tomorrow; $i += 3600) {
            // echo date("Y-m-d H:i:s", $i)."\n";
            $this->importData2($i);
        }
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
        $r = XYXCntByHourModel::HourDataExists($dataTime, $ym);
        if($r){
            echo(" 导入失败，数据已存在!\n");
            return false;
        }
        echo "查询数据…… ";
        var_dump($dataTime);
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
                        $add[$ym1][$date1][$v['game_id']]['etime_active_user'] = [];
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
                        $add[$ym2][$date2][$v['game_id']]['etime_active_user'] = [];
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
                                "active_uids"=>$res['active_uids'] == "" ? $v['uid'] : $res['active_uids'].",".$v['uid']
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
                                $add[$ym3][$date3][$v['game_id']]['etime_active_user'] = [];
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
                                        "active_uids"=>$res['active_uids'] == "" ? $v['uid'] : $res['active_uids'].",".$v['uid']
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
                                        $add[$ym4][$date4][$v['game_id']]['etime_active_user'] = [];
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
                $add[$ymn][$daten][$v['game_id']]['etime_active_user'][] = $v['uid'];
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
                    $d['active_uids'] = implode(",",$dataArr['active_user_num']);
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


                    if(!empty($dataArr['etime_active_user'])){
                        $arr2 = array_flip($dataArr['etime_active_user']);
                        $arr2 = array_flip($arr2);
                        $arr2 = array_values($arr2);
                        $d['etime_active_user'] = implode(",", $arr2);
                    }else{
                        $d['etime_active_user'] = "";
                    }
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

    public function apphour(){
        // $addTime = 1560656309;
        // $this->importData3($addTime);
        $dayFirstHourTime = strtotime(date("Y-m-d 00:i:s", 1560656309));
        $tomorrow = strtotime(date("Y-m-d 00:i:s", 1560656309+86400));
        for($i = $dayFirstHourTime; $i < $tomorrow; $i += 3600) {
            // echo date("Y-m-d H:i:s", $i)."\n";
            $this->importData3($i);
        }
    }
    private function importData3($time){
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

    public function wslogTestData(){
        $add = [];
        $uid = 100001;
        $a_time = 1560569909;
        $e_time = 1560570509;
        for($i=0;$i<1000;$i++){
            $add[] = ['uid'=>($uid+$i),'a_time'=>$a_time,'e_time'=>$e_time];
        }
        WsLogModel::db()->addAll($add,"ws_log_20190615");
    }

    public function xyxPlayTestData(){
        $add = [];
        $uid = 100001;
        $a_time = 1560569909;
        $e_time = 1560570509;
        for($i=0;$i<1000;$i++){
            $a_time1 = $a_time + rand(1,100);
            $e_time1 = $e_time + rand(1,50);
            $add[] = ['uid'=>($uid+$i),'a_time'=>$a_time1,'e_time'=>$e_time1,'game_id'=>1];
        }
        PlayedGamesModel::db()->addAll($add);
    }

    public function aaa(){
        $addTime = time();
        $dataTime = strtotime(date("Y-m-d H:00", $addTime - 86400));
        echo $dataTime;
    }

    public function testGoogleAdData(){
        $googleService = new GoogleAdService();
        $googleService->persistenceADDataByDate("2019-06-22");
    }

    public function phpinfo11(){
        phpinfo();
    }


}