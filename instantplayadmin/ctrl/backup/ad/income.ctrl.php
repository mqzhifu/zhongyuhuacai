<?php

/**
 * @Author: xuren
 * @Date:   2019-03-29 10:43:55
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-01 16:46:31
 */
class IncomeCtrl extends BaseCtrl{

	function date(){
		$this->addCss("/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css");
		$this->addCss("/assets/admin/pages/css/profile.css");
		$this->addCss("/assets/admin/pages/css/tasks.css");
		
		$this->addCss("/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css");
		$this->addCss("/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css");
		$this->addCss("/assets/global/plugins/select2/select2.css");
		$this->addCss("/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css");
		$this->addCss("/assets/global/css/plugins.css");

		$sql = "select id,name from games";
		$res = GamesModel::db()->getAllBySql($sql);
		if(!$res){
			$res = [];
		}

		$gamesDesc = [];
		foreach ($res as $v) {
			$gamesDesc[$v['id']] = $v['name'];
		}
		$this->assign('gamesDesc', $gamesDesc);
		$this->assign('adTypeDesc', AppAdvertiseModel::getAdvertiseTypeDesc());
		$this->assign('osDesc', AppAdvertiseModel::getOSTypeDesc());
		// $adService = new AdvertiseService();
		// echo json_encode($adService->getAdIncomeListByCondition('2019-03-03','2019-03-04',1,1));
		// echo json_encode($adService->getAllDay('2018-03-16','2018-03-25'));
		$this->display('ad/ad_income.html');
		
	}

	function getData(){
		$system = _g('system');
		$game_id = _g('game_id');
		$ad_type = _g('ad_type');

		$from = _g('from');
		$to = _g('to');
		// if(!$from && !$to){
		// 	$from = date("Y-m-d", strtotime('-30 day'));
  //           $to = date("Y-m-d",  time());
		// }
		if(!$from){
			if(!$to){
				$from = date("Y-m-d", strtotime('-30 day'));
            	$to = date("Y-m-d",  time());
			}else{
				$to1 = strtotime($to);
				$from = date("Y-m-d", strtotime('-30 day'), $to1);
			}
		}else{
			if(!$to){
				$from1 = strtotime($from);
				$to = date("Y-m-d",  time());
				
			}
		}
		
		if(!$from || !$to || !valid_date($from) || !valid_date($to) || strtotime($from)>strtotime($to)){
			//日期格式不正确
		}

		// 检测ad_type
		if(!$ad_type){
			$ad_type = null;
		}
		// 检测system
		if(!$system){
			$system = null;
		}

		if(!$game_id){
			$game_id = null;
		}

		$returnData = [];

		
		

		$adService = new AdvertiseService();
		$data = $adService->getAdIncomeListByCondition($from, $to, $system, $ad_type, $game_id);
		$dateArr = $adService->getAllDay($from, $to);
		// 综述数据
		$returnData['totalDescData'] = $this->convertToTotalDescData($data);
		// 折线图数据
		$returnData['lineChartData'] = $this->convertToLineChartData($data, $dateArr);
		// $returnData['datatTable'] = $this->convertTodatatTableData($data, $dateArr);

		$this->outputJson(200, 'succ', $returnData);

	}

	public function outputJson ($code, $message, $data=[])
    {
        header("Content-Type: application/json");
        echo json_encode([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
        exit(0);
    }


    private function convertToTotalDescData($data){
    	$totalDescData = [];

		$totalDescData['total_show'] = 0;
		$totalDescData['total_cost'] = 0;
		$totalDescData['total_click'] = 0;
		$totalDescData['total_ecpm'] = 0;
		$totalDescData['total_click_rate'] = 0;
		foreach ($data as $v) {
			$totalDescData['total_show'] += $v['show'];
			$totalDescData['total_cost'] += $v['cost'];
			$totalDescData['total_click'] += $v['click'];
			
		}
		$totalDescData['total_ecpm'] += $totalDescData['cost']*$totalDescData['show']*0.001;
		$totalDescData['total_click_rate'] += $totalDescData['show']>0 ? $totalDescData['click']/$totalDescData['show'] : 0;

		return $totalDescData;
    }

    private function convertToLineChartData($data, $dateArr){
    	$lineData = [];

    	
    	

    	$data1 = [];
    	for($i = 1; $i <= count($dateArr); $i++){
    		$data1[$dateArr[$i-1]] = 0;
    	}
    	$data2 = [];
    	for($i = 1; $i <= count($dateArr); $i++){
    		$data2[$dateArr[$i-1]] = 0;
    	}
    	$data3 = [];
    	for($i = 1; $i <= count($dateArr); $i++){
    		$data3[$dateArr[$i-1]] = 0;
    	}
    	$data4 = [];
    	for($i = 1; $i <= count($dateArr); $i++){
    		$data4[$dateArr[$i-1]] = 0;
    	}
    	$data5 = [];
    	for($i = 1; $i <= count($dateArr); $i++){
    		$data5[$dateArr[$i-1]] = 0;
    	}

    	foreach ($data as $v) {
    		$data1[$v['stat_datetime']] += $v['show'];
    		$data2[$v['stat_datetime']] += $v['cost'];
    		$data3[$v['stat_datetime']] += $v['click'];
    		
    	}

    	foreach ($data as $v) {
    		
    		$data4[$v['stat_datetime']] += $data2[$v['stat_datetime']]*$data1[$v['stat_datetime']]*0.001;
    		$data5[$v['stat_datetime']] += $data1[$v['stat_datetime']]>0 ? $data3[$v['stat_datetime']] / $data1[$v['stat_datetime']] : 0;
    	}


    	$obj1 = new LineObj();
    	$obj1->label = "总展示量";
    	$obj1->data = $data1;
    	$obj2 = new LineObj();
    	$obj2->label = "总分成收入";
    	$obj2->data = $data2;
    	$obj3 = new LineObj();
    	$obj3->label = "总点击量";
    	$obj3->data = $data3;
    	$obj4 = new LineObj();
    	$obj4->label = "总ECPM";
    	$obj4->data = $data4;
    	$obj5 = new LineObj();
    	$obj5->label = "总点击率";
    	$obj5->data = $data5;

    	$lineData[] = $obj1;
    	$lineData[] = $obj2;
    	$lineData[] = $obj3;
    	$lineData[] = $obj4;
    	$lineData[] = $obj5;
    	return $lineData;
    }

    private function convertTodatatTableData($data, $dateArr){
    	$returnData = [];

    	$data1 = [];
    	for($i = 0; $i < count($dateArr); $i++){
    		$data1[$dateArr[$i]][0] = $dateArr[$i];
    		$data1[$dateArr[$i]][1] = 0;
    		$data1[$dateArr[$i]][2] = 0;
    		$data1[$dateArr[$i]][3] = 0;
    		$data1[$dateArr[$i]][4] = 0;
    		$data1[$dateArr[$i]][5] = 0;
    	}

    	foreach ($data as $v) {
    		// $data1[$v['stat_datetime']][0] = $v['stat_datetime'];
    		$data1[$v['stat_datetime']][1] += $v['cost'];
    		// $data1[$v['stat_datetime']][2] += $v['cost'];
    		$data1[$v['stat_datetime']][3] += $v['show'];
    		$data1[$v['stat_datetime']][4] += $v['click'];
    		// $data1[$v['stat_datetime']][5] += $v['click'];
    	}

    	// foreach ($data as $v) {
    	// 	$data1[$v['stat_datetime']] += $v['show'];
    	// 	$data1[$v['stat_datetime']] += $v['cost'];
    	// 	$data1[$v['stat_datetime']] += $v['click'];
    		
    	// }

    	foreach ($data as $v) {
    		
    		$data1[$v['stat_datetime']][2] += $data1[$v['stat_datetime']][1]*$data1[$v['stat_datetime']][3]*0.001;
    		$data1[$v['stat_datetime']][5] += $data1[$v['stat_datetime']][3]>0 ? $data1[$v['stat_datetime']][4] / $data1[$v['stat_datetime']][3] : 0;
    	}

    	foreach ($data1 as $value) {
    		$returnData[] = $value;
    	}

    	return $returnData;

    }


    function getTableData(){
		$records = array();
        $records["data"] = array();


		$system = _g('system');
		$game_id = _g('game_id');
		$ad_type = _g('ad_type');

		$from = _g('from');
		$to = _g('to');
		// if(!$from && !$to){
		// 	$from = date("Y-m-d", strtotime('-30 day'));
  //           $to = date("Y-m-d",  time());
		// }

		if(!$from){
			if(!$to){
				$from = date("Y-m-d", strtotime('-30 day'));
            	$to = date("Y-m-d",  time());
			}else{
				$to1 = strtotime($to);
				$from = date("Y-m-d", strtotime('-30 day'), $to1);
			}
		}else{
			if(!$to){
				$from1 = strtotime($from);
				$to = date("Y-m-d",  time());
				
			}
		}

		if(!$from || !$to || !valid_date($from) || !valid_date($to) || strtotime($from)>strtotime($to)){
			//日期格式不正确
		}

		// 检测ad_type
		if(!$ad_type){
			$ad_type = null;
		}
		// 检测system
		if(!$system){
			$system = null;
		}

		if(!$game_id){
			$game_id = null;
		}

		$returnData = [];

		
		

		$adService = new AdvertiseService();
		$data = $adService->getAdIncomeListByCondition($from, $to, $system, $ad_type, $game_id);
		$dateArr = $adService->getAllDay($from, $to);
		$data = $this->convertTodatatTableData($data, $dateArr);

		// $data = UserModel::db()->getAllBySQL($sql);
		foreach ($data as $value) {
			$records["data"][] = array(
	            $value[0],
	            $value[1],
	            $value[2],
	            $value[3],
	            $value[4],
	            $value[5],
	            'xxx',
	        );
		}

        echo json_encode($records);
        exit;
  		
	}




}

// 折线一条数据对象
class LineObj{
	public $data;
	public $label;
}