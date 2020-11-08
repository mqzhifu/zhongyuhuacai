<?php
/**
 * Desc: 定时脚本,统计数据
 * User: zhangbingbing
 * Date: 2019/3/4 18:31
 */

Class CrontabOtherPlayedGamesService
{
    /**
     * 数据库连接
     * @var mysqli|null
     */
    private $connPlayedGames = null;
    private $connPlayedGamesOtherCount = null;

    private $table = 'played_games';
    private $otherCountTable = 'open_played_games_other_count';//该表主要是统计实时在线人数

    private $insertSql = '';

    /**
     * insert的字段
     * @var array
     */
    private static $INSERT_FIELDS = [
        'game_id' => '',
        'data' => '',
        'type' => '',
        'a_time' => '',
        'date_time' => '',
    ];
    /**
     * update时的字段
     * @var array
     */
    private static $UPDATE_FIELDS = [
        'data' => '',
    ];

    private static $FORMAT = [
        'insert' => '**insert**',
        'update' => '**update**',
    ];

    /**
     * 构造函数连接数据库
     * CrontabPlayedGamesService constructor.
     */
    public function __construct ()
    {
        $this->connPlayedGames = OpenPlayedGamesModel::db();
        $this->connPlayedGamesOtherCount = OpenPlayedGamesOtherCountModel::db();
        $insertFields = self::$INSERT_FIELDS;
        ksort($insertFields);
        if ($insertFields) {
            $fields = '`' . implode('`,`', array_keys($insertFields)) . '`';
            $format = self::$FORMAT['insert'];
            $this->insertSql = "INSERT INTO `{$this->otherCountTable}`({$fields}) VALUES({$format}) ";

            $updateFields = self::$UPDATE_FIELDS;
            if ($updateFields) {
                $format = self::$FORMAT['update'];
                $this->insertSql .= " ON DUPLICATE KEY UPDATE {$format}";
            }
        }

    }

    /**
     * 实时数据统计 间隔粒度5分钟
     * @param int $granularity
     * @return mixed
     * @throws Exception
     */
    public function countRealTimeData ($granularity = 5)
    {
        $now = time();
        if ((intval(date('i', $now)) % $granularity) == 0) {
            $now = strtotime(date('Y-m-d H:i:00', $now));
            $startTime = $now - $granularity * 60;//5分钟前
            $endTime = $now;
            $res = $this->selectData(' COUNT(*) ', $startTime, $endTime + 1);
            $msg = 'countRealTimeData - ' . date('Y-m-d H:i:s', $endTime);
            if ($res) {
                $this->insertData($now, OpenPlayedGamesOtherCountModel::$_type_0, $res);
                return $msg . '-success';
            }
            return $msg . '-null';
        }
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
                $this->connPlayedGamesOtherCount->execute($sql);
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
        $data = intval($dataInfo['c']);
        $info = [
            'game_id' => $gameId,
            'data' => $data,
            'type' => $type,
            'a_time' => $aTime,
            'date_time' => $dateTime,
        ];
        $insert = array_intersect_key($info, self::$INSERT_FIELDS);
        $update = array_intersect_key($info, self::$UPDATE_FIELDS);
        ksort($insert);
        ksort($update);
        $format[] = "'" . implode("','", $insert) . "'";
        foreach ($update as $k => $v) {
            $up[] = "`{$k}`='{$v}'";
        }
        $format[] = implode(',', $up);
        return str_replace(array_values(self::$FORMAT), $format, $this->insertSql);
    }
}
