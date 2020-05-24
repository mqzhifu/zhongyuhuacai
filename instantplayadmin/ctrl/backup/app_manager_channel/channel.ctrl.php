<?php

/**
 * @Author: Kir
 * @Date:   2019-04-28 17:06:53
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-30 09:43:43
 */


/**
 * 
 */
class ChannelCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->assign("gamesCount", $this->getGamesCount());
		$this->assign("games", $this->getGames());
		$this->assign("osDesc", ChannelsModel::getOsDesc());
		$this->display("app_manager_channel/channel.html");
	}


	function getGames()
	{
		$where = " is_online = " . GamesModel::$_online_true . " and status != 4";

		$games = GamesModel::db()->getAllBySql("select id,name from games where $where order by sort desc");

		return $games;
	}

	function getGamesCount()
	{
		$where = " is_online = " . GamesModel::$_online_true . " and status != 4";

		$count = GamesModel::db()->getCount($where);

		return $count;
	}

	function getOne()
	{
		$id = _g("id");
		$channel = ChannelsModel::db()->getRowById($id);
		if (!$channel) {
			$this->outputJson(0,"参数错误");
		}
		if ($channel['games'] && $channel['games'] != 'all') {
			$channel['games'] = explode(',', $channel['games']);
		}
		$this->outputJson(200,"",$channel);
	}

	function add()
	{
		$name = _g("channel_name");
		$f_key = _g("f_key");
		$os = _g("os");
		$gameIds = _g("gameIds");

		if ($gameIds!='all' && is_array($gameIds)) {
			$gameIds = implode(',', $gameIds);
		}

		if (ChannelsModel::db()->getRow("f_key=$f_key")) {
			$this->outputJson(0,"F值重复，请确认");
		}

		$data = [
			'f_key'=>$f_key,
			'name'=>$name,
			'os'=>$os,
			'games'=>$gameIds,
			'a_time'=>time(),
			'u_time'=>time(),
		];

		ChannelsModel::db()->add($data);

		$this->outputJson(200,"add success");
	}

	function update()
	{
		$id = _g("id");
		$name = _g("channel_name");
		$f_key = _g("f_key");
		$os = _g("os");
		$gameIds = _g("gameIds");

		if ($gameIds!='all' && is_array($gameIds)) {
			$gameIds = implode(',', $gameIds);
		}

		if (ChannelsModel::db()->getRow("f_key=$f_key and id!=$id")) {
			$this->outputJson(0,"F值重复，请确认");
		}

		if (!ChannelsModel::db()->getRowById($id)) {
			$this->outputJson(0,"参数错误");
		}

		$data = [
			'f_key'=>$f_key,
			'name'=>$name,
			'os'=>$os,
			'games'=>$gameIds,
			'u_time'=>time(),
		];

		ChannelsModel::db()->upById($id, $data);

		$this->outputJson(200,"update success");
	}

	function delete()
	{
		$id = _g("id");
		if (!ChannelsModel::db()->getRowById($id)) {
			$this->outputJson(0,"参数错误");
		}

		ChannelsModel::db()->delById($id);

		$this->outputJson(200,"delete success");
	}

	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();

        $cnt = ChannelsModel::db()->getCount($where);

        $iTotalRecords = $cnt;//DB中总记录数

        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                '',
                '',
                '',
                'u_time',
                '',
            );

            $order = " order by " .$sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录

            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始

            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

            $order.= " limit $iDisplayStart, $iDisplayLength ";

            $channels = ChannelsModel::db()->getAll($where . $order);

            foreach ($channels as $ch) {
    			$row = array(
                    '<input type="checkbox" name="id[]" value="'.$ch['id'].'">',
                    $ch['id'],
                    $ch['f_key'],
                    $ch['name'],
                    ChannelsModel::getOsDesc()[$ch['os']],
                    get_default_date($ch['u_time']),
                    '<button value='.$ch['id'].' class="btn btn-sm default blue" onclick="updateChannel(this)">配置</button>',
                );
                $records["data"][] = $row;
        	}

        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}


	function getWhere() 
	{
        $where = " 1 ";
        $f_key = _g("f_key");
        $name = _g("name");
        $os = _g("os");
        $from = _g("from");
        $to = _g("to");

        if (!is_null($f_key) && $f_key!='')
            $where .= " and f_key like '%$f_key%'";

        if (!is_null($os) && $os!='')
            $where .= " and os=$os";

        if (!is_null($name) && $name!='')
            $where .= " and name like '%$name%'";

        if (!is_null($from) && $from!='') {
            $where .= " and u_time >= '".strtotime($from)."'";
        }

        if (!is_null($to) && $to!='') {
            $where .= " and u_time <= '".strtotime("+1 day $to")."'";
        }

        return $where;
    }
}