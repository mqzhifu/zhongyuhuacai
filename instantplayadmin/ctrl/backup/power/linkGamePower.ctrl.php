<?php

/**
 * @Author: Kir
 * @Date:   2019-04-12 14:13:05
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-30 16:27:52
 */


/**
 * 
 */
class LinkGamePowerCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign('roleSearch', ['全部','链接游戏','微信游戏']);
		$this->display("power/linkGamePower/index.html");
	}


	function add()
	{
		$this->assign('roleSearch', ['全部','链接游戏','微信游戏']);
		$this->display("power/linkGamePower/account.html");
	}


	function modify()
	{
		$id = _g('id');
		$user = LinkGamePowerModel::db()->getRowById($id);
		$this->assign('user',$user);
		$this->assign('role',LinkGamePowerModel::getRoleDesc($user['role']));
		$this->assign('roleSearch', ['全部','链接游戏','微信游戏']);
		$this->display("power/linkGamePower/account.html");
	}


	function delete()
	{
		$id = _g('id');
		$user = LinkGamePowerModel::db()->getRowById($id);

		if (!$user) {
			exit(0);
		}

		LinkGamePowerModel::db()->delById($id);
		echo(200);
	}


	function submit()
	{
		$uid = _g('uid');
		$name = _g('name');
		$role_id = _g('role_id');
		if ($role_id == 0) {
			$role_id = '1,2';
		}

		if (!UserModel::db()->getRow("id = '$uid'")) {
			exit(0);
		}

		if ($user = LinkGamePowerModel::db()->getRow("uid=$uid")) {
			LinkGamePowerModel::db()->upById($user['id'], ['name'=>$name,'role'=>$role_id,'u_time'=>time()]);
		} else {
			LinkGamePowerModel::db()->add(['uid'=>$uid,'name'=>$name,'role'=>$role_id,'a_time'=>time(),'u_time'=>time()]);
		}
		echo(200);
	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = LinkGamePowerModel::db()->getCount($where);

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

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $roles = LinkGamePowerModel::db()->getAll($where . $order);

            $typeDesc = LinkGamePowerModel::getAccountDescs();

            foreach ($roles as $r) {
    			$row = array(
                    '<input type="checkbox" name="id[]" value="'.$r['id'].'">',
                    $r['id'],
                    $r['uid'],
                    $r['name'],
                    LinkGamePowerModel::getRoleDesc($r['role']),
                    get_default_date($r['u_time']),
                    '<a href="/power/no/linkGamePower/modify/id='.$r['id'].'" class="btn btn-xs default blue" data-id="'.$r['id'].'"><i class="fa fa-file-text"></i> 修改</a>',
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

        if($name = _g("name"))
            $where .= " and name like '%$name%' ";

        if($uid = _g("uid"))
            $where .= " and uid = $uid ";

        if(_g("role") != '')
        {
        	$role = _g("role");
        	if ($role == 0) {
        		$role = '1,2';
        	}
            $where .= " and role = '$role' ";
        }

        if($from = _g("from_u")){
            $where .= " and u_time >= '".strtotime($from)."' ";
        }

        if($to = _g("to_u")){
            $where .= " and u_time <= '".strtotime("+1 day $to")."' ";
        }
        return $where;
	}
}