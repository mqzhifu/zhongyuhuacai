<?php

/**
 * @Author: Kir
 * @Date:   2019-04-01 10:21:59
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-24 14:49:25
 */


/**
 * 
 */
class AdvertiseCtrl extends BaseCtrl
{
	
	function getGameAd($innerId)
	{
		if (!$innerId || !is_numeric($innerId)) {
			return $this->out(8064,$GLOBALS['code'][8064]);
		}
		$innerAD = OpenAdvertiseModel::db()->getRowById($innerId);
		if (!$innerAD) {
			return $this->out(1024,$GLOBALS['code'][1024]);
		}

		// 老版本兼容 channel = 1
		$outerAD = OpenADMapModel::db()->getRow(" inner_ad_id = $innerId and channel = 1");
		if (!$outerAD) {
			return $this->out(1024,$GLOBALS['code'][1024]);
		}

		return $this->out(200,['advertiser'=>$outerAD['advertiser'],'channel'=>$outerAD['channel'],'outerId'=>$outerAD['outer_ad_id']]);
	}


	function getAppAd($innerId)
	{
		if (!$innerId || !is_numeric($innerId)) {
			return $this->out(8064,$GLOBALS['code'][8064]);
		}
		$innerAD = AppAdvertiseModel::db()->getRowById($innerId);
		if (!$innerAD) {
			return $this->out(1024,$GLOBALS['code'][1024]);
		}
		
		// 老版本兼容 channel = 1
		$outerAD = AppADMapModel::db()->getRow(" inner_ad_id = $innerId and channel = 1");
		if (!$outerAD) {
			return $this->out(1024,$GLOBALS['code'][1024]);
		}

		return $this->out(200,['advertiser'=>$outerAD['advertiser'],'channel'=>$outerAD['channel'],'outerId'=>$outerAD['outer_ad_id'],'state'=>$innerAD['state']]);
	}
}