<?php

/**
 * @Author: xuren
 * @Date:   2019-03-19 10:27:39
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-24 10:35:27
 */
class GameCtrl extends BaseCtrl{
	
	function index(){
		$this->addCss("/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css");
		$this->addCss("/assets/admin/pages/css/profile.css");
		$this->addCss("/assets/admin/pages/css/tasks.css");
		
		$this->addCss("/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css");
		$this->addCss("/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css");
		$this->addCss("/assets/global/plugins/select2/select2.css");
		$this->addCss("/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css");
		$this->addCss("/assets/global/css/plugins.css");
		$this->display("games/game/gamedata.html");
	}

	function getNearMonthVisitLineData(){
		$gameRange = _g("gameRange");
		$type = _g("type");
		switch ($type) {
			case 1://近30天活跃用户
				$sql = "select count(*) as active,from_unixtime(a_time,'%d') as tt from access_log where date_sub(curdate(),interval 30 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s')) group by from_unixtime(a_time,'%Y-%m-%d') order by tt desc";
				break;
			case 2:
				# code...
				break;
			case 3:
			# code...
				break;
			case 4:
			# code...
				break;
			default:
				$sql = "select count(*) as active,from_unixtime(a_time,'%d') as tt from access_log where date_sub(curdate(),interval 30 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s')) group by from_unixtime(a_time,'%Y-%m-%d') order by tt desc";
				break;
		}
		$data = UserModel::db()->getAllBySQL($sql);
		$newData = [];
        foreach ($data as $value) {
        	$newData[] = [$value['tt'], $value['active']];
        }
		$obj = new Result();
        $obj->data = $newData;
        $obj->label = "活跃度";

        $resultData = [];
        $resultData[] = $obj;

        $this->outputJson(200, "succ", $resultData);

	}

	function getNearMonthIncomeLineData(){
		$gameRange = _g("gameRange");
		$type = _g("type");
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

class Result{
	public $data;
	public $label;
}

class Result2{
	public $name;
	public $litres;
}