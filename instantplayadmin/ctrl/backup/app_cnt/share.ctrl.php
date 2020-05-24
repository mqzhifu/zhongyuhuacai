<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/19
 * Time: 14:27
 */

/**
 * Class shareCtrl
 */
class shareCtrl extends BaseCtrl
{

    public function index()
    {
        if (_g("getlist")) {
            $this->getList();
        }
        // 获取反馈状态列表;
        $this->display("/app_cnt/share/index.html");
    }

    public function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();
        $select_sql1 = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new ;";
        $result = GoldcoinLogModel::getAll($select_sql1);
        foreach ($result as &$value){
            $a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['aaaa'] = GoldcoinLogModel::getAll($a);
            $value['huoyue'] = $value['aaaa'][0]['cnt'];
            unset($value['aaaa']);
            $b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['bbbb'] = GoldcoinLogModel::getAll($b);
            $value['xinzeng'] = $value['bbbb'][0]['cnt1'];
            unset($value['bbbb']);
        }

        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            // 数据处理;
            $selectSql = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            // 拼接活跃数和新增用户数【access_log,user】
            foreach ($data as &$v){
                $a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['aaaa'] = GoldcoinLogModel::getAll($a);
                $v['huoyue'] = $v['aaaa'][0]['cnt'];
                unset($v['aaaa']);
                $b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['bbbb'] = GoldcoinLogModel::getAll($b);
                $v['xinzeng'] = $v['bbbb'][0]['cnt1'];
                unset($v['bbbb']);
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['date_new'],// 日期
                    $v['huoyue'],//活跃
                    $v['xinzeng'],//新增用户
                    $v['uids'],//分享用户数
                    round($v['uids']/$v['huoyue']*100).'%',//分享用户比列
                    $v['to_uids'],//分享新增
                    round($v['to_uids']/($v['huoyue']-$v['to_uids'])*100).'%',//K
                    round($v['to_uids']/$v['xinzeng']*100).'%',//分享新增占比
                    round($v['to_uids']/$v['uids']*100).'%',//分享转化率;
                    $v['ids'],//分享次数
                    round($v['ids']/$v['uids']).'次',//人均分享次数
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
    function getWhere()
    {
        $where = " opt = 2 ";
        if ($title = trim(_g("title"))) {
            if ($title == 1) {
                $title = '提现';
            } elseif ($title == 2) {
                $title = '金币欢乐送';
            }
            $where .= " and title = '{$title}' ";
        }
        return $where;
    }

    public function way(){
        if (_g("getlist")) {
            $this->getListWay();
        }
        // 获取反馈状态列表;
        $this->assign("status_all", GoldcoinLogModel::getTypeTitle());
        $this->display("/app_cnt/share/index_way.html");
    }

    public function getListWay(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();
        $select_sql1 = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new ;";
        $result = GoldcoinLogModel::getAll($select_sql1);
        foreach ($result as &$value){
            $a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['aaaa'] = GoldcoinLogModel::getAll($a);
            $value['huoyue'] = $value['aaaa'][0]['cnt'];
            unset($value['aaaa']);
            $b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['bbbb'] = GoldcoinLogModel::getAll($b);
            $value['xinzeng'] = $value['bbbb'][0]['cnt1'];
            unset($value['bbbb']);
        }

        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            // 数据处理;
            $selectSql = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            // 简易计数器;
            $platform_14 = 0;
            $platform_4 = 0;
            $platform_9 = 0;
            $platform_toid_14 = 0;
            $platform_toid_4 = 0;
            $platform_toid_9 = 0;
            foreach ($data as &$vv){
                if($vv['platform'] == 14){
                    $platform_14 += $vv['platform'];
                }
                if($vv['platform'] == 4){
                    $platform_4 += $vv['platform'];
                }
                if($vv['platform'] == 9){
                    $platform_9 += $vv['platform'];
                }
                if($vv['platform'] == 14 && $vv['to_uid'] != 0){
                    $platform_toid_14 += $vv['platform'];
                }
                if($vv['platform'] == 4 && $vv['to_uid'] != 0){
                    $platform_toid_4 += $vv['platform'];
                }
                if($vv['platform'] == 9 && $vv['to_uid'] != 0){
                    $platform_toid_9 += $vv['platform'];
                }

            }
            // 拼接活跃数和新增用户数【access_log,user】
            foreach ($data as &$v){
                $sql_a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable1'] = GoldcoinLogModel::getAll($sql_a);
                $v['huoyue'] = $v['tmp_variable1'][0]['cnt'];
                $sql_b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable2'] = GoldcoinLogModel::getAll($sql_b);
                $v['xinzeng'] = $v['tmp_variable2'][0]['cnt1'];
                unset($v['tmp_variable1'], $v['tmp_variable2']);

                $sql_c = "select count(id) as id1,from_unixtime(a_time,'%Y-%m-%d') as tt from share where type = 4 AND from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable3'] = GoldcoinLogModel::getAll($sql_c);
                $v['fx_daohaoyou'] = $v['tmp_variable3'][0]['id1'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['date_new'],// 日期
                    $v['huoyue'],//活跃
                    $v['xinzeng'],//新增用户
                    $v['uids'],//分享用户数
                    $v['ids'],//分享次数
                    $platform_14,
                    $platform_toid_14,
                    $platform_4,
                    $platform_toid_4,
                    '-',
                    '-',
                    $platform_9,
                    $platform_toid_9,
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

    public function invitation(){
        if (_g("getlist")) {
            $this->getList();
        }
        // 获取反馈状态列表;
        $this->assign("status_all", GoldcoinLogModel::getTypeTitle());
        $this->display("/app_cnt/share/index_invitation.html");
    }

    public function getListInvitation(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();
        $select_sql1 = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new ;";
        $result = GoldcoinLogModel::getAll($select_sql1);
        foreach ($result as &$value){
            $a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['aaaa'] = GoldcoinLogModel::getAll($a);
            $value['huoyue'] = $value['aaaa'][0]['cnt'];
            unset($value['aaaa']);
            $b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$value['date_new']}'";
            $value['bbbb'] = GoldcoinLogModel::getAll($b);
            $value['xinzeng'] = $value['bbbb'][0]['cnt1'];
            unset($value['bbbb']);
        }

        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            // 数据处理;
            $selectSql = "SELECT *,count(id) AS ids, COUNT(DISTINCT(uid)) AS uids, COUNT(DISTINCT(to_uid) > 0) AS to_uids,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share GROUP BY date_new LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            // 简易计数器;
            $platform_14 = 0;
            $platform_4 = 0;
            $platform_9 = 0;
            $platform_toid_14 = 0;
            $platform_toid_4 = 0;
            $platform_toid_9 = 0;
            foreach ($data as &$vv){
                if($vv['platform'] == 14){
                    $platform_14 += $vv['platform'];
                }
                if($vv['platform'] == 4){
                    $platform_4 += $vv['platform'];
                }
                if($vv['platform'] == 9){
                    $platform_9 += $vv['platform'];
                }
                if($vv['platform'] == 14 && $vv['to_uid'] != 0){
                    $platform_toid_14 += $vv['platform'];
                }
                if($vv['platform'] == 4 && $vv['to_uid'] != 0){
                    $platform_toid_4 += $vv['platform'];
                }
                if($vv['platform'] == 9 && $vv['to_uid'] != 0){
                    $platform_toid_9 += $vv['platform'];
                }

            }
            // 拼接活跃数和新增用户数【access_log,user】
            foreach ($data as &$v){
                $sql_a = "select count(*) as cnt,from_unixtime(a_time,'%Y-%m-%d') as tt from access_log  where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable1'] = GoldcoinLogModel::getAll($sql_a);
                $v['huoyue'] = $v['tmp_variable1'][0]['cnt'];
                $sql_b = "select count(*) as cnt1,from_unixtime(a_time,'%Y-%m-%d') as tt from user where from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable2'] = GoldcoinLogModel::getAll($sql_b);
                $v['xinzeng'] = $v['tmp_variable2'][0]['cnt1'];
                unset($v['tmp_variable1'], $v['tmp_variable2']);

                $sql_c = "select count(id) as id1,from_unixtime(a_time,'%Y-%m-%d') as tt from share where type = 4 AND from_unixtime(a_time,'%Y-%m-%d') = '{$v['date_new']}'";
                $v['tmp_variable3'] = GoldcoinLogModel::getAll($sql_c);
                $v['fx_daohaoyou'] = $v['tmp_variable3'][0]['id1'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    'HKJHKJH',
                    $v['huoyue'],//活跃
                    $v['xinzeng'],//新增用户
                    $v['uids'],//分享用户数
                    $v['ids'],//分享次数
                    $platform_14,
                    $platform_toid_14,
                    $platform_4,
                    $platform_toid_4,
                    '-',
                    '-',
                    $platform_9,
                    $platform_toid_9,
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
}