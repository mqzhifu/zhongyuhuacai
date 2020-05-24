<?php

/**
 * @Author: Kir
 * @Date:   2019-05-16 10:06:30
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-17 15:10:41
 */


/**
 * 
 */
class GameAnalysisCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->addCss("assets/open/css/gameAnalysis.css");
		$this->addJs('assets/open/scripts/echarts.min.js');
		$this->addJs('assets/open/scripts/adAnalysis.js');
		$this->addJs('assets/open/scripts/laydate/laydate.js');
		$this->display("gameAnalysis.html", "new", "isLogin");
	}

	function getGamesData(){
		$watchType = _g("watchType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        // if(!$from || !$to){
        //     $this->outputJson(1,'no from or to');
        // }
        if($from && $to){
        	if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
	            $this->outputJson(2,'日期格式不正确');
	        }
        }
        
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        switch ($watchType) {
        	case 2:// 按周
        		$dayArr = $adService->getAllYearWeek(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y.W";
        		break;
        	case 3:// anyue
        		$dayArr = $adService->getAllMonth(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y-m";
        		break;
        	case 1://按日
        	default:
        		$dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y-m-d";
        		break;
        }

        $dayArr2 = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        $map = [];
        foreach ($dayArr2 as $value) {
            $map[$value] = [$value,0,0,0,0,0,0,0];
        }

        // 获取自己创建的游戏
        $openGameService = new OpenGamesService();
        $gameids = $openGameService->getOnLineGameidsByUid($this->_uid);
        if(empty($gameids)){
            $this->outputJson(1,'no data');
        }
        $gameidsStr = implode(",", $gameids);
        // 读取数据
        $xyx = XYXCntByDayModel::db()->getAllBySQL("select * from xyx_cnt_day where a_time between $from and $to and game_id in ($gameidsStr) ");
        // $historyXyx = XYXCntByDayModel::db()->getRowBySQL("select sum(active_user_num) as history_user_num from xyx_cnt_day where a_time<$from and game_id in ($gameidsStr) ");

        $purchase = PurchaseCntDayModel::db()->getAllBySQL("select * from purchase_cnt_day where a_time between $from and $to and game_id in ($gameidsStr)");
        $historyPur = PurchaseCntDayModel::db()->getRowBySQL("select sum(money) as history_money,sum(pay_users) as history_pay_users from purchase_cnt_day where a_time<$from and game_id in ($gameidsStr)");
        $historyPeople = PlayedGameUserModel::db()->getCount("a_time<$from and game_id in ($gameidsStr)");
        $retention = RetentionCntByDayModel::db()->getAllBySQL("select * from reg_retention_cnt_day where day_time between $from and $to and game_id in ($gameidsStr)");

        // 整理数据
        foreach ($xyx as $v) {
        	$map[date("Y-m-d", $v['a_time'])][1] += $v['new_reg_user'];
        	$map[date("Y-m-d", $v['a_time'])][2] += $v['active_user_num'];
        	$map[date("Y-m-d", $v['a_time'])][7] += $v['new_reg_user'];// 累计玩家
        }
        foreach ($purchase as $v) {
        	$map[date("Y-m-d", $v['a_time'])][3] += $v['money'];
        	$map[date("Y-m-d", $v['a_time'])][4] += $v['pay_users'];
        	$map[date("Y-m-d", $v['a_time'])][5] += $v['money'];// 累计流水
        	$map[date("Y-m-d", $v['a_time'])][6] += $v['pay_users'];// 累计付费玩家
        }
        $history_user_num = $historyPeople;
        $history_money = $historyPur['history_money'];
        $history_pay_users = $historyPur['history_pay_users'];
        foreach ($map as $key => $value) {
        	$history_money += $value[5];
        	$map[$key][5] = $history_money;

        	$history_pay_users += $value[6];
        	$map[$key][6] = $history_pay_users;

        	$history_user_num += $value[7];
	        $map[$key][7] = $history_user_num;
	    }

	    //折现和饼状
	    $gidsMap = [];
	    $pieMap = [];
		foreach ($gameids as $gid) {
			$dateMap = [];
	        foreach ($dayArr as $value) {
	            $dateMap[$value] = 0;
	        }
			$gidsMap[$gid] = $dateMap;
			$pieMap[$gid] = 0;
		}   
        switch ($type) {
            case PurchaseCntDayModel::$type_active_user_num :
            default:
                foreach ($xyx as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
                    $pieMap[$v['game_id']] += $v['active_user_num'];
                }
                break;
            case PurchaseCntDayModel::$type_new_reg_user_num :
                foreach ($xyx as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['new_reg_user'];
                    $pieMap[$v['game_id']] += $v['new_reg_user'];
                }
                break;
            case PurchaseCntDayModel::$type_single_day_income :
                foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $pieMap[$v['game_id']] += $v['money'];
                }
                break;
            case PurchaseCntDayModel::$type_retention1 :
            	foreach ($retention as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['day_time'])] += $v['retention_rate'];
                    $pieMap[$v['game_id']] += $v['retention_rate'];
                }
                break;
            case PurchaseCntDayModel::$type_avg_user_income :
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $pieMap[$v['game_id']] += $v['money'];
                }

                $gidsMap2 = [];
                $pieMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
					$pieMap2[$gid] = 0;
				}
				foreach ($xyx as $v) {
					$gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
					$pieMap2[$v['game_id']] += $v['active_user_num'];
				}

				foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
					$pieMap[$gid] = $pieMap2[$gid]==0 ? 0 : $pieMap[$gid]/$pieMap2[$gid];
				}
                break;
            case PurchaseCntDayModel::$type_permeate_rate :
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['pay_users'];
                    $pieMap[$v['game_id']] += $v['pay_users'];
                }

                $gidsMap2 = [];
                $pieMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
					$pieMap2[$gid] = 0;
				}
				foreach ($xyx as $v) {
					$gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
					$pieMap2[$v['game_id']] += $v['active_user_num'];
				}

				foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
					$pieMap[$gid] = $pieMap2[$gid]==0 ? 0 : $pieMap[$gid]/$pieMap2[$gid];
				}
                break;
            case PurchaseCntDayModel::$type_avg_pay_user_income :
            	$gidsMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
				}
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['pay_users'];
                }
                foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
				}
                break;
            
        }

        $res = GamesModel::db()->getAllBySQL("select id,name from games where id in ($gameidsStr)");
        $gidNameMap = array_column($res, "name","id");

        //折线图数据
        $lines = new LinesObj();
        $lines->dataTime = $dayArr;
        $lines->dataName = $gameids;
        $lines->dataArr = [];
        foreach ($gidsMap as $gid => $value) {
        	$item = new ItemObj();
        	$item->name = $gidNameMap[$gid];
        	$item->data = array_values($value);
        	$lines->dataArr[] = $item;
        }
        //饼状图数据
        $pieData = [];
        foreach ($pieMap as $gid => $value) {
        	$pie = new PieObj();
        	$pie->name = $gidNameMap[$gid];
        	$pie->value = $value;
        	$pieData[] = $pie;
        }

        $returnData = [
        	"lines"=>$lines,
        	"pie"=>$pieData,
        	"table"=>array_values($map)
        ];
        $this->outputJson(200, 'succ', $returnData);
        
    }

    function getLineAndPieData(){
    	$watchType = _g("watchType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        // if(!$from || !$to){
        //     $this->outputJson(1,'no from or to');
        // }
        if($from && $to){
        	if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
	            $this->outputJson(2,'日期格式不正确');
	        }
        }
        
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);
        
        $adService = new AdvertiseService();

        switch ($watchType) {
        	case 2:// 按周
        		$dayArr = $adService->getAllYearWeek(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y.W";
        		break;
        	case 3:// anyue
        		$dayArr = $adService->getAllMonth(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y-m";
        		break;
        	case 1://按日
        	default:
        		$dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        		$dateFormat = "Y-m-d";
        		break;
        }
        // 获取自己创建的游戏
        $openGameService = new OpenGamesService();
        $gameids = $openGameService->getOnLineGameidsByUid($this->_uid);
        if(empty($gameids)){
            $this->outputJson(1,'no data');
        }
        $gameidsStr = implode(",", $gameids);
        // 读取数据
        $xyx = XYXCntByDayModel::db()->getAllBySQL("select * from xyx_cnt_day where a_time between $from and $to and game_id in ($gameidsStr) ");
        // $historyXyx = XYXCntByDayModel::db()->getRowBySQL("select sum(active_user_num) as history_user_num from xyx_cnt_day where a_time<$from and game_id in ($gameidsStr) ");

        $purchase = PurchaseCntDayModel::db()->getAllBySQL("select * from purchase_cnt_day where a_time between $from and $to and game_id in ($gameidsStr)");
        // $historyPur = PurchaseCntDayModel::db()->getRowBySQL("select sum(money) as history_money,sum(pay_users) as history_pay_users from purchase_cnt_day where a_time<$from and game_id in ($gameidsStr)");

        $retention = RetentionCntByDayModel::db()->getAllBySQL("select * from reg_retention_cnt_day where day_time between $from and $to and game_id in ($gameidsStr)");
        // 整理数据

	    $gidsMap = [];
	    $pieMap = [];
		foreach ($gameids as $gid) {
			$dateMap = [];
	        foreach ($dayArr as $value) {
	            $dateMap[$value] = 0;
	        }
			$gidsMap[$gid] = $dateMap;
			$pieMap[$gid] = 0;
		}   
        switch ($type) {
            case PurchaseCntDayModel::$type_active_user_num :
            default:
                foreach ($xyx as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
                    $pieMap[$v['game_id']] += $v['active_user_num'];
                }
                break;
            case PurchaseCntDayModel::$type_new_reg_user_num :
                foreach ($xyx as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['new_reg_user'];
                    $pieMap[$v['game_id']] += $v['new_reg_user'];
                }
                break;
            case PurchaseCntDayModel::$type_single_day_income :
                foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $pieMap[$v['game_id']] += $v['money'];
                }
                break;
            case PurchaseCntDayModel::$type_retention1 :
            	foreach ($retention as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['day_time'])] += $v['retention_rate'];
                    $pieMap[$v['game_id']] += $v['retention_rate'];
                }
                break;
            case PurchaseCntDayModel::$type_avg_user_income :
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $pieMap[$v['game_id']] += $v['money'];
                }

                $gidsMap2 = [];
                $pieMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
					$pieMap2[$gid] = 0;
				}
				foreach ($xyx as $v) {
					$gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
					$pieMap2[$v['game_id']] += $v['active_user_num'];
				}

				foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
					$pieMap[$gid] = $pieMap2[$gid]==0 ? 0 : $pieMap[$gid]/$pieMap2[$gid];
				}
                break;
            case PurchaseCntDayModel::$type_permeate_rate :
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['pay_users'];
                    $pieMap[$v['game_id']] += $v['pay_users'];
                }

                $gidsMap2 = [];
                $pieMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
					$pieMap2[$gid] = 0;
				}
				foreach ($xyx as $v) {
					$gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['active_user_num'];
					$pieMap2[$v['game_id']] += $v['active_user_num'];
				}

				foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
					$pieMap[$gid] = $pieMap2[$gid]==0 ? 0 : $pieMap[$gid]/$pieMap2[$gid];
				}
                break;
            case PurchaseCntDayModel::$type_avg_pay_user_income :
            	$gidsMap2 = [];
				foreach ($gameids as $gid) {
					$dateMap = [];
			        foreach ($dayArr as $value) {
			            $dateMap[$value] = 0;
			        }
					$gidsMap2[$gid] = $dateMap;
				}
            	foreach ($purchase as $v) {
                    $gidsMap[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['money'];
                    $gidsMap2[$v['game_id']][date($dateFormat, $v['a_time'])] += $v['pay_users'];
                }
                foreach ($gidsMap as $gid => $v) {
					foreach ($v as $date => $v2) {
						$gidsMap[$gid][$date] = $gidsMap2[$gid][$date]==0 ? 0 : $gidsMap[$gid][$date]/$gidsMap2[$gid][$date];
					}
				}
                break;
            
        }

        $res = GamesModel::db()->getAllBySQL("select id,name from games where id in ($gameidsStr)");
        $gidNameMap = array_column($res, "name","id");

        //折线图数据
        $lines = new LinesObj();
        $lines->dataTime = $dayArr;
        $lines->dataName = $gameids;
        $lines->dataArr = [];
        foreach ($gidsMap as $gid => $value) {
        	$item = new ItemObj();
        	$item->name = $gidNameMap[$gid];
        	$item->data = array_values($value);
        	$lines->dataArr[] = $item;
        }
        //饼状图数据
        $pieData = [];
        foreach ($pieMap as $gid => $value) {
        	$pie = new PieObj();
        	$pie->name = $gidNameMap[$gid];
        	$pie->value = $value;
        	$pieData[] = $pie;
        }

        $returnData = [
        	"lines"=>$lines,
        	"pie"=>$pieData
        ];
        $this->outputJson(200, 'succ', $returnData);
    }

   	function getDetail(){
   		$day = _g("date");

   		if(!$day){
   			$this->outputJson(1, '未传入日期');
   		}
   		if(!FilterLib::regex($day,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
   		// 获取自己创建的游戏
        $openGameService = new OpenGamesService();
        $gameids = $openGameService->getOnLineGameidsByUid($this->_uid);
        if(empty($gameids)){
            $this->outputJson(3,'no data');
        }
        $dayTime = strtotime($day);
        $gameidsStr = implode(",", $gameids);
        // 读取数据
        $xyx = XYXCntByDayModel::db()->getAllBySQL("select * from xyx_cnt_day where a_time=$dayTime and game_id in ($gameidsStr) ");
        // $historyXyx = XYXCntByDayModel::db()->getAllBySQL("select sum(active_user_num) as history_user_num,game_id from xyx_cnt_day where a_time<$dayTime and game_id in ($gameidsStr) group by game_id ");
        $historyXyx = PlayedGameUserModel::db()->getAllBySQL("select count(uid) as history_user_num,game_id from played_game_user where a_time<$dayTime and game_id in ($gameidsStr) group by game_id");
        $purchase = PurchaseCntDayModel::db()->getAllBySQL("select * from purchase_cnt_day where a_time=$dayTime and game_id in ($gameidsStr)");
        $historyPur = PurchaseCntDayModel::db()->getAllBySQL("select sum(money) as history_money,sum(pay_users) as history_pay_users,game_id from purchase_cnt_day where a_time<$dayTime and game_id in ($gameidsStr) group by game_id ");

        $retention = RetentionCntByDayModel::db()->getAllBySQL("select * from reg_retention_cnt_day where day_time=$dayTime and game_id in ($gameidsStr)");

        $gidsMap = [];
		foreach ($gameids as $gid) {
			$gidsMap[$gid] = [$gid, 0, 0, 0, 0, 0, 0, 0];
		}


		foreach ($xyx as $v) {
			$gidsMap[$v['game_id']][1] += $v['new_reg_user'];
			$gidsMap[$v['game_id']][2] += $v['active_user_num'];
			$gidsMap[$v['game_id']][7] += $v['active_user_num'];
		}
		foreach ($purchase as $v) {
			$gidsMap[$v['game_id']][3] += $v['money'];
			$gidsMap[$v['game_id']][4] += $v['pay_users'];
			$gidsMap[$v['game_id']][5] += $v['money'];
			$gidsMap[$v['game_id']][6] += $v['pay_users'];
		}
		foreach ($historyXyx as $v) {
            $gidsMap[$v['game_id']][7] += $v['history_user_num'];   
		}
		foreach ($historyPur as $v) {
			$gidsMap[$v['game_id']][6] += $v['history_pay_users'];
            $gidsMap[$v['game_id']][5] += $v['history_money'];
		}

		$res = GamesModel::db()->getAllBySQL("select id,name from games where id in ($gameidsStr)");
        $gidNameMap = array_column($res, "name","id");
        $returnData = array_values($gidsMap);
		foreach ($returnData as &$value) {
			$value[0] = $gidNameMap[$value[0]];
		}
		$this->outputJson(200, 'succ', $returnData);
   	}

    // 限制6-1
    private function filtDate(&$from, &$to){
        $time61 = strtotime("2019-07-01");
        if($from < $time61){
            $from = $time61;
        }
        if($to < $time61){
            $to = $time61;
        }
    }
	
}

class LineObj{
    public $year;
    public $name;
    public $data_num;
    public $data_date;
}

class PieObj{
    public $value;
    public $name;
}

class LinesObj{
	public $dataTime;
	public $dataName;
	public $dataArr;
}
class ItemObj{
	public $name;
	public $type = 'line';
	public $smooth = true;
	public $data;
}