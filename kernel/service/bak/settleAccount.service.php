<?php

/**
 * @Author: xuren
 * @Date:   2019-05-22 12:02:50
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-30 09:41:55
 */
class SettleAccountService{

	public function setCutPercent($gameid, $day, $cost_p, $click_p = null, $show_p = null){
		$bill_period = strtotime(date("Y-m-01", strtotime($day)));
		$monthFirstDay = date("Y-m-01", strtotime($day));
        $monthLastDay  = date('Y-m-d', strtotime(date('Y-m-01', strtotime(date("Y-m-01", strtotime($day)).' +1 month')) . ' -1 day'));
		// 一.对账情况
		$bill = BillModel::db()->getRow("bill_type=".BillModel::$_type_ad." and bill_period=$bill_period and game_id=$gameid ");
		if($bill && $bill['status'] == BillModel::$_status_paid){
			return false;
		}
		// 二.更新暗扣
		$update = [];
		$update['cost_cut'] = $cost_p;
		if($click_p===null){
			$update['click_cut'] = $cost_p;
		}else{
			$update['click_cut'] = $click_p;
		}
		if($show_p===null){
			$update['show_cut'] = $cost_p;
		}else{
			$update['show_cut'] = $show_p;
		}
		// $update['u_time'] = time();
		// $res = GameCutModel::db()->update($update, "game_id=$gameid and stat_datetime='".$day."' limit 1");
		// if(!$res){
		// 	return false;
		// }
		// 三.更新内部ad详情
		$res2 = InnerAdDetailsByDayModel::db()->getAllBySQL("select * from inner_ad_details_byday where game_id=$gameid and stat_datetime='".$day."' ");
		foreach ($res2 as $v) {
			$udata = [];
			$udata['cut_click'] = $v['click'] * (1 - $update['click_cut']);
			$udata['cut_cost'] = $v['cost'] * (1 - $update['cost_cut']);
			$udata['cut_show'] = $v['show'] * (1 - $update['show_cut']);
			$udata['click_cut_p'] = $update['click_cut'];
			$udata['cost_cut_p'] = $update['cost_cut'];
			$udata['show_cut_p'] = $update['show_cut'];
			$udata['u_time'] = time();
			InnerAdDetailsByDayModel::db()->update($udata, " id=".$v['id']." limit 1");
		}

		// 四.重新计算当月bills
		$item = InnerAdDetailsByDayModel::db()->getRowBySQL("select sum(cost) cost,sum(cut_cost) cut_cost from inner_ad_details_byday where game_id=$gameid and stat_datetime between '".$monthFirstDay."' and '".$monthLastDay."' ");
		//更新暗扣后收入。及其分成后收入，及其扣税后收入
		$res4 = FinanceModel::db()->getRow("finance_type=".FinanceModel::$type_ad." and game_id=$gameid");
		if(!$res4){
			$res4['game_id'] = $gameid;
			$res4['divide'] = 0;
			$res4['slotting_allowance'] = 0;
			$res4['a_time'] = time();
			FinanceModel::db()->add($res4);
		}

		$this->recomputeADIncome($monthFirstDay);

		//通道费
		$divide = $res4['divide'];
		$allowance = $res4['slotting_allowance'];
		// 税率
		$gameInfo = GamesModel::db()->getRowBySQL("select g.uid,ou.type from games g,open_user ou where g.id=$gameid and g.uid=ou.uid");
		$uid = $gameInfo['uid'];

		$cut_revenue = $item['cut_cost'];
		$settle_revenue = $cut_revenue * (1 - $divide) * (1 - $allowance);
		
		if($gameInfo['type'] == OpenUserModel::TYPE_COMPANY){
			$openFinance = OpenFinanceModel::db()->getRow("uid=$uid");
			$tax_rate = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
			$after_tax = $settle_revenue * (1 - $tax_rate);
		}
		if($gameInfo['type'] == OpenUserModel::TYPE_PERSON){
			$after_tax = $this->getPersonAfterTax($settle_revenue);
		}
		

		
		$res3 = BillModel::db()->update(['cut_revenue'=>$cut_revenue, 'settle_revenue'=>$settle_revenue, 'after_tax'=>$after_tax], "bill_type=".BillModel::$_type_ad." and bill_period=$bill_period and game_id=$gameid limit 1");
		// if(!$res3){
		// 	return false;
		// }

		return true;


	}

	function getPersonAfterTax($money){
		if (PCK_AREA == 'en') {
            return $money;   
        }

        if($money <= 800 & $money >= 0){
            return $money;
        }else if($money <= 4000 && $money > 800){
            return $money-($money-800)*0.2;
        }else if($money <= 20000 && $money > 4000){
            return $money - ($money*(1-0.2)*0.2-0);
        }else if($money > 20000 && $money <= 50000){
            return $money - ($money*(1-0.2)*0.3-2000);;
        }else if($money > 50000){
            return $money - ($money*(1-0.2)*0.4-7000);;
        }else {
            return 0;
        }

    }

    function recompute($gameid, $type){
    	$day = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
    	$bill_period = strtotime(date("Y-m-01", strtotime($day)));
    	$monthFirstDay = date("Y-m-01", strtotime($day));
        $monthLastDay  = date('Y-m-d', strtotime(date('Y-m-01', strtotime($day.' +1 month')) . ' -1 day'));
        // 一.对账情况
		$bill = BillModel::db()->getRow("bill_type=".$type." and bill_period=$bill_period and game_id=$gameid ");
		if($bill && $bill['status'] == BillModel::$_status_paid){
			return false;
		}

    	if($type == FinanceModel::$type_purchase){
    		$lastMonthFirstDay = strtotime($monthFirstDay);
    		$lastMonthLastDay = strtotime($monthLastDay)+86399;
    		$purchaseSql = "select sum(money) as total,game_id from games_goods_order where waste_time between $lastMonthFirstDay and $lastMonthLastDay and status =".GamesGoodsOrderModel::$_status_ok." and game_id=$gameid";
        	$purchaseData = GamesGoodsOrderModel::db()->getRowBySQL($purchaseSql);

        	
			$cut_revenue = $purchaseData['total'];

    	}else if($type == FinanceModel::$type_ad){
    		// 四.重新计算当月bills
			$item = InnerAdDetailsByDayModel::db()->getRowBySQL("select sum(cost) cost,sum(cut_cost) cut_cost from inner_ad_details_byday where game_id=$gameid and stat_datetime between '".$monthFirstDay."' and '".$monthLastDay."' ");
			
			$cut_revenue = $item['cut_cost'];
    	}
    	$res4 = FinanceModel::db()->getRow("finance_type=".$type." and game_id=$gameid");
		if(!$res4){
			$res4['game_id'] = $gameid;
			$res4['divide'] = 0;
			$res4['slotting_allowance'] = 0;
			$res4['a_time'] = time();
			FinanceModel::db()->add($res4);
		}
		//通道费
		$divide = $res4['divide'];
		$allowance = $res4['slotting_allowance'];
		// 税率
		$gameInfo = GamesModel::db()->getRowBySQL("select g.uid,ou.type from games g,open_user ou where g.id=$gameid and g.uid=ou.uid");
		$uid = $gameInfo['uid'];

		
		$settle_revenue = $cut_revenue * (1 - $divide) * (1 - $allowance);
		
		if($gameInfo['type'] == OpenUserModel::TYPE_COMPANY){
			$openFinance = OpenFinanceModel::db()->getRow("uid=$uid");
			$tax_rate = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
			$after_tax = $settle_revenue * (1 - $tax_rate);
		}
		if($gameInfo['type'] == OpenUserModel::TYPE_PERSON){
			$after_tax = $this->getPersonAfterTax($settle_revenue);
		}
		

		
		$res3 = BillModel::db()->update(['cut_revenue'=>$cut_revenue, 'settle_revenue'=>$settle_revenue, 'after_tax'=>$after_tax, 'original_after_tax'=>$bill['original_revenue'] * (1 - $divide) * (1 - $allowance)], "bill_type=".$type." and bill_period=$bill_period and game_id=$gameid limit 1");

		return true;
    }

    /**
     * [recomputeADIncome description]
     * @param  [type] $period 2019-04-01
     * @return [type]         [description]
     */
    function recomputeADIncome($period){
    	$time = strtotime($period);
    	$res = IncomeModel::db()->getRow("settlement_interval=$time and type=2");
    	if($res){
    		$end = date("Y-m-d", strtotime($period." +1 month -1 day"));
	    	$sql = "select sum(cost) as original_revenue,sum(cut_cost) as cut_revenue from inner_ad_details_byday where stat_datetime between '".$period."' and '".$end."' ";
	        $res2 = InnerAdDetailsByDayModel::db()->getRowBySQL($sql);
	        if($res2){
	        	$update = ["estimate_income"=>$res2['original_revenue'], "divide_money"=>$res2['cut_revenue'], "cuted_money"=>$res2['original_revenue']-$res2['cut_revenue']];
	        	$res3 = IncomeModel::db()->update($update, "id=".$res['id']." limit 1");
	        	if($res3){
	        		return true;
	        	}
	        }else{
	        	return true;
	        }
    	}else{
    		return true;
    	}
    	return false;

    }

    function recomputeDivideAndTax($gameid, $type){
    	// $day = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
    	$day = date("Y-m-01");
    	$bill_period = strtotime(date("Y-m-01", strtotime($day)));
    	$monthFirstDay = date("Y-m-01", strtotime($day));
        $monthLastDay  = date('Y-m-d', strtotime(date('Y-m-01', strtotime($day.' +1 month')) . ' -1 day'));
        // 一.对账情况
		$bills = BillModel::db()->getAll("bill_type=".$type." and bill_period<=$bill_period and game_id=$gameid and status!=".BillModel::$_status_paid);
		// if(!$bill){
		// 	return false;
		// }
		// must
    	$res4 = FinanceModel::db()->getRow("finance_type=".$type." and game_id=$gameid");
		if(!$res4){
			$res4['game_id'] = $gameid;
			$res4['divide'] = 0;
			$res4['slotting_allowance'] = 0;
			$res4['a_time'] = time();
			FinanceModel::db()->add($res4);
		}
		//通道费 must
		$divide = $res4['divide'];
		$allowance = $res4['slotting_allowance'];
		// 税率 must
		$gameInfo = GamesModel::db()->getRowBySQL("select g.uid,ou.type from games g,open_user ou where g.id=$gameid and g.uid=ou.uid");
		$uid = $gameInfo['uid'];

		
		foreach ($bills as $bill) {
			$settle_revenue = $bill['cut_revenue'] * (1 - $divide) * (1 - $allowance);
		
			if($gameInfo['type'] == OpenUserModel::TYPE_COMPANY){
				$openFinance = OpenFinanceModel::db()->getRow("uid=$uid");
				$tax_rate = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
				$after_tax = $settle_revenue * (1 - $tax_rate);
				$original_after_tax = $bill['original_revenue'] * (1 - $divide) * (1 - $allowance) * (1 - $tax_rate);
			}
			if($gameInfo['type'] == OpenUserModel::TYPE_PERSON){
				$after_tax = $this->getPersonAfterTax($settle_revenue);
				$original_after_tax = $this->getPersonAfterTax($bill['original_revenue'] * (1 - $divide) * (1 - $allowance));
			}
			

			
			$res3 = BillModel::db()->update(['settle_revenue'=>$settle_revenue, 'after_tax'=>$after_tax, 'original_after_tax'=>$original_after_tax], "bill_type=".$type." and id=".$bill['id']." and game_id=$gameid limit 1");
		}

		return true;
    }

}