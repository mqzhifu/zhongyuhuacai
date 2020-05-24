<?php

/**
 * @Author: xuren
 * @Date:   2019-05-06 18:25:55
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 16:00:21
 */
class MakePurchaseCntByDay{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        $execTime = time();
        // $execTime = strtotime("2019-03-21 19:00");
        $dataTime = $execTime - 86400;//昨天
        echo "脚本执行时间".date("Y-m-d H:i:s", $execTime);
        $this->importData($dataTime);
    }

    private function importData($dataTime){
    	$startTime = strtotime(date("Y-m-d", $dataTime));
    	$endTime = $startTime + 86399;
    	
    	$where = " a_time between $startTime and $endTime ";
    	$count = PurchaseCntDayModel::db()->getCount($where);
    	if($count){
    		echo "数据已存在\n";
    		LogLib::appWriteFileHash("========MakePurchaseCntByDay======"."数据已存在");
    		return false;
    	}else{
    		// 获取数据1
	    	$sql = "select os_type,game_id,sum(money) as total_money,count(distinct uid) as pay_users from games_goods_order where (done_time between $startTime and $endTime ) and status=2 group by game_id,os_type ";
	    	$res = GamesGoodsOrderModel::db()->getAllBySQL($sql);
	    	$all = count($res);
	    	echo "查询".$all."条数据\n";
	    	LogLib::appWriteFileHash("========MakePurchaseCntByDay======"."查询".$all."条数据");
			// 获取数据2
			$sql2 = "select game_id,count(uid) as first_pay_users from first_pay_user where (a_time between $startTime and $endTime ) ";
			$res2 = FirstPayUserModel::db()->getAllBySQL($sql2);

			$addData = [];
			foreach ($res as $v) {
				$d = [
					"game_id"=>$v['game_id'],
					"money"=>$v['total_money'],
					"pay_users"=>$v['pay_users'],
					"os_type"=>$v['os_type'],
					"a_time"=>$startTime,
				];
				foreach ($res2 as $v2) {
					if($v['game_id'] == $v2['game_id']){
						$d['first_pay_users'] = $v2['first_pay_users'];
					}else{
						$d['first_pay_users'] = 0;
					}
				}
				$addData[] = $d;
			}
			$res3 = PurchaseCntDayModel::db()->addAll($addData);
			if($res3){
				echo "插入成功（".count($addData)."/".$all."）\n";
				LogLib::appWriteFileHash("========MakePurchaseCntByDay======"."插入成功（".count($addData)."/".$all."）");
				return true;
			}else{
				echo "db insert error\n";
				LogLib::appWriteFileHash("========MakePurchaseCntByDay======"."db insert error");
				return false;
			}
    	}

    }

}