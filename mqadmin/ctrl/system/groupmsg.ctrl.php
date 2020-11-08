<?php
class groupmsgCtrl extends BaseCtrl
{

    function index()
    {
        $this->setTitle('test');


        if(_g("getlist")){
            $this->getList();
        }



        $this->addCss('/assets/global/plugins/select2/select2.css');
        $this->addCss('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css');
        $this->addCss('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
//        $this->addCss('/assets/admin/layout4/css/themes/light.css');
        $this->addCss('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');




        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');
//        $this->addJs('/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js');

        $this->addJs('/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');

        $this->addJs('/assets/global/scripts/datatable.js');
        $this->addJs('/assets/global/plugins/bootbox/bootbox.min.js');

        $this->addJs('/js/jquery.validate.min.js');
        $this->addJs('/js/additional-methods_cn.js');

        $this->addJs('/js/jquery.form.js');

        $this->addJs('/js/pop_bootbox.js');
        $this->addJs('/js/pop_ajax.js');


        $status_desc = array(1=>'未处理',2=>'已提醒');

        $this->addHookJS("groupmsg_hook.html");
        $this->assign("status_desc",$status_desc);

        $this->display("groupmsg.html");

    }


    function delOne(){
        $id = _g('id');
        if(!$id)
            echo_json("id is null",'500');

        $info = scheduleModel::db()->getById($id);
        if(!$info)
            echo_json("id not in db",'501');

        scheduleModel::db()->delById($id);

        echo_json("ok ",'200');
    }

    function upstatus(){
        $html = $this->_st->compile("schedule_upstatus.html");
        $html = file_get_contents($html);
        echo_json($html);
    }

    function add(){
        if(_g('doings')){
            $text = _g("text");
            $pic = _g("pic");
//            $trigger_time = _g("trigger_time");


            if(!$text && !$pic)
                echo_json("文本图片选1",'500');

            if( $text && $pic)
                echo_json("文本图片不能多选",'500');


            $media_id = "";
            if($text){
                $content = $text;
                $type = 'text';
            }else{
                $imgUp = new ImageUpLoadLib(array('pic'),IMG_UPLOAD."/send_wx_pic");
                $imgUp->upLoad();
                $pic_url = "/www/upload/send_wx_pic/".$imgUp->info['up_pic_ipt']['uploadFileName'];

                $this->wechat = WechatLib::get_instance();

                $sys_url = IMG_UPLOAD."/send_wx_pic/".$imgUp->info['up_pic_ipt']['uploadFileName'];
                $data = '@'.$sys_url;
                $rs = $this->wechat->uploadMedia($data,'image');
                if(!$rs){
                    $err = "";
                    if($this->wechat->errCode){
                        $err .= $this->wechat->errCode. " ";
                    }

                    if($this->wechat->errmsg){
                        $err .= $this->wechat->errmsg. " ";
                    }

                    echo_json('上传到微信端-图片失败....',500);
                }

                $media_id = $rs['media_id'];
                $content = $pic_url;
                $type = 'image';
            }

            $userlist = wxUserModel::db()->getAll();
            if(!$userlist)
                echo_json('用户列表 为空....',500);

            $openids = "";
            foreach($userlist as $k=>$v){
                $openids .= ",".$v['openid'];
            }
            $openids = substr($openids,1);




            $data = array(
                'type'=>$type,
                'content'=>$content,
                'a_time'=>time(),
                'openids'=>$openids,
                'wx_media_id'=>$media_id,
                'admin_id'=>$this->_adminid
            );


            sendAllModel::db()->add($data);


//            if(!$openid)
//                echo_json("$openid is null",'500');
//
//            if(!$trigger_time)
//                echo_json("$trigger_time is null",'500');


//            $trigger_time = strtotime($trigger_time);

//            $data = array(
//                'title'=>$title,
//                'content'=>$content,
//                'trigger_time'=>$trigger_time,
//                'openid'=>$openid,
//                'a_time'=>time(),
//                'up_time'=>time(),
//
//            );
//
//            scheduleModel::db()->add($data);

            echo_json("ok",200);
        }else{
            $html = $this->_st->compile("groupmsg_add.html");
            $html = file_get_contents($html);
            echo_json($html);
        }




    }


    function getWhere(){
        return 1;
    }

    function getlist(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = getDataListTableWhere();

        $cnt = sendAllModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'id',
                'id',
                '',
                '',
                '',
                '',
                'add_time',
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

            $data = sendAllModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $status = "";
                if(!$v['status'] ){
                    $status = '未处理';
                }elseif($v['status']  == 1){
                    $status = '处理中';
                }elseif($v['status']  == 2){
                    $status = '已完成';
                }
                $uname = getAdminUnameByid($v['admin_id']);
                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['content'],
                    $uname,
                    $v['type'],
                    $v['success_num']."/".$v['fail_num'],
                    date("Y-m-d H:i:s",$v['a_time']),
                    $status,
                    "",
//                    '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }
}