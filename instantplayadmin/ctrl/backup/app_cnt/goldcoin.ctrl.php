<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/18
 * Time: 9:26
 */

/**
 * Class goldcoinCtrl
 */
class goldcoinCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            // 获取列表数据（goldcoin_log）;
            $this->getList();
        }
        // 获取反馈状态列表;
        // $this->assign("status_all",GoldcoinLogModel::getTypeTitle());
        $now_time = date('Y-m-d 00:00', time());
        $status_all[1] = '提现';
        $status_all[2] = '金币欢乐送';
        $status_all[3] = '购买游戏内道具';
        $this->assign("status_all", $status_all);
        $this->assign("now_time", $now_time);
        $this->display("/app_cnt/goldcoin/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $select_sql = "SELECT *,SUM(num) as xiaohaojinbizongshu,COUNT(id) AS cishu,COUNT(distinct uid) AS uids FROM goldcoin_log WHERE {$where} GROUP BY title ;";
        $result = GoldcoinLogModel::db()->query($select_sql);
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

            $selectSql = "SELECT *,SUM(num) as xiaohaojinbizongshu,COUNT(id) AS cishu,COUNT(distinct uid) AS uids FROM goldcoin_log WHERE {$where} GROUP BY title LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::db()->query($selectSql);
            $jinbi = 0;
            $renshu = 0;
            $cishu = 0;
            foreach ($data as $v){
                $jinbi += $v['xiaohaojinbizongshu'];
                $renshu += $v['uids'];
                $cishu += $v['cishu'];
            }
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['title'],
                    $v['content'],
                    $v['xiaohaojinbizongshu'],
                    $v['cishu'],
                    $v['uids'],
                    '<span style="font-weight: bold">（金币占比'.round($v['xiaohaojinbizongshu']/$jinbi*100).'%）</span>',
                    '<span style="font-weight: bold">（人数占比'.round($v['uids']/$renshu*100).'%）</span>',
                    '-',
                    '-',
                );
            }
            $records_new["data"][] = array(
                "<span style='color: red;font-weight: bold'>汇总</span>",
                "-",
                "<span style='color: red;font-weight: bold'>$jinbi</span>",
                "<span style='color: red;font-weight: bold'>$cishu</span>",
                "<span style='color: red;font-weight: bold'>$renshu</span>",
                "<span style='color: red;font-weight: bold'>100%</span>",
                "<span style='color: red;font-weight: bold'>100%</span>",
                '-',
                '-',
            );
            $records = array_merge_recursive($records, $records_new);
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
        $where = " opt = 2 ";
        if($title = trim(_g("title"))){
            if($title == 1){
                $title = '提现';
                $where .= " AND title = '{$title}' ";
            }elseif($title == 2){
                $title = '金币欢乐送';
                $where .= " AND title = '{$title}' ";
            }elseif ($title == 3){
                $title = '购买游戏内道具';
                $where .= " AND title = '{$title}' ";
            }
        }
        if($from = _g("from")){
            $where .= " AND a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $where .= " AND a_time <= '".strtotime("+1 day $to")."'";
        }
        return $where;
    }

}