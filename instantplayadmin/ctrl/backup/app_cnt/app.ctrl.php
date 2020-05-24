<?php
/**
 * Created by PhpStorm.
 * User: xiahongbo
 * Date: 2019/3/19
 * Time: 17:28
 */

/**
 * APP统计->APP数据汇总;
 * Class appCtrl
 */
class appCtrl extends BaseCtrl{
    /**
     * APP统计->APP数据汇总->日数据（默认）;
     */
    public function index(){
        if(_g("getlist")){
            $this->getList();
        }
        $this->display("/app_cnt/app/index.html");
    }

    public function getList()
    {
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();
        // ids总新增;
        // 男新增maleCount;
        // 女新增femaleCount;
        // huoyue总活跃;
        // maleCounthuoyue男活跃
        // femaleCounthuoyue女活跃
        // users_cnt每月的人数
        // zongrenshu总人数
        // nanrenshu男总人数
        // nvrenshu女总人数
        // 下面这条SQL后期进行优化的时候可能会用到，暂时先做保存;
        // count(CASE WHEN from_unixtime(user.a_time,'%Y-%m-%d') < '{$value['date_new']}' THEN from_unixtime(user.a_time,'%Y-%m-%d') END ) AS renshu
        $select_sql = "SELECT *,COUNT(id) AS ids, 
                       COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                       COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                       DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                       FROM user GROUP BY date_new ORDER BY date_new DESC;";
        $result = GoldcoinLogModel::db()->query($select_sql);
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = " SELECT *,COUNT(id) AS ids, 
                           COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                           COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                           DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                           FROM user GROUP BY date_new ORDER BY date_new DESC
                           LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::db()->query($selectSql);
            foreach ($data as &$value){
                $sql1 = "SELECT COUNT(access_log.id) AS cnt,
                     COUNT(user.id) AS users_cnt,
                     FROM_UNIXTIME(access_log.a_time,'%Y-%m-%d') AS tt,
                     COUNT(CASE WHEN user.sex = 1 THEN user.sex END ) AS maleCounthuoyue,
                     COUNT(CASE WHEN user.sex = 2 THEN user.sex END ) AS femaleCounthuoyue
                     FROM access_log LEFT JOIN user ON access_log.uid = user.id 
                     WHERE FROM_UNIXTIME(access_log.a_time,'%Y-%m-%d') = '{$value['date_new']}';";
                $tmp_val = GoldcoinLogModel::db()->query($sql1);
                $value['huoyue'] = $tmp_val[0]['cnt'];// 总活跃
                $value['maleCounthuoyue'] = $tmp_val[0]['maleCounthuoyue'];// 男活跃;
                $value['femaleCounthuoyue'] = $tmp_val[0]['femaleCounthuoyue'];// 女活跃;
                $value['users_cnt'] = $tmp_val[0]['users_cnt'];// 当月总数;
                $sql2 = "SELECT count(id) AS zongrenshu,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanrenshu,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvrenshu FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') <= '{$value['date_new']}'";
                $tmp_val1 = GoldcoinLogModel::db()->query($sql2);
                $value['zongrenshu'] = $tmp_val1[0]['zongrenshu'];
                $value['nanrenshu'] = $tmp_val1[0]['nanrenshu'];
                $value['nvrenshu'] = $tmp_val1[0]['nvrenshu'];
                $sql3 = "SELECT count(id) AS zl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvl FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') < '{$value['date_new']}'";
                $tmp_val2 = GoldcoinLogModel::db()->query($sql3);
                $value['zl'] = $tmp_val2[0]['zl'];
                $value['nanl'] = $tmp_val2[0]['nanl'];
                $value['nvl'] = $tmp_val2[0]['nvl'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['date_new'],
                    $v['ids'],
                    '',
                    $v['huoyue'],
                    $v['zl'],
                    $v['maleCount'],
                    $v['maleCounthuoyue'],
                    $v['nanl'],
                    $v['femaleCount'],
                    $v['femaleCounthuoyue'],
                    $v['nvl'],
                    $v['zongrenshu'],
                    $v['nanrenshu'],
                    $v['nvrenshu'],
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

    /**
     * 周维度数据;
     */
    public function week(){
        if(_g("getlist")){
            $this->recordWeek();
        }
        $this->display("/app_cnt/app/record_week.html");
    }

    /**
     * Ajax调用获取周维度数据详情;
     */
    public function recordWeek(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        // ids总新增;
        // huoyue总活跃;
        // maleCounthuoyue男活跃
        // femaleCounthuoyue女活跃
        // 下面这条SQL后期进行优化的时候可能会用到，暂时先做保存;
        // count(CASE WHEN from_unixtime(user.a_time,'%Y-%m-%d') < '{$value['date_new']}' THEN from_unixtime(user.a_time,'%Y-%m-%d') END ) AS renshu
        $select_sql = "SELECT *,COUNT(id) AS ids, 
                       COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                       COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                       DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%u') AS week_new
                       FROM user GROUP BY week_new ORDER BY week_new DESC;";
        $result = GoldcoinLogModel::getAll($select_sql);
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = " SELECT *,COUNT(id) AS ids, 
                           COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                           COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                           DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%u') AS week_new
                           FROM user GROUP BY week_new ORDER BY week_new DESC
                           LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            foreach ($data as &$value){
                $sql1 = "SELECT COUNT(access_log.id) AS cnt,
                     COUNT(user.id) AS users_cnt,
                     FROM_UNIXTIME(access_log.a_time,'%Y%u') AS tt,
                     COUNT(CASE WHEN user.sex = 1 THEN user.sex END ) AS maleCounthuoyue,
                     COUNT(CASE WHEN user.sex = 2 THEN user.sex END ) AS femaleCounthuoyue
                     FROM access_log LEFT JOIN user ON access_log.uid = user.id 
                     WHERE FROM_UNIXTIME(access_log.a_time,'%Y%u') = '{$value['week_new']}';";
                $tmp_val = GoldcoinLogModel::getAll($sql1);
                $value['huoyue'] = $tmp_val[0]['cnt'];// 总活跃
                $value['maleCounthuoyue'] = $tmp_val[0]['maleCounthuoyue'];// 男活跃;
                $value['femaleCounthuoyue'] = $tmp_val[0]['femaleCounthuoyue'];// 女活跃;
                $value['users_cnt'] = $tmp_val[0]['users_cnt'];// 当月总数;
                $sql3 = "SELECT count(id) AS zl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvl FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%u') < '{$value['week_new']}'";
                $tmp_val2 = GoldcoinLogModel::getAll($sql3);
                $value['zl'] = $tmp_val2[0]['zl'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['week_new'],
                    $v['ids'],
                    $v['huoyue'],
                    $v['zl'],
                    $v['maleCounthuoyue'],
                    $v['femaleCounthuoyue'],
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
     * 月维度数据;
     */
    public function month(){
        if(_g("getlist")){
            $this->recordMonth();
        }
        $this->display("/app_cnt/app/record_month.html");
    }

    /**
     * Ajax调用获取月维度数据详情;
     */
    public function recordMonth(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        // ids总新增;
        // huoyue总活跃;
        // maleCounthuoyue男活跃
        // femaleCounthuoyue女活跃
        // 下面这条SQL后期进行优化的时候可能会用到，暂时先做保存;
        // count(CASE WHEN from_unixtime(user.a_time,'%Y-%m-%d') < '{$value['date_new']}' THEN from_unixtime(user.a_time,'%Y-%m-%d') END ) AS renshu
        $select_sql = "SELECT *,COUNT(id) AS ids, 
                       COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                       COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                       DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%m') AS month_new
                       FROM user GROUP BY month_new ORDER BY month_new DESC;";
        $result = GoldcoinLogModel::getAll($select_sql);
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = " SELECT *,COUNT(id) AS ids, 
                           COUNT(CASE WHEN sex = 1 THEN sex END ) AS maleCount,
                           COUNT(CASE WHEN sex = 2 THEN sex END ) AS femaleCount,
                           DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%m') AS month_new
                           FROM user GROUP BY month_new ORDER BY month_new DESC
                           LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            foreach ($data as &$value){
                $sql1 = "SELECT COUNT(access_log.id) AS cnt,
                     COUNT(user.id) AS users_cnt,
                     FROM_UNIXTIME(access_log.a_time,'%Y%m') AS tt,
                     COUNT(CASE WHEN user.sex = 1 THEN user.sex END ) AS maleCounthuoyue,
                     COUNT(CASE WHEN user.sex = 2 THEN user.sex END ) AS femaleCounthuoyue
                     FROM access_log LEFT JOIN user ON access_log.uid = user.id 
                     WHERE FROM_UNIXTIME(access_log.a_time,'%Y%m') = '{$value['month_new']}';";
                $tmp_val = GoldcoinLogModel::getAll($sql1);
                $value['huoyue'] = $tmp_val[0]['cnt'];// 总活跃
                $value['maleCounthuoyue'] = $tmp_val[0]['maleCounthuoyue'];// 男活跃;
                $value['femaleCounthuoyue'] = $tmp_val[0]['femaleCounthuoyue'];// 女活跃;
                $value['users_cnt'] = $tmp_val[0]['users_cnt'];// 当月总数;
                $sql3 = "SELECT count(id) AS zl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nanl,COUNT(CASE WHEN sex = 1 THEN sex END ) AS nvl FROM user WHERE DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y%m') < '{$value['month_new']}'";
                $tmp_val2 = GoldcoinLogModel::getAll($sql3);
                $value['zl'] = $tmp_val2[0]['zl'];
            }
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['month_new'],
                    $v['ids'],
                    $v['huoyue'],
                    $v['zl'],
                    $v['maleCounthuoyue'],
                    $v['femaleCounthuoyue'],
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
     * 内购数据列表页;
     */
    public function purchase(){
        if(_g("getlist")){
            $this->recordInternal();
        }
        $this->display("/app_cnt/app/record_internal.html");
    }

    /**
     * Ajax调用内购数据详情页;
     */
    public function recordInternal(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        // ids总新增;
        // huoyue总活跃;
        // maleCounthuoyue男活跃
        // femaleCounthuoyue女活跃
        // 下面这条SQL后期进行优化的时候可能会用到，暂时先做保存;
        // count(CASE WHEN from_unixtime(user.a_time,'%Y-%m-%d') < '{$value['date_new']}' THEN from_unixtime(user.a_time,'%Y-%m-%d') END ) AS renshu
        $select_sql = "SELECT *,COUNT(id) AS ids,
                       COUNT(money) AS moneys, 
                       DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                       FROM games_goods_order GROUP BY date_new ORDER BY date_new DESC;";
        $result = GoldcoinLogModel::getAll($select_sql);
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = " SELECT *,COUNT(id) AS ids,
                           COUNT(money) AS moneys, 
                           COUNT(CASE WHEN status = 2 THEN status END ) AS pay,
                           DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new
                           FROM games_goods_order GROUP BY date_new ORDER BY date_new DESC
                           LIMIT $iDisplayStart, $end ";
            $data = GoldcoinLogModel::getAll($selectSql);
            foreach ($data as &$value){
                $sql1 = "SELECT COUNT(access_log.id) AS cnt
                         FROM access_log
                         WHERE FROM_UNIXTIME(a_time,'%Y-%m-%d') = '{$value['date_new']}';";
                $tmp_val = GoldcoinLogModel::getAll($sql1);
                $value['huoyue'] = $tmp_val[0]['cnt'];
            }
            // 日期，活跃，流水，充值人数，ARPU，APPRU，付费率，总金额,总充值人数
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['date_new'],
                    $v['huoyue'],
                    $v['moneys'],
                    $v['ids'],
                    '',
                    '',
                    round($v['pay']/$v['ids']*100).'%',
                    $v['moneys'],
                    $v['ids'],
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
     * 总留存数据;
     */
    public function retain(){
        if(_g("getlist")){
            $this->getRetainDataAll();
        }
        $this->display("/app_cnt/app/record_retain.html");
    }

    /**
     * 总留存数据;
     */
    public function getRetainDataAll(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $select_sql = "SELECT
                        first_day,
                        sum(CASE WHEN by_day = 0 THEN 1 ELSE 0 END) day_0,
                        sum(CASE WHEN by_day = 1 THEN 1 ELSE 0 END) day_1,
                        sum(CASE WHEN by_day = 2 THEN 1 ELSE 0 END) day_2,
                        sum(CASE WHEN by_day = 3 THEN 1 ELSE 0 END) day_3,
                        sum(CASE WHEN by_day = 4 THEN 1 ELSE 0 END) day_4,
                        sum(CASE WHEN by_day = 5 THEN 1 ELSE 0 END) day_5,
                        sum(CASE WHEN by_day = 6 THEN 1 ELSE 0 END) day_6,
                        sum(CASE WHEN by_day = 7 THEN 1 ELSE 0 END) day_7,
                        sum(CASE WHEN by_day = 14 THEN 1 ELSE 0 END) day_14,
                        sum(CASE WHEN by_day = 30 THEN 1 ELSE 0 END) day_30
                    FROM
                       (SELECT 
                          uid,
                          a_time,
                          first_day,
                          DATEDIFF(a_time,first_day) AS by_day
                       FROM
                         (SELECT
                            b.uid,
                            b.a_time,
                            c.first_day
                          FROM 
                            (SELECT
                                uid,
                                DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS a_time
                             FROM access_log
                             GROUP BY 1,2) b
                        LEFT JOIN
                          (SELECT
                              uid,
                              min(a_time) AS first_day
                           FROM
                               (SELECT 
                                    uid,
                                    DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS a_time
                                FROM 
                                    access_log
                                GROUP BY 1,2) a
                           GROUP BY 1) c
                         ON b.uid = c.uid
                         ORDER BY 1,2) e
                      ORDER BY 1,2) f
                    GROUP BY 1
                    ORDER BY first_day DESC;";
        $result = GoldcoinLogModel::db()->query($select_sql);
        $iTotalRecords = (count($result)) ? count($result) : 0;

        if (!empty($result) && is_array($result)) {
            $iDisplayLength = intval($_REQUEST['length']);
            if ('999999' == $iDisplayLength) {
                $iDisplayLength = $iTotalRecords;
            } else {
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }
            $iDisplayStart = intval($_REQUEST['start']);
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $selectSql = "  SELECT
                            first_day,
                            sum(CASE WHEN by_day = 0 THEN 1 ELSE 0 END) day_0,
                            sum(CASE WHEN by_day = 1 THEN 1 ELSE 0 END) day_1,
                            sum(CASE WHEN by_day = 2 THEN 1 ELSE 0 END) day_2,
                            sum(CASE WHEN by_day = 3 THEN 1 ELSE 0 END) day_3,
                            sum(CASE WHEN by_day = 4 THEN 1 ELSE 0 END) day_4,
                            sum(CASE WHEN by_day = 5 THEN 1 ELSE 0 END) day_5,
                            sum(CASE WHEN by_day = 6 THEN 1 ELSE 0 END) day_6,
                            sum(CASE WHEN by_day = 7 THEN 1 ELSE 0 END) day_7,
                            sum(CASE WHEN by_day = 14 THEN 1 ELSE 0 END) day_14,
                            sum(CASE WHEN by_day = 30 THEN 1 ELSE 0 END) day_30
                        FROM
                           (SELECT 
                              uid,
                              a_time,
                              first_day,
                              DATEDIFF(a_time,first_day) AS by_day
                           FROM
                             (SELECT
                                b.uid,
                                b.a_time,
                                c.first_day
                              FROM 
                                (SELECT
                                    uid,
                                    DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS a_time
                                 FROM access_log
                                 GROUP BY 1,2) b
                            LEFT JOIN
                              (SELECT
                                  uid,
                                  min(a_time) AS first_day
                               FROM
                                   (SELECT 
                                        uid,
                                        DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS a_time
                                    FROM 
                                        access_log
                                    GROUP BY 1,2) a
                               GROUP BY 1) c
                             ON b.uid = c.uid
                             ORDER BY 1,2) e
                          ORDER BY 1,2) f
                        GROUP BY 1
                        ORDER BY first_day DESC
                        LIMIT $iDisplayStart, $iDisplayLength ;";
            $data = GoldcoinLogModel::db()->query($selectSql);
            foreach ($data as $k => $v) {
                $records["data"][] = array(
                    $v['first_day'],
                    $v['day_0'],
                    round($v['day_1']/$v['day_0']*100).'%',
                    round($v['day_2']/$v['day_0']*100).'%',
                    round($v['day_3']/$v['day_0']*100).'%',
                    round($v['day_4']/$v['day_0']*100).'%',
                    round($v['day_5']/$v['day_0']*100).'%',
                    round($v['day_6']/$v['day_0']*100).'%',
                    round($v['day_7']/$v['day_0']*100).'%',
                    round($v['day_14']/$v['day_0']*100).'%',
                    round($v['day_30']/$v['day_0']*100).'%'
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