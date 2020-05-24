<?php

/**
 * @Author: xuren
 * @Date:   2019-03-25 11:05:41
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-03-26 17:26:28
 */
class GameCtrl extends BaseCtrl{

	function index(){
		$this->addCss("/assets/open/css/game-detail.css?1");
		$this->assign("statusDesc", PopularizationModel::getStatusDesc());
		if(_g('getList')){
			$this->getList();
		}
		$this->display("promotion/popularize.html");
		
	}

	function getList(){
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        $sql = "select count(*) as cnt from popularize";
        

        $cntSql = PopularizationModel::db()->getRowBySQL($sql);

        $cnt = 0;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'p_id',
                '',
                '',
                '',
                '',
                '',
                '',
            );
            $order = $sort[$order_column]." ".$order_dir;

            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
            if(999999 == $iDisplayLength){
                $iDisplayLength = $iTotalRecords;
            }else{
                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
            }

            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;


            // $sql = "SELECT COUNT(b.`uid`) AS login_cnt,b.`a_time` AS last_login_time,
            //         a.id,a.name,a.nickname,a.birthday,a.country,a.province,a.city,a.avatar,a.a_time,a.sex,a.is_online,a.cellphone,a.point,a.goldcoin,a.diamond,a.`type` 
            //         FROM user AS a  LEFT JOIN login AS b  ON a.id = b.uid where $where GROUP BY b.uid order by $order limit $iDisplayStart,$end ";
            $sql = "select * from popularize where $where GROUP BY id order by $order limit $iDisplayStart,$end ";

            // echo $sql;
            // exit;
//            echo $sql;exit;
            $data = PopularizationModel::db()->getAllBySQL($sql);
            foreach($data as $k=>$v){
                $records["data"][] = array(
                    $v['p_id'],
                    //$v['weight'],
                    $v['game_name'],
                    $v['game_id'],
                    get_default_date($v['start_launch_time']),
                    get_default_date($v['end_launch_time']),
                    PopularizationModel::getDescByStatus($this->checkLaunchStatus($v['start_launch_time'], $v['end_launch_time'])),
                   '<a href="#" class="btn btn-xs default red delone" onclick="one_del(this)" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.'<a href="#"  class="btn btn-xs default red edit" onclick="edit(this)" data-id="'.$v['id'].'"><i class="fa fa-edit"></i> 修改</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}

	function delone(){
		$id = _g('id');
		if(!$id || $id=='undefinded'){
			$this->outputJson(1, '参数不正确');
		}

		$res = PopularizationModel::db()->delById($id);
		if(!$res){
			$this->outputJson(2, 'db error');
		}

		$this->outputJson(200, 'succ');
	}

	function updateOne(){
		$id = _g('id');
		$game_id = _g('game_id');
		$start_launch_time = _g('start_launch_time');
		$end_launch_time = _g('end_launch_time');
		$popularization_column = _g('popularization_column');
		$weight = _g('weight');
		$played_num = _g('played_num');

		$row = GamesModel::db()->getRow('id="'.$game_id.'"');
		if(!$row){
			$this->outputJson(1,'game_id非法');
		}
		$update = [];
		$update['game_id'] = $row['id'];
		$update['p_id'] = $popularization_column;
		$update['weight'] = $weight;
		$update['game_name'] = $row['name'];
		$update['start_launch_time'] = strtotime($start_launch_time);
		$update['end_launch_time'] = strtotime($end_launch_time);
		$update['played_num'] = $played_num;
		$update['status'] = $this->checkLaunchStatus($update['start_launch_time'], $update['end_launch_time']);
		$res = PopularizationModel::db()->update($update, "id=$id limit 1");
		if(!$res){
			$this->outputJson(2, 'update error');
		}

		$this->outputJson(200, 'succ');
	}

	function getOnePopularization(){
		$id = _g('id');
		if($this->isIllegal($id)){
			$this->outputJson(1, '缺少参数');
		}

		$sql = "select * from popularize_column";
		$items = PopularizationModel::db()->getAllBySQL($sql);

		$res = PopularizationModel::db()->getRowById($id);
		$res['start_launch_time'] = date('Y-m-d H:i:s',$res['start_launch_time']);
		$res['end_launch_time'] = date('Y-m-d H:i:s',$res['end_launch_time']);
		$this->outputJson(200, 'succ', ['data1'=>$res,'data2'=>$items]);
	}

	function getPopularizationColumn(){
		$sql = "select * from popularize_column";
		$items = PopularizationModel::db()->getAllBySQL($sql);
		if(!$items){
			$this->outputJson(200,'');
		}

		$this->outputJson(200, 'succ', $items);
	}

	function getPopularizationList(){
		$all = PopularizationModel::db()->getAll();
		$this->outputJson(200, 'succ', $all);
	}

	function addPopularizationCol(){
		$add = [];
		$res = PopularizationModel::db()->add($add, 'popularize_column');
		$this->outputJson(200, 'succ', $res);
	}

	function addOne(){
		$game_id = _g('game_id');
		$start_launch_time = _g('start_launch_time');
		$end_launch_time = _g('end_launch_time');
		$popularization_column = _g('popularization_column');
		/*$weight = _g('weight');
		$played_num = _g('played_num');*/
        $weight = 0;
        $played_num = 0;
		if($this->isIllegal($game_id) || $this->isIllegal($start_launch_time) || $this->isIllegal($end_launch_time) || $this->isIllegal($popularization_column)){
			$this->outputJson(1, '非法参数');
		}

		$row = GamesModel::db()->getRow('id="'.$game_id.'"');
		if(!$row){
			$this->outputJson(3,'game_id非法');
		}
		// 增加推广位ID判断逻辑 by XiaHB Begin;
        $rs = PopularizationModel::db()->getAll('status = 1');
        $rs_new = array_column($rs, 'p_id');
        if(in_array($popularization_column, $rs_new)){
            $this->outputJson(3,'当前推广位已被占用，请勿重复选择！');
        }
		// 增加推广位ID判断逻辑 by XiaHB   End;
		$add = [];
		$add['game_id'] = $row['id'];
		$add['p_id'] = $popularization_column;
		$add['weight'] = $weight;
		$add['game_name'] = $row['name'];
		$add['start_launch_time'] = strtotime($start_launch_time);
		$add['end_launch_time'] = strtotime($end_launch_time);
		$add['played_num'] = $played_num;
		$add['status'] = $this->checkLaunchStatus($add['start_launch_time'], $add['end_launch_time']);
		$res = PopularizationModel::db()->add($add);
		if(!$res){
			$this->outputJson(2, 'add error');
		}

		$this->outputJson(200, 'succ');


	}

	private function checkLaunchStatus($startTime, $endTime){
		$now  = time();
		if($now < $startTime){
			return PopularizationModel::$status_launching;
		}else if($now >= $startTime && $now <= $endTime){
			return PopularizationModel::$status_launching;
		}else{
			return PopularizationModel::$status_unlaunched;
		}
		return PopularizationModel::$status_unlaunched;
	}

	private function isIllegal($a){
		return (!$a || $a == 'undefinded');
	}

	private function getWhere(){
		$where = " 1 ";
        if($game_name = _g("game_name"))
            $where .= " and game_name like '%$game_name%'";
		// if($a_time_from = _g("a_time_from")){
  //           $a_time_from .= ":00";
  //           $where .= " and a_time >= '".strtotime($a_time_from)."'";
  //       }

  //       if($a_time_to = _g("a_time_to")){
  //           $a_time_to .= ":59";
  //           $where .= " and a_time <= '".strtotime($a_time_to)."'";
  //       }

        return $where;
	}

	public function outputJson ($code, $message, $data=[])
    {
        header("Content-Type: application/json");
        echo json_encode([
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ]);
        exit(0);
    }

}