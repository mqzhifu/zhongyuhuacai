<?php

/**
 * @Author: Kir
 * @Date:   2019-06-15 11:25:49
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-16 23:14:14
 */


/**
 * 
 */
class ForeignAppAdCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->addCss("/assets/open/css/game-detail.css?1");

		$this->assign('advertiser_type',AppAdvertiseModel::getAdvertiserTypeDesc());
    	$this->assign('ad_channel',AppAdvertiseModel::getChannelDesc());
    	$this->assign('system',AppAdvertiseModel::getOSTypeDesc());
    	$this->assign('advertise_type',AppAdvertiseModel::getAdvertiseTypeDesc());
    	$this->assign('stateDesc',AppAdvertiseModel::getStateDesc());
	 	$this->display('app_manager_ad/app_ad_en.html');
	}


	function getList() {
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


            $sql = "select * from app_advertise where `status`!= 4 order by id desc limit $iDisplayStart,$iDisplayLength";

            $data = AppAdvertiseModel::db()->getAllBySQL($sql);
            
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['title'],
                    $v['id'],
                    AppAdvertiseModel::getDescByAdType($v['advertise_type']),
                    AppAdvertiseModel::getStateDesc()[$v['state']],
                    '<button class="btn btn-xs default blue" onclick="getDetail(this)" data-id="'.$v['id'].'">操作</button>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}


	function getDetails()
	{
    	$id = _g('id');
    	if($id=="undefined"||!$id){
    		$this->outputJson(0, "id不存在");
    	}

        $data = AppAdvertiseModel::db()->getRowById($id);
        if(!$data){
        	$this->outputJson(1, "信息不存在");
        }

        $items = ForeignAdMapModel::db()->getAllBySql("select m.*,g.name from (select * from foreign_ad_map where inner_id = $id and type = 2) as m left join foreign_ad_group as g on m.group_id = g.id");

        foreach ($items as &$ad) {
            $ad['channel_desc'] = AppAdvertiseModel::getChannelDesc()[$ad['channel_id']];
            $ad['system_desc'] = AppAdvertiseModel::getOSTypeDesc()[$ad['os']];
        }

        $groups = ForeignAdGroupModel::db()->getAll("ad_type = ".$data['advertise_type']);

		$returnData = [];
		$returnData['base'] = $data;
		$returnData['real_ad_data'] = $items;
		$returnData['groups'] = $groups;

		
		$this->outputJson(200, "成功", $returnData);
    }


    function addAppAd() 
    {
		$advertise_type = _g('advertise_type');
		$title = _g('title');

		if(!$advertise_type || $advertise_type =='undefined' || !$title || $title =='undefined'){
			$this->outputJson(1, '缺少参数');
		}

		$add = [];
		$add['status'] = AppAdvertiseModel::$status_pause;
		$add['advertise_type'] = $advertise_type;
		$add['title'] = $title;
		$add['state'] = 1;
		$add['a_time'] = time();
		AppAdvertiseModel::db()->add($add);
		$this->outputJson(200, 'succ');
	}


	function updateAd()
	{
    	$id = _g('id');
		$title = _g('title');
		$status = _g('status');
		$state = _g('state');
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


    function setADMap() 
    {
    	$inner_id = _g('inner_id');
        $channel = _g('channel');
        $os = _g('os');
        $group_id = _g('group_id');

        if ($channel == '' || $os == '' || $group_id == '' || $inner_id == '') {
            $this->outputJson(0,'参数错误', []);
        }
    	
    	$data = [
    		'type'=>2,
            'inner_id'=>$inner_id, 
            'channel_id'=>$channel, 
            'os'=>$os, 
            'group_id'=>$group_id, 
            'u_time'=>time()
        ];

        $id = _g('id');

        if ($id) {
        	ForeignAdMapModel::db()->upById($id, $data);
        } else {
        	$id = ForeignAdMapModel::db()->add($data);
        }
    	
    	if(!$id){
    		$this->outputJson(0, 'db error');
    	}

    	$this->outputJson(200, 'succ', $id);
    }

    function deleteADMap() 
    {
    	$ad_map_id = _g('ad_map_id');
    	if(!$ad_map_id || $ad_map_id == 'undefined'){
    		$this->outputJson(1,'fail');
    	}

    	$res = ForeignAdMapModel::db()->delById($ad_map_id);
    	if(!$res){
    		$this->outputJson(2,'fail');
    	}
    	$this->outputJson(200, 'succ');
    }
}