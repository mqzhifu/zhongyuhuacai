<?php

/**
 * @Author: Kir
 * @Date:   2019-03-18 20:26:46
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-09 11:27:14
 */


class GoldcoinSummaryCtrl extends BaseCtrl
{
	function index()
	{
		$type = _g('type');
		if (!$type) {
			$type=1;
		}
		if ($type == 1) 
			$title = ['获取类型', '类型描述', '产出金币', '次数', '人数', '金币占比', '人数占比'];
		elseif ($type == 2) 
			$title = ['获取类型', '类型描述', '消耗金币', '次数', '人数', '金币占比', '人数占比'];
		elseif ($type == 3) 
			$title = ['日期', '产出金币', '消耗金币', '未消耗金币', '总产出金币', '总消耗金币', '总未消耗金币'];

		$this->assign('dateNow', date('Y-m-d H:i', time()));
		$this->assign('selected', $type);
		$this->assign('title', $title);
		$this->assign('logs', $this->getList($type));
		$this->assign('type', [1=>'产出金币', 2=>'消耗金币', 3=>'汇总']);

		if ($from = _g('from')) {
			$this->assign('from', $from);
		}
		if ($to = _g('to')) {
			$this->assign('to', $to);
		}
		$this->display("app_cnt/goldcoin_summary/index.html");
	}

	function getList($type)
	{
		$records = array();

        $where = $this->getWhere();

        if ($type == 3) {
        	$sql = "select opt, sum(num) as coins from goldcoin_log group by opt";
	        $summary = GoldcoinLogModel::db()->getAllBySQL($sql);

	        $sql = "select date_format(from_unixtime(a_time), '%Y-%m-%d') as date, sum(num) as coins from goldcoin_log where $where and opt=1 group by date";
	        $adds = GoldcoinLogModel::db()->getAllBySQL($sql);

	        $sql = "select date_format(from_unixtime(a_time), '%Y-%m-%d') as date, sum(num) as coins from goldcoin_log where $where and opt=2 group by date";
	        $minus = GoldcoinLogModel::db()->getAllBySQL($sql);

	        $total_add=0;
	        $total_minus=0;
	        foreach ($summary as $sum) {
	        	if ($sum['opt'] == 1) {
	        		$total_add = $sum['coins'];
	        	} elseif ($sum['opt'] == 2) {
	        		$total_minus = $sum['coins'];
	        	}
	        }

	        foreach ($adds as $add) {
		        $row = [
		        	'date'=>$add['date'],
		        	'add'=>$add['coins'],
		        	'minus'=>0,
		        	'remain'=>$add['coins'],
		        	'total_add'=>$total_add,
		        	'total_minus'=>$total_minus,
		        	'total_remain'=>$total_add+$total_minus,
        		];
        		$records[] = $row;
	        }

	        foreach ($minus as $mis) {
	        	$hasSet = false;
	        	foreach ($records as &$rec) {
	        		if ($mis['date'] == $rec['date']) {
	        			$rec['minus'] = $mis['coins'];
	        			$rec['remain'] += $rec['minus'];
	        			$hasSet = true;
	        			break;
	        		}
	        	}

	        	if (!$hasSet) {
	        		$row = [
			        	'date'=>$mis['date'],
			        	'add'=>0,
			        	'minus'=>$mis['coins'],
			        	'remain'=>$mis['coins'],
			        	'total_add'=>$total_add,
			        	'total_minus'=>$total_minus,
			        	'total_remain'=>$total_add+$total_minus,
		    		];
		    		$records[] = $row;
	        	}
	        }

	        $dates = array_column($records, 'date');
	        array_multisort($dates, SORT_DESC, $records);

        } else {
        	$sql = "select sum(num) as coins, count(id) as cnts from goldcoin_log where $where and opt=$type";
	        $summary = GoldcoinLogModel::db()->getRowBySQL($sql);

	        $sql = "select type, sum(num) as coins, count(id) as cnt, count(distinct uid) as users from goldcoin_log where $where and opt=$type group by type";
	        $logs = GoldcoinLogModel::db()->getAllBySQL($sql);

	        $typeTitles = GoldcoinLogModel::getTypeTitle();
	        $typeDescs = GoldcoinLogModel::getTypeDesc();

	        $users=0;
	        foreach ($logs as $log)	
	        	$users += $log['users'];

	        foreach ($logs as $log) {
	        	$row = [
	        		$typeTitles[$log['type']],
	        		$typeDescs[$log['type']],
	        		$log['coins'],
	        		$log['cnt'],
	        		$log['users'],
	        		100*round($log['coins']/$summary['coins'],2).'%',
	        		100*round($log['users']/$users,2).'%',
	        	];
	        	$records[] = $row;
	        }

	        $records[] = ['汇总', '-', $summary['coins'], $summary['cnts'], $users, '100%', '100%'];
        }
        return $records;
	}


	function getWhere() 
	{
        $where = " 1 ";
        $from = _g("from");
        $to = _g("to");

        if (!is_null($from) && $from!='') {
        	$from = strtotime(date('Y-m-d', strtotime($from)));
        	$where.= " and a_time >= '".$from."'";
        }

        if (!is_null($to) && $to!='') {
        	$to = strtotime(date('Y-m-d', strtotime($to)));
	        $to = strtotime('+1 day', $to);
        	$where.= " and a_time <= '".$to."'";
        }

        if (!$from && !$to) {
        	$from = strtotime('today');
        	$where.= " and a_time >= '".$from."'";
        }

        return $where;
    }
}