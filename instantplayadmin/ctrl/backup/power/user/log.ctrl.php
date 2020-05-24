<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class LogCtrl extends BaseCtrl
{
    function index()
    {
        if(_g("getlist")){
            $this->getList();
        }

        $this->display("/power/user/admin_list.html");

    }

    function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = AdminLogModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                'admin_uid',
                '',
                '',
                '',
                '',
                '',
                'a_time',
                '',
                '',
                '',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $data = AdminLogModel::db()->getAll($where . " order by " .  $order . " limit $iDisplayStart,$iDisplayLength ");

            $menu = MenuModel::db()->getAll();

            foreach($data as $k=>$v){
                $desc = '';
                if ($v['cate']) {
                    $desc .= $this->getLogDesc($menu, 'dir_name', $v['cate']);
                }
                if ($v['sub']) {
                    $desc .= ' - '.$this->getLogDesc($menu, 'dir_name', $v['sub']);
                }
                if ($v['ctrl']) {
                    $desc .= ' - '.$this->getLogDesc($menu, 'ctrl', $v['ctrl']);
                }
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['admin_uid'],
                    $v['cate'],
                    $v['sub'],
                    $v['ctrl'],
                    $v['ac'],
                    $desc,
                    get_default_date($v['a_time']),
                    $v['request'],
                    $v['return'],
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

    function getWhere()
    {
        $where = " 1 ";
        $admin_uid = _g("admin_uid");
        $cate = _g("log_cate");
        $sub = _g("log_sub");
        $ctrl = _g("log_ctrl");
        $ac = _g("log_ac");

        $from = _g("from");
        $to = _g("to");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($admin_uid)
            $where .=" and admin_uid = '$admin_uid' ";

        if($cate)
            $where .=" and cate like '%$cate%' ";

        if($sub)
            $where .=" and sub like '%$sub%' ";

        if($ctrl)
            $where .=" and ctrl like '%$ctrl%' ";

        if($ac)
            $where .=" and ac like '%$ac%' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime("+1 day $to");


        return $where;
    }


    function getLogDesc($menu, $param, $val)
    {
        foreach ($menu as $m) {
            if ($m[$param] == $val) {
                return $m['name'];
            }
        }
    }


}