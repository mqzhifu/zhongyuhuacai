<?php

/**
 * @Author: Kir
 * @Date:   2019-03-21 20:08:46
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-31 17:26:29
 */

/**
 * 
 */
class BusinessProcessCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->assign("billStatus", BillModel::getBillStatusDesc());
		$this->assign("billPeriod", BillModel::getBillPeriod());
		$this->display("/finance/purchase/business_process/index.html");
	}


	function settlement()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}
		$this->assign('bill', $bill);

		$game_id = $bill['game_id'];
		if (!$uid = GamesModel::db()->getRowById($game_id)['uid']) {
			exit(0);
		};
		$developer = OpenUserModel::db()->getRow("uid = $uid");

		$developer['business'] = $this->getStaticFileUrl('business', $developer['business'], "open");
		$developer['idcard_img'] = $this->getStaticFileUrl('idcard', $developer['idcard_img'], "open");
		$developer['idcard2_img'] = $this->getStaticFileUrl('idcard', $developer['idcard2_img'], "open");
		$developer['idcard_start_date'] = date("Y-m-d", $developer['idcard_start_date']);
        $developer['idcard_end_date'] = date("Y-m-d", $developer['idcard_end_date']);
		$this->assign('developer', $developer);

		$openFinance = OpenFinanceModel::db()->getRow("uid = $uid");
		$openFinance['tax_rate'] = OpenFinanceModel::getTaxRate($openFinance['tax_type'], $openFinance['invoice_type']);
		$openFinance['tax_img'] = $this->getStaticFileUrl('tax', $openFinance['tax_img'], "open");
		$openFinance['tax_type'] = OpenFinanceModel::getTaxType()[$openFinance['tax_type']];
		$openFinance['invoice_type'] = OpenFinanceModel::getInvoiceType()[$openFinance['invoice_type']];
		$openFinance['idcard_img'] = $this->getStaticFileUrl('idcard', $openFinance['idcard_img'], "open");
		$openFinance['idcard2_img'] = $this->getStaticFileUrl('idcard', $openFinance['idcard2_img'], "open");
        $openFinance['bank'] = $this->getStaticFileUrl('bank', $openFinance['bank'], "open");
		$this->assign("openFinance", $openFinance);

		$finance = FinanceModel::db()->getRow("game_id = $game_id");
		$finance['contract_start_date'] = date('Y-m-d', $finance['contract_start_date']);
		$finance['contract_end_date'] = date('Y-m-d', $finance['contract_end_date']);
		$this->assign("finance", $finance);

		$this->assign("id", $id);
		$this->assign("contract_body", FinanceModel::$_contract_body_desc);
		$this->assign("account_period", FinanceModel::$_account_period_desc);
		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->assign("settlementStatus", BillModel::getSettlementStatusDesc());
		$this->display("/finance/purchase/business_process/settlement.html");
	}

	function payApply()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}
		if ($bill['bill_img']) {
			$bill['bill_img'] = $this->getStaticFileUrl('finance', $bill['bill_img']);
		}
		if ($bill['invoice_img']) {
			$bill['invoice_img'] = $this->getStaticFileUrl('finance', $bill['invoice_img']);
		}
		$this->assign('bill', $bill);

		$game_id = $bill['game_id'];
		if (!$uid = GamesModel::db()->getRowById($game_id)['uid']) {
			exit(0);
		};
		$developer = OpenUserModel::db()->getRow("uid = $uid");
		
		$developer['business'] = $this->getStaticFileUrl('business', $developer['business'], "open");
		$developer['idcard_img'] = $this->getStaticFileUrl('idcard', $developer['idcard_img'], "open");
		$developer['idcard2_img'] = $this->getStaticFileUrl('idcard', $developer['idcard2_img'], "open");

		$this->assign('developer', $developer);

		$openFinance = OpenFinanceModel::db()->getRow("uid = $uid");
		$openFinance['tax_img'] = $this->getStaticFileUrl('tax_img', $openFinance['tax_img'], "open");
		$openFinance['tax_type'] = OpenFinanceModel::getTaxType()[$openFinance['tax_type']];
		$openFinance['invoice_type'] = OpenFinanceModel::getInvoiceType()[$openFinance['invoice_type']];
		$this->assign("openFinance", $openFinance);

		$finance = FinanceModel::db()->getRow("game_id = $game_id");
		$finance['contract_start_date'] = date('Y-m-d', $finance['contract_start_date']);
		$finance['contract_end_date'] = date('Y-m-d', $finance['contract_end_date']);
		$this->assign("finance", $finance);

		$this->assign("id", $id);
		$this->assign("contract_body", FinanceModel::$_contract_body_desc);
		$this->assign("account_period", FinanceModel::$_account_period_desc);
		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->assign("payStatus", [BillModel::$_status_pay_applied=>'申请付款']);
		$this->display("/finance/purchase/business_process/pay_apply.html");
	}

	function save()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}
		$game_id = $bill['game_id'];

		if (!$finance = FinanceModel::db()->getRow("game_id = $game_id")) {
			exit(0);
		}
		// $data = [
		// 	'divide'=>number_format(_g("divide")/100, 3),
		// 	'slotting_allowance'=>number_format(_g("slotting_allowance")/100, 3),
		// 	'u_time'=>time(),
		// ];
		// if (!FinanceModel::db()->update($data, "game_id = $game_id limit 1")) {
		// 	exit(0);
		// }

		$uploadService = new UploadService();

		$bill_img = "";
		$invoice_img = "";

		if ($_FILES["bill_img"]) {
			$rs = $uploadService->uploadFileByApp("bill_img", "finance", "", 1);
			if ($rs['code'] != 200) {
				$this->outputJson($rs['code'], $rs['msg']);
			}
			$bill_img = $rs['msg'];
		}

		if ($_FILES["invoice_img"]) {
			$rs = $uploadService->uploadFileByApp("invoice_img", "finance", "", 1);
			if ($rs['code'] != 200) {
				$this->outputJson($rs['code'], $rs['msg']);
			}
			$invoice_img = $rs['msg'];
		}
		
		if($bill_img == "" || $invoice_img == ""){
			echo json_encode(0,"上传失败");
		}

		$data = [
			'after_tax'=>_g('after_tax'), 
			'status'=>_g('status'), 
			'bill_img'=>$bill_img,
			'invoice_img'=>$invoice_img,
			'u_time'=>time(),
		];

		if (!BillModel::db()->upById($id, $data)) {
			exit(0);
		}

		echo json_encode(200);
        exit;

	}


	function printBill()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}
		if (!$game = GamesModel::db()->getRowById($bill['game_id'])) {
			exit(0);
		}
		if (!$user = OpenUserModel::db()->getRow('uid='.$game['uid'])) {
			exit(0);
		}
		// 1公司 2个人
    	if ($user['type'] == 1) {
    		$uname = $user['company'];
    	} elseif ($user['type'] == 2) {
    		$uname = $user['contact'];
    	}

    	$partnerName = $uname;
    	$settlementBs = $game['name'].' 内购收入结算';
    	$firstDay = date('Y-m-01', $bill['bill_period']);
    	$lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
    	$settlementTime = $firstDay.' 至 '.$lastDay;
    	$settlementNo = $bill['settle_id'];

		$printBillService = new printBillService();
		$printBillService->printElectronicBills($partnerName, $settlementBs, $settlementTime, $settlementNo, date('Y-m', $bill['bill_period']), $bill['after_tax']);

	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from (select b.id,b.game_id,gu.game_name,gu.type, gu.company, gu.contact, b.settle_id,b.original_revenue,b.settle_revenue,b.bill_period,b.status,b.bill_type  from game_bills as b left join( select g.id, g.name as game_name, o.type, o.company,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on b.game_id = gu.id ) as c where bill_type=1 and $where";

        $cntSql = BillModel::db()->getRowBySQL($sql);
		$cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }
        $iTotalRecords = $cnt;//DB中总记录数

        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
            	'id',
                'game_id',
                '',
                '',
                'type',
                '',
                'original_revenue',
                'original_after_tax',
                'cut_revenue',
                'after_tax',
                'bill_period',
                'status',
                ''
            );
            $order = " order by ". $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $sql = "select uid, b.id,b.game_id,gu.game_name,gu.type, gu.company,gu.account_holder, gu.contact, b.settle_id,b.original_revenue,b.settle_revenue,b.cut_revenue,b.original_after_tax,b.after_tax,b.bill_period,b.status,b.bill_type from game_bills as b left join( select g.uid, g.id, g.name as game_name, o.type, o.company,o.account_holder,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on b.game_id = gu.id where bill_type=1 and $where";

            $data = BillModel::db()->getAllBySQL($sql. $order. " limit $iDisplayStart,$iDisplayLength ");
            $accountType = OpenUserModel::getAccountDescs();
            $billStatus = BillModel::getBillStatusDesc();

            foreach($data as $k=>$v){
            	// 1公司 2个人
            	if ($v['type'] == 1) {
            		$uname = $v['company'];
            	} elseif ($v['type'] == 2) {
            		$uname = $v['account_holder'];
            	}
            	$openFinance = OpenFinanceModel::db()->getRow("uid =".$v['uid']." ");
                $row = array(
                	$v['id'],
                    $v['game_id'],
                    $v['game_name'],
                    $uname,
                    $accountType[$v['type']], 
                    $v['settle_id'],
                    $v['original_revenue'],
                    $v['original_after_tax'],
                    $v['cut_revenue'],
                    $v['after_tax'],
                    date('Y-m', $v['bill_period']),
                    $billStatus[$v['status']],
                    '<a href="/finance/purchase/businessProcess/settlement/id='.$v['id'].'" class="btn btn-icon-only yellow " title="收入结算设置"><i class="fa fa-cogs"></i> </a>
                    <a href="/finance/purchase/businessProcess/printBill/id='.$v['id'].'" class="btn btn-icon-only green " target="_blank" title="下载对账单"><i class="fa fa-plus"></i> </a>
                    <a href="" class="btn btn-icon-only blue " title="推送对账单"><i class="fa fa-bullhorn"></i> </a>
                    <a href="/finance/purchase/businessProcess/payApply/id='.$v['id'].'" class="btn btn-icon-only red " title="申请付款"><i class="fa fa-edit"></i> </a>
                    ',
                );

                $records["data"][] = $row;
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}

	function getWhere()
	{
		$where = " 1 ";

        if($uname = _g("uname"))
            $where .= " and uname like '%$uname%' ";

        if($game_name = _g("game_name"))
            $where .= " and game_name like '%$game_name%' ";

        if($game_id = _g("game_id"))
            $where .= " and game_id = $game_id ";

        if($type = _g("type"))
            $where .= " and type = $type ";

        if($settle_id = _g("settle_id"))
            $where .= " and settle_id like '%$settle_id%' ";

        if($bill_period = _g("bill_period")){
        	$bill_period = strtotime($bill_period);
            $where .= " and bill_period = '$bill_period' ";
        }

        if($status = _g("status"))
            $where .= " and status = $status ";


        return $where;
	}
}