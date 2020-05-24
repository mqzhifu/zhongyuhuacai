<?php

/**
 * @Author: Kir
 * @Date:   2019-03-20 20:04:09
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-31 17:31:33
 */


/**
 * 
 */
class MoneyDivideCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("accountType", OpenUserModel::getAccountDescs());
		$this->display("/finance/ad/money_divide/index.html");
	}


	function divideSetting()
	{
		if (!$game_id = _g('game_id')) {
            exit(0);
        }
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

        $finance = FinanceModel::db()->getRow("game_id = $game_id and finance_type =".FinanceModel::$type_ad);
        $finance['contract_start_date'] = date('Y-m-d', $finance['contract_start_date']);
        $finance['contract_end_date'] = date('Y-m-d', $finance['contract_end_date']);
        $this->assign("finance", $finance);

        $this->assign("game_id", $game_id);
        $this->assign("contract_body", FinanceModel::$_contract_body_desc);
        $this->assign("account_period", FinanceModel::$_account_period_desc);
        $this->assign("accountType", OpenUserModel::getAccountDescs());
        $this->display("/finance/ad/money_divide/divide_setting.html");
	}

	function save()
	{
		if (!$game_id = _g('game_id')) {
			exit(0);
		}
		$finance = FinanceModel::db()->getRow("game_id = $game_id and finance_type=".FinanceModel::$type_ad);

		$data = [
            'finance_type'=>FinanceModel::$type_ad,
			'game_id'=>_g("game_id"),
			'contract_start_date'=>strtotime(_g("contract_start_date")),
			'contract_end_date'=>strtotime(_g("contract_end_date")),
			'contract_body'=>_g("contract_body"),
			'account_period'=>_g("account_period"),
			'divide'=>number_format(_g("divide")/100, 3),
			'slotting_allowance'=>number_format(_g("slotting_allowance")/100, 3),
			'u_time'=>time(),
		];

		if (!$finance) {
			$data['a_time'] = time();
			if (FinanceModel::db()->add($data)) {
				$settleAccountService = new SettleAccountService();
                $result = $settleAccountService->recomputeDivideAndTax($game_id, FinanceModel::$type_ad);
                if($result){
                    echo json_encode(200);
                    exit;
                }
			}
		} else {
			if (FinanceModel::db()->update($data, "game_id = $game_id and finance_type=".FinanceModel::$type_ad." limit 1")) {
                // 保存分成比例时，动态更新未结算的账单。2019/05/28 by xuren
                if($finance['divide']==$data['divide'] || $finance['slotting_allowance']==$data['slotting_allowance']){
                    $settleAccountService = new SettleAccountService();
                    $result = $settleAccountService->recomputeDivideAndTax($game_id, FinanceModel::$type_ad);
                    if($result){
                        echo json_encode(200);
                        exit;
                    }
                }
				
			}

		}

		echo json_encode(0);
        exit;

	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from (select gu.id as game_id,gu.game_name,gu.type, gu.company, gu.contact,f.divide,f.slotting_allowance,f.contract_start_date,f.contract_end_date,f.u_time from game_finance as f right join( select g.id, g.name as game_name, o.type, o.company,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on f.game_id = gu.id ) as c where $where";

        $cntSql = FinanceModel::db()->getRowBySQL($sql);
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
                'game_id',
                '',
                '',
                'type',
                'divide',
                'slotting_allowance',
                'contract_start_date',
                'contract_end_date',
                'u_time',
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

            $sql = "select * from (select gu.id as game_id,gu.game_name,gu.type, gu.company,gu.account_holder, gu.contact,f.divide,f.slotting_allowance,f.contract_start_date,f.contract_end_date,f.u_time from (select * from game_finance where finance_type=".FinanceModel::$type_ad.") as f right join( select g.id, g.name as game_name, o.type, o.company,o.account_holder,o.contact from games as g left join open_user as o on g.uid=o.uid) as gu on f.game_id = gu.id) as c where $where";

            $data = FinanceModel::db()->getAllBySQL($sql. $order. " limit $iDisplayStart,$iDisplayLength ");
            $accountType = OpenUserModel::getAccountDescs();

            foreach($data as $k=>$v){
            	// 1公司 2个人
            	if ($v['type'] == 1) {
            		$uname = $v['company'];
            	} elseif ($v['type'] == 2) {
            		$uname = $v['account_holder'];
            	}
                $row = array(
                    $v['game_id'],
                    $v['game_name'],
                    $uname,
                    $accountType[$v['type']],
                    $v['divide']*100 . '%',
                    $v['slotting_allowance']*100 . '%',
                    date('Y-m-d', $v['contract_start_date']),
                    date('Y-m-d', $v['contract_end_date']),
                    get_default_date($v['u_time']),
                    '<a href="/finance/ad/moneyDivide/divideSetting/game_id='.$v['game_id'].'" class="btn btn-xs default blue" data-id="'.$v['game_id'].'" ><i class="fa fa-file-text"></i> 编辑</a>',
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

        if($from = _g("from")){
            $where .= " and u_time >= '".strtotime($from)."' ";
        }

        if($to = _g("to")){
            $where .= " and u_time <= '".strtotime("+1 day $to")."' ";
        }

        // $where .= " and finance_type=".FinanceModel::$type_ad." ";
        return $where;
	}
}