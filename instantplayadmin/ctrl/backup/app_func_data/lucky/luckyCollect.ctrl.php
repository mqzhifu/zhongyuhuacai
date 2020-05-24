<?php

class luckyCollectCtrl extends BaseCtrl{
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        // 获取反馈状态列表;
        $now_time = date('Y-m-d 00:00', time());
        $status_all['92'] = 'Lucky筹码兑换';
        $status_all['93'] = 'Lucky现金兑换';
        $this->assign("status_all", $status_all);
        $this->assign("now_time", $now_time);
        $this->display("/app_func_data/luckyCollect/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $select_sql = "SELECT COUNT(id) FROM goldcoin_log WHERE {$where} GROUP BY type;";
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

            $selectSql = "SELECT *,SUM(num) as xiaohaojinbizongshu,COUNT(id) AS cishu,COUNT(distinct uid) AS uids FROM goldcoin_log WHERE {$where} GROUP BY type LIMIT $iDisplayStart, $iDisplayLength ";

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
        $where = " type IN (92,93)";
        if($title = trim(_g("title"))){
            $where .= " AND type = $title ";
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