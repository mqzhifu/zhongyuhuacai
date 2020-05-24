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
class gameDetailCtrl extends BaseCtrl{
    public function index(){
        if (_g("getlist")) {
            $this->getList();
        }
        $shareType = shareGameCntModel::getTypeTitle();
        $this->assign('shareType', $shareType);
        $this->display("/app_share/gameDetail/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        $sql = "SELECT COUNT(id) as cnt FROM share_game_cnt WHERE {$where}";
        $result = shareGameCntModel::getAll($sql);
        $iTotalRecords = $result[0]['cnt'];

        if (!empty($result)){
            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if('999999' == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $select_sql = "SELECT * FROM  share_game_cnt WHERE {$where} ORDER BY a_time DESC LIMIT $iDisplayStart, $iDisplayLength ";
            $data = shareGameCntModel::getAll($select_sql);
            foreach($data as $k=>$v){
                // step1:获取游戏名称;
                $gameInfo = gamesModel::db()->getById($v['game_id']);
                // step2:获取当天分享人数;
                // step3:获取当天分享次数;
                $peopleResult = shareGameCntModel::SHARE_ALL;
                $peopleInfo = $peopleResult[$v['share_path']];
                $platform = $peopleInfo['platform'];
                $platform_method = $peopleInfo['platform_method'];
                // 时间处理;
                $aTime = date('Y-m-d', $v['a_time']);
                $beginTime = strtotime($aTime.' 00:00:00');
                $endTime = strtotime($aTime.' 23:59:59');
                $gameId = $v['game_id'];
                $selectSql = "SELECT COUNT(DISTINCT(uid)) AS uid_cnt FROM share WHERE platform = {$platform} AND platform_method = {$platform_method} AND game_id = {$gameId} AND type = 80 AND a_time >= {$beginTime} AND a_time <= {$endTime} ;";
                $people = ShareModel::db()->query($selectSql);
                $num = ShareModel::db()->getCount(" platform = $platform AND platform_method = $platform_method AND game_id = $gameId AND type = 80 AND a_time >= {$beginTime} AND a_time <= {$endTime} ");
                $records["data"][] = array(
                    $v['id'],
                    $v['a_time'] = $aTime,
                    $v['share_path'] = shareGameCntModel::getTypeTitleByKey($v['share_path']),
                    $v['game_id'],
                    $v['game_name'] = $gameInfo['name'],
                    '<span style="font-weight: bold">'.$v['people'] = (NULL == $people[0]['uid_cnt'])?0:$people[0]['uid_cnt'].'</span>',
                    '<span style="font-weight: bold">'.$v['num'] = $num.'</span>',
                    '<span style="font-weight: bold">'.$v['click'].'</span>',
                    '<span style="font-weight: bold">'.$v['login'].'</span>',
                    '<span style="font-weight: bold">'.$v['download'].'</span>',
                    '---'
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
    function getWhere(){
        $where = " 1 = 1 ";
        if($game_id = trim(_g("game_id"))){
            $where .= " AND game_id = '".$game_id."'";
        }
        if($share_path = (int)_g("share_path")){
            $where .= " AND share_path = '".$share_path."'";
        }
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