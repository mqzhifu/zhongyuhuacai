<?php

/**
 * @Author: Kir
 * @Date:   2019-06-12 15:14:08
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-17 11:12:18
 */


/**
 * 
 */
class GroupCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("adTypeDesc", AppAdvertiseModel::getAdvertiseTypeDesc());
		$this->assign('advertiser_type',AppAdvertiseModel::getAdvertiserTypeDesc());
        $this->assign('originStatusDesc',ForeignAdOriginModel::getStatusDesc());
		$this->display("ad_en/group.html");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = ForeignAdGroupModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $data = ForeignAdGroupModel::db()->getAll("$where ORDER BY u_time DESC LIMIT $iDisplayStart,$iDisplayLength ");

            foreach ($data as $val) {
    			$row = array(
                    $val['name'],
                    AppAdvertiseModel::getAdvertiseTypeDesc()[$val['ad_type']],
                    get_default_date($val['a_time']),
                    get_default_date($val['u_time']),
                    '<button type="button"  attr-id='.$val['id'].' onclick="getGroup(this)" class="btn btn-sm default blue">编辑</button> <button type="button"  attr-id='.$val['id'].' onclick="getStrategy(this)" class="btn btn-sm default green">策略</button>',
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
		$where = "1";
		
		if ($ad_type = _g("ad_type")) {
			$where .= " and ad_type = $ad_type";
		}

		if ($name = _g("name")) {
			$where .= " and name like '%$name%'";
		}

		if ($from_a = _g("from_a")) {
            $where .= " and a_time >= '".strtotime($from_a)."'";
        }

        if ($to_a = _g("to_a")) {
            $where .= " and a_time <= '".strtotime("$to_a +1 day")."'";
        }

        if ($from_u = _g("from_u")) {
            $where .= " and u_time >= '".strtotime($from_u)."'";
        }

        if ($to_u = _g("to_u")) {
            $where .= " and u_time <= '".strtotime("$to_u +1 day")."'";
        }

		return $where;
	}


    function getGroup() 
    {
        $group_id = _g("group_id");
        $group = ForeignAdGroupModel::db()->getById($group_id);

        if (!$group) {
            $this->outputJson(0, "未找到分组信息");
        }

        $origins = ForeignAdOriginModel::db()->getAll("group_id = $group_id order by sort desc");

        foreach ($origins as &$org) {
            $org['status_desc'] = ForeignAdOriginModel::getStatusDesc()[$org['status']];
            $org['advertiser_desc'] = AppAdvertiseModel::getAdvertiserTypeDesc()[$org['advertiser']];
        }

        return $this->outputJson(200, "succ", ["group"=>$group, "origins"=>$origins]);
    }


    function setGroup() 
    {
        $name = _g("name");
        $ad_type = _g("ad_type");
        $origins = _g("origins");

        if (!$name || !$ad_type || !is_numeric($ad_type)) {
            $this->outputJson(0, "参数有误");
        }

        $now = time();
        $group = [
            'name' => $name,
            'ad_type' => $ad_type,
            'u_time' => $now,
        ];

        $group_id = _g("group_id");

        if ($group_id) {
            ForeignAdGroupModel::db()->upById($group_id, $group);
        } else {
            $group['a_time'] = $now;
            $group_id = ForeignAdGroupModel::db()->add($group);
        }

        if (!$group_id) {
            $this->outputJson(0, "fail");
        }

        foreach ($origins as $unit) {
            if (!$unit['advertiser'] || !$unit['outer_id']) {
                $this->outputJson(0, "参数有误2");
            }
            $unit['group_id'] = $group_id;
            $unit['status'] = 1;
            $unit['a_time'] = $now;
            $unit['u_time'] = $now;

            $origin_id = ForeignAdOriginModel::db()->add($unit);

            if (!$origin_id) {
                $this->outputJson(0, "fail");
            }
        }

        $this->outputJson(200, "succ");
    }


    function setOrigin() 
    {
        $advertiser = _g("advertiser");
        $outer_id = _g("outer_id");
        $group_id = _g("group_id");
        $sort = _g("sort");
        $status = _g("status");

        $now = time();
        $origin = ['u_time' => $now];

        if ($advertiser) {
            $origin['advertiser'] = $advertiser;
        }

        if ($outer_id) {
            $origin['outer_id'] = $outer_id;
        }

        if ($group_id) {
            $origin['group_id'] = $group_id;
        }

        if ($sort != '') {
            $origin['sort'] = $sort;
        }

        if ($status) {
            $origin['status'] = $status;
        }

        $origin_id = _g("origin_id");

        if ($origin_id) {
            ForeignAdOriginModel::db()->upById($origin_id, $origin);
        } else {
            $origin['a_time'] = $now;
            $origin['status'] = 1;
            $origin_id = ForeignAdOriginModel::db()->add($origin);
        }

        $this->outputJson(200, "succ", $origin_id);

    }


    function deleteOrigin()
    {
        $origin_id = _g("origin_id");

        if (!$origin_id) {
            $this->outputJson(0, "参数有误");
        }

        if (!ForeignAdOriginModel::db()->getById($origin_id)) {
            $this->outputJson(0, "fail");
        }

        ForeignAdOriginModel::db()->delById($origin_id);

        $this->outputJson(200, "succ");
    }

}