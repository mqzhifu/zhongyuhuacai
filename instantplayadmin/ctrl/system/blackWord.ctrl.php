<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class BlackWordCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("system/black_word_list.html");
    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = _g("draw");

        $where = $this->getDataListTableWhere();

        $cnt = BlackWordModel::db()->getCount($where);
        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
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

            $data = BlackWordModel::db()->getAll($where .  $order. " limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['name'],
                    $v['type'],
                    $v['sub_type'],
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

    function getDataListTableWhere(){
        $where = 1;

        $name = _g("name");
//        $sex = _g("");
//        $status = _g("status");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";


//        if($sex)
//            $where .=" and sex = '$sex' ";
//
//        if($status)
//            $where .=" and status = '$status' ";
//
//        if($nickname)
//            $where .=" and nickname = '$nickname' ";

        if($name)
            $where .= " and name like '%$name%'";

        return $where;
    }


}