<?php
class SmsRuleCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("system/sms_rule_list.html");
    }


    function getList(){
        $this->getData();
    }

    function getData(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = SmsRuleModel::db()->getCount($where);

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

            $data = SmsRuleModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['title'],
                    $v['content'],
                    $v['period_times'],
                    $v['day_times'],
                    $v['type'],
                    $v['period'],
                    $v['third_id'],
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
        $title = _g("title");
        $content = _g("content");
        $period_times = _g("period_times");
        $day_times = _g('day_times');
        $type = _g('type');
        $period = _g('period');

        $from = _g("from");
        $to = _g("to");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($title)
            $where .=" and $title like '%$title%' ";

        if($content)
            $where .=" and $content like '%$content%' ";


        if($period_times)
            $where .=" and period_times = '$period_times' ";

        if($day_times)
            $where .=" and day_times = '$day_times' ";

        if($type)
            $where .=" and type = '$type' ";

        if($period)
            $where .=" and period = '$period' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        return $where;
    }


}