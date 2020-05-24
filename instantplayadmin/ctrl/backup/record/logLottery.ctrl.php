<?php

/**
 * @Author: Kir
 * @Date:   2019-01-31 10:32:01
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-01-31 15:10:05
 */


set_time_limit(600);
header("Content-type:text/html;charset=utf-8");

class LogLotteryCtrl extends BaseCtrl
{
    function index() {
        if(_g("getlist")){
            $this->getList();
        }

		$this->assign("typeDesc",LotteryLogModel::getTypeDesc());        

        $this->display("log/lottery_list.html");

    }


    function getList() {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = LotteryLogModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                'uid',
                'a_time',
                'reward_type',
                'reward_goldcoin',
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

            $data = LotteryLogModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    get_default_date($v['a_time']),
                    LotteryLogModel::getTypeDescByKey($v['reward_type']),
                    $v['reward_goldcoin'],
                    "",
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

    function getWhere() {
        $where = " 1 ";
        $id = _g("id");
        $uid = _g("uid");
        $from = _g("from");
        $to = _g("to");
		$reward_type = _g("reward_type");
		$reward_goldcoin = _g("reward_goldcoin");

        if (!is_null($id) && $id!='')
            $where .= " and id = '$id'";

        if (!is_null($uid) && $uid!='')
            $where .= " and uid = '$uid'";

        if (!is_null($from) && $from!='') {
            $from .= ":00";
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if (!is_null($to) && $to!='') {
            $to .= ":59";
            $where .= " and a_time <= '".strtotime($to)."'";
        }

        if (!is_null($reward_type) && $reward_type!='')
            $where .= " and reward_type = '$reward_type'";

        if (!is_null($reward_goldcoin) && $reward_goldcoin!='')
            $where .= " and reward_goldcoin = '$reward_goldcoin'";


        return $where;
    }

}