<?php

/**
 * @Author: xuren
 * @Date:   2019-03-19 10:26:40
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-02 11:40:55
 */
class DataTotalCtrl extends BaseCtrl{

	function index(){

		
		$this->addCss("/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css");
		$this->addCss("/assets/admin/pages/css/profile.css");
		$this->addCss("/assets/admin/pages/css/tasks.css");
		
		$this->addCss("/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css");
		$this->addCss("/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css");
		$this->addCss("/assets/global/plugins/select2/select2.css");
		$this->addCss("/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css");
		$this->addCss("/assets/global/css/plugins.css");

		

		$this->display("platform/datatotal.html");
	}
	/**
	 * 获取汇总数据
	 * @return [type] [description]
	 */
	function getTotalData(){
		$osType = _g('osType');

		$returnData = [];

		$developerNum = InformationModel::db()->getCount();
		$company = InformationModel::db()->getCount("type=".InformationModel::TYPE_COMPANY);
		$person = InformationModel::db()->getCount("type=".InformationModel::TYPE_PERSON);
		$returnData['developerNum'] = $developerNum;
		$returnData['company'] = $company;
		$returnData['person'] = $person;

		$gameNum = GamesModel::db()->getCount();
		$returnData['gameNum'] = $gameNum;
		$returnData['inpurchasing_gameNum'] = 0;
		$returnData['ad_gameNum'] = 0;

		// $sql = "select count(id) from access_log  where date_sub(curdate(), interval 7 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s'))";
		$open_week_active = AccesslogModel::db()->getCount(" date_sub(curdate(), interval 7 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s'))");
		$open_month_active = AccesslogModel::db()->getCount(" date_sub(curdate(), interval 30 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s'))");
		$returnData['open_week_active'] = $open_week_active;
		$returnData['open_month_active'] = $open_month_active;

		$returnData['game_week_add'] = 0;
		$returnData['game_month_add'] = 0;

		$game_week_active = PlayedGamesModel::db()->getCount(" date_sub(curdate(), interval 7 day) <= date(from_unixtime(a_time, '%Y-%m-%d %H:%i:%s'))");
		$game_month_active = PlayedGamesModel::db()->getCount(" date_sub(curdate(), interval 30 day) <= date(from_unixtime(a_time, '%Y-%m-%d %H:%i:%s'))");


		$returnData['game_week_active'] = $game_week_active;
		$returnData['game_month_active'] = $game_month_active;

		$sql = "select sum(cost) as totalcost from open_advertise_income where date_sub(curdate(), interval 7 day) <= stat_datetime";
		$item = AdvertiseModel::db()->getRowById($sql);
		$sql2 = "select sum(cost) as totalcost2 from open_advertise_income where date_sub(curdate(), interval 30 day) <= stat_datetime";
		$item2 = AdvertiseModel::db()->getRowById($sql2);

		$returnData['ad_week_flow'] = $item['totalcost'];
		$returnData['ad_month_flow'] = 0;

		$this->outputJson(200, 'succ', $returnData);

	}
	/**
	 * 获取月活跃
	 * @return [type] [description]
	 */
	function getMonthActive(){

		$sql = "select count(*) as cnt,from_unixtime(a_time,'%m') as tt from access_log group by from_unixtime(a_time,'%Y-%m')  order by tt  desc limit 7 ";

        $data = UserModel::db()->getAllBySQL($sql);
        $newData = [];
        foreach ($data as $value) {
        	$newData[] = [$value['tt'], $value['cnt']];
        }
        $obj = new Result();
        $obj->data = $newData;
        $obj->label = "xxxx";

        $resultData = [];
        $resultData[] = $obj;

        $this->outputJson(200, "succ", $resultData);
	}

	/**
	 * 获取游戏活跃折线数据
	 * @return [type] [description]
	 */
	function getGameActiveLine(){

		$type = _g("type");
		switch($type){
			case 1://按日
			$sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id where to_days(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s')) = to_days(now()) group by g.id order by count(g.id) desc limit 10";
			break;
			case 2://按周
			$sql = "select count(g.id) as active,g.id,YEARWEEK(now()) from games g left join played_games pg on g.id=pg.game_id where YEARWEEK(date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y-%m-%d')) = YEARWEEK(now()) group by g.id order by count(g.id) desc limit 10";
			break;
			case 3://按月
			$sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id where date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y%m') = date_format(curdate(),'%Y%m') group by g.id order by count(g.id) desc limit 10";
			default:
			$sql = "select count(g.id) as active,g.id from games g left join played_games pg on g.id=pg.game_id group by g.id order by count(g.id) desc limit 10";

		}
		$data = UserModel::db()->getAllBySQL($sql);
		$resultData = [];
		foreach ($data as $value) {
			switch ($type) {
				case '1'://按日
					$sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%d') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m-%d')  order by tt  desc limit 7";
					break;
				case '2'://按周
					$sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%u') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%u')  order by tt  desc limit 7";
					break;
				case '3'://按月
					$sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%m') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m')  order by tt  desc limit 7";
					break;
				default://按日
					$sql2 = "select count(game_id) active,game_id,from_unixtime(a_time,'%d') as tt from played_games where game_id=".$value['id']." group by from_unixtime(a_time,'%Y-%m-%d')  order by tt  desc limit 7";
					break;
			}
			$res = UserModel::db()->getAllBySQL($sql2);
			if($res){
				$newData = [];
		        foreach ($res as $value) {
		        	$newData[] = [$value['tt'], $value['active']];
		        }
		        $obj = new Result();
		        $obj->data = $newData;
		        $obj->label = $res[0]['game_id'];

		        $resultData[] = $obj;
			}
		}

		$this->outputJson(200, "succ", $resultData);

	}

	/**
	 * 获取游戏活跃排行数据
	 * @return [type] [description]
	 */
	function getGameActiveRank(){
		$records = array();
        $records["data"] = array();
		$type = _g("type");

		switch($type){
			case 1://按日
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id where to_days(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s')) = to_days(now()) group by g.name order by count(g.name) desc limit 10";
			break;
			case 2://按周
			$sql = "select count(g.name) as active,g.name,YEARWEEK(now()) from games g left join played_games pg on g.id=pg.game_id where YEARWEEK(date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y-%m-%d')) = YEARWEEK(now()) group by g.name order by count(g.name) desc limit 10";
			break;
			case 3://按月
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id where date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y%m') = date_format(curdate(),'%Y%m') group by g.name order by count(g.name) desc limit 10";
			break;
			default:
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id group by g.name order by count(g.name) desc limit 10";

		}

		$data = UserModel::db()->getAllBySQL($sql);

		foreach ($data as $value) {
			$records["data"][] = array(
	            $value['name'],
	            $value['active'],
	            "+1",
	        );
		}

        echo json_encode($records);
        exit;
  		
	}

	/**
	 * 获取游戏活跃饼状数据
	 * @return [type] [description]
	 */
	function getGameActivePie(){
		$type = _g("type");

		switch($type){
			case 1://按日
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id where to_days(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s')) = to_days(now()) group by g.name order by count(g.name) desc limit 10";
			break;
			case 2://按周
			$sql = "select count(g.name) as active,g.name,YEARWEEK(now()) from games g left join played_games pg on g.id=pg.game_id where YEARWEEK(date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y-%m-%d')) = YEARWEEK(now()) group by g.name order by count(g.name) desc limit 10";
			break;
			case 3://按月
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id where date_format(from_unixtime(pg.a_time,'%Y-%m-%d %H:%i:%s'),'%Y%m') = date_format(curdate(),'%Y%m') group by g.name order by count(g.name) desc limit 10";
			break;
			default:
			$sql = "select count(g.name) as active,g.name from games g left join played_games pg on g.id=pg.game_id group by g.name order by count(g.name) desc limit 10";

		}

		$res = UserModel::db()->getAllBySQL($sql);
		$resultData = [];
		if($res){
	        foreach ($res as $value) {
	        	
	        	$obj = new Result2();
		        $obj->name = $value['name'];
		        $obj->litres = $value['active'];
		        $resultData[] = $obj;
	        }
		}
		$this->outputJson(200, "succ", $resultData);

	}

}

class Result{
	public $data;
	public $label;
}

class Result2{
	public $name;
	public $litres;
}