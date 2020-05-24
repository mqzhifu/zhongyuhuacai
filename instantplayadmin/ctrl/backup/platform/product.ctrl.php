<?php

/**
 * @Author: xuren
 * @Date:   2019-03-19 10:27:39
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-04-24 10:34:55
 */
class ProductCtrl extends BaseCtrl{
	
	function index(){

		$this->addCss("/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css");
		$this->addCss("/assets/admin/pages/css/profile.css");
		$this->addCss("/assets/admin/pages/css/tasks.css");
		
		$this->addCss("/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css");
		$this->addCss("/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css");
		$this->addCss("/assets/global/plugins/select2/select2.css");
		$this->addCss("/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css");
		$this->addCss("/assets/global/css/plugins.css");
		$this->display("platform/product.html");
	}

	function getTopData(){
		$type = _g("type");
		switch ($type) {
			case 1:
				$sql = "select count(ac) as num,concat('/',ctrl,'/',ac,'/') as page from access_log where date_sub(curdate(),interval 7 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s')) group by ac order by count(ac) desc limit 10";
				break;
			case 2:
				$sql = "select count(ac) as num,concat('/',ctrl,'/',ac,'/') as page from access_log where date_sub(curdate(),interval 30 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s')) group by ac order by count(ac) desc limit 10";
				break;
			default:
				$sql = "select count(ac) as num,concat('/',ctrl,'/',ac,'/') as page from access_log where date_sub(curdate(),interval 7 day) <= date(from_unixtime(a_time,'%Y-%m-%d %H:%i:%s')) group by ac order by count(ac) desc limit 10";
				break;
		}
		$res = UserModel::db()->getAllBySQL($sql);
		$total = 0;
		foreach ($res as $value) {
			$total += $value['num'];
		}

		foreach ($res as &$value) {
			$value['percent'] = $total==0 ? 0 : round($value['num']/$total,2);
		}

		$this->outputJson(200, "succ", $res);
	}

	function getSexData(){
		$type1 = _g("type1");//1 活跃 2新增
		$type2 = _g("type2");// 7 30
		switch ($type1) {
			case 1:
				if($type2 == 1){
					$sql = "SELECT count(u.sex) num,u.sex FROM access_log a left join user u on a.uid=u.id where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(from_unixtime(a.a_time,'%Y%m%d')) group by u.sex";
				}else if($type2 == 2){
					$sql = "SELECT count(u.sex) num,u.sex FROM access_log a left join user u on a.uid=u.id where DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(from_unixtime(a.a_time,'%Y%m%d')) group by u.sex";
				}
				break;
			case 2:
				# code...
				break;
			default:
				$sql = "SELECT count(u.sex) num,u.sex FROM access_log a left join user u on a.uid=u.id where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(from_unixtime(a.a_time,'%Y%m%d')) group by u.sex";
				break;
		}
		$res = UserModel::db()->getAllBySQL($sql);

		$resultData = [];
		if($res){
	        foreach ($res as $value) {
	        	
	        	$obj = new Result();
		        $obj->data = $value['num'];
		        
		        if($value['sex'] == 1){
		        	$value['sex'] = "男";
		        }elseif ($value['sex'] == 2) {
		        	$value['sex'] = "女";
		        }else{
		        	$value['sex'] = "未知";
		        }
		        $obj->label = $value['sex'];
		        $resultData[] = $obj;
	        }
		}
		$this->outputJson(200, "succ", $resultData);
		
	}

	function getAgeData(){
		_g("type1");
		_g("type2");

		$sql = "SELECT ROUND(DATEDIFF(CURDATE(), birthday)/365.2422) from user";
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