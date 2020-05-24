<?php

/**
 * @Author: xuren
 * @Date:   2019-04-08 13:52:41
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 15:35:42
 */
 
class generateSettleData{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr)
    {
        echo '脚本执行时间'.date('Y-m-d H:i:s')."\n";
        $day = date('Y-m-01');
        // $day = "2019-05-01";
        // $day = "2019-06-01";
        $this->exe($day);

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
                // if($v['original_after_tax'] > $v['original_revenue']){
                //     echo $v['original_revenue'] * (1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['divide'])*(1 - $divideArrMap[$v['game_id']][FinanceModel::$type_ad]['slotting_allowance']);
                //     exit;
                // }
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

function o($str){
//    $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//    var_dump($encode);
//    var_dump($str);
//    var_dump(iconv("UTF-8","gbk//TRANSLIT",$str));
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }

    echo $str."\n";
}