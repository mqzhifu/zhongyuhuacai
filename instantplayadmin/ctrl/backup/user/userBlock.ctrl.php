<?php

/**
 * @Author: Kir
 * @Date:   2019-04-15 17:39:23
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-28 14:42:25
 */

/**
 * 
 */
class UserBlockCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign('operate', UserBlockLogModel::getOperateDesc());
		$this->display("user/user_block.html");
	}


	function operate()
	{
		$this->assign('durationDesc', UserBlockLogModel::getDurationDesc());
		$this->assign('operate', UserBlockLogModel::getOperateDesc());
		$this->display("user/user_block_operate.html");
	}


	function block()
	{
		$admin = $this->_adminid;
		$uid = _g("uid");
		$type = _g("type");
		$duration = _g("duration");
		$detail = _g("reason");
		if (!$uid || !$type || !$duration || !$detail || !$admin) {
			$this->outputJson(0,"提交信息不全");
		}
		if ($type != UserBlockLogModel::$_block) {
			$this->outputJson(0,"类型错误");
		}
		if (!$user = UserModel::db()->getRowById($uid)) {
			$this->outputJson(0,"UID不存在");
		}

        UserBlackModel::block($uid, 1, $detail, time()+$duration);
		UserBlockLogModel::addLog($uid,$type,$admin,$duration,$detail);

		$this->outputJson(200,"success");
	}

	function unblock()
	{
		$admin = $this->_adminid;
		$uid = _g("uid");
		$type = _g("type");
		if (!$uid || $type != UserBlockLogModel::$_unblock) {
			$this->outputJson(0,"提交信息有误");
		}
		if (!$user = UserModel::db()->getRowById($uid)) {
			$this->outputJson(0,"UID不存在");
		}
		if (!UserBlackModel::isBlocked($uid)) {
			$this->outputJson(0,"用户未被封停");
		} else {
			UserBlackModel::db()->update(['status'=>2], "uid = $uid limit 1");
		}
		UserBlockLogModel::addLog($uid,$type,$admin);
		$this->outputJson(200,"success");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = UserBlockLogModel::db()->getCount($where);

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

            $logs = UserBlockLogModel::db()->getAll($where . $order);

            foreach ($logs as $log) {
    			$log = array(
                    '<input type="checkbox" name="id[]" value="'.$log['id'].'">',
                    $log['id'],
                    $log['uid'],
                    UserBlockLogModel::getOperateDesc()[$log['type']],
                    $log['detail'],
                    $this->revertTime($log['duration']),
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

	function revertTime($timestamp)
	{
		if (!$timestamp) {
			return '';
		}
		if ($timestr = UserBlockLogModel::getDurationDesc()[$timestamp]) {
			return $timestr;
		}
		else {
			return ($timestamp/60)."分钟";
		}
			
	}

	function getWhere()
	{
		$where = " 1 ";

        if($uid = _g("uid"))
            $where .= " and uid = $uid ";

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