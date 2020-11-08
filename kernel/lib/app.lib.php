<?php
//每个APP应用程序的控制
class AppLib{
	function getApp($appid){
		$where = " where app_id = $appid ";
		
		$sql = "select * from app $where";
		return $this->db->getRow($sql);
	}
	
	function getCtrl($appid ,$ctrl,$ac){
		$where = " where app_id = '$appid' and ctrl = '$ctrl' and  ac = '$ac'";
			
		$sql = "select * from app_ctrl $where order by ctrl  ";
		$rs =  $this->db->getRow($sql);
		return $rs;
	}
	
	function authApp($appid){
		$app_info = $this->getApp($appid);
		if(!$app_info)
			stop($appid.':未注册','APP');
		
		
		if($app_info['status'] != 1)
			stop('APP 未开启:'.$appid,'APP');
		
		return $app_info;
		
	}
	
	function authCtrl($app,$ctrl,$ac){
		$ctrlInfo = $this->getCtrl($app,$ctrl,$ac);
		if(!$ctrlInfo)
			stop("ctrl:{$ctrl},ac:{$ac}".':未注册','APP');
		
		if($ctrlInfo['status'] != 1)
			stop("ctrl:{$ctrl},ac:{$ac}".':未开启','APP');
		
		return $ctrlInfo;
		
	}
}
