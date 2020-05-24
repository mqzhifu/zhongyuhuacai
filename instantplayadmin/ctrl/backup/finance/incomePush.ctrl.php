<?php

/**
 * @Author: xuren
 * @Date:   2019-03-27 11:23:03
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-29 14:39:14
 */
class IncomePushCtrl extends BaseCtrl{

	 function index(){
	 	$this->addCss("/assets/open/css/game-detail.css?1");
	 	if(_g('getList')){
	 		$this->getList();
	 	}
	 	$this->display('finance/income_push.html');
	 }

	 function getList(){
	 	$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $sql = "select count(*) as cnt from income";
        
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


            $sql = "select * from income GROUP BY id limit $iDisplayStart,$end ";

            $data = IncomeModel::db()->getAllBySQL($sql);
            $uploadService = new UploadService();
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    IncomeModel::getDescByType($v['type']),
                    date('Y-m',$v['settlement_interval']),
                    $v['estimate_income'],
                    $v['cuted_money'],
                    $v['divide_money'],
                    get_default_date($v['a_time']),
                   	get_default_date($v['u_time']),
                   '<a href="#" class="btn btn-xs default red delone" onclick="set_push_emails(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 修改</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	 }

	 function getOne(){
	 	$id = _g('id');
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}

	 	$res = IncomeModel::db()->getRowById($id);

	 	$returnData = $res['settlement_interval'];
	 	$this->outputJson(200, 'succ', $returnData);
	 }

	 function getEmails(){
	 	$id = _g('id');
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}
	 	$where = "income_id='".$id."'";
	 	$res = IncomePushEmailsModel::db()->getAll($where);
	 	$this->outputJson(200, 'succ', $res);
	 }

	 function addOneEmail(){
	 	$id = _g('id');
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}

	 	$add = [];
	 	$add['income_id'] = $id;
		$res = IncomePushEmailsModel::db()->add($add);
		$this->outputJson(200, 'succ', $res);
	 }

	 function delOneEmail(){
	 	$id = _g('id');
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}
	 	$res = IncomePushEmailsModel::db()->delById($id);
	 	if(!$res){
	 		$this->outputJson(2,'db error');
	 	}
	 	$this->outputJson(200, 'succ');
	 }

	 function updateOneEmail(){
	 	$id = _g('id');
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}
	 	$name = _g('name');
	 	$email = _g('email');

	 	$update = [];
	 	$update['name'] = $name;
	 	$update['email'] = $email;
	 	$res = IncomePushEmailsModel::db()->update($update, "id=$id limit 1");
	 	if(!$res){
	 		$this->outputJson(2,'db error');
	 	}

	 	$this->outputJson(200, 'succ');
	 }

	 function setCutPervent(){
	 	$id = _g('id');

	 	$startTime = strtotime(date('Y-m-10'));
        $endTime  = strtotime(date('Y-m-14'));
        $nowTime = time();
        if($nowTime < $startTime || $nowTime > $endTime){
        	$this->outputJson(3, '每月10到14号间才可操作');
        }
	 	if(!$id || $id == 'undefined'){
	 		$this->outputJson(1, 'id不存在');
	 	}
	 	$cut_percent = _g('cut_percent');
	 	if(!$cut_percent || $id =='undefined'){
	 		$this->outputJson(2, '未填写扣款比例');
	 	}
	 	$update = [];
	 	$update['cut_percent'] = $cut_percent;
	 	$item = IncomeModel::db()->getRowById($id);
	 	if($item['cut_percent'] != $cut_percent){
	 		$res = IncomeModel::db()->update($update, "id=$id limit 1");
	 		if($res){
	 			$settlePeriodTime = strtotime(date('Y-m-1'). " -1 month");
	 			
	 			$where = "bill_type=".$item['type']." and bill_period=".$settlePeriodTime;
	 			$count = BillModel::db()->getCount($where);
	 			$sql = "update game_bills set original_revenue=original_revenue*".$cut_percent."/".$item['cut_percent'].", settle_revenue=settle_revenue*".$cut_percent."/".$item['cut_percent'].", after_tax=after_tax*".$cut_percent."/".$item['cut_percent']." where $where limit $count";

	 			BillModel::db()->execute($sql);
	 		}
	 		
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