<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class VerifiercodeCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $statusOption = VerifiercodeModel::getStatusOption();
        $typeOption = VerifiercodeModel::getTypeOption();
        $ruleOptions = SmsRuleModel::getAllFormatOption();



        $this->assign("ruleOptions",$ruleOptions);
        $this->assign("typeOption",$typeOption);
        $this->assign("statusOption",$statusOption);
        $this->display("/system/verifiercode_list.html");
    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = VerifiercodeModel::db()->getCount($where);
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

            $data = VerifiercodeModel::db()->getAll($where . $order);

            foreach($data as $k=>$v){
                $ruleName = "";
                if($v['rule_id']){
                    $rule = SmsRuleModel::db()->getById($v['rule_id']);
                    $ruleName = $rule['title'];
                }

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['code'],
                    VerifiercodeModel::STATUS_DESC[$v['status']],
                    VerifiercodeModel::TYPE_DESC[$v['type']],
                    $v['uid'],
                   $v['addr'],
                   get_default_date( $v['expire_time']),
                    $ruleName,

                    get_default_date($v['a_time']),

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
        $code = _g("code");
        $uid = _g("uid");
        $status = _g("status");
        $type = _g('type');
        $rule_id = _g('rule_id');

        $from = _g("from");
        $to = _g("to");

        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($code)
            $where .=" and code = '$code' ";

        if($uid)
            $where .=" and uid = '$uid' ";

        if($type)
            $where .=" and type = '$type' ";

        if($rule_id)
            $where .=" and rule_id = '$rule_id' ";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

        if($status)
            $where .=" and status = '$status' ";


//        if($uname)
//            $where .=" and uname = '$uname' ";

        return $where;
    }


}