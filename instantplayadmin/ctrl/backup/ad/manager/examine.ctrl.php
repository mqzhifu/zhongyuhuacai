<?php
class ExamineCtrl extends BaseCtrl{

    function ok() {
    	$this->init();
        $this->display("ad/manager.html");
    }

    function audit() {
    	$this->init();
    	$this->display("ad/manager.html");
    }

    function pause() {
    	$this->init();
        $this->display("ad/manager.html");
    }

    function delete() {
        $this->init();
        $this->display("ad/manager.html");
    }

    private function init() {
        $this->addCss("/assets/open/css/game-detail.css?1");
        
        $this->assign('advertiser_type',OpenAdvertiseModel::getAdvertiserTypeDesc());
        $this->assign('ad_channel',OpenAdvertiseModel::getChannelDesc());
        $this->assign('system',OpenAdvertiseModel::getOSTypeDesc());
        $this->assign('developerType',['1'=>'公司','2'=>'个人']);
        $this->assign('advertise_type',OpenAdvertiseModel::getAdvertiseTypeDesc());
        $this->assign('statusDesc',OpenAdvertiseModel::getSubStatusDesc());
        $this->assign('directionDesc',OpenAdvertiseModel::getAdDirectionDesc());
    }


    function getList() {
        $status = _g("status");

    	$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        
        $where .= " and oa.`status`=".$status;

        $sql = "select count(*) as cnt from (open_advertise oa left join open_user ou on oa.uid=ou.uid) left join games g on oa.game_id=g.id where $where";
        // var_dump($sql);
        // exit;
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

            $sql = "select ou.type,ou.company,ou.account_holder,g.name,oa.title,oa.id,oa.adtoutiao_id,oa.advertise_type,oa.status,oa.direction,oa.a_time,oa.u_time,oa.game_id from (open_advertise oa left join games g on oa.game_id=g.id) left join open_user ou on g.uid=ou.uid where $where order by oa.id desc limit $iDisplayStart,$iDisplayLength ";

            // var_dump($sql);
            // exit;
            $data = UserModel::db()->getAllBySQL($sql);

            // $arr = ['1'=>'审核','2'=>'暂停','3'=>'有效','4'=>'删除'];
            foreach($data as $k=>$v){
                $viewHtml = '<button class="btn btn-xs default yellow delone" data-id="'.$v['id'].'" onclick="getDetail(this)">操作</button>';
                $strategyHtml = '<button class="btn btn-xs default blue delone" data-id="'.$v['id'].'" onclick="getStrategy(this)">策略</button>';
            	$extraHtml = "";
            	if($status == 1){
            		$extraHtml = $viewHtml;
            	}else if($status == 2){
            		$extraHtml = $viewHtml.'<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="recover(this)">恢复</button>';
            	}else if($status == 3){
            		$extraHtml = $viewHtml . $strategyHtml . '<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="pause(this)">暂停</button>';
            	} else if ($status == 4) {
                    $extraHtml = $viewHtml.'<button class="btn btn-xs default red delone" data-id="'.$v['id'].'" onclick="recover2(this)">恢复</button>';
                }
                
                $records["data"][] = array(
                    $v['game_id'],
                    $v['name'],
                    $this->getDeveloperDescByType($v['type']),
                    $this->getDeveloperName($v),
                    $v['title'],
                    $v['id'],
                    $this->getADDescByType($v['advertise_type']),
                    $this->getADStatusDescByStatus($v['status']),
                    $this->getDirectionDesc($v['direction']),
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

    function getDeveloperName($dp){
        if($dp['type'] == OpenUserModel::TYPE_PERSON){
            return $dp['account_holder'];
        }

        if($dp['type'] == OpenUserModel::TYPE_COMPANY){
            return $dp['company'];
        }
        return "";
    }

    function getDetails(){
    	$id = _g('id');
    	if($id=="undefined"||!$id){
    		$this->outputJson(0, "id不存在");
    	}

    	$sql = "select oa.status,oa.id,oa.game_id,g.name,oa.uid,oa.title,oa.advertise_type,oa.direction,oa.frequency_type,oa.interval,oa.times,oa.period from open_advertise oa left join games g on oa.game_id=g.id where oa.id=$id";
        $data = UserModel::db()->getRowBySQL($sql);
        if(!$data){
        	$this->outputJson(1, "信息不存在");
        }

        $data['advertise_type_desc'] = $this->getADDescByType($data['advertise_type']);
        $data['direction'] = $this->getDirectionDesc($data['direction']);

        $sql2 = "select id,system,advertiser,channel,outer_ad_id from ad_map where inner_ad_id=$id";
        $items = UserModel::db()->getAllBySQL($sql2);

        foreach ($items as &$ad) {
            $ad['channel_desc'] = OpenAdvertiseModel::getChannelDesc()[$ad['channel']];
            $ad['advertiser_desc'] = OpenAdvertiseModel::getAdvertiserTypeDesc()[$ad['advertiser']];
            $ad['system_desc'] = OpenAdvertiseModel::getOSTypeDesc()[$ad['system']];
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

        $data = OpenADMapModel::db()->getAll($where);

        foreach ($data as &$ad) {
            $ad['channel'] = OpenAdvertiseModel::getChannelDesc()[$ad['channel']];
            $ad['advertiser'] = OpenAdvertiseModel::getAdvertiserTypeDesc()[$ad['advertiser']];
            $ad['system'] = OpenAdvertiseModel::getOSTypeDesc()[$ad['system']];
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

        if(!OpenADMapModel::db()->getRowById($id)){
            $this->outputJson(0, "查询不到该广告位信息");
        }

        $data = [
            'sort'=>$sort,
            'status'=>$status,
            'u_time'=>time(),
        ];

        OpenADMapModel::db()->upById($id, $data);

        $this->outputJson(200, "成功", []);
    }

    function getWhere(){
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

        if($direction = _g('direction')){
            $where .= " and oa.direction=$direction";
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

    function getDeveloperDescByType($type){
    	$arr = ['1'=>'公司','2'=>'个人'];
    	return array_key_exists($type, $arr) ? $arr[$type] : '未知';
    }

    function getADDescByType($type){
    	$arr = OpenAdvertiseModel::getAdvertiseTypeDesc();
    	return array_key_exists($type, $arr) ? $arr[$type] : '未知';
    }

    function getADStatusDescByStatus($status){
    	$arr = OpenAdvertiseModel::getStatusDesc();
    	return array_key_exists($status, $arr) ? $arr[$status] : '未知';
    }

    function getDirectionDesc($direction){
        $arr = OpenAdvertiseModel::getAdDirectionDesc();
        return array_key_exists($direction, $arr) ? $arr[$direction] : '未知';
    }

    function checkADStatus($status){
    	$arr = OpenAdvertiseModel::getStatusDesc();
    	return array_key_exists($status, $arr);
    }

    function auditAd() {
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

    function addADMap(){
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
            'a_time'=>time()
        ];
    	$id = UserModel::db()->add($add, 'ad_map');
    	if(!$id){
    		$this->outputJson(0, 'db error');
    	}

    	$this->outputJson(200, 'succ', $id);
    }

    function updateADMap(){
    	$ad_map_id = _g('ad_map_id');
    	$inner_ad_id = _g('inner_ad_id');

    	if(!$inner_ad_id || $inner_ad_id == 'undefined'){
    		$this->outputJson(1,'fail');
    	}
    	if(!$ad_map_id || $ad_map_id == 'undefined'){
    		$this->outputJson(2,'fail');
    	}

    	$update = [];
    	if($system = _g('system')){
    		$update['system'] = $system;
    	}
    	if($advertiser = _g('advertiser')){
    		$update['advertiser'] = $advertiser;
    	}
    	if($channel = _g('channel')){
    		$update['channel'] = $channel;
    	}

    	if($outer_ad_id = _g('outer_ad_id')){
    		$update['outer_ad_id'] = $outer_ad_id;
    	}
    	$u_time = time();
    	$update['u_time'] = $u_time;
    	$res = UserModel::db()->update($update, " id=$ad_map_id and inner_ad_id=$inner_ad_id limit 1", 'ad_map');
    	if(!$res){
    		$this->outputJson(3,'fail');
    	}
    	$this->outputJson(200, 'succ', $ad_map_id);
    }

    function deleteADMap(){
    	$ad_map_id = _g('ad_map_id');
    	if(!$ad_map_id || $ad_map_id == 'undefined'){
    		$this->outputJson(1,'fail');
    	}

    	$where = " id=$ad_map_id limit 1";
    	$res = UserModel::db()->delete($where, 'ad_map');
    	if(!$res){
    		$this->outputJson(2,'fail');

    	}
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