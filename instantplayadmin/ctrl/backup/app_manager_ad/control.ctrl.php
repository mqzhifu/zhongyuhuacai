<?php

/**
 * @Author: Kir
 * @Date:   2019-06-03 20:53:47
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-17 15:02:08
 */

/**
 * 
 */
class ControlCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("adTypeDesc", AppAdvertiseModel::getAdvertiseTypeDesc());
		$this->assign("ctrlTypeDesc", AdCtrlModel::getCtrlTypeDesc());
		$this->assign("timeCtrlTypeDesc", AdCtrlModel::getTimeCtrlTypeDesc());
		$this->assign("userTypeDesc", AdCtrlModel::getUserTypeDesc());
		$this->display("app_manager_ad/control.html");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = AdCtrlModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $data = AdCtrlModel::db()->getAllBySql("
            	SELECT
					ac.*,
					admin_user.nickname 
				FROM
					( SELECT * FROM ad_ctrl WHERE $where LIMIT $iDisplayStart,$iDisplayLength ) AS ac
					LEFT JOIN admin_user ON ac.admin = admin_user.id
				ORDER BY u_time DESC
            ");

            foreach ($data as $val) {
            	$status = '有效';
            	$timeRange = '';
            	if ($val['time_ctrl_type'] == AdCtrlModel::$_time_ctrl_type_set) {
            		if ($val['to'] < time()) {
            			$status = '失效';
            		}
            		$timeRange = date("Y-m-d", $val['from']) .' 至 '. date("Y-m-d", $val['to']);
            	} elseif ($val['time_ctrl_type'] == AdCtrlModel::$_time_ctrl_type_forever) {
            		$timeRange = '永久';
            	}

            	if (PCK_AREA == 'en') {
            		$row = array(
	                    $status,
	                    AppAdvertiseModel::getAdvertiseTypeDesc()[$val['ad_type']],
	                    AdCtrlModel::getCtrlTypeDesc()[$val['ctrl_type']],
	                    AdCtrlModel::getTimeCtrlTypeDesc()[$val['time_ctrl_type']],
	                    $timeRange,
	                    AdCtrlModel::getUserTypeDesc()[$val['user_type']],
	                    $val['nickname'],
	                    get_default_date($val['a_time']),
	                    get_default_date($val['u_time']),
	                    '<button type="button" name="editCtrl" attr-id='.$val['id'].' onclick="getConfig(this)" class="btn btn-sm default blue">编辑</button>',
	                );
            	} else {
            		$row = array(
	                    $status,
	                    AppAdvertiseModel::getAdvertiseTypeDesc()[$val['ad_type']],
	                    $val['ad_id'],
	                    AdCtrlModel::getCtrlTypeDesc()[$val['ctrl_type']],
	                    AdCtrlModel::getTimeCtrlTypeDesc()[$val['time_ctrl_type']],
	                    $timeRange,
	                    AdCtrlModel::getUserTypeDesc()[$val['user_type']],
	                    $val['nickname'],
	                    get_default_date($val['a_time']),
	                    get_default_date($val['u_time']),
	                    '<button type="button" name="editCtrl" attr-id='.$val['id'].' onclick="getConfig(this)" class="btn btn-sm default blue">编辑</button>',
	                );
            	}

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
		$where = "type = 2";
		if ($ctrl_status = _g("ctrl_status")) {
			$now = time();
			if ($ctrl_status == 1) {
				$where .= " and ( time_ctrl_type = 1 or (time_ctrl_type = 2 and `from` < $now and `to` > $now)) ";
			} elseif ($ctrl_status == 2) {
				$where .= " and (time_ctrl_type = 2 and (`to` < $now or `from` > $now)) ";
			}
		}
		if ($ad_type = _g("ad_type")) {
			$where .= " and ad_type = $ad_type";
		}

		if ($ad_id = _g("ad_id")) {
			$where .= " and ad_id like '%$ad_id%'";
		}

		if ($ctrl_type = _g("ctrl_type")) {
			$where .= " and ctrl_type = $ctrl_type";
		}

		if ($time_ctrl_type = _g("time_ctrl_type")) {
			$where .= " and time_ctrl_type = $time_ctrl_type";
		}

		$user_type = _g("user_type");
		if ($user_type != '') {
			$where .= " and user_type = $user_type";
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


	function getConfig()
	{
		if (!$id = _g('id')) {
			$this->outputJson(0, "参数有误");
		}
		$record = AdCtrlModel::db()->getRowById($id);
		if (!$record) {
			$this->outputJson(0, "查找不到该记录");
		}
		if ($record['time_ctrl_type'] == AdCtrlModel::$_time_ctrl_type_set) {
			$record['from'] = date("Y-m-d", $record['from']);
			$record['to'] = date("Y-m-d", $record['to']);
		}

		$where = "advertise_type = {$record['ad_type']}";

		$adIds = AppAdvertiseModel::db()->getAllBySql("select id from app_advertise where $where");

		$this->outputJson(200, "succ", ['record'=>$record, 'adIds'=>$adIds]);	
	}


	function getAdIds()
	{
		$adType = _g('adType');

		if (!$adType = _g('adType')) {
			$this->outputJson(0, "请选择广告类型");
		}

		$where = "advertise_type = $adType";

		$adIds = AppAdvertiseModel::db()->getAllBySql("select id from app_advertise where $where");

		$this->outputJson(200, "succ", $adIds);
	}


	function save()
	{
		$id = _g("id");
		$isNew = _g("isNew");
		if (!$id && !$isNew) {
			$this->outputJson(0, "参数错误");
		}
		if ($id) {
			$record = AdCtrlModel::db()->getRowById($id);
			if (!$record) {
				$this->outputJson(0, "查找不到该记录");
			}
		}
		$adType = _g("adType");
		$ctrlType = _g("ctrlType");
		$timeCtrlType = _g("timeCtrlType");
		$userType = _g("userType");

		if ($adType == '' || $ctrlType == '' || $timeCtrlType == '' || $userType == '') {
			$this->outputJson(0, "缺少参数");
		}

		$data = [
			'type' => 2,
			'ad_type' => $adType,
			'ctrl_type' => $ctrlType,
			'time_ctrl_type' => $timeCtrlType,
			'user_type' => $userType,
			'u_time' => time(),
		];

		$now = time();
		$where = "type=2 and ad_type=$adType and ctrl_type=$ctrlType and ( time_ctrl_type = 1 or (time_ctrl_type = 2 and `from` < $now and `to` > $now)) ";

		if ($adId) {
			$data['ad_id'] = $adId;
			$where .= " and ad_id = $adId ";
		}

		if ($ctrlType == AdCtrlModel::$_ctrl_type_frequency) {
			$frequencyType = _g("frequencyType");
			if ($frequencyType == '') {
				$this->outputJson(0, "缺少参数");
			}
			$where .= " and frequency_type=$frequencyType ";

			if ($frequencyType == AdCtrlModel::$_frequency_type_interval) {
				$interval = _g("interval");
				if ($interval == '') {
					$this->outputJson(0, "缺少参数");
				}
				$data['frequency_type'] = $frequencyType;
				$data['interval'] = $interval;

			} elseif ($frequencyType == AdCtrlModel::$_frequency_type_times) {
				$times = _g("times");
				$period = _g("period");
				if ($times == '' || $period == '') {
					$this->outputJson(0, "缺少参数");
				}
				$data['frequency_type'] = $frequencyType;
				$data['times'] = $times;
				$data['period'] = $period;
			} else {
				$this->outputJson(0, "频次控制类型错误");
			}
		} elseif ($ctrlType == AdCtrlModel::$_ctrl_type_state) {
			$state = _g("state");
			if ($state == '') {
				$this->outputJson(0, "缺少参数");
			}
			$where .= " and state=$state ";
			$data['state'] = $state;
		} else {
			$this->outputJson(0, "操作控制类型错误");
		}

		$from = _g("from");
		$to = _g("to");
		if ($timeCtrlType == AdCtrlModel::$_time_ctrl_type_set) {
			if ($from == '' || $to == '') {
				$this->outputJson(0, "缺少参数");
			}
			$from = strtotime($from);
			$to = strtotime("$to 23:59:59");
			if ($from >= $to) {
				$this->outputJson(0, "开始时间晚于结束时间");
			}
			$data['from'] = $from;
			$data['to'] = $to;
		}

		if ($isNew) {
			if ($this->isConflict($where, $timeCtrlType, $userType, $from, $to)) {
				$this->outputJson(0, "新配置和旧配置有冲突，请检查");
			} else {
				$data['admin'] = $this->_adminid;
				$data['a_time'] = time();
				if (AdCtrlModel::db()->add($data)) {
					$this->outputJson(200, "添加成功");
				}
			}
		} else {
			$where .= " and id!=$id ";
			if ($this->isConflict($where, $timeCtrlType, $userType, $from, $to)) {
				$this->outputJson(0, "新配置和旧配置有冲突，请检查");
			} elseif (AdCtrlModel::db()->upById($id, $data)) {
				$this->outputJson(200, "更新成功");
			}
		}
		$this->outputJson(0, "失败");
	}

	function isConflict($where, $timeCtrlType, $userType, $from=0, $to=0) 
	{
		$records = AdCtrlModel::db()->getAll($where);

		if (!$records) {
			return false;
		}

		foreach ($records as $rec) {
			if ($this->isTimeCross($timeCtrlType, $rec['time_ctrl_type'], $from, $to, $rec['from'], $rec['to'])) {
				if ($userType == AdCtrlModel::$_user_type_all || $rec['user_type'] == AdCtrlModel::$_user_type_all) {
					return true;
				} elseif ($userType == $rec['user_type']) {
					return true;
				}
			}
		}

		return false;
	}

	function isTimeCross($type1, $type2, $beginTime1=0, $endTime1=0, $beginTime2=0, $endTime2=0) {
		if ($type1 == AdCtrlModel::$_time_ctrl_type_forever 
			|| $type2 == AdCtrlModel::$_time_ctrl_type_forever) {
			return true;
		}
        $status = $beginTime2 - $beginTime1;
        if($status > 0){
            $status2 = $beginTime2 - $endTime1;
            if($status2 >= 0){
                return false; // 无交集
            }else{
                return true; // 有交集
            }
        }else{
            $status2 = $endTime2 - $beginTime1;
            if($status2 > 0){
                return true;
            }else{
                return false;
            }
        }
	}
}