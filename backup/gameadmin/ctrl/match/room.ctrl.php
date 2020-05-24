<?php
/**
 * Created by PhpStorm.
 * User: Kir
 * Date: 2019/6/25
 * Time: 15:20
 */

class RoomCtrl extends BaseCtrl
{
    function index()
    {
        $this->assign("statusDesc", RoomModel::getStatusDesc());
        $this->assign("resultDesc", RoomModel::getResultDesc());
        $this->display("match/room.html");
    }

    function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $iTotalRecords = RoomModel::db()->getCount($where);

        if ($iTotalRecords){
            $iDisplayLength = intval($_REQUEST['length']);
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $data = RoomModel::db()->getAll("$where ORDER BY id DESC LIMIT $iDisplayStart,$iDisplayLength");

            foreach ($data as $k=>$v) {
                $records["data"][] = array(
                    $v['id'],
                    $v['room_id'],
                    $v['from_uid'],
                    $v['to_uid'],
                    RoomModel::getStatusDesc()[$v['status']],
                    RoomModel::getResultDesc()[$v['result']],
                    $v['robot_level'],
                    $v['score'],
                    $v['end_timer_id'],
                    $v['robot_timer'],
                    $v['app_id'],
                    $v['app_type_id'],
                    get_default_date($v['game_start_time']),
                    get_default_date($v['game_end_time']),
                    get_default_date($v['game_sign_time']),
                    get_default_date($v['from_leave_time']),
                    get_default_date($v['to_leave_time']),
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
        if ($from_uid = _g("from_uid")) {
            $where .= " and from_uid = '$from_uid' ";
        }
        if ($to_uid = _g("to_uid")) {
            $where .= " and to_uid = '$to_uid' ";
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
        if ($result = _g("result")) {
            $where .= " and result = $result ";
        }
        if ($robot_level = _g("robot_level")) {
            $where .= " and robot_level = '$robot_level' ";
        }
        if ($score = _g("score")) {
            $where .= " and score = '$score' ";
        }
        if ($end_timer_id = _g("end_timer_id")) {
            $where .= " and end_timer_id = '$end_timer_id' ";
        }
        if ($robot_timer = _g("robot_timer")) {
            $where .= " and robot_timer = '$robot_timer' ";
        }
        if ($app_id = _g("app_id")) {
            $where .= " and app_id = '$app_id' ";
        }
        if ($app_type_id = _g("app_type_id")) {
            $where .= " and app_type_id = '$app_type_id' ";
        }
        if ($f_game_start_time = _g("f_game_start_time")) {
            $f_game_start_time = strtotime($f_game_start_time);
            $where .= " and game_start_time >= $f_game_start_time ";
        }
        if ($t_game_start_time = _g("to_a")) {
            $t_game_start_time = strtotime("+1 day $t_game_start_time");
            $where .= " and game_start_time <= $t_game_start_time ";
        }
        if ($f_game_to_time = _g("f_game_to_time")) {
            $f_game_to_time = strtotime($f_game_to_time);
            $where .= " and game_to_time >= $f_game_to_time ";
        }
        if ($t_game_to_time = _g("to_a")) {
            $t_game_to_time = strtotime("+1 day $t_game_to_time");
            $where .= " and game_to_time <= $t_game_to_time ";
        }
        if ($f_game_sign_time = _g("f_game_sign_time")) {
            $f_game_sign_time = strtotime($f_game_sign_time);
            $where .= " and game_sign_time >= $f_game_sign_time ";
        }
        if ($t_game_sign_time = _g("to_a")) {
            $t_game_sign_time = strtotime("+1 day $t_game_sign_time");
            $where .= " and game_sign_time <= $t_game_sign_time ";
        }
        if ($f_from_leave_time = _g("f_from_leave_time")) {
            $f_from_leave_time = strtotime($f_from_leave_time);
            $where .= " and from_leave_time >= $f_from_leave_time ";
        }
        if ($t_from_leave_time = _g("to_a")) {
            $t_from_leave_time = strtotime("+1 day $t_from_leave_time");
            $where .= " and from_leave_time <= $t_from_leave_time ";
        }
        if ($f_to_leave_time = _g("f_to_leave_time")) {
            $f_to_leave_time = strtotime($f_to_leave_time);
            $where .= " and to_leave_time >= $f_to_leave_time ";
        }
        if ($t_to_leave_time = _g("to_a")) {
            $t_to_leave_time = strtotime("+1 day $t_to_leave_time");
            $where .= " and to_leave_time <= $t_to_leave_time ";
        }
        return $where;
    }
}