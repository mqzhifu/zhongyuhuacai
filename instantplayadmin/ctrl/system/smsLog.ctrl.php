<?php
class SmsLogCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("getMsgStatusOptionHtml", SmsLogModel::getMsgStatusOptionHtml());
        $this->assign("getChannelOptionHtml", SmsLogModel::getChannelOptionHtml());
        $this->assign("getStatusOptionHtml", SmsLogModel::getStatusOptionHtml());

        $this->display("system/sms_log_list.html");
    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = SmsLogModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                'id',
                'id',
                'rule_id',
                'uid',
                'content',
                'status',
                'channel',
                'IP',
                'cellphone',
                'out_no',
                'third_back_info',
                'third_callback_info',
                'third_callback_time',
                'third_callback_status',
                'third_callback_report_time',
                'a_time',
                '',
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

            $data = SmsLogModel::db()->getAll($where . $order);

            $aliSdk = new AliSmsLib();
            $space = "&nbsp;";


//                'third_callback_status',
//                'third_callback_report_time',
//                'a_time',

            foreach($data as $k=>$v){
//                $aliTemplate = $aliSdk->QuerySmsTemplate($v['third_id']);
//                $TemplateStatus = AliSmsLib::SMS_TEMPLATE_STATUS_DESC[$aliTemplate['TemplateStatus']];
//                $reason = "";
//                if($aliTemplate['TemplateStatus'] == AliSmsLib::SMS_TEMPLATE_STATUS_FAIL){
//                    $reason = $aliSdk['Reason'];
//                }
//                $TemplateStatus = AliSmsLib::SMS_TEMPLATE_STATUS_DESC[$v['third_status']];

                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['rule_id'],
                    $v['uid'],
                    $v['content'],
                    $v['status'],
                    $v['channel'],
                    $v['IP'],
                    $v['cellphone'],
                    $v['out_no'],
//                    $v['third_back_info'],
                    '',
                    $v['third_callback_info'],
                    $v['third_callback_time'],
                    $v['third_callback_status'],
                    $v['third_callback_report_time'],

                    '',
//                    '<a href="/system/no/smsRule/editone/id='.$v['id'].'" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-edit"></i>编辑</a>'.
//                    '<a href="/system/no/smsRule/sendMsg/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-edit"></i>测试通道</a>'.
//                    '<button class="btn btn-xs default red delone margin-bottom-5" data-id="'.$v['id'].'" ><i class="fa fa-share-alt"></i>删除</button>',
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