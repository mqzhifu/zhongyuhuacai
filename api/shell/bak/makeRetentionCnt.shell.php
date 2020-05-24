<?php

/**
 * @Author: xuren
 * @Date:   2019-05-09 09:35:10
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 20:25:22
 */
class MakeRetentionCnt{
	function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');
        $t1 = time();
        $addTime = time();
        // $addTime = strtotime("2019-05-08 09:35:10");

        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";

		$this->importData($addTime);        
		$t2 = time();
		echo "脚本执行了".($t2-$t1)."秒\n";
    }

    private function importData($addTime){
    	// 一天前得当天0点时间
    	$timePoint = strtotime(date("Y-m-d", $addTime - 86400));
    	$pointDate = date("Y-m-d", $timePoint);
    	$where = " day_time=$timePoint ";
    	$count = RetentionCntByDayModel::db()->getCount($where);
    	if($count){
    		echo "数据已存在，插入失败\n";
    		return false;
    	}else{
    		// 注册日期范围
    		$reg_day_start = $timePoint;
    		$reg_day_end = $timePoint + 86399;
    		// 次流
    		$day1_start = $reg_day_start + 86400;
    		$day1_end = $reg_day_end + 86400;
    		// 3日留存
    		$day3_start = $reg_day_start + 86400*3;
    		$day3_end = $reg_day_end + 86400*3;
    		// 3日留存
    		$day7_start = $reg_day_start + 86400*7;
    		$day7_end = $reg_day_end + 86400*7;

    		$sql = "select reg.game_id,count(distinct reg.uid) new_user_num,count(distinct r1.uid) retention_num,count(distinct r1.uid)/count(distinct reg.uid) retention_rate,count(distinct r3.uid) retention3_num,count(distinct r3.uid)/count(distinct reg.uid) retention3_rate,count(distinct r7.uid) retention7_num,count(distinct r7.uid)/count(distinct reg.uid) retention7_rate from (select uid,game_id from played_game_user where a_time between $reg_day_start and $reg_day_end) reg left join (select game_id,uid from played_games where a_time between $day1_start and $day1_end) r1 on reg.uid=r1.uid and reg.game_id=r1.game_id left join (select game_id,uid from played_games where a_time between $day1_start and $day3_end) r3 on reg.uid=r3.uid and reg.game_id=r3.game_id left join (select game_id,uid from played_games where a_time between $day1_start and $day7_end) r7 on reg.uid=r7.uid and reg.game_id=r7.game_id group by reg.game_id";

    		$res = PlayedGameUserModel::db()->getAllBySQL($sql);

    		$t1 = time();
    		foreach ($res as &$v) {
    			$v['day_time'] = $timePoint;
    			$v['a_time'] = $t1;
    			$v['u_time'] = $t1;
    		}

    		$res2 = RetentionCntByDayModel::db()->addAll($res);
    		if($res2){
    			echo "导入成功".count($res)."\n";
    			// return true;
    		}else{
    			echo "导入失败\n";
    			// return false;
    		}

    		for($i=0;$i<8;$i++){
    			$timePoint = $timePoint - 86400;
    			$pointDate = date("Y-m-d", $timePoint);
	    		// 注册日期范围
	    		$reg_day_start = $timePoint;
	    		$reg_day_end = $timePoint + 86399;
	    		// 次流
	    		$day1_start = $reg_day_start + 86400;
	    		$day1_end = $reg_day_end + 86400;
	    		// 3日留存
	    		$day3_start = $reg_day_start + 86400*3;
	    		$day3_end = $reg_day_end + 86400*3;
	    		// 3日留存
	    		$day7_start = $reg_day_start + 86400*7;
	    		$day7_end = $reg_day_end + 86400*7;

	    		$sql = "select reg.game_id,count(distinct reg.uid) new_user_num,count(distinct r1.uid) retention_num,count(distinct r1.uid)/count(distinct reg.uid) retention_rate,count(distinct r3.uid) retention3_num,count(distinct r3.uid)/count(distinct reg.uid) retention3_rate,count(distinct r7.uid) retention7_num,count(distinct r7.uid)/count(distinct reg.uid) retention7_rate from (select uid,game_id from played_game_user where a_time between $reg_day_start and $reg_day_end) reg left join (select game_id,uid from played_games where a_time between $day1_start and $day1_end) r1 on reg.uid=r1.uid and reg.game_id=r1.game_id left join (select game_id,uid from played_games where a_time between $day1_start and $day3_end) r3 on reg.uid=r3.uid and reg.game_id=r3.game_id left join (select game_id,uid from played_games where a_time between $day1_start and $day7_end) r7 on reg.uid=r7.uid and reg.game_id=r7.game_id group by reg.game_id";

	    		$res = PlayedGameUserModel::db()->getAllBySQL($sql);
	    		$u_time = time();
	    		foreach ($res as &$v) {
	    			$gid = $v['game_id'];
	    			$v['u_time'] = $u_time;
	    			unset($v['new_user_num']);
	    			unset($v['game_id']);
	    			$res2 = RetentionCntByDayModel::db()->update($v," game_id=$gid and day_time=$timePoint limit 1");
		    		if($res2){
		    			echo "更新".$pointDate."成功".$gid." ".$v['retention_num']." ".$v['retention_rate']." ".$v['retention3_num']." ".$v['retention3_rate']." ".$v['retention7_num']." ".$v['retention7_rate']."\n";
		    		}else{
		    			echo "数据不存在更新".$pointDate."失败".$gid." ".$v['retention_num']." ".$v['retention_rate']." ".$v['retention3_num']." ".$v['retention3_rate']." ".$v['retention7_num']." ".$v['retention7_rate']."\n";
		    		}
	    		}
    		}

    	}

        echo "脚本执行结束\n";

    	
    	
    }
}