<?php

/**
 * 基于已有的订单数据和广告收入数据聚合测试数据（测试使用）
 * @Author: xuren
 * @Date:   2019-06-05 17:29:02
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 17:54:26
 */
class generateTestSettlementData{
	function __construct($c){
        $this->commands = $c;
    }
    public function run($attr){
        ini_set('display_errors','On');

        // $day = date("Y-m-d");
        // $yesterday = date("Y-m-d", strtotime($day." -1 day"));
        // $dataTime = strtotime($yesterday);
        // $day = "2019-05-22";
        // $day = "2019-04-15";
        $this->aggregateByDay("2019-05-30");
        $this->importData(1557849600);
        $this->exe("2019-06-01");
    }

    //聚合广告数据
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
        // 获取历史暗扣比例
        $gameIdsStr = implode(',', array_unique(array_column($total, 'game_id')));
        $historyData = InnerAdDetailsByDayModel::db()->getAllBySQL("select click_cut_p,cost_cut_p,show_cut_p,game_id from inner_ad_details_byday where stat_datetime='".$day."' and game_id in ($gameIdsStr) group by game_id");
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
                isset($value['game_id']['click_cut_p']) ? $click_percent = $value['game_id']['click_cut_p'] : 1;
                isset($value['game_id']['cost_cut_p']) ? $cost_percent = $value['game_id']['cost_cut_p'] : 1;
                isset($value['game_id']['show_cut_p']) ? $show_percent = $value['game_id']['show_cut_p'] : 1;
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

    //聚合内购数据
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


    private function exe($day){
        $nowTime = time();
        $items = [];

        $lastMonthTime = strtotime($day . ' -1 month');
        $bill_period = date('Y-m', $lastMonthTime);
        $bill_period2 = date('Ym', $lastMonthTime);

        $lastMonthFirstDay = date('Y-m-d', strtotime($day . ' -1 month'));
        $lastMonthLastDay  = date('Y-m-d', strtotime($day . ' -1 day'));
        echo "查詢".$lastMonthFirstDay."數據\n";
        LogLib::appWriteFileHash("========generateSettleData======查询 $lastMonthFirstDay 数据");
        $res2 = IncomeModel::db()->getRowBySql("select * from `income` where settlement_interval=$lastMonthTime limit 1");
        // $dateStr2 = date('Y-m-d', $res2['settlement_interval']);
        // $nowDate2 = date('Y-m-d', $nowTime);
        
        if(!$res2){
            
            // 广告
            // $items = advertiseIncomeModel::getMonthXYXData($lastMonthFirstDay);
            $items = $this->getAdData($lastMonthFirstDay, $lastMonthLastDay);
            $total = 0;
            $total3 = 0;
            foreach ($items as $v) {
                $total += $v['original_revenue'];
                $total3 += $v['cut_revenue'];
            }

            $data = [];
            $data['type'] = IncomeModel::$income_type_ad;
            $data['settlement_interval'] = $lastMonthTime;
            $data['estimate_income'] = $total;
            $data['cuted_money'] = $total - $total3;
            $data['divide_money'] = $total3;
            // $data['cut_percent'] = 0.05;
            $data['u_time'] = $nowTime;
            $data['a_time'] = $nowTime;
            $res = IncomeModel::db()->add($data);
            if($res){
                echo "income插入广告数据1条\n";
                LogLib::appWriteFileHash("========generateSettleData====== income插入一条 $lastMonthFirstDay广告数据");
            }else{
                echo "income插入广告数据失败\n";
                LogLib::appWriteFileHash("========generateSettleData====== income插入 $lastMonthFirstDay广告数据失败");
            }

            // 内购
            $total2 = 0;
            $purchaseDataSet = $this->getOrderSet($lastMonthFirstDay, $lastMonthLastDay);
            foreach ($purchaseDataSet as $value) {
                $total2 += $value['total'];
            }

            $data['type'] = IncomeModel::$income_type_purchase;
            $data['settlement_interval'] = $lastMonthTime;
            $data['estimate_income'] = $total2;
            $data['cuted_money'] = 0;
            $data['divide_money'] = $total2;
            // $data['cut_percent'] = 0;
            $data['u_time'] = $nowTime;
            $data['a_time'] = $nowTime;
            $res = IncomeModel::db()->add($data);
            if($res){
                echo "income插入内购数据1条\n";
                LogLib::appWriteFileHash("========generateSettleData====== income插入一条 $lastMonthFirstDay内购数据");
            }else{
                echo "income插入内购数据失败\n";
                LogLib::appWriteFileHash("========generateSettleData====== income插入 $lastMonthFirstDay内购数据失败");
            }

        }else{
            echo "income数据已存在，插入失败\n";
            LogLib::appWriteFileHash("========generateSettleData====== income 已存在$lastMonthFirstDay数据，插入失败");
        }

        // 向bill表插入数据
        $res = BillModel::db()->getRowBySql("select * from `game_bills` where bill_period=$lastMonthTime limit 1");
        // $datetime = $res['bill_period'];
        if(!$res){
            $data = $this->getAdData($lastMonthFirstDay, $lastMonthLastDay);
            $data2 = $this->getPurchaseData2($lastMonthFirstDay, $lastMonthLastDay);
            $all = [];
            // 准备广告结算数据
            $gameIds = array_column($data,'game_id');
            $gameIds2 = array_column($data2,'game_id');
            $gameIds = array_unique(array_merge($gameIds,$gameIds2));
            if(empty($gameIds)){
                echo "无bills数据\n";
                LogLib::appWriteFileHash("========generateSettleData====== 无bills数据");
                return false;
            }
            $gameIdsStr = implode(',', $gameIds);

            $sql2 = "select * from game_finance where game_id in ($gameIdsStr)";
            $financeList = FinanceModel::db()->getAllBySQL($sql2);
            $divideArrMap = [];
            foreach ($gameIds as $gid) {
                $divideArrMap[$gid][FinanceModel::$type_ad] = ['slotting_allowance'=>0, 'divide'=>0];
                $divideArrMap[$gid][FinanceModel::$type_purchase] = ['slotting_allowance'=>0, 'divide'=>0];
            }
            foreach ($financeList as $value) {
                $divideArrMap[$value['game_id']][$value['finance_type']] = $value;
            }
            // 获取games表得uidlist
            $sql3 = "select uid,id from games where id in ($gameIdsStr)";
            $gameUidList = GamesModel::db()->getAllBySQL($sql3);
            // 获取指定uid得open_finance表得税类型
            $uids = array_column($gameUidList, 'uid');
            $uidsStr = implode(',', $uids);
            $idUidMap = array_column($gameUidList, 'uid', 'id');
            $idTaxRateMap = [];
            foreach ($idUidMap as $gameId => $uid) {
                $idTaxRateMap[$gameId] = 0;
            }
            
            $sql4 = "select tax_type,invoice_type,uid from open_finance where uid in ($uidsStr)";
            $openFinanceList = GamesModel::db()->getAllBySQL($sql4);
            // 转换为[gameid=>['tax_type'=>1,'invoice_type'=>1,'uid'=>1]]形式方便q取用
            foreach ($idUidMap as $gameId => $uid) {
                foreach ($openFinanceList as $openFinance) {
                    if($openFinance['uid'] == $uid){
                        // $idUidMap[$gameId] = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
                        $idTaxRateMap[$gameId] = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
                    }
                }
            }
            // 结算服务
            $settleAccountService = new SettleAccountService();

            foreach ($data as &$v) {

                $dividePer = (1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['divide'])*(1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['slotting_allowance']);
                $v['settle_id'] = "KX-AD-".$bill_period2.$v['game_id'];
                $v['settle_revenue'] = $v['cut_revenue'] * $dividePer;
                
                $v['bill_type'] = BillModel::$_type_ad;
                $v['bill_period'] = $lastMonthTime;
                $v['a_time'] = $nowTime;
                if($v['type'] == OpenUserModel::TYPE_COMPANY){
                    $v['original_after_tax'] = $v['original_revenue'] * $dividePer * (1 - $idTaxRateMap[$v['game_id']]);
                    $v['after_tax'] = $v['settle_revenue'] * (1 - $idTaxRateMap[$v['game_id']]);    
                }else{
                    $v['original_after_tax'] = $settleAccountService->getPersonAfterTax($v['original_revenue'] * $dividePer);
                    $v['after_tax'] = $settleAccountService->getPersonAfterTax($v['settle_revenue']);
                }
                if($v['original_after_tax'] > $v['original_revenue']){
                    echo $v['original_revenue'] * (1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['divide'])*(1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['slotting_allowance']);
                    exit;
                }
                unset($v['type']);
            }

            foreach ($data2 as &$v) {

                $dividePer2 = (1 - $divideArrMap[$v['game_id']][FinanceModel::$type_purchase]['divide'])*(1 - $divideArrMap[$v['game_id']][FinanceModel::$type_purchase]['slotting_allowance']);
                $v['cut_revenue'] = $v['original_revenue'];
                $v['settle_id'] = "KX-IAP-".$bill_period2.$v['game_id'];
                $v['settle_revenue'] = $v['cut_revenue'] * $dividePer2;
                // $v['after_tax'] = $v['settle_revenue'] * (1 - $idTaxRateMap[$v['game_id']]);
                $v['bill_type'] = BillModel::$_type_purchase;
                $v['bill_period'] = $lastMonthTime;
                $v['a_time'] = $nowTime;
                if($v['type'] == OpenUserModel::TYPE_COMPANY){
                    $v['original_after_tax'] = $v['original_revenue'] * $dividePer2 * (1 - $idTaxRateMap[$v['game_id']]);
                    $v['after_tax'] = $v['settle_revenue'] * (1 - $idTaxRateMap[$v['game_id']]);    
                }else{
                    $v['original_after_tax'] = $settleAccountService->getPersonAfterTax($v['original_revenue'] * $dividePer2);
                    $v['after_tax'] = $settleAccountService->getPersonAfterTax($v['settle_revenue']);
                }
                unset($v['type']);
            }
            // $allData = array_merge($data,$data2);

            $res3 = BillModel::db()->addAll($data);
            if($res3){
                echo "插入广告账单".count($data)."数据\n";
                LogLib::appWriteFileHash("========generateSettleData====== 插入广告账单".count($data)."数据");
            }else{
                echo "插入".count($data)."条广告账单失败";
                LogLib::appWriteFileHash("========generateSettleData======"."插入".count($data)."条广告账单失败");
            }
            $res4 = BillModel::db()->addAll($data2);
            if($res4){
                echo "插入内购账单".count($data2)."数据\n";
                LogLib::appWriteFileHash("========generateSettleData======"."插入内购账单".count($data2)."数据");
            }else{
                echo "插入".count($data2)."内购账单失败\n";
                LogLib::appWriteFileHash("========generateSettleData======"."插入".count($data2)."内购账单失败");
            }
        }else{
            echo "bills数据已存在，插入失败\n";
            LogLib::appWriteFileHash("========generateSettleData======"."bills数据已存在，插入失败");
        }
    }

    function getOrderSet($lastMonthFirstDay, $lastMonthLastDay){
        // $lastMonthFirstDay = date('Y-m-d', strtotime($day . ' -1 month'));
        // $lastMonthLastDay  = date('Y-m-d', strtotime($day . ' -1 day'));
        $purchaseSql = "select sum(money) as total,game_id from games_goods_order where from_unixtime(done_time,'%Y-%m-%d') between str_to_date('".$lastMonthFirstDay."','%Y-%m-%d') and str_to_date('".$lastMonthLastDay."','%Y-%m-%d') and status =".GamesGoodsOrderModel::$_status_ok." group by game_id";

        $purchaseDataSet = GamesGoodsOrderModel::db()->getAllBySQL($purchaseSql);
        return $purchaseDataSet;
    }

    function getPurchaseData($lastMonthFirstDay, $lastMonthLastDay){
        $startTime = strtotime($lastMonthFirstDay);
        $endTime = strtotime($lastMonthLastDay);
        // return PurchaseCntDayModel::db()->getAllBySQL("select sum(money) as money,game_id from purchase_cnt_day where a_time between $startTime and $endTime group by game_id");
        return PurchaseCntDayModel::db()->getAllBySQL("select sum(p.money) as original_revenue,p.game_id,o.type from kxgame_log.purchase_cnt_day p,kxgame.games g,kxgame.open_user o where p.game_id=g.id and g.uid=o.uid and p.a_time between $startTime and $endTime group by p.game_id");
    }

    function getPurchaseData2($lastMonthFirstDay, $lastMonthLastDay){
        $startTime = strtotime($lastMonthFirstDay);
        $endTime = strtotime($lastMonthLastDay);
        // return PurchaseCntDayModel::db()->getAllBySQL("select sum(money) as money,game_id from purchase_cnt_day where a_time between $startTime and $endTime group by game_id");
        return GamesGoodsOrderModel::db()->getAllBySQL("select sum(p.money) as original_revenue,p.game_id,o.type from games_goods_order p,kxgame.games g,kxgame.open_user o where p.game_id=g.id and g.uid=o.uid and from_unixtime(p.done_time,'%Y-%m-%d') between str_to_date('".$lastMonthFirstDay."','%Y-%m-%d') and str_to_date('".$lastMonthLastDay."','%Y-%m-%d') and p.status =".GamesGoodsOrderModel::$_status_ok." group by game_id");
    }

    function getAdData($lastMonthFirstDay, $lastMonthLastDay){
        // $sql = "select game_id,sum(cost) as original_revenue,sum(cut_cost) as cut_revenue from inner_ad_details_byday where stat_datetime between '".$lastMonthFirstDay."' and '".$lastMonthLastDay."' group by game_id";
        $sql = "select i.game_id,sum(i.cost) as original_revenue,sum(i.cut_cost) as cut_revenue,o.type from inner_ad_details_byday i,open_user o where i.uid=o.uid and i.stat_datetime between '".$lastMonthFirstDay."' and '".$lastMonthLastDay."' group by i.game_id";
        return InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
    }
}