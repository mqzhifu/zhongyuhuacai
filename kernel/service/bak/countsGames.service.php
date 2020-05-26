<?php

/**
 * Desc: 游戏数据统计
 * User: zhangbingbing
 * Date: 2019/3/12 13:18
 */
class CountsGamesService
{
    private $table = "open_played_games_counts";
    private $otherTable = "open_played_games_other_count";

    private $yesterdayTime = 0;
    private $beforeYesterdayTime = 0;
    private $lastWeekTime = 0;
    private $lastMonthTime = 0;
    private $time = [];
    private $yesterdayData = [];

    public function __construct ()
    {
        $base = strtotime(date('Y-m-d'));//今日0点
        $day = 24 * 60 * 60;
        //昨日23:59:59
        $this->yesterdayTime = $base - 1;

        //前日23:59:59
        $this->beforeYesterdayTime = $base - $day - 1;

        //上周同一天23:59:59
        $this->lastWeekTime = $base - 7 * $day - 1;

        //上月同一天23:59:59 [按30天计算]
        $this->lastMonthTime = $base - 30 * $day - 1;
    }

    private function setParams ()
    {
        //昨日概况
        $yesterdayData = [
            'all_register' => [
                'count' => 0,
                'percent1' => '0%',
                'percent7' => '0%',
                'percent30' => '0%',
            ],
            'active' => [
                'count' => 0,
                'percent1' => '0%',
                'percent7' => '0%',
                'percent30' => '0%',
            ],
            'new_register' => [
                'count' => 0,
                'percent1' => '0%',
                'percent7' => '0%',
                'percent30' => '0%',
            ],
            'stay_time' => [
                'count' => 0,
                'percent1' => '0%',
                'percent7' => '0%',
                'percent30' => '0%',
            ]
        ];
        $this->yesterdayData = $yesterdayData;
    }

    /**
     * 返回数据格式
     * @param array $data
     * @param int $code
     * @param string $msg
     * @return array
     */
    private function returnData ($data = [], $code = 0, $msg = '')
    {
        $r = [
            'data' => $data,
            'msg' => $msg,
            'code' => $code
        ];
        return $r;
    }

    /**
     * 昨日概况 - 图表数据
     * @param $gameId
     * @return array
     * @throws Exception
     */
    public function getYesterdayData ($gameId)
    {
        $gameId = intval($gameId);
        if ($gameId <= 0) {
            return $this->returnData([], 1, '参数有误！');
        }
        $this->setParams();
        //昨日所有类别的数据
        $data = $this->selectGamesData($gameId, $this->yesterdayTime, 0, '*', 'LIMIT 1');
        if (!isset($data[0])) {
            return $this->yesterdayData;
        }
        $data1 = $this->selectGamesData($gameId, $this->beforeYesterdayTime, 0, '*', 'LIMIT 1');
        $data7 = $this->selectGamesData($gameId, $this->lastWeekTime, 0, '*', 'LIMIT 1');
        $data30 = $this->selectGamesData($gameId, $this->lastMonthTime, 0, '*', 'LIMIT 1');

        foreach ($this->yesterdayData as $key => $value) {
            $this->yesterdayData[$key]['count'] = $data[0][$key];
            // 日百分比
            $this->yesterdayData[$key]['percent1'] = $this->dealPercent($data[0][$key], isset($data1[0][$key]) ? $data1[0][$key] : 0);
            // 周百分比
            $this->yesterdayData[$key]['percent7'] = $this->dealPercent($data[0][$key], isset($data7[0][$key]) ? $data7[0][$key] : 0);
            // 月百分比
            $this->yesterdayData[$key]['percent30'] = $this->dealPercent($data[0][$key], isset($data30[0][$key]) ? $data30[0][$key] : 0);
        }

        return $this->returnData($this->yesterdayData);
    }

    /**
     * 从表里查询数据
     * @param $gameId
     * @param $startTime
     * @param $endTime
     * @param string $select
     * @param string $other
     * @return array
     */
    private function selectGamesData ($gameId, $startTime, $endTime = 0, $select = '*', $other = '')
    {
        $startTime = strtotime(date('Y-m-d 23:59:59', $startTime));
        if ($endTime > 0) {
            //查询时间段内的数据
            $endTime = strtotime(date('Y-m-d 23:59:59', $endTime));
            $sql = "SELECT {$select} FROM {$this->table} WHERE `game_id`={$gameId} AND `a_time`>={$startTime} AND `a_time`<={$endTime}{$other}";
        } else {
            //查询某个时间值的数据
            $sql = "SELECT {$select} FROM {$this->table} WHERE `game_id`={$gameId} AND `a_time`={$startTime} {$other}";
        }
        return OpenPlayedGamesCountsModel::db()->query($sql);
    }

    /**
     * 从另一个表里获取实时统计数据
     * @param $gameId
     * @param $aTime
     * @param int $eTime
     * @param int $type
     * @param string $select
     * @param string $other
     * @return array
     */
    private function selectRealTimeGamesData ($gameId, $aTime, $eTime = 0, $type = 0, $select = '*', $other = '')
    {
        if ($eTime > 0) {
            $sql = "SELECT {$select} FROM {$this->otherTable} WHERE `game_id`={$gameId} AND `a_time`>={$aTime} AND `a_time`<={$eTime} AND `type`={$type} {$other}";
        } else {
            $sql = "SELECT {$select} FROM {$this->otherTable} WHERE `game_id`={$gameId} AND `a_time`={$aTime} AND `type`={$type} {$other}";
        }
        return OpenPlayedGamesOtherCountModel::db()->query($sql);
    }

    public function options ($type, $key)
    {
        $options = [
            //访问趋势折线图
            'visit' => [
                'active' => '活跃用户数',
                'new_register' => '新增注册用户数'
            ],
            //访问分析
            'analysis' => [
                'all_register' => '累计注册用户数',
                'active' => '活跃用户数',
                'visit' => '访问次数',
                'new_register' => '新增注册用户数',
                'stay_time' => '人均停留时长',
            ],
            //访问分析 留存
            'keep' => [
                'new_register' => '新增用户留存'
            ],
            //用户画像
            'portrait' => [
                'sex_json' => '活跃用户性别分布',
                'device_json' => '活跃用户机型分布',
                'n_sex_json' => '新增用户性别分布',
                'n_device_json' => '新增用户机型分布',
            ],
        ];
        if (isset($options[$key][$type])) {
            return $options[$key][$type];
        }
        return [];
    }

    /**
     *  近30天访问趋势 包含options中的visit数组
     * @param $gameId
     * @param $option 获取的数据类别
     * @param int $day 天数，默认30天
     * @return array
     * @throws Exception
     */
    public function getGameVisitData ($gameId, $option, $day = 30)
    {
        if (!$this->options($option, 'visit') || $gameId <= 0) {
            return $this->returnData([], 1, '参数有误！');
        }
        $day = intval($day);
        if ($day > 30 || $day <= 0) {
            $day = 30;
        }
        $dayTime = 24 * 60 * 60;//一天的时间
        $endTime = strtotime(date('Y-m-d')) - 1;//昨天
        $startTime = $endTime - ($day - 1) * $dayTime;//n天前

        $res = $this->selectGamesData($gameId, $startTime, $endTime);
        $data = $this->dealResult($res, $startTime, $endTime, $dayTime, $option);
        return $this->returnData($data);
    }

    /**
     * 访问分析 折线图 包含options中的analysis数组
     * @param $gameId
     * @param $startTime
     * @param $endTime
     * @param $option 类别
     * @return array
     */
    public function getGameAnalysisData ($gameId, $startTime, $endTime, $option)
    {
        if (!$this->options($option, 'analysis') || $startTime <= 0 || $endTime <= 0 || $gameId <= 0) {
            return $this->returnData([], 1, '参数有误！');
        }
        $res = $this->selectGamesData($gameId, $startTime, $endTime);

        $dayTime = 24 * 60 * 60;//一天的时间
        $data['chart'] = $this->dealResult($res, $startTime, $endTime, $dayTime, $option);
        $data['table'] = array_reverse($this->dealResult($res, $startTime, $endTime, $dayTime, '*'));
        return $this->returnData($data);
    }

    /**
     * 访问分析 留存
     * @param $gameId
     * @param $startTime
     * @param $endTime
     * @param $option
     * @return array
     */
    public function getGameUserKeepData ($gameId, $startTime, $endTime, $option)
    {
        if (!$this->options($option, 'keep') || $gameId <= 0 || $startTime <= 0 || $endTime <= 0) {
            return $this->returnData([], 1, '参数有误！');
        }
        $res = $this->selectGamesData($gameId, $startTime, $endTime);
        $dayTime = 24 * 60 * 60;
        $data = $this->dealResult($res, $startTime, $endTime, $dayTime, '*');

        foreach ($data as $d => $val) {
            $data[$d] = [
                'login1' => $this->dealPercent($val['login1'], $val[$option], false),
                'login3' => $this->dealPercent($val['login3'], $val[$option], false),
                'login7' => $this->dealPercent($val['login7'], $val[$option], false),
            ];
        }
        $data1['table'] = array_reverse($data);
        $data1['chart'] = $data;
        return $data1;
    }

    /**
     * 用户画像
     * @param $gameId
     * @param $type 1昨日 2上周 3上月
     * @param $option
     * @return array
     */
    public function getGameUserPortraitData ($gameId, $type, $option)
    {
        $time = $this->getUserPortraitTime($type);
        if (!$this->options($option, 'portrait') || $gameId <= 0 || !$time) {
            return $this->returnData([], 1, '参数有误！');
        }
        $res = $this->selectGamesData($gameId, $time['start'], $time['end']);
        $data = [];
        foreach ($res as $k => $val) {
            if ($val[$option]) {
                $data[] = json_decode($val[$option], true);
            }
        }
        $data1 = [];
        foreach ($data as $k1 => $val1) {
            foreach ($val1 as $key => $value) {
                $data1[$key] += $value;
            }
        }
        return $this->returnData($data1);
    }

    /**
     * 获取用户画像函数的时间
     * @param $type
     * @return array|mixed
     */
    private function getUserPortraitTime ($type)
    {
        $types = [
            //昨日
            1 => [
                'start' => $this->yesterdayTime,
                'end' => 0,
            ],
            //上周
            2 => [
                'start' => $this->lastWeekTime,
                'end' => $this->yesterdayTime,
            ],
            //上月
            3 => [
                'start' => $this->lastMonthTime,
                'end' => $this->yesterdayTime,
            ],
        ];
        if (isset($types[$type])) {
            return $types[$type];
        }
        return [];
    }

    /**
     * 格式化从数据库查出来的数据
     * @param $res
     * @param $startTime
     * @param $endTime
     * @param $interval
     * @param $field
     * @param string $dateFormat
     * @return array
     */
    private function dealResult ($res, $startTime, $endTime, $interval, $field, $dateFormat = 'Y-m-d')
    {
        $dealRes = [];
        foreach ($res as $k => $val) {
            if ($field == '*') {
                unset($val['id']);
                unset($val['c_date']);
                $d = date($dateFormat, $val['a_time']);
                $val['date_time'] = $d;
                $dealRes[$d] = $val;
            } else {
                $dealRes[date($dateFormat, $val['a_time'])] = $val[$field];
            }
        }
        $currentTime = $startTime;
        $data = [];
        $i = 1;
        while ($currentTime <= $endTime) {
            $k = date($dateFormat, $currentTime);
            isset($dealRes[$k]) ? $data[$k] = $dealRes[$k] : $data[$k] = 0;
            $currentTime = $i * $interval + $startTime;
            $i++;
        }
        return $data;
    }

    /**
     * 实时统计 - 实时趋势，粒度5分钟
     * @param $gameId
     * @param $startTime
     * @param $endTime
     * @return array
     * @throws Exception
     */
    public function getGameRealTimeData ($gameId, $startTime, $endTime)
    {
        $si = floor(date('m', $startTime) / 5) * 5;
        $startTime = strtotime(date("Y-m-d H:{$si}:00", $startTime));

        $ei = floor(date('m', $endTime) / 5) * 5;
        $endTime = strtotime(date("Y-m-d H:{$ei}:00", $endTime));

        $gameId = intval($gameId);
        if ($startTime <= 0 || $endTime <= 0 || $gameId <= 0) {
            return $this->returnData([], 1, '参数有误！');
        }
        $res = $this->selectRealTimeGamesData($gameId, $startTime, $endTime, OpenPlayedGamesOtherCountModel::$_type_0);

        $iTime = 5 * 60;//间隔时间
        $data = $this->dealResult($res, $startTime, $endTime, $iTime, 'data', 'Y-m-d H:i:s');

        return $this->returnData($data);
    }

    /**
     * 计算百分率
     * @param int $number 值
     * @param int $denominator 分母
     * @param bool $subtract $number-$denominator是否相减
     * @return string
     */
    private function dealPercent ($number, $denominator, $subtract = true)
    {
        $number = intval($number);
        $denominator = intval($denominator);
        if ($denominator > 0) {
            if ($subtract) {
                return strval(round((($number - $denominator) / $denominator) * 100, 2)) . '%';
            } else {
                return strval(round(($number / $denominator) * 100, 2)) . '%';
            }
        } elseif ($number > 0) {
            return '100%';
        } else {
            return '0%';
        }
    }

}