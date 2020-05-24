<?php

/**
 * @Author: Kir
 * @Date:   2019-04-16 17:10:47
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-28 14:42:16
 */

/**
 * 
 */
class IpBlockCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign('operate', IpBlockLogModel::getOperateDesc());
		$this->display("user/ip_block.html");
	}


	function operate()
	{
		$this->assign('operate', IpBlockLogModel::getOperateDesc());
		$this->display("user/ip_block_operate.html");
	}


	function block()
	{
		$admin = $this->_adminid;
		$ip = _g("ip");
		$type = _g("type");
		$detail = _g("reason");
		if (!$ip || !$type || !$detail || !$admin) {
			$this->outputJson(0,"提交信息不全");
		}
		if ($type != IpBlockLogModel::$_block) {
			$this->outputJson(0,"类型错误");
		}

        IpBlockModel::block($ip, IpBlockModel::$_type_access_too_frequent, $detail);
		IpBlockLogModel::addLog($ip,$type,$admin,$detail);
		$this->outputJson(200,"success");
	}

	function unblock()
	{
		$admin = $this->_adminid;
		$ip = _g("ip");
		$type = _g("type");
		if (!$ip || $type != IpBlockLogModel::$_unblock) {
			$this->outputJson(0,"提交信息有误");
		}

        if (!IpBlockModel::isBlocked($ip)) {
            $this->outputJson(0,"该IP未被封停");
        } else {
            IpBlockModel::db()->update(['status'=>IpBlockModel::$_status_unblocked], "ip = '$ip' limit 1");
        }

		IpBlockLogModel::addLog($ip,$type,$admin);
		$this->outputJson(200,"success");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = IpBlockLogModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                '',
                '',
                '',
                'duration',
                '',
                'a_time',
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

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $logs = IpBlockLogModel::db()->getAll($where . $order);

            foreach ($logs as $log) {
    			$log = array(
                    '<input type="checkbox" name="id[]" value="'.$log['id'].'">',
                    $log['id'],
                    $log['ip'],
                    IpBlockLogModel::getOperateDesc()[$log['type']],
                    $log['detail'],
                    AdminUserModel::getFieldById($log['admin'],'nickname'),
                    get_default_date($log['a_time']),
                    '',
                );
                $records["data"][] = $log;
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

        if($ip = _g("ip"))
            $where .= " and ip like '%$ip%' ";

        if($type = _g("type"))
            $where .= " and type = $type ";

        if($from = _g("from")){
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $where .= " and a_time <= '".strtotime("$to +1 day")."'";
        }
        return $where;
	}
}