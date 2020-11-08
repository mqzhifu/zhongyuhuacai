<?php
/**
 * Desc: 定时脚本,统计played_games表中的数据
 * User: zhangbingbing
 * Date: 2019/3/12 10:08
 */
set_time_limit(60 * 60);//脚本超时时间30分钟

Class CrontabCountPlayedGamesService
{
    /**
     * 数据库连接
     * @var mysqli|null
     */
    private $connPlayedGames = null;
    private $connPlayedGamesCounts = null;

    private $table = 'played_games';
    private $countTable = 'open_played_games_counts';//该表主要是统计累计注册人数，活跃人数等
    private $countUidTable = 'open_played_games_uid_count';

    private $insertSql = '';

    /**
     * insert的字段
     * @var array
     */
    private static $INSERT_FIELDS = [
        'game_id' => '',
        'all_register' => '',
        'active' => '',
        'new_register' => '',
        'stay_time' => '',
        'login1' => '',
        'login3' => '',
        'login7' => '',
        'visit' => '',
        'sex_json' => '',
        'area_json' => '',
        'device_json' => '',
        'n_sex_json' => '',
        'n_area_json' => '',
        'n_device_json' => '',
        'a_time' => '',
        'date_time' => '',
        'c_date' => '',
    ];
    /**
     * update时的字段
     * @var array
     */
    private static $UPDATE_FIELDS = [
        'all_register' => '',
        'active' => '',
        'new_register' => '',
        'stay_time' => '',
        'login1' => '',
        'login3' => '',
        'login7' => '',
        'visit' => '',
        'sex_json' => '',
        'area_json' => '',
        'device_json' => '',
        'n_sex_json' => '',
        'n_area_json' => '',
        'n_device_json' => '',
    ];

    private static $FORMAT = [
        'fields' => '**fields**',
        'insert' => '**insert**',
        'update' => '**update**',
    ];

    /**
     * 构造函数连接数据库
     * CrontabCountPlayedGamesService constructor.
     */
    public function __construct ()
    {
        $this->connPlayedGames = OpenPlayedGamesModel::db();
        $this->connPlayedGamesCounts = OpenPlayedGamesCountsModel::db();
        $formatFields = self::$FORMAT['fields'];
        $format = self::$FORMAT['insert'];
        $this->insertSql = "INSERT INTO `{$this->countTable}`({$formatFields}) VALUES({$format}) ";
        $updateFields = self::$UPDATE_FIELDS;
        if ($updateFields) {
            $format = self::$FORMAT['update'];
            $this->insertSql .= " ON DUPLICATE KEY UPDATE {$format}";
        }

    }

    /**
     * 统计昨日数据
     * @return bool
     * @throws Exception
     */
    public function countYesterdayData ()
    {
        $base = strtotime(date('Y-m-d')) - 24 * 60 * 60;//昨日0点
        return $this->countPlayedGamesData($base);
    }

    /**
     * 开始统计
     * @return array
     * @throws Exception
     */
    public function countByDate ()
    {
        //'2019/2/25';
        $min = strtotime('2019-2-25');
        $max = strtotime(date('Y-m-d'));//今日0点
        $r = [];
        while ($max > $min) {
            $r[] = $this->countPlayedGamesData($min);
            $min = $min + 24 * 60 * 60;
        }
        return $r;
    }

    public function countUserInfo ()
    {
        //'2019/2/25';
        $min = strtotime('2019-2-25');
        $max = strtotime(date('Y-m-d'));//今日0点
        $r = [];
        while ($max > $min) {
            $r[] = $this->countGameUserInfoData($min);
            $min = $min + 24 * 60 * 60;
        }
        return $r;
    }

    /**
     * 新增用户，次登，3日登，7日登
     * @param $base 基准时间
     * @param $loginBase 查询时间
     * @param $type
     * @return string
     * @throws Exception
     */
    private function countPlayedGamesKeepData ($base, $loginBase, $type)
    {
        $types = ['login1', 'login3', 'login7'];
        if (!in_array($type, $types)) {
            return '参数有误';
        }
        $bTime = $this->dealBaseTime($base);
        $loginTime = $this->dealBaseTime($loginBase);

        $insertTime = $loginTime['t24'] - 1;//基准时间的23:59:59

        //获取查询时间的新增用户id
        $sql = "SELECT * FROM {$this->countUidTable} WHERE `a_time`>={$loginTime['t0']} AND `a_time`<{$loginTime['t24']} ORDER BY `game_id`";
        $res = OpenPlayedGamesUidCountModel::db()->query($sql);

        //将gid对应的uid对应起来组成数组
        $data = [];
        foreach ($res as $k => $info) {
            $data[$info['game_id']][] = $info['uid'];
        }

        $iData = [];
        foreach ($data as $gameId => $uid) {
            $in = implode(',', $uid);
            $sql = "SELECT COUNT(DISTINCT `uid`) AS `c` FROM {$this->table} WHERE `game_id`={$gameId} AND `uid` IN({$in}) AND `a_time`>={$bTime['t0']} AND `a_time`<{$bTime['t24']} ";
            $c = $this->connPlayedGames->query($sql);
            $iData[] = [
                'game_id' => $gameId,
                'c' => intval($c[0]['c']),
            ];
        }
        if ($iData) {
            $this->insertData($insertTime, $type, $iData);
        }

        return $type . '_' . date('Y-m-d', $loginBase) . '_' . __FUNCTION__;
    }

    /**
     * 处理时间
     * @param $base
     * @return array
     */
    private function dealBaseTime ($base)
    {
        $base = strtotime(date('Y-m-d', $base));//time字符归为0点时刻
        $day = 24 * 60 * 60;
        //传入基准时间的0点和24点
        $bTime = [
            't0' => $base,
            't24' => $base + $day,
        ];
        return $bTime;
    }

    /**
     * 统计新注册用户和活跃用户的男女，机型等信息
     * @param $base
     * @return bool
     * @throws Exception
     */
    private function countGameUserInfoData ($base)
    {
        $bTime = $this->dealBaseTime($base);

        $insertTime = $bTime['t24'] - 1;//插库时间为基准时间的23:59:59

        //基准时间内的game_id
        $sql = "SELECT DISTINCT(`game_id`) FROM {$this->table} WHERE `a_time`>={$bTime['t0']} AND `a_time`<{$bTime['t24']} ORDER BY `game_id`";
        $gameIds = $this->connPlayedGames->query($sql);
        $cacheData = [];
        $data = [];
        foreach ($gameIds as $k => $val) {
            //活跃用户数
            $sql = "SELECT DISTINCT(`uid`) FROM {$this->table} WHERE `a_time`>={$bTime['t0']} AND `a_time`<{$bTime['t24']} AND `game_id`={$val['game_id']} ORDER BY `uid`";
            $uids = $this->connPlayedGames->query($sql);

            foreach ($uids as $k1 => $uid) {
                if (isset($cacheData[$uid['uid']])) {
                    $data1 = $cacheData[$uid['uid']];
                } else {
                    $sql = "SELECT `sex`,`os`,`device_model` FROM `login` WHERE `login_type`=1 AND `uid`={$uid['uid']} AND `a_time`>={$bTime['t0']} AND `a_time`<{$bTime['t24']} LIMIT 1";
                    $res = LoginModel::db()->query($sql);

                    $data1 = [
                        'sex' => isset($res[0]['sex']) ? $res[0]['sex'] : 0,
                        //'os' => isset($res[0]['os']) ? $res[0]['os'] : 'null',
                        'device_model' => isset($res[0]['device_model']) ? $res[0]['device_model'] : 'null',
                    ];

                    $cacheData[$uid['uid']] = $data1;
                }
                //这里面的信息包含着今日新增的数据
                $data['sex'][] = $data1['sex'];
                //$data[$val['game_id']]['os'][] = $data1['os'];
                $data['device_model'][] = $data1['device_model'];
            }
            if ($data) {
                $sexJsonInfo[] = [
                    'game_id' => $val['game_id'],
                    'c' => json_encode(array_count_values($data['sex']))
                ];
                $deviceJsonInfo[] = [
                    'game_id' => $val['game_id'],
                    'c' => json_encode(array_count_values($data['device_model']))
                ];
            }
            $data = [];
            unset($uids);
            unset($data1);
            unset($res);
            $sql = "SELECT * FROM {$this->countUidTable} WHERE `a_time`>={$bTime['t0']} AND `a_time`<{$bTime['t24']} AND `game_id`={$val['game_id']} ORDER BY `uid`";
            $uids = OpenPlayedGamesUidCountModel::db()->query($sql);
            foreach ($uids as $k1 => $uid) {
                $data1 = $cacheData[$uid['uid']];
                $data['sex'][] = $data1['sex'];
                //$data[$val['game_id']]['os'][] = $data1['os'];
                $data['device_model'][] = $data1['device_model'];
            }
            if ($data) {
                $nSexJsonInfo[] = [
                    'game_id' => $val['game_id'],
                    'c' => json_encode(array_count_values($data['sex']))
                ];
                $nDeviceJsonInfo[] = [
                    'game_id' => $val['game_id'],
                    'c' => json_encode(array_count_values($data['device_model']))
                ];
            }
        }
        if (isset($sexJsonInfo)) {
            $this->insertData($insertTime, 'sex_json', $sexJsonInfo);
        }
        if (isset($deviceJsonInfo)) {
            $this->insertData($insertTime, 'device_json', $deviceJsonInfo);
        }
        if (isset($nSexJsonInfo)) {
            $this->insertData($insertTime, 'n_sex_json', $nSexJsonInfo);
        }
        if (isset($nDeviceJsonInfo)) {
            $this->insertData($insertTime, 'n_device_json', $nDeviceJsonInfo);
        }

        return __FUNCTION__;
    }

    /**
     * 统计新注册用户的uid
     * @param $base
     * @return bool
     * @throws Exception
     */
    private function countGameNewRegisterUidData ($base)
    {
        $bTime = $this->dealBaseTime($base);
        //基准时间的注册用户uid
        $sql = "SELECT `uid`,`game_id`,`a_time` FROM {$this->table} WHERE `a_time`<{$bTime['t24']} AND `a_time`>={$bTime['t0']} ORDER BY `a_time` DESC ";
        $res = $this->connPlayedGames->query($sql);

        $data = [];
        foreach ($res as $k => $v) {
            $data[$v['game_id']][$v['uid']] = $v['a_time'];
        }
        $data1 = [];
        foreach ($data as $gid => $uidInfo) {
            foreach ($uidInfo as $uid => $time) {
                $sql = "SELECT `id` FROM {$this->table} WHERE `a_time`<{$bTime['t0']} AND `uid`={$uid} AND `game_id`={$gid} LIMIT 1";
                $have = $this->connPlayedGames->query($sql);
                if (!$have) {
                    $data1[] = [
                        'game_id' => $gid,
                        'uid' => $uid,
                        'a_time' => $time,
                        'date_time' => date('Y-m-d H:i:s', $time),
                        'c_date' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        foreach ($data1 as $k => $v) {
            $fields = "`" . implode("`,`", array_keys($v)) . "`";
            $val = "'" . implode("','", array_values($v)) . "'";
            $sql = "INSERT INTO {$this->countUidTable}({$fields}) VALUES({$val}) ON DUPLICATE KEY UPDATE `c_date`='{$v['c_date']}'";
            OpenPlayedGamesUidCountModel::db()->execute($sql);
        }
        return __FUNCTION__;
    }

    /**
     * 统计注册，新增，活跃，时长等
     * @param $base
     * @return bool
     * @throws Exception
     */
    private function countPlayedGamesData ($base)
    {
        $bTime = $this->dealBaseTime($base);
        $day = 24 * 60 * 60;
        $insertTime = $bTime['t24'] - 1;//插库时间为基准时间的23:59:59

        $select = 'COUNT(DISTINCT `uid`)';

        //截止基准时间,累计注册用户数
        $res = $this->selectData($select, 1, $bTime['t24']);
        if ($res) {
            $this->insertData($insertTime, 'all_register', $res);

            //记录下新增的注册用户数
            $befTime = $insertTime - $day;//前一日的时间
            $sql = "SELECT `all_register`,`game_id` FROM {$this->countTable} WHERE `a_time`={$befTime} GROUP BY `game_id`";

            $befRes = $this->connPlayedGamesCounts->query($sql);
            $befRes1 = [];
            foreach ($befRes as $k => $v) {
                $befRes1[$v['game_id']] = $v['all_register'];
            }
            $newRes = [];
            foreach ($res as $k => $v) {
                if (isset($befRes1[$v['game_id']])) {
                    $c = $v['c'] - $befRes1[$v['game_id']];
                } else {
                    $c = $v['c'];
                }
                $newRes[] = [
                    'game_id' => $v['game_id'],
                    'c' => $c,
                ];
            }
            if ($newRes) {
                $this->insertData($insertTime, 'new_register', $newRes);
            }
        }

        //基准时间的活跃用户数
        $res = $this->selectData($select, $bTime['t0'], $bTime['t24']);
        if ($res) {
            $this->insertData($insertTime, 'active', $res);
        }

        //基准时间的访问次数
        $select = 'COUNT(`uid`)';
        $res = $this->selectData($select, $bTime['t0'], $bTime['t24']);
        if ($res) {
            $this->insertData($insertTime, 'visit', $res);
        }

        //昨日人均停留时长 单位s
        //select (sum(e_time)-sum(a_time)) as a,game_id from played_games where a_time is not null and e_time is not null group by game_id;
        $select = '(SUM(e_time)-SUM(a_time))';
        $other = ' AND `a_time` IS NOT NULL AND `e_time` IS NOT NULL ';
        $res = $this->selectData($select, $bTime['t0'], $bTime['t24'], $other);
        if ($res) {
            $this->insertData($insertTime, 'stay_time', $res);
        }
        $return[] = __FUNCTION__;
        //统计新注册的uid
        $return[] = $this->countGameNewRegisterUidData($base);

        //统计 次登 3日登 7日登 数量
        $bTimeLogin = [
            'login1' => $base - $day,//1日前
            'login3' => $base - 3 * $day,//3日前
            'login7' => $base - 7 * $day,//7日前
        ];
        foreach ($bTimeLogin as $type => $time) {
            $return[] = $this->countPlayedGamesKeepData($base, $time, $type);
        }

        $return[] = $this->countGameUserInfoData($base);

        return date('Y-m-d H:i:s') . "#" . implode("#", $return) . "# count_time:" . date('Y-m-d', $base);
    }

    /**
     * 查询
     * @param string $select
     * @param int $startTime
     * @param int $endTime
     * @param string $other
     * @return array|bool|mysqli_result
     * @throws Exception
     */
    private function selectData ($select = '*', $startTime = 1, $endTime = 0, $other = '')
    {
        if ($endTime && $select) {
            if ($select != '*') {
                $select = $select . ' as c,`game_id` ';
            }
            $other .= ' GROUP BY `game_id` ';
            $sql = " SELECT {$select} FROM `{$this->table}` WHERE `a_time`<{$endTime} AND `a_time`>={$startTime} {$other}";
            return $this->connPlayedGames->query($sql);
        }
        return [];
    }

    /**
     * 插入
     * @param $aTime
     * @param $type
     * @param array $iData
     * @throws Exception
     */
    private function insertData ($aTime, $type, $iData = [])
    {
        $sqls = [];
        if ($iData && $this->insertSql) {
            if (count($iData) == count($iData, 1)) {//一维数组
                $sqls[] = $this->dealSql($aTime, $type, $iData);
            } else {//多维数组
                foreach ($iData as $k => $value) {
                    $sqls[] = $this->dealSql($aTime, $type, $value);
                }
            }
            foreach ($sqls as $k => $sql) {
                $this->connPlayedGamesCounts->execute($sql);
            }
        }
    }

    /**
     * 处理sql语句
     * @param $aTime
     * @param $type
     * @param $dataInfo
     * @return mixed
     */
    private function dealSql ($aTime, $type, $dataInfo)
    {
        $dateTime = date('Y-m-d H:i:s', $aTime);
        $gameId = intval($dataInfo['game_id']);
        $data = $dataInfo['c'];
        $info = [
            'game_id' => $gameId,
            $type => $data,
            'a_time' => $aTime,
            'date_time' => $dateTime,
            'c_date' => date('Y-m-d H:i:s'),
        ];

        $insert = array_intersect_key($info, self::$INSERT_FIELDS);
        $update = array_intersect_key($info, self::$UPDATE_FIELDS);

        $format[] = "`" . implode("`,`", array_keys($insert)) . "`";
        $format[] = "'" . implode("','", $insert) . "'";

        foreach ($update as $k => $v) {
            $up[] = "`{$k}`='{$v}'";
        }
        $format[] = implode(',', $up);
        return str_replace(array_values(self::$FORMAT), $format, $this->insertSql);
    }
}
