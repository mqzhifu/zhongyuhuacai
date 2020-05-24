<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class UserSignCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("sexDesc",UserModel::getSexDesc());
        $this->assign("onlineDesc",UserModel::getOnlineDesc());
        $this->assign("typeDesc",UserModel::getTypeDesc());

        $this->display("user/sign_list.html");

    }


    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getDataListTableWhere();

        $cnt = UserSignModel::db()->getCount($where);

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

            $data = UserSignModel::db()->getAll(" $where order by $order limit $iDisplayStart,$end");

            foreach($data as $k=>$v){
                $avatarImg = "";
                if(arrKeyIssetAndExist($v,'avatar')){
                    $avatarImg = "<img width='50' height='50' src='{$v['avatar']}' />";
                }


                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['uid'],
                    get_default_date($v['a_time']),
                    $v['reward'],
                    '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.
                    '<a href="/user/showDetail/uid='.$v['id'].'" class="btn btn-xs default yellow" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-trash-o"></i> 详情</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function searchAllDel(){
        $where = $this->getWhere();

        if($where == ' 1 ')
            out_err("没有搜索条件，那么就是清空全表，禁止这样操作!",500,'ajax');

        $type = _g("type");
        if(!$type || (  $type != 'in' && $type != 'out' ) ){
            out_err("para type:error....!",500,'ajax');
        }

        if($type == 'in'){
            $cnt = Sms_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from sms_log where ".$where . "  limit 100000";
            Sms_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');
        }else{
            $cnt = Send_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from send_log where ".$where. "  limit 100000";
            Send_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');

        }
    }

    function delOne($uid){
        if(!(int)$uid){
            exit("uid is null");
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            exit("uid not in db ");
        }
        var_dump($user);
        //删除表数据


//        $info = Sms_logModel::db()->getById($id);
//
//        if($info){
//            $uid = $this->_sess->getValue('id');
//            $str = "收件箱删除一条：".$info['message_id'];
//            admin_db_log_writer($str,$uid,'inbox_del');
//
//            $rs = Sms_logModel::db()->delById($id);
//        }



        //任务
//        $delRs = TaskUserModel::db()->delete(" uid = $uid limit 1000");
//        echo "del task rs :".$delRs. "</br>";

        //用户
        $delRs = UserModel::db()->delById($uid);
        echo "del user rs :".$delRs. "</br>";
        //金币日志
        $delRs = GoldcoinLogModel::db()->delete(" uid = $uid limit 1000");
        echo "del gold coin log  rs :".$delRs. "</br>";
        //怒气日志
        $delRs = AngryLogModel::db()->delete(" uid = $uid limit 1000");
        echo "del gold angry log  rs :".$delRs. "</br>";
        //好友守卫
        $delRs = FriendDefenseModel::db()->delete(" src_uid = $uid or target_uid = $uid limit 1000");
        echo "del gold friend defense log  rs :".$delRs. "</br>";
        //登陆日志
        $delRs = LoginModel::db()->delete(" uid = $uid limit 1000");
        echo "del login  rs :".$delRs. "</br>";


        //删除缓存

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);

        echo "clear uinfo cache rs :".$cacheRs. "</br>";



        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['towerMap']['key'],$uid);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear towerMap cache rs :".$cacheRs. "</br>";

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear token cache rs :".$cacheRs. "</br>";

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['heartbeat']['key'],$uid);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear uinfo heartbeat rs :".$cacheRs. "</br>";

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['eventMsg']['key'],$uid);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear uinfo eventMsg rs :".$cacheRs. "</br>";


        echo "end";exit;



    }

    function delBat(){
        $ids = _g("ids");
        if(!$ids)
            return 0;

        $ids = explode(",",$ids);
        $type = _g("type");
        $str_ids = "";
        foreach($ids as $k=>$id){
            if($id){
                if($type == 'in'){
                    $info = Sms_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Sms_logModel::db()->delById($id);
                    }
                }else{
                    $info = Send_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Send_logModel::db()->delById($id);
                    }
                }
            }
        }

        if($type == 'in'){
            $str = "收件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'inbox_del');
        }else{
            $str = "发件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'sendbox_del');
        }

    }


    function setSend(){


        if(_g("opt")){
            $content = _g("content");
            if(!$content){
                exit("内容不能为空");
            }

            $status = _g("status");
            if(!$status){
                $status = 0;
                $desc = "关闭";
            }else{
                $status= 1;
                $desc = "开启";
            }

            $uid = $this->_sess->getValue('id');
            $str = "状态：$desc,设置回复内容:".$content;
            admin_db_log_writer($str,$uid,'set_send_text');

            ConstModel::db()->update(array('content'=>$content,'status'=>$status)," id = 1 limit 1 ");
            exit('设置成功');
        }

        $const = ConstModel::db()->getRow(" id = 1");
        $this->assign("content",$const['content']);
        $this->assign("status",$const['status']);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("www/set_send_hook.html");

        $this->display("www/set_send.html");
    }


    function export(){
        $where = $this->getWhere();
        $type = _g("type");
        if($type == 'in'){
            $data = Sms_logModel::db()->getAll($where,'',"message_id,mobile,message,telcom,recevice_date,mobile_attach");
            $str = "导出excel：收件箱：";
        }else{
            $data = Send_logModel::db()->getAll($where,'',"message_id,mobile,message,telcom,recevice_date,mobile_attach");
            $str = "导出excel：发件箱";
        }



        if(!$data)
            exit('数据为空，不需要导出');

        $uid = $this->_sess->getValue('id');
        $str .= count($data);
        admin_db_log_writer($str,$uid,'export_excel');

        include PLUGIN . "/phpexcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $first = array(
            '上行方ID',
            '用户手机号',
            '短信内容',
            '运营方',
            '发送时间',
            '城市名'
        );

        $num = 65;
        $x = 0;
        foreach($first as $k2=>$v2){
            $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x)."1" , $v2);
            $x++;
        }

        $line_num = 1;
        foreach($data as $k=>$line){
            $line_num ++;
            $x = 0;
            foreach($line as $k2=>$v2){
                if($x == 1){
                    $first = substr($v2,0,2);
                    if($first == 86){
                        $v2 = substr($v2,2);
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue( chr($num+$x).$line_num , $v2);
                $x++;
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }





    function getWhere(){
        $where = " 1 ";
        if($mobile = _g("mobile"))
            $where .= " and mobile = '$mobile'";

        if($message = _g("message"))
            $where .= " and mobile like '%$message%'";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " and add_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " and add_time <= '".strtotime($to)."'";
        }


        return $where;
    }



    function getDataListTableWhere(){
        $where = 1;
        $openid = _g("openid");
        $sex = _g("sex");
        $status = _g("status");

        $nickname = _g('name');
//        $nickname_byoid = _g('nickname_byoid');
//        $content = _g('content');
//        $is_online = _g('is_online');
//        $uname = _g('uname');

        $from = _g("from");
        $to = _g("to");

//        $trigger_time_from = _g("trigger_time_from");
//        $trigger_time_to = _g("trigger_time_to");


//        $uptime_from = _g("uptime_from");
//        $uptime_to = _g("uptime_to");


        $id = _g("id");
        if($id)
            $where .=" and id = '$id' ";

        if($openid)
            $where .=" and openid = '$openid' ";

        if($sex)
            $where .=" and sex = '$sex' ";

        if($status)
            $where .=" and status = '$status' ";

        if($nickname)
            $where .=" and nickname = '$nickname' ";

//        if($nickname_byoid){
//            $user = wxUserModel::db()->getRow(" nickname='$nickname_byoid'");
//            if(!$user){
//                $where .= " and 0 ";
//            }else{
//                $where .=  " and openid = '{$user['openid']}' ";
//            }
//        }

//        if($content)
//            $where .= " and content like '%$content%'";

        if($from)
            $where .=" and a_time >=  ".strtotime($from);

        if($to)
            $where .=" and a_time <= ".strtotime($to);

//        if($trigger_time_from)
//            $where .=" and trigger_time_from >=  ".strtotime($trigger_time_from);
//
//        if($trigger_time_to)
//            $where .=" and trigger_time_to <= ".strtotime($trigger_time_to);
//
//        if($uptime_from)
//            $where .=" and up_time >=  ".strtotime($uptime_from);
//
//        if($uptime_to)
//            $where .=" and up_time <= ".strtotime($uptime_to);



//        if($is_online)
//            $where .=" and is_online = '$is_online' ";


//        if($uname)
//            $where .=" and uname = '$uname' ";

        return $where;
    }


}