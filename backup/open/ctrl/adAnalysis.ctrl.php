<?php

/**
 * @Author: Kir
 * @Date:   2019-05-10 15:55:38
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-30 11:08:52
 */

/**
 * 
 */
class AdAnalysisCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->addJs('assets/open/scripts/laydate/laydate.js');
		$this->addJs("assets/open/scripts/echarts.min.js");
		$this->addJs("assets/open/scripts/adAnalysis.js");
		$this->display("adAnalysis.html","new","isLogin");
	}

	public function getLineData(){
        $adType = _g("adType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        $adTypeWhereStr = "";
        if($adType){
            $adTypeWhereStr = " and advertise_type=$adType";
        }else{
            $adTypeWhereStr = " and advertise_type in (1,2,3)";
        }
        // 获取自己创建的游戏
        $openGameService = new OpenGamesService();
        $gameids = $openGameService->getOnLineGameidsByUid($this->_uid);
        if(empty($gameids)){
        	$this->outputJson(200,'no data');
        }
        $gameidsStr = implode(",", $gameids);
        // 时间过滤
        if(!$from || !$to){
            $this->outputJson(1,'no from or to');
        }
        if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case 1:// 曝光量
                // $sql = "select oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id in ($gameidsStr) and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id in ($gameidsStr) $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['show'];
                }
                break;
            case 2:// 点击量
                // $sql = "select oa.click,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id in ($gameidsStr) and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_click as click,b.stat_datetime from (select id,title from open_advertise where game_id in ($gameidsStr) $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['click'];
                }
                break;
            case 3:// 点击率
                // $sql = "select b.title,oa.click,oa.click_rate,oa.cost,oa.ecpm,oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id in ($gameidsStr) and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_click as click,b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id in ($gameidsStr) $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                $clickMap = [];
                foreach ($dayArr as $value) {
                    $clickMap[$value] = 0;
                }
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['show'];
                    $clickMap[$v['stat_datetime']] += $v['click'];
                }
                foreach ($map as $key => $value) {
                    $map[$key] = $value==0 ? 0 : $clickMap[$key]/$value;
                }
                break;
            case 4:// 收入
                // $sql = "select oa.cost,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id in ($gameidsStr) and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_cost as cost,b.stat_datetime from (select id,title from open_advertise where game_id in ($gameidsStr) $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['cost'];
                }
                break;            
            default:
                break;
        }
        

        $map = $this->filtData($map, 0);

        
        $line = new LineObj();
        $line->name = OpenAdvertiseModel::getAdvertiseTypeDesc()[$adType];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";

        $this->outputJson(200, 'succ', $line);
    }

    public function getTableData(){
        $adType = _g("adType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        $adTypeWhereStr = "";
        if($adType){
            $adTypeWhereStr = " and advertise_type=$adType";
        }else{
            $adTypeWhereStr = " and advertise_type in (1,2,3)";
        }
        // 获取自己创建的游戏
        $openGameService = new OpenGamesService();
        $gameids = $openGameService->getOnLineGameidsByUid($this->_uid);
        if(empty($gameids)){
        	$this->outputJson(200,'no data');
        }
        $gameidsStr = implode(",", $gameids);
        // 时间过滤
        if(!$from || !$to){
            $this->outputJson(1,'no from or to');
        }
        if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);
        
        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        $dayArr = array_reverse($dayArr);

        // $sql = "select b.title,oa.click,oa.click_rate,oa.cost,oa.ecpm,oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id in ($gameidsStr) and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to order by stat_datetime desc";
        $sql = "select a.title,if(b.cut_show=0,0,(b.cut_click/b.cut_show)) as click_rate,b.cut_click as click,b.cut_cost as cost,b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id in ($gameidsStr) $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to order by b.stat_datetime desc";

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = [$value,0,0,0,0,0];
        }

        $data = advertiseIncomeModel::db()->getAllBySQL($sql);
        $returnData = [];

        $initFiltDay = date("Y-m-d", time()-86400);
        if($type == 1){//汇总数据
            foreach ($data as $v) {
                $map[$v['stat_datetime']][1] += $v['show'];
                $map[$v['stat_datetime']][2] += $v['click'];
                $map[$v['stat_datetime']][4] += round($v['cost'],2);
                $map[$v['stat_datetime']][4] = round($map[$v['stat_datetime']][4],2);

            }
            foreach ($map as &$value) {
                $value[3] = number_format($value[1]==0 ? 0 : $value[2]/$value[1]*100,2)."%";
            }
            $map = $this->filtData($map, [$initFiltDay,0,0,"0.00%",0]);
            // $returnData = array_values($map);
        }elseif($type == 2){
            foreach ($data as $v) {
                $map[$v['stat_datetime']][1] = $v['title'];
                $map[$v['stat_datetime']][2] = $v['show'];
                $map[$v['stat_datetime']][3] = $v['click'];
                $map[$v['stat_datetime']][4] = number_format($v['click_rate']*100,2)."%";
                $map[$v['stat_datetime']][5] = $v['cost'];
                // $returnData[] = [$v['stat_datetime'], $v['title'], $v['show'], $v['click'], number_format($v['click_rate'],2), $v['cost']];  
            }
            
            $map = $this->filtData($map, [$initFiltDay,$map[$initFiltDay][1],0,0,'0.00%',0]);
        }
        $returnData = array_values($map);
        $this->outputJson(200, 'succ', $returnData);

    }

    private function filtData($map, $initData){
        $filtTime = strtotime(date("Y-m-d 16:00:00"));
        $filtDate = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
        if($filtTime >= time() && isset($map[$filtDate])){
            $map[$filtDate] = $initData;
        }
        return $map;
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