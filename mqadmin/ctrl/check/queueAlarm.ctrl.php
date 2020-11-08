<?php
class QueueAlarmCtrl extends BaseCtrl
{
    function index()
    {
        $roles = QueueAlarmModel::db()->getAll();
        $this->assign('roles', $roles);

        $this->display("check/queue_alarm_list.html");
    }

    function add(){
        if(_g("opt")){

            $queue_id =_g("queue_id");
            $msg_ready_num = _g("msg_ready_num");
            $del = _g("del");


            $data = array(
                'queue_id'=>$queue_id,
                'msg_ready_num'=>$msg_ready_num,
                'del'=>$del,
                'a_time'=>time(),
                'admin_id'=>$this->_adminid,
            );
            QueueAlarmModel::db()->add($data);
            $this->ok("成功");
        }

//        $AuditConfigOption = AuditConfitModel::getOption();
//        $this->assign("auditConfigOption",$AuditConfigOption);

        $getQueueOption = QueueModel::getOption();
        $this->assign('getQueueOption', $getQueueOption);

        $getOption = TopicModel::getOption();
        $this->assign('getOption', $getOption);

        $getTypeOptions = TopicModel::getTypeOptions();
        $getNormalTypeOptions = TopicModel::getNormalTypeOptions();

        $this->assign("getNormalTypeOptions",$getNormalTypeOptions);
        $this->assign("getTypeOptions",$getTypeOptions);

        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("/check/queue_alarm_add_hook.html");
        $this->display("/check/queue_alarm_add.html");
    }

    function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = QueueAlarmModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "desc";


            $sort = array(
                '',
                'id',
                '',
                '',
                '',
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

            $data = QueueAlarmModel::db()->getAll($where . $order. " limit $iDisplayStart,$iDisplayLength ");
//            $roles = RolesModel::db()->getAll();
//            $roleNames = [];
//            foreach ($roles as $role) {
//                $roleNames[$role['id']] = $role['name'];
//            }

            foreach($data as $k=>$v){
                $queueName = TopicModel::getFieldById($v['queue_id'],'name');
                $adminName = AdminUserModel::getFieldById($v['admin_id'],'uname');
                $row = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $queueName,
                    $v['msg_ready_num'],
                    $v['del'],
                    $adminName,
                    get_default_date($v['a_time']),
                    '<a href="/system/no/adminUser/editInfo/id='.$v['id'].'" class="btn btn-xs default green" data-id="'.$v['id'].'" target=""><i class="fa fa-file-text"></i>修改</a>',
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
        if($uname = _g("uname"))
            $where .= " and uname like '%$uname%' ";

        if($nickname = _g("nickname"))
            $where .= " and nickname like '%$nickname%' ";

        if($role_id = _g("role_id"))
            $where .= " and role_id = $role_id ";

        if($from = _g("from")){
            $where .= " and a_time >= '".strtotime($from)."' ";
        }

        if($to = _g("to")){
            $where .= " and a_time <= '".strtotime("+1 day $to")."' ";
        }


        return $where;
    }


    //登陆用户，修改自己的密码
    function upSelfPs(){
        if(_g("opt")){
            $old_ps = _g("old_ps");
            if(!$old_ps){
                exit("原密码不能为空");
            }

            $ps = _g("ps");
            if(!$ps){
                exit("新密码不能为空");
            }

            $ps_sure = _g("ps_sure");
            if(!$ps_sure){
                exit("确认密码不能为空");
            }

            if($ps_sure != $ps){
                exit("两次密码不一致");
            }

            if(strlen($ps)<6)
                exit("新密码至少6个字符");

            $uid = $this->_sess->getValue('id');

            $user = AdminUserModel::db()->getRow(" id = $uid");
            if($user['ps'] != md5($old_ps) ){
                exit('原始密码错误');
            }


//            $str = "修改密码:新($ps),旧($old_ps)";
//            admin_db_log_writer($str,$uid,'up_ps');

            AdminUserModel::db()->update(array('ps'=>md5($ps) )," id = $uid limit 1 ");
            $this->_sess->none();
            echo "<script>alert('新密码设置成功，请您重新登陆');location.href='/';</script>";
//            jump("/");
        }


        $this->addJs('/assets/global/plugins/jquery-validation/js/jquery.validate.min.js');
        $this->addJs('/assets/global/plugins/jquery-validation/js/additional-methods.min.js');

        $this->addHookJS("system/upps_hook.html");

        $this->display("system/upps.html");

    }

    /**
     * 修改用户权限;
     */
    public function editInfo(){
        $id = _g('id');// 主键ID;
        $userInfo = AdminUserModel::db()->getRow(" id = $id");
        $roles = RolesModel::db()->getAll();
        foreach ($roles as $k => $value){
            if($userInfo['role_id'] == $value['id']){
                $roleName = $value['name'].'（'.'当前'.'）';
            }
        }
        $this->assign('roleName', $roleName);
        $this->assign('roles', $roles);
        $this->assign('userInfo', $userInfo);
        $this->display('system/admin_edit.html');
    }

    /**
     * 修改角色保存;
     */
    public function editInfoSave(){
        // 只有超级管理员才能有权限修改;
        $adminId = $this->_adminid;
        $userMes = AdminUserModel::db()->getById($adminId);
        if(empty($userMes)){
            echo '-1002';exit();
        }else if(1 != $userMes['role_id']){
            echo '-1003';exit();
        }


        $id = _g("id");
        $role_id = _g("role_id");
        $pass = _g("pass");
        if(strlen($pass)<6){
            echo '-1004';exit();
        }
        $userInfo = AdminUserModel::db()->getRow(" id = $id");
        if($pass != $userInfo['ps'] && md5($pass) != $userInfo['ps']){
            $res1 = AdminUserModel::db()->update(array('ps'=>md5($pass) )," id = $id limit 1 ");
        }
        if(!empty($role_id)){
            if($userInfo['role_id'] == $role_id){
                echo '-1001';exit();
            }
            $res2 = AdminUserModel::db()->update(array('role_id'=>$role_id )," id = $id limit 1 ");
        }
        if(1 == $res1 || 1 == $res2 ){
            if(1 == $res1 && $adminId == $id){
                $this->_sess->none();
                echo '-1006';exit();
            }
            echo '200';exit();
        }else if (1 != $res1 && 1 != $res2 ){
            echo '-1005';exit();
        }

    }

}