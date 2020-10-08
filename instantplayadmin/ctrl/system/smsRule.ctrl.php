<?php
class SmsRuleCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("getTemplateTypeOptionHtml", AliSmsLib::getTemplateTypeOptionHtml());
        $this->assign("getTemplateStatusOptionHtml", AliSmsLib::getTemplateStatusOptionHtml());
        $this->assign("getStatusOptionHtml", SmsLogModel::getChannelOptionHtml());

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
                'third_status',
                'third_memo',
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

            $aliSdk = new AliSmsLib();
            $space = "&nbsp;";
            foreach($data as $k=>$v){
                $aliTemplate = $aliSdk->QuerySmsTemplate($v['third_id']);
                $TemplateStatus = AliSmsLib::SMS_TEMPLATE_STATUS_DESC[$aliTemplate['TemplateStatus']];
                $reason = "";
                if($aliTemplate['TemplateStatus'] == AliSmsLib::SMS_TEMPLATE_STATUS_FAIL){
                    $reason = $aliSdk['Reason'];
                }
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
                    $TemplateStatus,
                    $reason,
                    '<a href="/system/no/smsRule/editone/id='.$v['id'].'" class="btn red btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-edit"></i>编辑</a>'.
                    '<a href="/system/no/smsRule/sendMsg/id='.$v['id'].'" class="btn blue btn-xs margin-bottom-5" data-id="'.$v['id'].'"><i class="fa fa-edit"></i>测试通道</a>',
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
    function sendMsg(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $info = SmsRuleModel::db()->getById($id);
        if(!$info){
            $this->notice("id not in db");
        }

        if(_g("opt")){
            $mobile = _g("mobile");
            $VerifierCodeLib = new VerifierCodeLib();
            $rs = $VerifierCodeLib->sendCode(VerifierCodeLib::TypeCellphone,$mobile,$id);
            if($rs['code'] == 200){
                $this->ok("发送成功");
            }else{
                $this->notice("失败：VerifierCodeLib->sendCode"." " .$rs['code'] . "&nbsp;" .$rs['msg']);
            }
        }

        $this->assign("info",$info);

        $this->addHookJS("/system/sms_rule_send_msg_hook.html");
        $this->display("system/sms_rule_send_msg.html");
    }

    function add(){
        if(_g("opt")){
            $data = array(
                'title'=>_g("title"),
                'content'=>_g("content"),
                'period_times'=>_g("period_times"),
                'day_times'=>_g("day_times"),
                'period'=>_g("period"),
                'third_id'=>_g("third_id"),
                'type'=>_g('type'),
                'channel'=>_g('channel'),
                'memo'=>_g("memo"),
            );

            if(!$data['title']){
                $this->notice("标题 不能为空");
            }

            if(!$data['content']){
                $this->notice("内容 不能为空");
            }

            if(!$data['period_times']){
                $this->notice("period_times 不能为空");
            }

            if(!$data['day_times']){
                $this->notice("day_times 不能为空");
            }

            if(!$data['period']){
                $this->notice("period 不能为空");
            }

//            if(!$data['third_id']){
//                $this->notice("3方渠道供应商-模板ID 不能为空");
//            }

            if(!$data['type'] && $data['type'] != 0 && $data['type'] != '0'){
                $this->notice("type 不能为空");
            }

            if(!$data['channel']){
                $this->notice("channel 不能为空");
            }

            if(!$data['memo']){
                $this->notice("描述 不能为空");
            }

            $aliSmsLib = new AliSmsLib();
            $AddSmsTemplateRs =  $aliSmsLib->AddSmsTemplate($data['type'],$data['title'],$data['content'],$data['memo']);
            if($AddSmsTemplateRs['Code'] == "ok" || $AddSmsTemplateRs['Code'] == "OK"){
                $data['third_id'] = $AddSmsTemplateRs['TemplateCode'];
//                $data['third_status'] = AliSmsLib::SMS_TEMPLATE_STATUS_AUDIT;
                $data['third_memo'] = "";
                SmsRuleModel::db()->add($data);
                $this->ok("ok");
            }else{
                $this->notice("请示3方创建模板失败:".json_encode($AddSmsTemplateRs));
            }

        }

        $this->assign("getTemplateTypeOptionHtml", AliSmsLib::getTemplateTypeOptionHtml());
        $this->assign("getTemplateStatusOptionHtml", AliSmsLib::getTemplateStatusOptionHtml());
        $this->assign("getStatusOptionHtml", SmsLogModel::getChannelOptionHtml());

        $this->addHookJS("/system/sms_rule_add_hook.html");
        $this->display("/system/sms_rule_add.html");
    }

    function editone(){
        $id = _g("id");
        if(!$id){
            $this->notice("id is null");
        }

        $info = SmsRuleModel::db()->getById($id);
        if(!$info){
            $this->notice("id not in db");
        }

        if(_g("opt")){
            $data = array(
                'title'=>_g("title"),
                'content'=>_g("content"),
                'period_times'=>_g("period_times"),
                'day_times'=>_g("day_times"),
                'period'=>_g("period"),
                'third_id'=>_g("third_id"),
            );

            if(!$data['title']){
                $this->notice("标题 不能为空");
            }

            if(!$data['content']){
                $this->notice("内容 不能为空");
            }

            if(!$data['period_times']){
                $this->notice("period_times 不能为空");
            }

            if(!$data['day_times']){
                $this->notice("day_times 不能为空");
            }

            if(!$data['period']){
                $this->notice("period 不能为空");
            }

            if(!$data['third_id']){
                $this->notice("3方渠道供应商-模板ID 不能为空");
            }

            SmsRuleModel::db()->upById($id,$data);
            $this->ok("成功");

        }
        $aliSdk = new AliSmsLib();
        $aliTemplate = $aliSdk->QuerySmsTemplate($info['third_id']);
        $TemplateStatus = AliSmsLib::SMS_TEMPLATE_STATUS_DESC[$aliTemplate['TemplateStatus']];
        $reason = "";
        if($aliTemplate['TemplateStatus'] == AliSmsLib::SMS_TEMPLATE_STATUS_FAIL){
            $reason = $aliSdk['Reason'];
        }

        $info['channel_desc'] = SmsLogModel::CHANNEL_DESC[$info['channel']];

        $this->assign("reason",$reason);
        $this->assign("TemplateStatus",$TemplateStatus);

        $this->assign("info",$info);

        $this->addHookJS("/system/sms_rule_editone_hook.html");
        $this->display("system/sms_rule_editone.html");
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