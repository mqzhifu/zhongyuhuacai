<?php

/**
 * @Author: xuren
 * @Date:   2019-05-31 09:40:28
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-31 15:02:39
 */
class SettlementCtrl extends BaseCtrl{

	function index(){
		$this->addCss("assets/open/css/finance.css");
		$this->display("finance.html","new","isLogin");
	}
	function getIncomeInfo(){
		$incomeType = _g("incomeType");
		$uid = $this->_uid;
		$sql = "select gb.after_tax,gb.status,gb.bill_period from game_bills gb left join games g on g.id=gb.game_id where g.uid=$uid and gb.bill_type=$incomeType and gb.status in (1,2,3,4,5,6) ";
		$data = BillModel::db()->getAllBySQL($sql);
		$info = InformationModel::db()->getRow("uid=$uid");
		$info['type'];
		$paidTotal = 0;
		$noPayTotal = 0;

		// $billStatusDescArr = BillModel::getBillStatusDesc();
		$tableData = [];
		foreach ($data as $v) {
			if($v['status'] == 6){
				$paidTotal += $v['after_tax'];
			}else{
				$noPayTotal += $v['after_tax'];
			}
			$tableData[] = [$this->periodTimeToDateRange($v['bill_period']), $this->getAccountTypeDesc($info['type']), $v['after_tax'], $this->getBillStatusDesc($v['status']==6 ? 6 : 1)];
		}

		$returnData = [];
		$returnData['total'] = ['nopay'=>$noPayTotal,'paid'=>$paidTotal];
		$returnData['table'] = $tableData;
		$this->outputJson(200, 'succ',$returnData);
	}

	function getBillStatusDesc($status){
		if($status == 6){
			return "已付款";
		}
		if($status == 1){
			return "对账中";
		}
		return "未知状态";
	}


	function getAccountTypeDesc($type){
		if($type == InformationModel::TYPE_PERSON){
			return "个人";
		}
		if($type == InformationModel::TYPE_COMPANY){
			return "公司";
		}
		return "未知";
	}


	function periodTimeToDateRange($firstDayTime){
		$firstDay = date("Y-m-1",$firstDayTime);
		$lastDay = date("Y-m-d", strtotime($firstDay." +1 month -1 day"));
		return $firstDay." - ".$lastDay;
	}

	function getSettleAccountInfo(){
		$uid = $this->_uid;
		$info = OpenFinanceModel::db()->getRow("uid=$uid");
		if(!$info){
			$this->outputJson(1, '无财务信息');
		}
		// $info['status'];
		// $info['bank_account'];

		$openUser = InformationModel::db()->getRow("uid=$uid");
		$accountName = "";
		if($openUser['type'] == InformationModel::TYPE_PERSON){
			$accountName = $openUser['account_holder'];
		}
		if($openUser['type'] == InformationModel::TYPE_COMPANY){
			$accountName = $openUser['company'];
		}
		// $openUser['email'];

		$returnData = [];
		$returnData['accountName'] = $accountName;
		$returnData['invoiceType'] = OpenFinanceModel::getInvoiceType()[$info['invoice_type']];
		$returnData['status'] = OpenFinanceModel::getStatusDescs()[$info['status']];
		$returnData['account'] = $info['bank_name']." ".$info['bank_account'];
		$returnData['email'] = $openUser['email'];

		$this->outputJson(200, 'succ', $returnData);

	}
}