<?php
//将分散的 ws 日志，汇总到一张表里面
class MakeWsLogCntByDay{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        $addTime = time();

        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";
        $tomorrow = strtotime("tomorrow");

        date("Y-m-d", $addTime);
        $dayFirstHourTime = strtotime(date("Y-m-d 00:i:s", $addTime));
        for($i = $dayFirstHourTime; $i < $tomorrow; $i += 3600) {
        	// echo date("Y-m-d H:i:s", $i)."\n";
        	$this->importData($i);
        }
        // 需要添加的数据time
        

    }

    private function time2oclock($time){
    	return strtotime(date("Y-m-d H:00", $time));
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

}



