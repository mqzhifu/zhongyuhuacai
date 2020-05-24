<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");

/**
 * @Author: Kir
 * @Date:   2019-03-18 10:14:58
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-05 14:24:19
 */


/**
 * 
 */
class MoneyCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign('stateDesc', MoneyOrderModel::getStateDesc());
        $this->assign('typeDesc', MoneyOrderModel::getTypeDesc());
		$this->display("app_manager/money/money.html");
	}


	function detail()
	{
		if ($id = _g('id')) {
			$sql = "select m.id,m.num,m.uid,m.status,m.state,m.element_id,m.thir_back_info,m.a_time,m.u_time,u.nickname,u.goldcoin from (money_order as m left join user as u on m.uid = u.id) where m.id = $id";
			if ($order = MoneyOrderModel::db()->getRowBySQL($sql)) {
				$order['goldcoin'] = number_format($order['goldcoin']/10000, 1);
                $order['thir_back_info'] = json_decode(json_encode(simplexml_load_string($order['thir_back_info'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
                // $order['thir_back_info'] = json_encode($order['thir_back_info']);
				$this->assign('order', $order);
			}
		}
		$stateDesc = MoneyOrderModel::getStateDesc();
		unset($stateDesc[MoneyOrderModel::$_state_unaudited]);
		$this->assign('stateDesc', $stateDesc);
		$this->display('app_manager/money/audit.html');
	}


	function audit()
	{
        if (!$admin_uid = $this->_adminid) {
            exit(0);
        }
		$id = _g('id');
		$state = _g('state');
		$stateDesc = MoneyOrderModel::getStateDesc();
		unset($stateDesc[MoneyOrderModel::$_state_unaudited]);

		if (!array_key_exists($state, $stateDesc)) {
			echo json_encode(0);
        	exit;
		}

        $info = MoneyOrderModel::db()->getById($id);
        if($info['state']){
            exit("该订单已被审核过请不要重复审核，避免错误！");
        }

        $getMoneyRs = array('code'=>7020);
        if($state == 1){
            include_once CONFIG_DIR . IS_NAME."/main.php";
            $lib =  new BankService();
            $getMoneyRs = $lib->getMoney($info['uid'],$info['element_id'],$id);
            LogLib::appWriteFileHash($getMoneyRs);
            if($getMoneyRs['code'] == 200){

            }else{
                $state = 2;
            }
        }

        $status = 2;
        if($state == 1){
            $status = 1;
        }

        $info = MoneyOrderModel::db()->getById($id);

        MoneyOrderModel::db()->upById($id, ['state'=>$state, 'admin_uid'=>$admin_uid, 'u_time'=>time()]);


        $data = "500000000000##{$info['num']}##{$status}##{$info['a_time']}##".time()."##{$info['in_trade_no']}##{$getMoneyRs['code']}##1##开心小游戏";
        LogLib::appWriteFileHash($data);

        $lib = new ImTencentLib();
        $rs = $lib->adminSendOne($info['uid'],$data,4);
        LogLib::appWriteFileHash($rs);


        if($state == 1){

        }else{
            $userS = new UserService();
            $rs = $userS->addGoldcoin($info['uid'],$info['num'] * 10000,GoldcoinLogModel::$_type_get_money_back);
            LogLib::appWriteFileHash($rs);
        }


        $content = "200000000000";
        $lib = new PushXinGeLib();
        $rs = $lib->pushAndroidMsgOneMsgByToken($info['uid'],"upUserGoldInfo",$content);
        $rs [] =  "upUserGoldInfo= ==== == = = = =    pushAndroidMsgOneMsgByToken ";

        LogLib::appWriteFileHash($rs);

        echo json_encode(200);
        exit;
	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from (select m.id,m.num,m.uid,m.status,m.state,m.type,m.element_id,m.a_time,m.u_time,u.nickname,u.goldcoin from (money_order as m left join user as u on m.uid = u.id) where $where ) as c";

        $cntSql = MoneyOrderModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数

        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                'uid',
                '',
                'balance',
                'num',
                '',
                '',
                'a_time',
                'u_time',
                '',
            );

            $order = " order by " .$sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $sql = "select m.id,m.num,m.uid,m.status,m.state,m.type,m.element_id,m.a_time,m.u_time,u.nickname,m.balance from (money_order as m left join user as u on m.uid = u.id) where $where";

            $orders = MoneyOrderModel::db()->getAllBySQL($sql . $order);
            $stateDesc = MoneyOrderModel::getStateDesc();
            $typeDesc = MoneyOrderModel::getTypeDesc();

            foreach ($orders as $od) {
    			$row = array(
                    '<input type="checkbox" name="id[]" value="'.$od['id'].'">',
                    $od['id'],
                    $od['uid'],
                    $od['nickname'],
                    number_format($od['balance']/10000, 1),
                    $od['num'],
                    $typeDesc[$od['type']],
                    $stateDesc[$od['state']],
                    get_default_date($od['a_time']),
                    get_default_date($od['u_time']),
                    '<a href="/app_manager/no/money/detail/id='.$od['id'].'" class="btn btn-icon-only blue"><i class="fa fa-edit"></i></a>',
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
        $uid = _g("uid");
        $nickname = _g("nickname");
		$money = _g("money");
        $type = _g("type");
		$state = _g("state");
        $from_a = _g("from_a");
        $to_a = _g("to_a");
        $from_u = _g("from_u");
        $to_u = _g("to_u");

        if (!is_null($uid) && $uid!='')
            $where .= " and m.uid = '$uid'";

        if (!is_null($nickname) && $nickname!='')
            $where .= " and u.nickname like '%$nickname%'";

        if (!is_null($from_a) && $from_a!='') {
            $where .= " and m.a_time >= '".strtotime($from_a)."'";
        }

        if (!is_null($to_a) && $to_a!='') {
            $where .= " and m.a_time <= '".strtotime("$to_a +1 day")."'";
        }

        if (!is_null($from_u) && $from_u!='') {
            $where .= " and m.u_time >= '".strtotime($from_u)."'";
        }

        if (!is_null($to_u) && $to_u!='') {
            $where .= " and m.u_time <= '".strtotime("$to_u +1 day")."'";
        }

        if (!is_null($money) && $money!='')
            $where .= " and m.num = '$money'";

        if (!is_null($state) && $state!='')
            $where .= " and m.state = '$state'";

        if (!is_null($type) && $type!='')
            $where .= " and m.type = '$type'";

        return $where;
    }
}