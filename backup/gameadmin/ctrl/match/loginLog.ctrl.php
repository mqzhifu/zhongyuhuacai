<?php
/**
 * Created by PhpStorm.
 * User: Kir
 * Date: 2019/6/25
 * Time: 15:20
 */

class LoginLogCtrl extends BaseCtrl
{
    function index()
    {
        $this->assign("statusDesc", LoginModel::getStatusDesc());
        $this->assign("closeStatusDesc", LoginModel::getCloseStatusDesc());
        $matchService = new GameMatchService();
        $users = $matchService->getOnlineUserTotal();
        $this->assign("onlineUsers", $users ? $users : 0);
        $this->display("match/login_log.html");
    }

    function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $iTotalRecords = LoginModel::db()->getCount($where);

        if ($iTotalRecords){
            $iDisplayLength = intval($_REQUEST['length']);
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $data = LoginModel::db()->getAll("$where ORDER BY id DESC LIMIT $iDisplayStart,$iDisplayLength");

            $statusDesc = LoginModel::getStatusDesc();
            $closeStatusDesc = LoginModel::getCloseStatusDesc();
            foreach ($data as $k=>$v) {
                $records["data"][] = array(
                    $v['id'],
                    $v['fd'],
                    $v['uid'],
                    $v['ip'],
                    $v['room_id'],
                    $statusDesc[$v['status']],
                    $v['close_status'] ? $closeStatusDesc[$v['close_status']] : $closeStatusDesc[0],
                    $v['app_id'],
                    get_default_date($v['a_time']),
                    get_default_date($v['e_time']),
                    '',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);exit();
    }

    function getWhere()
    {
        $where = " 1 ";
        if ($fd = _g("fd")) {
            $where .= " and fd = '$fd' ";
        }
        if ($uid = _g("uid")) {
            $where .= " and uid = '$uid' ";
        }
        if ($ip = _g("ip")) {
            $where .= " and ip = '$ip' ";
        }
        if ($room_id = _g("room_id")) {
            $where .= " and room_id = '$room_id' ";
        }
        if ($status = _g("status")) {
            $where .= " and status = $status ";
        }
        $close_status = _g("close_status");
        if ($close_status != '') {
            if ($close_status == 0) {
                $where .= " and close_status is null ";
            } else {
                $where .= " and close_status = $close_status ";
            }
        }
        if ($app_id = _g("app_id")) {
            $where .= " and app_id = '$app_id' ";
        }
        if ($from_a = _g("from_a")) {
            $from_a = strtotime($from_a);
            $where .= " and a_time >= $from_a ";
        }
        if ($to_a = _g("to_a")) {
            $to_a = strtotime("+1 day $to_a");
            $where .= " and a_time <= $to_a ";
        }
        if ($from_e = _g("from_e")) {
            $from_e = strtotime($from_e);
            $where .= " and e_time >= $from_e ";
        }
        if ($to_e = _g("to_e")) {
            $to_e = strtotime("+1 day $to_e");
            $where .= " and e_time <= $to_e ";
        }
        return $where;
    }
}