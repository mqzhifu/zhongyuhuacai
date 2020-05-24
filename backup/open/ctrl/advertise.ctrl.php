<?php

/**
 * open后台广告位
 * Class AdvertiseCtrl
 */
class AdvertiseCtrl extends BaseCtrl
{
    public function __construct ($frame = null, $ctrl, $ac)
    {
        parent::__construct($frame, $ctrl, $ac);
        $gameid = _g("gameid");
        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameid, 'ad')) {
            echo "<script>alert('您没有该权限');history.go(-1);</script>";
            exit(0);
        }

    }

    /**
     * 展示某一个游戏的广告
     */
    public function manage()
    {
        $this->checkGame();

        $this->display('advertise/adManagement.html', 'new', 'isLogin');
    }

    public function getAds()
    {
        $this->checkGame();

        $length = 10;
        $gameid = _g("gameid");
        $page = _g("page");
        $start = ($page-1) * $length;

        $where = " game_id = $gameid and status <> 4 ";
        $count = OpenAdvertiseModel::db()->getCount($where);

        $totalPage = ceil($count/$length);
        if ($page > $totalPage) {
            $list = [];
        } else {
            $list = OpenAdvertiseModel::db()->getAll("$where order by u_time desc limit $start,$length");
        }

        $AdvertiseTypeDesc = OpenAdvertiseModel::getAdvertiseTypeDesc();
        $directionDesc = OpenAdvertiseModel::getAdDirectionDesc();

        foreach ($list as $k => $value) {
            if (isset($AdvertiseTypeDesc[$value['advertise_type']])) {
                $value['advertise_type_desc'] = $AdvertiseTypeDesc[$value['advertise_type']];
            } else {
                $value['advertise_type_desc'] = '';
            }
            if (isset($directionDesc[$value['direction']])) {
                if ($value['advertise_type'] == OpenAdvertiseModel::$_advertise_type_3) {
                    $value['direction_desc'] = '';
                } else {
                    $value['direction_desc'] = $directionDesc[$value['direction']];
                }
            } else {
                $value['direction_desc'] = '';
            }
            $list[$k] = $value;
        }

        $this->outputJson(200, "succ", ['totalPage'=>$totalPage,'list'=>$list]);
    }


    /**
     * 给某个游戏添加广告
     */
    public function add()
    {
        $this->checkGame();

        $this->assign('adType', OpenAdvertiseModel::getAdvertiseTypeDesc());
        $this->assign('direction', OpenAdvertiseModel::getAdDirectionDesc());
        $this->display('advertise/adCreation.html', 'new', 'isLogin');

    }


    public function addSubmit()
    {

        $gameinfo = $this->checkGame();
        $gameid = $gameinfo["id"];

        $title = _g("title");
        $adType = _g("adType");
        $frequencyType = _g("frequencyType");
        $interval = _g("interval");
        $times = _g("times");
        $period = _g("period");

        if (!$title || !$adType) {
            $this->outputJson(0, "参数有误");
        }

        $ad = [
            'game_id' => $gameid,
            'uid' => $this->_uid,
            'title' => $title,
            'advertise_type' => $adType,
            'status' => OpenAdvertiseModel::$_status_wait,
            'a_time' => time(),
            'u_time' => time(),
        ];

        if (PCK_AREA != 'en') {
            $direction = _g("direction");
            if ($adType != OpenAdvertiseModel::$_advertise_type_3 && !$direction) {
                $this->outputJson(0, "添加失败，未选择广告方向");
            }
            $ad['direction'] = $direction;
        }

        if ($adType == OpenAdvertiseModel::$_advertise_type_1) {
            if (!$frequencyType) {
                $this->outputJson(0, "参数有误");
            }
            $ad['frequency_type'] = $frequencyType;

            if ($frequencyType == OpenAdvertiseModel::$_strategy_interval) {
                if (!$interval) {
                    $this->outputJson(0, "参数有误");
                }
                $ad['interval'] = $interval;
            } elseif ($frequencyType == OpenAdvertiseModel::$_strategy_times) {
                if (!$times || !$period) {
                    $this->outputJson(0, "参数有误");
                }
                $ad['times'] = $times;
                $ad['period'] = $period;
            }
        }

        if (OpenAdvertiseModel::db()->add($ad)) {
            $this->outputJson(200, "添加成功");
        }
    }

    // 广告开关状态
    public function switchAd()
    {
        $gameinfo = $this->checkGame();
        $gid = $gameinfo["id"];
        $aid = _g("aid");
        $status = _g("status");
        if ($status != 2 && $status != 3) {
            $this->outputJson(0, "参数错误");
        }
        $updateInfo = [
            'status'=>$status
        ];
        $res = $this->openAdvertiseService->updateAdvertise($this->_uid, $gid, $aid, $updateInfo);
        if ($res['result']) {
            $this->outputJson(200, "succ");
        }

    }

    public function getAdDetail()
    {
        $gameinfo = $this->checkGame();
        $aid = _g("aid");

        $ad = OpenAdvertiseModel::db()->getRowById($aid);

        if (!$ad) {
            $this->outputJson(0, "查询不到该广告位信息");
        } else {
            $this->outputJson(200, "succ", $ad);
        }

    }


    /**
     * 更新某一个游戏的信息
     */
    public function update ()
    {
        $filter = $this->filterParam(['gameid' => 'intval', 'aid' => 'intval']);
        $gid = $filter['gameid'];
        $aid = $filter['aid'];
        if (!$this->_uid || !$gid || !$aid) {
            $res = [
                'result' => false
            ];
        } else {
            if ($this->openAdvertiseService->getAdvertiseInfo($gid, $aid)) {
                $updateInfo = $this->filterParam($this->openUpdateAdFields('update'), true);
                if (!$updateInfo) {
                    $res = [
                        'result' => false
                    ];
                } else {
                    $res = $this->openAdvertiseService->updateAdvertise($this->_uid, $gid, $aid, $updateInfo);
                }
            } else {
                $res = [
                    'result' => false,
                    'msg' => '用户不存在该游戏广告位信息！',
                ];
            }

        }
        echo json_encode($res);
        return;
    }

    /**
     * 软删除某一个游戏的广告信息
     */
    public function delete ()
    {
        $filter = $this->filterParam(['gameid' => 'intval', 'aid' => 'intval']);
        $gid = $filter['gameid'];
        $aid = $filter['aid'];
        if (!$this->_uid || !$gid || !$aid) {
            $res = [
                'result' => false
            ];
        } else {
            if ($this->openGamesService->getUserGamesInfo($this->_uid, $gid)) {
                $res = $this->openAdvertiseService->deleteAdvertise($this->_uid, $gid, $aid);
            } else {
                $res = [
                    'result' => false,
                    'msg' => '所要删除的信息不存在！',
                ];
            }
        }
        echo json_encode($res);
        return;
    }



    public function chart ()
    {

        $this->checkGame();

        $gid = $this->filterParam(['gameid' => 'intval'])['gameid'];

        $page = 1;

        if(empty(_g("start_date")) || empty(_g("end_date"))) {
            $endDate = date("Y-m-d", time());
            $startDate = date("Y-m-d", strtotime("$endDate -1 week"));
        } else {
            if(strtotime(_g("start_date")) > 0 || strtotime(_g("end_date")) > 0) {
                $endDate = date("Y-m-d", strtotime(_g("start_date")));
                $startDate = date("Y-m-d", strtotime(_g("end_date")));
            } else {
                echo '日期格式不对哦~';die;
            }

        }

        $where = " game_id = $gid and stat_datetime between '". $startDate ."' and '". $endDate ."'";
        $order = " order by a_time desc";

        $totalPage = OpenAdvertiseModel::countAdvertiseList($where);//总记录数

        $pageInfo = PageLib::getPageInfo($totalPage, 20, $page);

        $limit = " limit {$pageInfo['start']}, {$pageInfo['end']}";

        $list = OpenAdvertiseModel::getAdvertiseList($where, $order, $limit);

        $field = "sum(`show`) sum_show, sum(click) sum_click, sum(click_rate) sum_click_rate, sum(cost) sum_cost";
        $selTotalSql = "select $field from open_advertise a left join open_advertise_income ai on a.adtoutiao_id = ai.ad_slot_id where $where ";
        $totalData = OpenAdvertiseModel::db()->getRowBySQL($selTotalSql);

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gid, 'revenue')) {
            echo "<script>alert('您没有该权限');history.go(-1);</script>";
            exit(0);
        }

        $this->assign('gameid', $gid);
        $adType = OpenAdvertiseModel::getAdvertiseTypeDesc();
        $this->assign('adType', $adType);
        $this->assign('totalPage', $totalPage);

        $this->assign('list', $list);
        $this->assign('totalData', $totalData);

        // $this->addCss('assets/open/css/vertise-chart-normalize.css');
        $this->addCss('assets/open/css/vertise-chart.css');
        $this->addJs('assets/open/scripts/echarts.js');
        $this->addCss('/layui/css/layui.css');
        $this->addJs('/layui/layui.js');
        $this->display('advertise/advertise_chart.html');
    }

    /**
     * 获取按时间选取的下拉框内容
     * @return array
     */
    private function getAdTimeInfo ()
    {
        $adTime = [
            0 => '今日累计',
            1 => '昨天',
            2 => '过去7天',
            3 => '过去30天',
            4 => '本月累计',
            5 => '上月',
            6 => '所有时间',
        ];
        return $adTime;
    }

    /**
     * 收入图表Json信息
     */
    public function income ()
    {
        $filter = $this->filterParam(['gameid' => 'intval', 'type' => 'intval', 'curr' => 'intval']);
        $type = $filter['type'];
        $gameId = $filter["gameid"];
        $page = !empty($filter["curr"]) ? $filter["curr"] : 1;

        if(empty(_g("start_date")) || empty(_g("end_date"))) {
            $endDate = date("Y-m-d", time());
            $startDate = date("Y-m-d", strtotime("$endDate -1 week"));
        } else {
            $endDate = date("Y-m-d", strtotime(_g("end_date")));
            $startDate = date("Y-m-d", strtotime(_g("start_date")));
        }

        //获取两个时间区间的所有天
        $allDay = self::getAllDay($startDate, $endDate);

        //查询时间区间的所有数据
        $where = " game_id = $gameId and stat_datetime between '". $startDate ."' and '". $endDate ."'";
        if(!empty($type)) {
            $where .= "and advertise_type = $type";
        }
        $order = " order by stat_datetime desc";

        $totalPage = OpenAdvertiseModel::countAdvertiseList($where);//总记录数

        $pageInfo = PageLib::getPageInfo($totalPage, 10, $page);

        $limit = " limit {$pageInfo['start']}, {$pageInfo['end']}";

        $advertiseList = OpenAdvertiseModel::getAdvertiseList($where, $order, $limit);  //列表数据加limit

        $advertiseChartList = OpenAdvertiseModel::getAdvertiseList($where, $order);  //图表数据去掉limit

        //整理返回值 为下边整合数据用
        $list = '';
        foreach ($advertiseChartList as $k => $v) {
            if(!isset($list[$v['stat_datetime']])) {
                $list[$v['stat_datetime']] = $v;
            } else {
                $list[$v['stat_datetime']]['show'] += $v['show'];              //曝光量
                $list[$v['stat_datetime']]['click'] += $v['click'];            //点击量
                $list[$v['stat_datetime']]['click_rate'] += $v['click_rate'];  //点记率
                $list[$v['stat_datetime']]['cost'] += $v['cost'];            //收入
            }
        }

        //遍历所有的天 从结果集中插入指标数据
        foreach ($allDay as $k => $v) {
            if(isset($list[$v]) && $list[$v]['stat_datetime'] == $v) {
                $showArray[] = $list[$v]['show'];              //曝光量
                $clickArray[] = $list[$v]['click'];            //点击量
                $clickRateArray[] = $list[$v]['click_rate'];  //点记率
                $incomeArray[] = $list[$v]['cost'];            //收入
            } else {
                $showArray[] = 0;              //曝光量
                $clickArray[] = 0;             //点击量
                $clickRateArray[] = 0;         //点记率
                $incomeArray[] = 0;            //收入
            }
        }

        $res = [
            'title' => '关键指标趋势图',
            'date' => $allDay,
            'income' => $incomeArray,
            'income_count' => array_sum($incomeArray),
            'show' => $showArray,
            'show_count' => array_sum($showArray),
            'click' => $clickArray,
            'click_rate' => $clickRateArray,
            'tabledata' => $advertiseList,
            'result' => true,
        ];

        echo json_encode($res);
        return;
    }

    public function test ()
    {
        $s = (new AdtoutiaoService())->getAppAdList('2019-02-28', '2019-03-1');
        print_r($s);
    }

    private function openUpdateAdFields ($opeartion = 'add')
    {
        $updateFields = [
            'title' => '',
            'frequency_type' => 'intval',
            'interval' => 'intval',
            'times' => 'intval',
            'period' => 'intval',
        ];
        if ($opeartion == 'add') {
            $updateFields['advertise_type'] = 'intval';
            $updateFields['direction'] = 'intval';
        }
        return $updateFields;
    }

    public function paymanage() {
        $this->checkGame();
        $gid = $this->filterParam(['gameid' => 'intval'])['gameid'];
        $userId = $this->_uid;

        //交易列表
        $dealsql = "select * from open_advertise_deal where user_id = $userId and game_id = $gid order by end_date desc";
        $dealList = OpenAdvertiseModel::db()->getAllBySQL($dealsql);
        foreach ($dealList as $k=>$v) {
            $dealList[$k]['create_date'] = date("Y-m-d", $v['create_time']);
        }

        //银行卡列表
        $banksql = "select * from open_bank where user_id = $userId and status = 1 order by create_time desc";
        $bankList = bankModel::db()->getAllBySQL($banksql);

        //已存在的最后对账日期
        $dealEndDate = !empty($dealList) ? $dealList[0]['end_date'] : '';

        $payCost = 0;
        $field = "sum(cost) sum_cost";
        if(empty($dealEndDate)) {
            $where = "game_id = $gid";
        } else {
            $where = "game_id = $gid and stat_datetime <= '".$dealEndDate."'";
            $sql = "select $field from open_advertise a left join open_advertise_income ai on a.adtoutiao_id = ai.ad_slot_id where $where";
            $payCostArr = OpenAdvertiseModel::db()->getRowBySQL($sql);
            $payCost = $payCostArr["sum_cost"];
            $where = "uid = $userId and game_id = $gid and stat_datetime > '".$dealEndDate."'";
        }

        $sql = "select $field from open_advertise a left join open_advertise_income ai on a.adtoutiao_id = ai.ad_slot_id where $where";
        $unpayCostArr = OpenAdvertiseModel::db()->getRowBySQL($sql);
        $unpayCost = $unpayCostArr["sum_cost"];

        $this->assign('payCost', $payCost);
        $this->assign('unpayCost', $unpayCost);
        $this->assign('dealList', $dealList);
        $this->assign('bankList', $bankList);

        $this->addCss('assets/open/css/advertise-style.css');
        $this->addCss('assets/open/css/vertise-chart.css');
        $this->addJs('assets/open/scripts/echarts.js');
        $this->display('advertise/paymanage.html');
    }

    /**
     * Date: 2019/3/4
     * author: haopeng
     * doc:获取两个日期之间的所有天
     * @param $startDate string
     * @param $endDate string
     * @return array
     */
    public static function getAllDay($startDate, $endDate) {
        //默认取一周的时间
        if(empty($startDate) || empty($endDate)) {
            $endDate = date("Y-m-d", time());
            $startDate = date("Y-m-d", strtotime("$endDate -1 week"));
        } else {
            $endDate = date("Y-m-d", strtotime($endDate));
            $startDate = date("Y-m-d", strtotime($startDate));
        }

        $dayTotal = ceil((strtotime($endDate) - strtotime($startDate)) / (3600 * 24)) + 1;

        $allDayArr = [$endDate];
        for($i = 1; $i < $dayTotal; $i++) {
            array_push($allDayArr, date("Y-m-d", strtotime("$endDate -$i day")));
        }

        return $allDayArr;
    }


    public function statistics()
    {
        $this->checkGame();
        $this->addCss("assets/open/css/adStatistics.css");
        $this->addJs("assets/open/scripts/echarts.min.js");
        $this->addJs("assets/open/scripts/adAnalysis.js");
        $this->addJs("assets/open/scripts/laydate/laydate.js");
        $this->display("advertise/adStatistics.html", "new", "isLogin");
    }

    /**
     * 1.获取广告类型、统计类型
     * 2.
     * @return [type] [description]
     */
    public function getAdData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $adType = _g("adType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");
        // 新需求增加所有广告类型
        $adTypeWhereStr = "";
        if($adType){
            $adTypeWhereStr = " and advertise_type=$adType";
        }else{
            $adTypeWhereStr = " and advertise_type in (1,2,3)";
        }

        // 时间过滤
        if(!$from || !$to){
            $this->outputJson(1,'no from or to');
        }
        if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));


        // $sql = "select b.title,oa.click,oa.click_rate,oa.cost,oa.ecpm,oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
        $sql = "select a.title,if(b.cut_show=0,0,(b.cut_click/b.cut_show)) as click_rate,b.cut_click as click,b.cut_cost as cost,b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id  $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";

        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = [0,0,0,0,$value];
        }
        $summary = [0,0,0,0];

        //过滤时间
        $filtTime = strtotime(date("Y-m-d 16:00:00"));
        $filtDate = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));

        $data = advertiseIncomeModel::db()->getAllBySQL($sql);
        foreach ($data as $v) {
            $map[$v['stat_datetime']][0] += $v['show'];
            $map[$v['stat_datetime']][1] += $v['click'];
            // $map[$v['stat_datetime']][0] += $v['click_rate'];
            $map[$v['stat_datetime']][3] += $v['cost'];

            // if没到下午4点
            if($v['stat_datetime'] == $filtDate){
                if($filtTime > time()){
                    $summary[0] += $v['show'];
                    $summary[1] += $v['click'];
                    $summary[3] += $v['cost'];
                }    
            }else{
                $summary[0] += $v['show'];
                $summary[1] += $v['click'];
                $summary[3] += $v['cost'];
            }
            
            
        }
        $map = $this->filtData($map, [0,0,0,0,date("Y-m-d", time()-86400)]);

        foreach ($map as &$value) {
            $value[2] = $value[0]==0 ? 0 : $value[1]/$value[0];
        }
        $summary[2] = $summary[0]==0 ? 0 : $summary[1]/$summary[0];

        // $line = new LineObj();
        // $line->name = "";
        // $line->data_num = array_keys($map);
        // $line->data_date = ;
        // $line->year= "";
        $returnData = [
            "summary"=>$summary,
            "line"=>$line,
            "table"=>array_values($map)
        ];

        $this->outputJson(200, 'succ', $returnData);


    }

    public function getLineData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $adType = _g("adType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        $adTypeWhereStr = "";
        if($adType){
            $adTypeWhereStr = " and advertise_type=$adType";
        }else{
            $adTypeWhereStr = " and advertise_type in (1,2,3)";
        }
        // 时间过滤
        if(!$from || !$to){
            $this->outputJson(1,'no from or to');
        }
        if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);

        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));


        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = 0;
        }

        switch ($type) {
            case 1:// 曝光量
                // $sql = "select oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['show'];
                }
                break;
            case 2:// 点击量
                // $sql = "select oa.click,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql = "select b.cut_click as `click`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['click'];
                }
                break;
            case 3:// 点击率
                // $sql = "select b.title,oa.click,oa.click_rate,oa.cost,oa.ecpm,oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql ="select b.cut_click as `click`,b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                $clickMap = [];
                foreach ($dayArr as $value) {
                    $clickMap[$value] = 0;
                }
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['show'];
                    $clickMap[$v['stat_datetime']] += $v['click'];
                }
                foreach ($map as $key => $value) {
                    $map[$key] = $value==0 ? 0 : $clickMap[$key]/$value;
                }
                break;
            case 4:// 收入
                // $sql = "select oa.cost,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to";
                // $data = advertiseIncomeModel::db()->getAllBySQL($sql);
                $sql ="select b.cut_cost as `cost`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to";
                $data = InnerAdDetailsByDayModel::db()->getAllBySQL($sql);
                foreach ($data as $v) {
                    $map[$v['stat_datetime']] += $v['cost'];
                }
                break;            
            default:
                break;
        }
        

        $map = $this->filtData($map, 0);

        
        $line = new LineObj();
        $line->name = OpenAdvertiseModel::getAdvertiseTypeDesc()[$adType];
        $line->data_num = array_keys($map);
        $line->data_date = array_values($map);
        $line->year= "";

        $this->outputJson(200, 'succ', $line);
    }

    public function getTableData(){
        $info = $this->checkGame();
        $game_id = $info['id'];
        $adType = _g("adType");
        $type = _g("type");
        $from = _g("from");
        $to = _g("to");

        $adTypeWhereStr = "";
        if($adType){
            $adTypeWhereStr = " and advertise_type=$adType";
        }else{
            $adTypeWhereStr = " and advertise_type in (1,2,3)";
        }
        // 时间过滤
        if(!$from || !$to){
            $this->outputJson(1,'no from or to');
        }
        if(!FilterLib::regex($from,'date') || !FilterLib::regex($to,'date')){
            $this->outputJson(2,'日期格式不正确');
        }
        $to = strtotime($to);
        $from = strtotime($from);
        if(($to-$from) > 86400*60){
            $from = $to-86400*60;
        }

        $this->filtDate($from, $to);
        
        $adService = new AdvertiseService();
        $dayArr = $adService->getAllDay(date("Y-m-d", $from), date("Y-m-d", $to));
        $dayArr = array_reverse($dayArr);

        // $sql = "select b.title,oa.click,oa.click_rate,oa.cost,oa.ecpm,oa.show,oa.stat_datetime from (select outer_ad_id,a.title from (select id,title from open_advertise where game_id=$game_id and advertise_type=$adType) as a inner join ad_map am on a.id=am.inner_ad_id) as b inner join open_advertise_income oa on b.outer_ad_id=oa.ad_slot_id where unix_timestamp(stat_datetime) between $from and $to order by stat_datetime desc";
        $sql = "select a.title,if(b.cut_show=0,0,(b.cut_click/b.cut_show)) as click_rate,b.cut_click as click,b.cut_cost as cost,b.cut_show as `show`,b.stat_datetime from (select id,title from open_advertise where game_id=$game_id  $adTypeWhereStr) a inner join inner_ad_details_byday b on a.id=b.inner_ad_id where unix_timestamp(b.stat_datetime) between $from and $to order by stat_datetime desc";
        $map = [];
        foreach ($dayArr as $value) {
            $map[$value] = [$value,0,0,0,0,0];
        }

        $data = advertiseIncomeModel::db()->getAllBySQL($sql);
        $returnData = [];
        // if($type == 1){//汇总数据
        //     foreach ($data as $v) {
        //         $map[$v['stat_datetime']][1] += $v['show'];
        //         $map[$v['stat_datetime']][2] += $v['click'];
        //         $map[$v['stat_datetime']][4] += $v['cost'];

        //     }
        //     foreach ($map as &$value) {
        //         $value[3] = number_format($value[1]==0 ? 0 : $value[2]/$value[1],2);
        //     }
        //     $returnData = array_values($map);
        // }elseif($type == 2){
        //     foreach ($data as $v) {
        //         $returnData[] = [$v['stat_datetime'], $v['title'], $v['show'], ($v['click']*100)."%", number_format($v['click_rate'],2), $v['cost']];  
        //     }
        // }
        $initFiltDay = date("Y-m-d", time()-86400);
        if($type == 1){//汇总数据
            foreach ($data as $v) {
                $map[$v['stat_datetime']][1] += $v['show'];
                $map[$v['stat_datetime']][2] += $v['click'];
                $map[$v['stat_datetime']][4] += round($v['cost'],2);
                $map[$v['stat_datetime']][4] = round($map[$v['stat_datetime']][4],2);
            }
            foreach ($map as &$value) {
                $value[3] = number_format($value[1]==0 ? 0 : $value[2]/$value[1]*100,2)."%";
            }
            $map = $this->filtData($map, [$initFiltDay,0,0,"0.00%",0]);
            // $returnData = array_values($map);
        }elseif($type == 2){
            foreach ($data as $v) {
                $map[$v['stat_datetime']][1] = $v['title'];
                $map[$v['stat_datetime']][2] = $v['show'];
                $map[$v['stat_datetime']][3] = $v['click'];
                $map[$v['stat_datetime']][4] = number_format($v['click_rate']*100,2)."%";
                $map[$v['stat_datetime']][5] = $v['cost'];
                // $returnData[] = [$v['stat_datetime'], $v['title'], $v['show'], $v['click'], number_format($v['click_rate'],2), $v['cost']];  
            }
            
            $map = $this->filtData($map, [$initFiltDay,$map[$initFiltDay][1],0,0,'0.00%',0]);
        }
        $returnData = array_values($map);
        $this->outputJson(200, 'succ', $returnData);

    }

    private function filtData($map, $initData){
        $filtTime = strtotime(date("Y-m-d 16:00:00"));
        $filtDate = date("Y-m-d", strtotime(date("Y-m-d")." -1 day"));
        if($filtTime >= time() && isset($map[$filtDate])){
            $map[$filtDate] = $initData;
        }
        return $map;
    }

    // 限制6-1
    private function filtDate(&$from, &$to){
        $time61 = strtotime("2019-07-01");
        if($from < $time61){
            $from = $time61;
        }
        if($to < $time61){
            $to = $time61;
        }
    }
}

class LineObj{
    public $year;
    public $name;
    public $data_num;
    public $data_date;
}