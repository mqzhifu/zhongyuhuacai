<?php

/**
 * @Author: xuren
 * @Date:   2019-03-27 15:27:15
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-03 20:24:10
 */
class ShowCtrl extends BaseCtrl{

	function index(){
		$this->addCss("/assets/open/css/game-detail.css?1");
	 	if(_g('getList')){
	 		$this->getList();
	 	}

	 	$this->assign('advertiser_type',AppAdvertiseModel::getAdvertiserTypeDesc());
    	$this->assign('ad_channel',AppAdvertiseModel::getChannelDesc());
    	$this->assign('system',AppAdvertiseModel::getOSTypeDesc());
    	$this->assign('advertise_type',AppAdvertiseModel::getAdvertiseTypeDesc());
    	$this->assign('direction',AppAdvertiseModel::getAdDirectionDesc());
    	$this->assign('stateDesc',AppAdvertiseModel::getStateDesc());
	 	$this->display('app_manager_ad/app_ad_show.html');
	}

	function getList(){
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $sql = "select count(*) as cnt from app_advertise";
        
        $cntSql = IncomeModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;


            $sql = "select * from app_advertise where `status`!= 4 GROUP BY id order by id desc limit $iDisplayStart,$iDisplayLength";

            $data = AppAdvertiseModel::db()->getAllBySQL($sql);
            
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['title'],
                    $v['id'],
                    AppAdvertiseModel::getDescByAdType($v['advertise_type']),
                    AppAdvertiseModel::getAdDirectionDesc()[$v['direction']],
                    AppAdvertiseModel::getStateDesc()[$v['state']],
                    '<button class="btn btn-xs default blue" onclick="getDetail(this)" data-id="'.$v['id'].'">操作</button>'.'<button class="btn btn-xs default green" onclick="getStrategy(this)" data-id="'.$v['id'].'">策略</button>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}

	function getAppAdMapList(){
		$inner_ad_id = _g('inner_ad_id');
		if(!$inner_ad_id || $inner_ad_id == 'undefined'){
			$this->outputJson(1, '缺少参数id');
		}
		$where = "inner_ad_id=$inner_ad_id";
		$res = AppADMapModel::db()->getAll($where);
		$this->outputJson(200, 'succ', $res);
	}

	/**
	 * 获取app广告位信息和广告位映射信息
	 * @return [type] [description]
	 */
	function getDetails(){
    	$id = _g('id');
    	if($id=="undefined"||!$id){
    		$this->outputJson(0, "id不存在");
    	}

        $data = AppAdvertiseModel::db()->getRowById($id);
        if(!$data){
        	$this->outputJson(1, "信息不存在");
        }

		
        $where = "inner_ad_id=$id";
        $items = AppADMapModel::db()->getAll($where);

        foreach ($items as &$ad) {
            $ad['channel_desc'] = AppAdvertiseModel::getChannelDesc()[$ad['channel']];
            $ad['advertiser_desc'] = AppAdvertiseModel::getAdvertiserTypeDesc()[$ad['advertiser']];
            $ad['system_desc'] = AppAdvertiseModel::getOSTypeDesc()[$ad['system']];
        }

		$returnData = [];
		$returnData['base'] = $data;
		$returnData['real_ad_data'] = $items;

		
		$this->outputJson(200, "成功", $returnData);
    }

    function getStrategy(){
        $id = _g('id');
        if($id=="undefined"||!$id){
            $this->outputJson(0, "参数错误");
        }
        $where = "inner_ad_id = $id";

        $channel = _g('channel');
        if ($channel != '') {
            $where .= " and channel = $channel";
        }

        $data = AppADMapModel::db()->getAll($where);

        foreach ($data as &$ad) {
            $ad['channel'] = AppAdvertiseModel::getChannelDesc()[$ad['channel']];
            $ad['advertiser'] = AppAdvertiseModel::getAdvertiserTypeDesc()[$ad['advertiser']];
            $ad['system'] = AppAdvertiseModel::getOSTypeDesc()[$ad['system']];
        }

        $this->outputJson(200, "成功", $data);
    }

    function saveStrategy(){
        $id = _g('id');
        $sort = _g('sort');
        $status = _g('status');
        if($id=='' || $sort=='' || $status==''){
            $this->outputJson(0, "参数错误");
        }

        if(!AppADMapModel::db()->getRowById($id)){
            $this->outputJson(0, "查询不到该广告位信息");
        }

        $data = [
            'sort'=>$sort,
            'status'=>$status,
        ];

        AppADMapModel::db()->upById($id, $data);

        $this->outputJson(200, "成功", []);
    }

    function updateAd(){
    	$id = _g('id');
		$title = _g('title');
		$status = _g('status');
		$state = _g('state');
		$direction = _g('direction');
		$advertise_type = _g('advertise_type');
		if(!$id || $id == 'undefined'){
			$this->outputJson(1, '缺少参数id');
		}

		$where = "id=$id limit 1";
		$update = [];

		if($title && $title != 'undefined'){
			$update['title'] = $title;
		}
		if($status && $status != 'undefined'){
			$update['status'] = $status;
		}
		if($advertise_type && $advertise_type != 'undefined'){
			$update['advertise_type'] = $advertise_type;
		}
		if ($state !='') {
			$update['state'] = $state;
		}
		if ($direction !='') {
			$update['direction'] = $direction;
		}

		// 广告频次可设置
        if ($frequencyType = _g("frequencyType")) {
            $update['frequency_type'] = $frequencyType;
            $update['interval'] = _g("interval");
            $update['times'] = _g("times");
            $update['period'] = _g("period");
        }

		if(!$update){
			$this->outputJson(2, '无可更新内容');
		}

		$update['u_time'] = time();
		
		$res = AppAdvertiseModel::db()->update($update, $where);
		if(!$res){
			$this->outputJson(3, '更新失败');
		}
		$this->outputJson(200, 'succ');
    }
	/**
	 * 更新一个广告映射内容
	 */
	function updateOneAdMap(){
		$ad_map_id = _g('ad_map_id');
		$system = _g('system');
		$advertiser = _g('advertiser');
		$channel = _g('channel');
		$outer_ad_id = _g('outer_ad_id');
		if(!$ad_map_id || $ad_map_id == 'undefined'){
			$this->outputJson(1, '缺少参数id');
		}

		$where = "id=$ad_map_id limit 1";
		$update = [];
		if($system && $system != 'undefined'){
			$update['system'] = $system;
		}
		if($advertiser && $advertiser != 'undefined'){
			$update['advertiser'] = $advertiser;
		}
		if($channel && $channel != 'undefined'){
			$update['channel'] = $channel;
		}
		if($outer_ad_id && $outer_ad_id != 'undefined'){
			$update['outer_ad_id'] = $outer_ad_id;
		}
		if(!$update){
			$this->outputJson(2, '无可更新内容');
		}
		
		$res = AppADMapModel::db()->update($update, $where);
		if(!$res){
			$this->outputJson(3, '更新失败');
		}
		$this->outputJson(200, 'succ', $ad_map_id);
	}

	function delOneAdMap(){
		$id = _g('ad_map_id');
		if(!$id || $id == 'undefined'){
			$this->outputJson(1, '缺少参数id');
		}

		$res = AppADMapModel::db()->delById($id);
		if(!$res){
			$this->outputJson(2, '删除失败');
		}

		$this->outputJson(200, 'succ');
	}

	function delOneAd(){
		$id = _g('id');
		if(!$id || $id == 'undefined'){
			$this->outputJson(1, '缺少参数id');
		}

		$update = [];
		$update['u_time'] = time();
		$update['status'] = AppAdvertiseModel::$status_del;

		$where = "id=$id limit 1";
		$res = AppAdvertiseModel::db()->update($update, $where);
		if(!$res){
			$this->outputJson(2, 'db error');
		}
		$this->outputJson(200, 'succ');

	}

	/**
	 * 添加一个app广告映射
	 */
	function addOneAdMap(){
		$inner_ad_id = _g('inner_ad_id');
        $channel = _g('channel');
        $advertiser = _g('advertiser');
        $system = _g('system');
        $outer_ad_id = _g('outer_ad_id');

        if ($channel == '' || $advertiser == '' || $system == '' || $outer_ad_id == '' || $inner_ad_id == '') {
                $this->outputJson(0,'参数错误', []);
            }

    	
    	$add = [
            'inner_ad_id'=>$inner_ad_id, 
            'channel'=>$channel, 
            'advertiser'=>$advertiser, 
            'system'=>$system, 
            'outer_ad_id'=>$outer_ad_id, 
        ];
    	$id = AppADMapModel::db()->add($add);
    	if(!$id){
    		$this->outputJson(0, 'db error');
    	}

		$this->outputJson(200, 'succ', $id);
	}

	function addAppAd(){
		$advertise_type = _g('advertise_type');
		$title = _g('title');
		$direction = _g('direction');

		if(!$advertise_type || $advertise_type =='undefined' || !$title || $title =='undefined' || !$direction){
			$this->outputJson(1, '缺少参数');
		}

		$add = [];
		$add['status'] = AppAdvertiseModel::$status_pause;
		$add['advertise_type'] = $advertise_type;
		$add['title'] = $title;
		$add['direction'] = $direction;
		$add['state'] = 1;
		$add['a_time'] = time();
		AppAdvertiseModel::db()->add($add);
		$this->outputJson(200, 'succ');
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
}