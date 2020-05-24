<?php

/**
 * 导入6月1号到当前时间的广告数据
 * @Author: xuren
 * @Date:   2019-06-03 14:10:42
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-06-03 14:14:29
 */
class importAdDataBat{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr)
    {
    	$obj = new AdtoutiaoService();
    	$startData = "2019-06-01";
        // $endDate = date("Y-m-d",  time());
        $endDate = "2019-06-03";
		$r = $obj->contabImportDataByInterval($startData, $endDate);
    }
}