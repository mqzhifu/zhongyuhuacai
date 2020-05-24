<?php

/**
 * @Author: Kir
 * @Date:   2019-03-23 15:45:17
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-31 17:27:31
 */

/**
 * 
 */
class FinanceProcessCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->assign("financeStatus", ['未收票', '财务收票']);
		$this->assign("payStatus", ['待付款', '已付款']);
		$this->assign("billPeriod", BillModel::getBillPeriod());
		$this->display("/finance/purchase/finance_process/index.html");
	}


	function checkBill()
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
		$this->assign("payStatus", [BillModel::$_status_finance_received=>'财务收票']);
		$this->display("/finance/purchase/finance_process/check_bill.html");
	}

	function save()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}

		if (_g('status') != BillModel::$_status_finance_received) {
			exit(0);
		}

		$data = [
			'status'=>_g('status'), 
			'u_time'=>time(),
		];

		if (!BillModel::db()->upById($id, $data)) {
			exit(0);
		}

		echo json_encode(200);
        exit;

	}

	function pay()
	{
		if (!$id = _g('id')) {
			exit(0);
		}
		if (!$bill = BillModel::db()->getRowById($id)) {
			exit(0);
		}
		if ($bill['status']!=BillModel::$_status_finance_received) {
			exit(0);
		}

		$data = [
			'status'=>BillModel::$_status_paid, 
			'u_time'=>time(),
		];

		if (!BillModel::db()->upById($id, $data)) {
			exit(0);
		}

		echo json_encode(200);
        exit;
	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from (select b.id,b.game_id,gu.game_name,gu.type, gu.company, gu.contact, b.settle_id,b.original_revenue,b.settle_revenue,b.bill_period,b.status,b.bill_type  from game_bills as b left join( select g.id, g.name as game_name, o.type, o.company,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on b.game_id = gu.id ) as c where bill_type=1  and $where";

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
            	'',
            	'id',
                'game_id',
                '',
                '',
                'type',
                '',
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

            $sql = "select uid, b.id,b.game_id,gu.game_name,gu.type, gu.company,gu.account_holder, gu.contact, b.settle_id,b.after_tax,b.bill_period,b.status,b.bill_type from game_bills as b left join( select g.uid, g.id, g.name as game_name, o.type, o.company,o.account_holder,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on b.game_id = gu.id where bill_type=1 and $where";

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

            	if ($v['status'] < BillModel::$_status_finance_received) {
            		$financeStatus = '未收票';
            		$payStatus = '待付款';
            	} elseif ($v['status'] < BillModel::$_status_paid) {
            		$financeStatus = '财务收票';
            		$payStatus = ['待付款'];
            	} else {
            		$financeStatus = '财务收票';
            		$payStatus = ['已付款'];
            	}

                $openFinance = OpenFinanceModel::db()->getRow("uid =".$v['uid']." ");
                $row = array(
                	'<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                	$v['id'],
                    $v['game_id'],
                    $v['game_name'],
                    $uname,
                    $accountType[$v['type']],
                    $v['settle_id'],
                    $v['after_tax'],
                    date('Y-m', $v['bill_period']),
                    $financeStatus,
                    $payStatus,
                    '<a href="/finance/purchase/financeProcess/checkBill/id='.$v['id'].'" class="btn btn-xs default blue" data-id="'.$v['id'].'" ><i class="fa fa-file-text"></i> 票据</a>',
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
		$where = " status >=".BillModel::$_status_pay_applied.' ';

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

        $financeStatus = _g("financeStatus");
        if($financeStatus != '') {
        	if ($financeStatus == 0) {
        		$where .= " and status =". BillModel::$_status_pay_applied;
        	}
        	if ($financeStatus == 1) {
        		# 0未收票 1已收票
        		$where .= " and status >=". BillModel::$_status_finance_received;
        	}
        }
        $payStatus = _g("payStatus");
        if($payStatus != '') {
        	if ($payStatus == 0) {
        		# 0待付款 1已付款
        		$where .= " and status <". BillModel::$_status_paid;
        	}
        	if ($payStatus == 1) {
        		# 0待付款 1已付款
        		$where .= " and status >=". BillModel::$_status_paid;
        	}
        }

        return $where;
	}
}