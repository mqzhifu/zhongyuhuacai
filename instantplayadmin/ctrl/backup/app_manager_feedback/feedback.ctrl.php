<?php
/**
 * Created by PhpStorm.
 * Date: 2019/3/15
 * Time: 10:41
 */

/**
 * Class feedbackCtrl
 */
class feedbackCtrl extends BaseCtrl{
    /**
     * 用户反馈列表页;
     */
    public function index(){
        if(_g("getlist")){
            // 获取列表数据（feedback）;
            $this->getList();
        }
        // 获取反馈状态列表;
        $this->assign("status_all",FeedbackModel::getStatusAll());
        $this->display("app_manager_feedback/feedback/index.html");
    }

    /**
     * 逻辑处理;
     */
    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $sql = "SELECT * FROM feedback WHERE {$where}";
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

            $select_sql = "SELECT * FROM  feedback WHERE {$where} ORDER BY id DESC LIMIT $iDisplayStart, $end ";
            $data = FeedbackModel::getAll($select_sql);

            foreach($data as $k=>$v){
                switch ($v["status"]){
                    case 1:
                        $v["status"] = '未回复';
                        break;
                    case 2:
                        $v["status"] = '已回复';
                        break;
                    case 3:
                        $v["status"] = '追加';
                        break;
                }
                $userService = new UserService();
                $name_nick = $userService->getUinfoById($v['uid']);
                $nickName = ($name_nick["nickname"])?$name_nick["nickname"]:'-';
                $records["data"][] = array(
                    $v['id'],
                    $v['uid'],
                    $v['contact'] = $nickName,
                    $v['app_version'],
                    $v['content'],
                    $v['status'],
                    $v['a_time'] = date('Y-m-d H:i:s', $v['a_time']),
                    '<a href="/app_manager_feedback/no/feedback/getInfo/id='.$v['id'].'" class="btn btn-xs default yellow" data-id="'.$v['id'].'" target=""><i class="fa fa-file-text"></i> 点击回复</a>',
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
     * 获取edit页信息;
     */
    public function getInfo(){
        $id = _g("id");
        $sql = "SELECT feedback.*,user.cellphone,user.email FROM feedback LEFT JOIN user ON feedback.uid = user.id WHERE feedback.id = {$id}";
        $result = FeedbackModel::getAll($sql);
        switch ($result[0]["status"]){
            case 1:
                $result[0]["status"] = '未回复';
                break;
            case 2:
                $result[0]["status"] = '已回复';
                break;
            case 3:
                $result[0]["status"] = '追加';
                break;
        }
        $this->assign('result', $result[0]);

        $this->display("app_manager_feedback/feedback/detail.html");
    }

    /**
     *  逻辑待完善;
     */
    public function edit(){
        $id = _g("id");
        $content_back = trim(_g("content_back"));
        $selectSql = "select * from feedback where id = {$id} limit 1";
        $rs = FeedbackModel::getAll($selectSql);
        if(!empty($rs)){
            $status = ($rs[0]['status'] == 2)?3:2;
            if($rs[0]['status'] == 3){
                echo "<script>alert('您已操作过追加回复，切勿重复操作！');location.href='/app_manager_feedback/no/feedback/index/';</script>";
            }else{
                $upData['content_back'] = $content_back;
                $upData['status'] = $status;
                $rs = FeedbackModel::db()->upById($id, $upData);
            }
        }
        if($rs){
            echo "<script>alert('回复成功！');location.href='/app_manager_feedback/no/feedback/index/'</script>";
        }else{
            echo "<script>alert('回复失败！');location.href='/app_manager_feedback/no/feedback/index/';</script>";
        }
    }

    /**
     * ip整合test，暂存
     */
    public function iptestBackups(){
        $updateSql = "select * from login WHERE ip is not null;";
        $rs = FeedbackModel::db()->query($updateSql);
        if(!empty($rs) && is_array($rs)){
            foreach ($rs as &$value){
                $url = "http://ip.taobao.com/service/getIpInfo.php?ip={$value['ip']}";
                echo $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER,0);
                curl_setopt($ch, CURLOPT_POST, false);// false为GET提交方式;
                $output = curl_exec($ch);
                curl_close($ch);
                $outputArray = json_decode($output,true);
                if(0 == $outputArray['code']){
                    $update['addr'] = $value['data']['country'].'-'.$value['data']['region'].'-'.$value['data']['city'];
                    $updateSql = "update login set addr = '{$update['addr']}' WHERE id = {$value['id']} limit 1 ;";
                    FeedbackModel::updateInfo($updateSql);
                }
            }
        }
    }

}