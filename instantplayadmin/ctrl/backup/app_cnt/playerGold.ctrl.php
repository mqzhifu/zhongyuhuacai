<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/23
 * Time: 15:06
 */
class playerGoldCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            // 获取列表数据（goldcoin_log）;
            $this->getList();
        }
        $opt_status[1] = '获取记录';
        $opt_status[2] = '消耗记录';
        $this->assign("status_all",GoldcoinLogModel::getTypeTitle());
        $this->assign("opt_status",$opt_status);
        $this->display("/app_cnt/playerGold/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $result = GoldcoinLogModel::db()->getCount();
        $iTotalRecords = ($result > 0)?$result:0;
        /*$select_sql = "SELECT * FROM goldcoin_log  WHERE {$where} ORDER BY id DESC ;";
        $result = GoldcoinLogModel::db()->query($select_sql);
        $iTotalRecords = (count($result))?count($result):0;*/

        if ($result){
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = "SELECT * FROM goldcoin_log  WHERE {$where} ORDER BY id DESC LIMIT $iDisplayStart, $iDisplayLength ";
            $data = GoldcoinLogModel::db()->query($selectSql);
            $userService = new UserService;
            foreach($data as &$v){
                $user_info = $userService ->getUinfoById($v['uid']);
                $select_sql = "SELECT sum(num) AS num_all FROM  goldcoin_log WHERE uid = {$v['uid']} AND a_time  <= {$v['a_time']};";
                $goldSurplus = GoldcoinLogModel::db()->query($select_sql);
                $records["data"][] = array(
                    $v['id'],
                    get_default_date($v['a_time']),
                    $v['uid'],
                    $user_info['name'],
                    $user_info['cellphone'],
                    $v['opt'] = (1 == $v['opt'])?'获取':'消耗',
                    GoldcoinLogModel::getTypeTitleByKey($v['type']),
                    GoldcoinLogModel::getTypeDescByKey($v['type']),
                    $v['num'],
                    $v['num_all'] = $goldSurplus[0]['num_all'],
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
        $where = " 1 = 1 ";
        if($uid = _g("uid"))
            $where .= " AND goldcoin_log.uid = $uid";

        if($name = trim(_g("name")))
            $where .= " AND user.name = $name";

        if($cellphone = trim(_g("cellphone")))
            $where .= " AND user.cellphone = $cellphone";

        if($opt_status = trim(_g("opt")))
            $where .= " AND goldcoin_log.opt = $opt_status";

        if($title = trim(_g("title")))
            $where .= " AND goldcoin_log.type = $title";

        if($from = _g("from")){
            $where .= " AND goldcoin_log.a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $where .= " AND goldcoin_log.a_time <= '".strtotime("+1 day $to")."'";
        }

        return $where;
    }
}