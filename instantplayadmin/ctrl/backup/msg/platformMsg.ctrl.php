<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/21
 * Time: 16:36
 */

/**
 * Class platformMsgCtrl
 */
class platformMsgCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("msg/platformMsg/index.html");
    }

    /**
     * 逻辑处理;
     */
    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $sql = "SELECT * FROM msg WHERE {$where}";
        $result = FeedbackModel::getAll($sql);
        $iTotalRecords = (count($result))?count($result):0;

        if (!empty($result) && is_array($result)){
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $select_sql = "SELECT msg.*,admin_user.uname FROM  msg LEFT JOIN admin_user ON msg.from_uid = admin_user.id WHERE {$where} ORDER BY msg.id DESC LIMIT $iDisplayStart, $end ";
            $data = FeedbackModel::getAll($select_sql);
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['id'],
                    '指定发送',
                    $v['send_reason'],
                    $v['title'],
                    $v['content'],
                    $v['to_uid'],
                    $v['uname'],
                    get_default_date($v['a_time']),
                    '-',
                );
            }
        }
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
        die();
    }

    /**
     * @return string
     */
    function getWhere(){
        $where = " 1 = 1";

        if($from = _g("from")){
            $from .= ":00";
            $where .= " AND a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $to .= ":59";
            $where .= " AND a_time <= '".strtotime($to)."'";
        }

        return $where;
    }

    /**
     * 消息内容的添加;
     */
    public function addplatformMsg(){
        // 可能用到的静态文件;
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");

        $this->display("msg/platformMsg/addplatformMsg.html");
    }

    /**
     * msg落表;
     */
    public function platformMsgSave(){
        // 开发者信息;kf_ids
        $toUid = _g("kf_ids");
        $retArr = explode(';', $toUid);
        // Prevent people from adding semicolons to their hands;
        foreach ($retArr as $k=>$v){
            if(empty($v)){
                unset($retArr[$k]);
            }
        }
        $type = count($retArr);
        $type = ($type <= 1)?'1':'2';
        $send_reason = _g("send_reason");// 发送原因;
        $send_title = _g("send_title");// 发送标题;
        $send_content = _g("send_content");// 发送内容;
        $fromUid = $this->_adminid;
        // 添加数据整合;
        $insertData = [];
        $insertData['to_uid'] = $toUid;
        $insertData['send_reason'] = $send_reason;
        $insertData['title'] = $send_title;
        $insertData['content'] = $send_content;
        $insertData['from_uid'] = $fromUid;
        $insertData['a_time'] = time();
        $insertData['type'] = $type;
        // $insertData['type'] = MsgModel::$_type_p2p;
        /*if($type == 1){
            $fansBother = FansBotherModel::db()->getById(" uid = $fromUid and to_uid = $toUid");
            if(!$fansBother){
                $insertData['is_read'] = 2;// 未读;
            }else{
                $insertData['is_read'] = 1;// 已读;
            }
        }else{
            $insertData['is_read'] = 2;// 未读;
        }*/
        $insertData['is_read'] = 2;// 未读;
        $msgModel = new MsgModel();
        $msgId = $msgModel->addData($insertData);
        // table：msg信息添加成功后调用发送方法;
        if($msgId){
            foreach ($retArr as $k => $value_touid){
                if(!empty($value_touid)){
                    $this->sendNotifyMsg($value_touid, $type, $send_title, $send_content);
                }else{
                    unset($retArr[$k]);
                }

            }
        }
        jump("/msg/no/platformMsg/index/");
    }

    /**
     * @param $toUid
     * @param $type
     * @param $title
     * @param $content
     * @return array
     */
    public function sendNotifyMsg($toUid, $type, $title, $content){
        // $fromUid = 100000;
        $userService = new UserService();
        $user = $userService->getUinfoById($toUid);
        if($user['push_status'] == 2){
            return out_pc(8236);
        }
        $insertData = array(
            'uid'=>$toUid,
            'title'=>$title,
            'type'=>$type,
            'content'=>$content,
            'a_time'=>time(),
        );
        $insertData['is_read'] = 2;
        openNotificationModel::db()->add($insertData);
    }


    /*public function test(){
        $notificationService = new openNotificationService();
        $returnArray = $notificationService->sendNotifyMsg(1,2,3,4);
    }*/
}