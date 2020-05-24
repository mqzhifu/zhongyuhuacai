<?php
/**
 * Class gameOsCtrl
 */
class gameOsCtrl extends BaseCtrl{
    public function index(){
        if (_g("getlist")) {
            $this->getList();
        }
        $this->display("/app_share/gameOs/index.html");
    }

    public function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);
        $where = $this->getWhere();

        //$sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new) as cnt FROM share_game_cnt WHERE {$where} GROUP BY game_id ORDER BY date_new DESC ;";
        $sql = "SELECT COUNT(id) as cnt FROM share WHERE {$where} GROUP BY game_id ;";
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
            $select_sql = " SELECT *,DATE_FORMAT(FROM_UNIXTIME(a_time),'%Y-%m-%d') AS date_new, 
            COUNT(CASE WHEN platform = 6 THEN platform END ) AS platform_fb,
            COUNT(CASE WHEN platform = 15 THEN platform END ) AS platform_messager,
            COUNT(CASE WHEN platform = 16 THEN platform END ) AS platform_app
            FROM share WHERE {$where} GROUP BY a_time,game_id ORDER BY date_new DESC LIMIT $iDisplayStart, $iDisplayLength ;";
            $data = shareModel::db()->getAllBySQL($select_sql);
            foreach($data as $k=>$v){
                // step1:获取游戏名称;
                $gameInfo = gamesModel::db()->getById($v['game_id']);
                $records["data"][] = array(
                    $v['date_new'],
                    $v['game_id'],
                    $v['game_name'] = $gameInfo['name'],
                    $v['platform_fb'],
                    $v['platform_messager'],
                    $v['platform_app'],
                    '---'
                );
            }
        }
        /*$records['data'] = $this->array_unique_fb($records['data']);
        $records['data'] = array_values($records['data']);*/
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
        $where = " game_id != 0 ";
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