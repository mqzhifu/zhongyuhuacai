<?php
/**
 * Class MoneyOsCtrl
 */
class MoneyOsCtrl extends BaseCtrl{

    function index(){
        $this->display("app_cnt/money/show.html");
    }


    function getList() {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
        $iDisplayStart = intval($_REQUEST['start']);//limit 起始

        // 以下均计算0点时刻
        // 数据头
        if ($from = _g("from")) {
            $first_date = strtotime(date('Y-m-d', strtotime($from)));
        } else {
            $first_date = strtotime(date('Y-m-d', MoneyOrderModel::db()->getRow()['a_time']));
        }

        // 数据尾
        if ($to = _g("to")) {
            $to = strtotime(date('Y-m-d', strtotime($to)));
            $last_date = strtotime('+1 day', $to);
        } else {
            $last_date = strtotime('tomorrow')-1;
        }


        // 数据总长度（天数）
        $totalLength = ceil(($last_date-$first_date)/86400);

        // 分页
        // 每页数据尾
        $displayEndDate = $last_date - 86400*$iDisplayStart;
        // 每页数据头
        $currentPageLength = ($totalLength-$iDisplayStart) > $iDisplayLength ? $iDisplayLength : ($totalLength-$iDisplayStart);
        $displayStartDate = $displayEndDate - 86400*($currentPageLength);

        $order_sort = [];
        $order_dir = $order_sort[0]['dir'] ?: "desc";
        $order = " order by id ".$order_dir;
        $where = " status = 4 and a_time >= $displayStartDate and a_time <= $displayEndDate $order";// 新增status = 2 (weixin成功) state=1 （审核通过）

        $orders = MoneyOrderModel::db()->getAll($where);
        $orderIndex = ['10'=>'5', '20'=>'6'];

        $total = ['money'=>0, 'person'=>0, 'money_per'=>0, '10'=>0, '20'=>0];

        // 创建空数据
        for ($i=0; $i < $currentPageLength; $i++) {
            $date = date('Y-m-d', $displayEndDate - $i * 86400);
            $records["data"][] = ['-', $date, 0, 0, 0, 0, 0, '-'];
        }


        foreach ($orders as $order) {
            $date = date('Y-m-d', $order['a_time']);
            foreach ($records["data"] as &$countPerDay) {
                if ($countPerDay[1] == $date) {
                    // 加算提现总数
                    $countPerDay[2] = round($countPerDay[2]+$order['num'], 1);

                    $total['money'] = round($total['money']+$order['num'], 1);;
                    // 加算提现人数
                    $countPerDay[3] ++;
                    $total['person'] ++;
                    // 人均提现
                    $countPerDay[4] = round($countPerDay[2]/$countPerDay[3],1);
                    // 加算分项人数
                    $countPerDay[$orderIndex[$order['num']]] ++;
                    $total[$order['num']] ++;
                    break;
                }
            }

        }

//        $records['data'][] = ['汇总', '-', $total['money'], $total['person'], $total['person']==0 ? 0:round($total['money']/$total['person'],1), $total['10'], $total['20'], '-'];
        $tmp = $total['person']==0 ? 0:round($total['money']/$total['person'],1);
        $records["data"][] = array(
            "<span style='color: darkred;font-weight: bold'>汇总</span>",
            "-",
            "<span style='color: darkred;font-weight: bold'>{$total['money']}</span>",
            "<span style='color: darkred;font-weight: bold'>{$total['person']}</span>",
            "<span style='color: darkred;font-weight: bold'>{$tmp}</span>",
            "<span style='color: darkred;font-weight: bold'>{$total['10']}</span>",
            "<span style='color: darkred;font-weight: bold'>{$total['20']}</span>",
            '-',
        );

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $totalLength;
        $records["recordsFiltered"] = $totalLength;

        echo json_encode($records);
        exit;
    }

}