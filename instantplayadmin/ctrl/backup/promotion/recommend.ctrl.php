<?php

/**
 * @Author: Kir
 * @Date:   2019-04-15 17:39:23
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-28 14:43:11
 */

/**
 * 
 */
class RecommendCtrl extends BaseCtrl
{
	
	function index()
	{
        $this->assign("gamesCount", $this->getAllGamesCount());
        $this->assign("games", $this->getAllGames());
        $this->assign("types", GameRecommendModel::getTypeDesc());
		$this->display("promotion/recommend.html");
	}

    function getAllGames()
    {
        $where = " is_online = " . GamesModel::$_online_true . " and status != 4";

        $games = GamesModel::db()->getAllBySql("select id,name from games where $where order by sort desc");

        return $games;
    }
    function getAllGamesCount()
    {
        $where = " is_online = " . GamesModel::$_online_true . " and status != 4";

        $count = GamesModel::db()->getCount($where);

        return $count;
    }

    function getRecommendGames()
    {
        $type = _g("type");
        if (!is_numeric($type)) {
            $this->outputJson(0,"参数有误");
        }

        $games = GameRecommendModel::db()->getAll("type = $type");
        $this->outputJson(200, 'succ', $games);
    }


	function update()
	{
		$type = _g('type');
        $recs = _g('recs');
        if (!array_key_exists($type, GameRecommendModel::getTypeDesc())) {
            $this->outputJson(0,"类型有误");
        };
        $hasGames = GameRecommendModel::db()->getAllBySQL("select game_id, sort from game_recommend where type = $type");

        $recs = is_array($recs) ? $recs : [];
        $insert = $this->getDiffArray($recs, $hasGames);
        $delete = $this->getDiffArray($hasGames, $recs);

        $now = time();
        foreach ($insert as $data) {
            $data['type'] = $type;
            $data['a_time'] = $now;
            $data['u_time'] = $now;
            GameRecommendModel::db()->add($data);
        }

        if (!empty($delete)) {
            $delIds = array_column($delete, 'game_id');
            $cnt = count($delIds);
            $delIds = implode(',', $delIds);
            GameRecommendModel::db()->delete("game_id in ($delIds) limit $cnt");
        }

		$this->outputJson(200,"success");
	}

    function edit()
    {
        $id = _g('id');
        $sort = _g('sort');
        if (!GameRecommendModel::db()->getRowById($id)) {
            $this->outputJson(0,"游戏不存在");
        }
        $data = [
            'sort'=>$sort,
            'u_time'=>time(),
        ];
        GameRecommendModel::db()->upById($id,$data);
        $this->outputJson(200,"success");
    }

	function delete()
	{
		$id = _g('id');
		if (!GameRecommendModel::db()->getRowById($id)) {
			$this->outputJson(0,"游戏不存在");
		}
		GameRecommendModel::db()->delById($id);
		$this->outputJson(200,"success");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $recommends = GameRecommendModel::db()->getAll();
        $recTypes = GameRecommendModel::getTypeDesc();

        foreach ($recTypes as $key => $value) {
            $data = [
                $value,
                $this->getEarliestAtime($recommends, $key),
                $this->getLatestUtime($recommends, $key),
                '<button class="btn btn-sm default blue edit_btn" onclick="edit(this)" attr-id="'.$key.'" attr-val="'.$value.'">编辑</button>',
            ];
            $records["data"][] = $data;
        }


        $records["draw"] = $sEcho;
        $records["recordsTotal"] = count($records["data"]);
        $records["recordsFiltered"] = count($records["data"]);

        echo json_encode($records);
        exit;
	}

    function getLatestUtime($recommends,$type) 
    {
        $time = 0;
        foreach ($recommends as $key => $value) {
            if ($value['type'] == $type) {
                if ($value['u_time'] > $time) {
                    $time = $value['u_time'];
                }
            }
        }
        return get_default_date($time);
    }

    function getEarliestAtime($recommends,$type)
    {
        $time = 0;
        foreach ($recommends as $key => $value) {
            if ($value['type'] == $type) {
                if (!$time || $value['a_time'] < $time) {
                    $time = $value['a_time'];
                }
            }
        }
        return get_default_date($time);
    }

    function getDiffArray($arr1, $arr2) {
	    $diff = [];
	    foreach ($arr1 as $v) {
	        if (!in_array($v, $arr2)) {
                $diff[] = $v;
            }
        }
	    return $diff;
	}


}