<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/4/29
 * Time: 11:21
 */

/**
 * Class gameDetailCtrl
 */
class gameCtrl extends BaseCtrl{
    public function index(){
        if (_g("getlist")) {
            $this->getList();
        }
        $this->display("/app_share/game/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        //$sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new) as cnt FROM share_game_cnt WHERE {$where} GROUP BY game_id ORDER BY date_new DESC ;";
        $sql = "SELECT COUNT(id) as cnt FROM share_game_cnt WHERE {$where} GROUP BY game_id ;";
        $result = shareGameCntModel::getAll($sql);
        $iTotalRecords = count($result);

        if (0 != $iTotalRecords){
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $select_sql = " SELECT *,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new FROM share_game_cnt WHERE {$where} GROUP BY a_time ORDER BY date_new DESC LIMIT $iDisplayStart, $iDisplayLength ;";
            $data = shareGameCntModel::getAll($select_sql);
            foreach($data as $k=>$v){
                // step1:获取游戏名称;
                $gameInfo = gamesModel::db()->getById($v['game_id']);
                // 时间处理;
                $aTime = date('Y-m-d', $v['a_time']);
                $beginTime = strtotime($aTime.' 00:00:00');
                $endTime = strtotime($aTime.' 23:59:59');
                $gameId = $v['game_id'];
                $type = shareGameCntModel::$_type_game_share_add_friends;
                $selectSql = "SELECT COUNT(DISTINCT(uid)) AS uid_cnt FROM share WHERE game_id = {$gameId} AND a_time >= {$beginTime} AND a_time <= {$endTime} ;";
                $people = ShareModel::db()->query($selectSql);
                $selectQuery = "SELECT sum(click) AS click_count, sum(login) AS login_count, sum(download) AS download_count FROM share_game_cnt WHERE game_id = {$v['game_id']} AND a_time >= {$beginTime} AND a_time <= {$endTime} ;";
                $counts = ShareModel::db()->query($selectQuery);
                $num = ShareModel::db()->getCount(" game_id = $gameId AND a_time >= {$beginTime} AND a_time <= {$endTime} ");
                $records["data"][] = array(
                    $v['date_new'],
                    $v['game_id'],
                    $v['game_name'] = $gameInfo['name'],
                    $v['people'] = (NULL == $people[0]['uid_cnt'])?0:$people[0]['uid_cnt'],
                    $v['num'] = $num,
                    $v['click_count'] = $counts[0]['click_count'],
                    $v['login_count'] = $counts[0]['login_count'],
                    $v['download_count'] = $counts[0]['download_count'],
                    '---'
                );
            }
        }
        $records['data'] = $this->array_unique_fb($records['data']);
        $records['data'] = array_values($records['data']);
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
        die();
    }

    function array_unique_fb($array2D) {

        foreach ($array2D as $v) {

            $v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串

            $temp[] = $v;

        }

        $temp = array_unique($temp);//去掉重复的字符串,也就是重复的一维数组

        foreach ($temp as $k => $v) {

            $temp[$k] = explode(",", $v);//再将拆开的数组重新组装

        }

        return $temp;

    }

    /**
     * @return string
     */
    function getWhere(){
        $where = " 1 = 1 ";
        if($from = _g("from")){
            $from = strtotime($from.' 00:00:00');
            $where .= " and a_time >= '".$from."'";
        }
        if($to = _g("to")){
            $to = strtotime($to.' 23:59:59');
            $where .= " and a_time <= '".$to."'";
        }
        return $where;
    }

}