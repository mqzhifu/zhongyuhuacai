<?php

/**
 * @Author: Kir
 * @Date:   2019-06-14 15:31:35
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-17 14:41:49
 */

/**
 * 
 */
class ForeignAdCtrl extends BaseCtrl
{
	function ok() 
	{
    	$this->init();
        $this->display("ad/ad_en.html");
    }

    function audit() 
    {
    	$this->init();
        $this->display("ad/ad_en.html");
    }

    function pause() 
    {
    	$this->init();
        $this->display("ad/ad_en.html");
    }

    function delete() 
    {
    	$this->init();
        $this->display("ad/ad_en.html");
    }

    private function init() 
    {
    	$this->addCss("/assets/open/css/game-detail.css?1");
        
        $this->assign('advertiser_type',OpenAdvertiseModel::getAdvertiserTypeDesc());
        $this->assign('ad_channel',OpenAdvertiseModel::getChannelDesc());
        $this->assign('system',OpenAdvertiseModel::getOSTypeDesc());
        $this->assign('developerType',['1'=>'公司','2'=>'个人']);
        $this->assign('statusDesc',OpenAdvertiseModel::getSubStatusDesc());
        $this->assign('advertise_type',OpenAdvertiseModel::getAdvertiseTypeDesc());
    }


    function getList()
    {
    	$status = _g("status");

    	$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        
        $where .= " and oa.`status`=".$status;

        $sql = "select count(*) as cnt from (open_advertise oa left join open_user ou on oa.uid=ou.uid) left join games g on oa.game_id=g.id where $where";

        $cntSql = UserModel::db()->getRowBySQL($sql);
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $sql = "select ou.type,ou.company,ou.account_holder,g.name,oa.title,oa.id,oa.advertise_type,oa.status,oa.a_time,oa.u_time,oa.game_id from (open_advertise oa left join open_user ou on oa.uid=ou.uid) left join games g on oa.game_id=g.id where $where order by oa.id desc limit $iDisplayStart,$iDisplayLength ";

            $data = UserModel::db()->getAllBySQL($sql);

            foreach($data as $k=>$v){
                $viewHtml = '<button class="btn btn-xs default yellow delone" data-id="'.$v['id'].'" onclick="getDetail(this)">操作</button>';
            	$extraHtml = "";
            	if($status == 1){
            		$extraHtml = $viewHtml;
            	}else if($status == 2){
            		$extraHtml = $viewHtml.'<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="recover(this)">恢复</button>';
            	}else if($status == 3){
            		$extraHtml = $viewHtml . '<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="pause(this)">暂停</button>';
            	} else if ($status == 4) {
                    $extraHtml = $viewHtml.'<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="recover2(this)">恢复</button>';
                }
                
                $records["data"][] = array(
                    $v['game_id'],
                    $v['name'],
                    OpenUserModel::getAccountDescs()[$v['type']],
                    $this->getDeveloperName($v),
                    $v['title'],
                    $v['id'],
                    OpenAdvertiseModel::getAdvertiseTypeDesc()[$v['advertise_type']],
                    OpenAdvertiseModel::getStatusDesc()[$v['status']],
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    $extraHtml,
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    private function getDeveloperName($dp)
    {
        if($dp['type'] == OpenUserModel::TYPE_PERSON){
            return $dp['account_holder'];
        }

        if($dp['type'] == OpenUserModel::TYPE_COMPANY){
            return $dp['company'];
        }
        return "";
    }


    private function getWhere()
    {
    	$where = " 1 ";
        if($type = _g("type")){
        	$where .= " and ou.type=$type";
        }
        if($advertise_type = _g("advertise_type")){
        	$where .= " and oa.advertise_type=$advertise_type";
        }
        if($name = _g("name")){
        	$where .= " and g.name like '%$name%'";
        }
        if($company = _g('company')){
            $where .= " and ou.company like '%$company%'";
        }
        if($title = _g('title')){
            $where .= " and oa.title like '%$title%'";
        }
        if($id = _g('id')){
            $where .= " and oa.id=$id";
        }

        if($game_id = _g('game_id')){
            $where .= " and oa.game_id=$game_id";
        }

        if($from = _g('from')){
            $from_time = strtotime($from.' 00:00:00');
            $where .= " and oa.a_time >= '{$from_time}' ";
        }
        if($to = _g('to')){
            $to_time = strtotime($to.' 23:59:59');
            $where .= " and oa.a_time <= '{$to_time}' ";
        }

        if($from1 = _g('from1')){
            $from_time1 = strtotime($from1.' 00:00:00');
            $where .= " and oa.u_time >= '{$from_time1}' ";
        }
        if($to1 = _g('to1')){
            $to_time1 = strtotime($from1.' 23:59:59');
            $where .= " and oa.u_time <= '{$to_time1}' ";
        }

        return $where;
    }

    function getDetails() 
    {
    	$id = _g('id');
    	if($id=="undefined"||!$id){
    		$this->outputJson(0, "id不存在");
    	}

    	$sql = "select oa.status,oa.id,oa.game_id,g.name,oa.uid,oa.title,oa.advertise_type,oa.direction,oa.frequency_type,oa.interval,oa.times,oa.period from open_advertise oa left join games g on oa.game_id=g.id where oa.id=$id";
        $data = UserModel::db()->getRowBySQL($sql);
        if(!$data){
        	$this->outputJson(1, "信息不存在");
        }

        $data['advertise_type_desc'] = OpenAdvertiseModel::getAdvertiseTypeDesc()[$data['advertise_type']];

        $items = ForeignAdMapModel::db()->getAllBySql("select m.*,g.name from (select * from foreign_ad_map where inner_id = $id and type = 1) as m left join foreign_ad_group as g on m.group_id = g.id");

        foreach ($items as &$ad) {
            $ad['channel_desc'] = OpenAdvertiseModel::getChannelDesc()[$ad['channel_id']];
            $ad['system_desc'] = OpenAdvertiseModel::getOSTypeDesc()[$ad['os']];
        }

        $groups = ForeignAdGroupModel::db()->getAll("ad_type = ".$data['advertise_type']);

		$returnData = [];
		$returnData['base'] = $data;
		$returnData['real_ad_data'] = $items;
		$returnData['groups'] = $groups;

		
		$this->outputJson(200, "成功", $returnData);
    }


    function auditAd() 
    {
    	$id = _g('id');
		if(!$id || $id == 'undefined'){
			$this->outputJson(1, '参数不正确');
		} 	


        if ($status = _g('status')) {
            $update = ['status'=>$status];
            $reason = _g('reason');
        }
        
        // 广告频次可设置
        if ($frequencyType = _g("frequencyType")) {
            $update['frequency_type'] = $frequencyType;
            $update['interval'] = _g("interval");
            $update['times'] = _g("times");
            $update['period'] = _g("period");
        }
		$where = "id=$id limit 1";
        $update['u_time'] = time();
    	$res = UserModel::db()->update($update, $where, 'open_advertise');
    	if(!$res){
    		$this->outputJson(2,'db error');
    	}

        if($status == 2 && ($reason != 'undefined' || !$reason)){//暂停
            $row = UserModel::db()->getRowById($id, 'id','open_advertise');
            $notificationService = new openNotificationService();
            $title = "广告开通审核结果";
            $content = "开发者你好，经平台审核，你的广告位（".$row['title'].",".$row['id'].",".OpenAdvertiseModel::getDescByAdType($row['advertise_type'])."）未通过审核，具体原因如下：".$reason;
            $arr = $notificationService->sendNotifyMsg($row['uid'],1,$title,$content);
        }
        if($status == 3 && ($reason != 'undefined' || !$reason)){//审核通过
            $row = UserModel::db()->getRowById($id, 'id','open_advertise');
            $notificationService = new openNotificationService();
            $title = "广告开通审核结果";
            $content = "开发者你好，经平台审核，你的广告位（".$row['title'].",".$row['id'].",".$row['advertise_type']."）已通过审核";
            $notificationService->sendNotifyMsg($row['uid'],1,$title,$content);
        }

    	$this->outputJson(200, 'succ',$res);
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
    		'type'=>1,
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