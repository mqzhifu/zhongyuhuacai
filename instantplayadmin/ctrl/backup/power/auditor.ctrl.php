<?php

/**
 * @Author: xuren
 * @Date:   2019-03-28 16:45:18
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-28 17:48:50
 */
class AuditorCtrl extends BaseCtrl{
	function index(){
		if(_g('getList')){
	 		$this->getList();
	 	}
		$this->display('power/app_game_auditor.html');
	}

	function getList(){
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $sql = "select count(*) as cnt from app_manager a inner join user u on a.uid=u.id where $where";
        
        $cntSql = IncomeModel::db()->getRowBySQL($sql);

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
                'a.id',
                '',
                '',
                '',
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

            $sql = "select a.id,a.uid,u.nickname from app_manager a inner join user u on a.uid=u.id where $where GROUP BY a.id $order limit $iDisplayStart,$end ";

            $data = AppManagerModel::db()->getAllBySQL($sql);
            // $roles = RolesModel::db()->getAll();
            // $roleNames = [];
            // foreach ($roles as $role) {
            //     $roleNames[$role['id']] = $role['name'];
            // }

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    $v['nickname'],
                    '<a href="javascript:void(0)" class="btn btn-xs default red" data-id="'.$v['id'].'" onclick="delOne(this)"><i class="fa fa-trash-o"></i>删除</a>',
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
        if($uid = _g("uname"))
            $where .= " and a.uid=$uid ";

        if($nickname = _g("nickname"))
            $where .= " and u.nickname like '%$nickname%' ";

        return $where;
    }




    function addOne(){
    	$uid = _g('uid');
    	if(!$uid || $uid=='undefined'){
    		echo(0);
    		return;
    	}

    	$add = [];
    	$add['uid'] = $uid;
    	$res = AppManagerModel::db()->add($add);
    	if(!$res){
    		echo(0);
    		return;
    	}

    	echo(1);

    }

    function delOne(){
    	$id = _g('id');
    	if(!$id || $id=='undefined'){
    		echo(0);
    		return;
    	}

    	$where = "id=$id limit 1";
    	$res = AppManagerModel::db()->delete($where);
    	if(!$res){
    		echo(0);
    		return;
    	}

    	echo(1);
    }
}