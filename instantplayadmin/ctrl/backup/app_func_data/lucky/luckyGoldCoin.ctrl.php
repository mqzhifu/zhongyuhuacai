<?php
/**
 * Class luckyGoldCoinCtrl
 */
class luckyGoldCoinCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            // 获取列表数据（goldcoin_log）;
            $this->getList();
        }
        $this->assign("status_all", ['92'=>'Lucky筹码兑换', '93'=>'Lucky奖金兑换']);
        $this->display("/app_func_data/luckyGoldCoin/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $result = GoldcoinLogModel::db()->getCount($where);
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
                $memo = '-';
                if(92 == $v['type']){
                    $memo = '消耗筹码'.$v['memo'].'个';
                }elseif (93 == $v['type']){
                    $memo = '花费现金'.$v['memo'].'元';
                }
                $records["data"][] = array(
                    $v['id'],
                    get_default_date($v['a_time']),
                    $v['uid'],
                    $user_info['name'],
                    $v['opt'] = (1 == $v['opt'])?'获取':'消耗',
                    $memo,
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
        $where = " type IN (92,93) ";
        if($uid = _g("uid"))
            $where .= " AND goldcoin_log.uid = $uid";

        if($name = trim(_g("name")))
            $where .= " AND user.name = $name";

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