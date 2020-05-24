<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/21
 * Time: 16:36
 */

/**
 * 推送消息;
 * Class appPushCtrl
 */
class appPushCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("msg/appPush/index.html");
    }

    /**
     * 逻辑处理;
     */
    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $sql = "SELECT * FROM open_admin_apppush WHERE {$where}";
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

            $select_sql = "SELECT open_admin_apppush.*,admin_user.uname FROM  open_admin_apppush LEFT JOIN admin_user ON open_admin_apppush.operation_id = admin_user.id WHERE {$where} order by id desc LIMIT $iDisplayStart, $end ";
            $data = FeedbackModel::getAll($select_sql);
            foreach($data as $k=>$v){
                switch ($v["send_type"]){
                    case 1:
                        $v["send_type"] = '离线PUSH消息';
                        break;
                    case 2:
                        $v["send_type"] = '指定发送';
                        break;
                    case 3:
                        $v["send_type"] = '平台发送';
                        break;
                    default:
                        $v["send_type"] = '-';
                }
                switch ($v["push_type"]){
                    case 1:
                        $v["push_type"] = '应用内顶部提示消息';
                        break;
                    case 2:
                        $v["push_type"] = '应用内底部提示消息';
                        break;
                    default:
                        $v["push_type"] = '-';
                }
                switch ($v["platform_type"]){
                    case 1:
                        $v["platform_type"] = 'Android';
                        break;
                    case 2:
                        $v["platform_type"] = 'ios';
                        break;
                    default:
                        $v["platform_type"] = '-';
                }
                $records["data"][] = array(
                    $v['id'],
                    get_default_date($v['a_time']),
                    $v['uname'],
                    $v['send_type'],
                    $v['push_type'],
                    $v['platform_type'],
                    $v['send_title'],
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
                    '-',
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
        if($id = _g("uid"))
            $where .= " and uid = $id";

        if($status = _g("status"))
            $where .= " AND status = $status";

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
    public function addMsg(){
        // 发送类型;
        $sendTypes = openAdminAppPushModel::getSendTypes();
        $this->assign("sendTypes", $sendTypes);

        // PUSH类型;
        $pushTypes = openAdminAppPushModel::getPushTypes();
        $this->assign("pushTypes", $pushTypes);

        // 平台选择;
        $platformTypes = openAdminAppPushModel::getPlatformTypes();
        $this->assign("platformTypes", $platformTypes);

        // 一定用得到的静态文件，但是分配了就报错;
         /*$this->addCss("/assets/open/css/time-admin.css");
         $this->addCss("/assets/open/css/time-jquery-ui.css");*/

        // 可能用到的静态文件;
        $this->addCss("/assets/open/css/massage-set-create.css");
        $this->addCss("/assets/open/css/wickedpicker.min.css");
        $this->addJs("/assets/open/scripts/wickedpicker.min.js");
//        $this->addJs("/assets/open/scripts/jquery.min.js");

        $this->display("msg/appPush/addMsg.html");
    }


    public function pushSave(){
        $fruit = dump(_g("fruit"));// 是否立即发送（1）;
        // open_admin_apppush逻辑待完善;
        // 发送类型;
        $send_type = _g("send_type");

        // PUSH类型;
        $push_type = _g("push_type");

        // 平台类型;
        $platform_type = _g("platform_type");

        // 开发者信息;kf_ids
        $developer_information = _g("kf_ids");

        // 发送原因;
        $send_reason = _g("push_reason");

        // 发送标题;
        $send_title = _g("push_title");

        // 发送内容;
        $send_content = _g("push_content");

        // 发送时间;
        $send_time = _g("act_start_time");
        $send_time = $send_time.':00';
        $insertData = [];
        $insertData['send_type'] = $send_type;
        $insertData['push_type'] = $push_type;
        $insertData['platform_type'] = $platform_type;
        $insertData['developer_information'] = $developer_information;
        $insertData['send_reason'] = $send_reason;
        $insertData['send_title'] = $send_title;
        $insertData['send_content'] = $send_content;
        $insertData['send_time'] = $send_time;
        $insertData['operation_id'] = $this->_adminid;// 当前登录用户的ID
        $insertData['finish_time'] = date('Y-m-d H:i:s');
        $adminModel = new openAdminAppPushModel();
        $res_id = $adminModel->addData($insertData);
        if($res_id && $fruit == 1){
            $sql = "select * from open_admin_apppush WHERE id = {$res_id};";
            $info = openAdminAppPushModel::db()->query($sql);
            if(!empty($info) && is_array($info)){
                foreach ($info as $item) {
                    $comma_separated = explode(";", $item['developer_information']);
                    $str_count = count($comma_separated);
                    // $comma_separated = implode(",", $comma_separated);
                    $str_count_type = (1 == $str_count)?'single':'all';
                    // 以此参数作为判断单条发送或是多条发送的依据;
                    // 调用腾迅-信鸽发送接口;
                    $lib =  new PushXinGeLib();
                    if('single' == $str_count_type){
                        $lib->pushAndroidNotifyOneMsgByToken($item['developer_information'], $item['send_title'], $item['send_content'], array('typeId'=>1000,'taskConfigId'=>1));
                    }else{
                        // 多条发送暂时循环调用单次发送的方法;
                        foreach ($comma_separated as $value){
                            $lib->pushAndroidNotifyOneMsgByToken($value, $item['send_title'], $item['send_content'], array('typeId'=>1000,'taskConfigId'=>1));
                        }
                    }
                }
            }
        }
        if($res_id){
            jump("/msg/no/appPush/index/");
        }
    }


}