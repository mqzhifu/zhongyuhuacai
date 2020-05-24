<?php

/**
 * 批量聚合广告数据 聚合2019-06-01到执行脚本当天得数据
 * @Author: xuren
 * @Date:   2019-05-22 16:41:10
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-05 17:34:32
 */
class aggregateADBat{
	function __construct($c){
        $this->commands = $c;
    }
    public function run($attr){
        ini_set('display_errors','On');

        $day = date("Y-m-d");
        $nowDayTime = strtotime($day);
        // $yesterday = date("Y-m-d", strtotime($day." -1 day"));
        // $day = "2019-05-22";
        // $day = "2019-04-15";
        $startDayTime = strtotime("2019-06-01");
        echo "aggregateADBat---start----".date("Y-m-d H:i:s")."\n";
        for($dayTime = $startDayTime; $dayTime <= $nowDayTime; $dayTime += 86400){
            $this->aggregateByDay(date("Y-m-d", $dayTime));
        }
        echo "aggregateADBat---end----".date("Y-m-d H:i:s")."\n";
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
                // isset($value['game_id']['click_cut_p']) ? $click_percent = $value['game_id']['click_cut_p'] : 1;
                if(isset($value['game_id']['click_cut_p']) && $value['game_id']['click_cut_p']){
                    $click_percent = $value['game_id']['click_cut_p'];
                }

                if(isset($value['game_id']['cost_cut_p']) && $value['game_id']['cost_cut_p']){
                    $cost_percent = $value['game_id']['cost_cut_p'];
                }
                // isset($value['game_id']['cost_cut_p']) ? $cost_percent = $value['game_id']['cost_cut_p'] : 1;

                if(isset($value['game_id']['show_cut_p']) && $value['game_id']['show_cut_p']){
                    $show_percent = $value['game_id']['show_cut_p'];
                }
                // isset($value['game_id']['show_cut_p']) ? $show_percent = $value['game_id']['show_cut_p'] : 1;
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
}