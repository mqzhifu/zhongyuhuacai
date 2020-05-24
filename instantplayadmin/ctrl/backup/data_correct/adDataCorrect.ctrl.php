<?php

/**
 * @Author: xuren
 * @Date:   2019-05-23 13:59:35
 * @Last Modified by:   xuren
 * @Last Modified time: 2019-05-29 15:51:04
 */
class AdDataCorrectCtrl extends BaseCtrl{
	function index(){
	 	if(_g('getList')){
	 		$this->getList();
	 	}
	 	$gameNameList = GamesModel::getOnlineGamesNameList();
	 	$this->assign("gamesDesc", $gameNameList);
	 	// $this->assign("developersDesc",[]);
	 	$this->assign("developersTypeDesc",OpenUserModel::getAccountDescs());
	 	$category = GamesCategoryModel::db()->getAll();
        $categoryDesc = [];
        foreach ($category as $value) {
            $categoryDesc[$value['id']] = $value['name_cn'];
        }
	 	$this->assign("gamesTypeDesc", $categoryDesc);

	 	$this->display('data_correct/ad_correct_index.html');
	}

	function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        
        $sql = "select count(*) as cnt from games g inner join open_user as ou on g.uid=ou.uid where $where";


        $cntSql = UserModel::db()->getRowBySQL($sql);
        $cnt = (0 == $cntSql['cnt'])?0:$cntSql['cnt'];
        // var_dump($cntSql);
        // exit;
        if(arrKeyIssetAndExist($cntSql,'cnt')){
            $cnt = $cntSql['cnt'];
        }

        $iTotalRecords = $cnt;//DB中总记录数
        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                'g.id',
                '',
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


            
            $sql = "select g.id,g.name,g.category,ou.type,ou.company,ou.account_holder,g.a_time,g.u_time from games g inner join open_user ou on g.uid=ou.uid where $where order by $order limit $iDisplayStart,$iDisplayLength ";

            
            $data = UserModel::db()->getAllBySQL($sql);
            // 获取游戏类型
            $category = GamesCategoryModel::db()->getAll();
            $categoryDesc = [];
            foreach ($category as $value) {
                $categoryDesc[$value['id']] = $value['name_cn'];
            }

            foreach($data as $k=>$v){
                
                $records["data"][] = array(
                    $v['id'],
                    $v['name'],
                    $categoryDesc[$v['category']],
                    $v['type']==1 ? $v['company'] : $v['account_holder'],
                    OpenUserModel::getAccountDescs()[$v['type']],
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    '<a class="btn btn-xs default red delone" onclick="getDetail('.$v['id'].')"><i class="fa fa-file-text"></i>'.'配置'.'</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getDetails(){
    	$length = 10;
    	$page = _g("page");
        $start = ($page-1) * $length;
    	
    	$where = $this->getWhere2();

    	$cntArr = InnerAdDetailsByDayModel::db()->getRowBySQL("SELECT COUNT(*) AS cnt from (SELECT * FROM inner_ad_details_byday AS a where $where GROUP BY  a.stat_datetime,a.game_id) abc");
    	$totalPage = ceil($cntArr['cnt']/$length);
    	if($page > $totalPage){
    		$this->outputJson(200,'no page',["nowPage"=>$totalPage<1 ? 1 : $totalPage, "totalPage"=>1,"list"=>[]]);
    	}

    	$res = InnerAdDetailsByDayModel::db()->getAllBySQL("SELECT a.id AS aid,a.stat_datetime,IFNULL(a.click_cut_p,0) click_cut,IFNULL(a.show_cut_p,0) show_cut,IFNULL(a.cost_cut_p,0) cost_cut,IFNULL(sum(a.cut_cost),0) cut_cost,IFNULL(sum(a.click),0) click,IFNULL(sum(a.`show`),0) `show`,IFNULL(sum(a.cost),0) cost FROM  inner_ad_details_byday AS a where $where GROUP BY  a.stat_datetime,a.game_id limit $start,$length");
    	// $returnData = [];
    	// foreach ($res as $v) {
    	// 	$returnData = [
    	// 		$v['']
    	// 	];
    	// }
    	$this->outputJson(200, "succ", ["nowPage"=>$page, "totalPage"=>$totalPage, "list"=>$res]);
    }

    function changeCutPercent(){
    	$gameid = _g('gameid');
    	$day = _g("date");
    	$cost_p = _g("costPercent");
    	$click_p = _g("clickPercent");
    	$show_p = _g("showPercent");
    	$settleAccountService = new SettleAccountService();
    	$res = $settleAccountService->setCutPercent($gameid, $day, $cost_p, $click_p, $show_p);
    	if(!$res){
    		$this->outputJson(1, 'fail');
    	}
    	$this->outputJson(200, 'succ');

    }

    function getWhere2(){
    	$where = " 1 ";
        if($game_id = _g("gameid")){
        	$where .= " and a.game_id=$game_id";
        }
        if($from = _g("from")){
        	$where .= " and a.stat_datetime>='".$from."'";
        }
        if($to = _g("to")){
        	$where .= " and a.stat_datetime<='".$to."'";
        }
        return $where;
    }

    function getWhere(){
        $where = " 1 ";
        if($game_id = _g("game_id")){
        	$where .= " and g.id=$game_id";
        }
        if($category = _g("category")){
        	$where .= " and g.category=$category";
        }
        if($name = _g("developerName")){
        	$where .= " and ( ou.company=$name or ou.account_holder=$name )";
        }
        if($developerType = _g("developerType")){
        	$where .= " and ou.type=$developerType";
        }
        
        return $where;
    }

    function repullAdDataByDate(){
        $date = _g("date");
        $toutiaoService = new AdtoutiaoService();
        if(!$date){
            return;
        }
        $toutiaoService->contabImportDataByInterval($date, $date);
        LogLib::appWriteFileHash("管理员".$this->_adminid."拉取".$date."穿山甲广告数据");
        // $this->outputJson(200,'导入广告数据操作执行');
    }

    function reGenerateData(){
        $date = _g("date");
        if(!$date){
            $this->outputJson(1,'日期不存在');
        }
        $succ = $this->aggregateByDay($date);
        LogLib::appWriteFileHash("管理员".$this->_adminid."聚合".$date."穿山甲广告数据(".$succ.")");
        // if($succ){
        //     $this->outputJson(200,'聚合成功');
        // }else{
        //     $this->outputJson(2,'数据存在或者无数据，聚合失败');
        // }
    }

    
    // excel.upload;
   function updateFile(){
       require_once(PLUGIN . "phpexcel/PHPExcel.php");
       require_once(PLUGIN . "phpexcel/PHPExcel/IOFactory.php");

       $tmp_files = $_FILES['file'];
       $file = $tmp_files['tmp_name'];
       $file = iconv("utf-8", "gb2312", $file);
       $nameSuffix = substr($tmp_files['name'], strrpos($tmp_files['name'], '.') + 1);
       if(!in_array($nameSuffix, ['xls','xlsx','csv'])){
           echo '1003';exit();
       }
       if(empty($file) OR !file_exists($file)) {
           echo '1002';exit();
       }else{
           echo '1001';exit();
       }
   }

    // excel.save;
    function save(){

        // $info = TencentAdIncomeModel::db()->getAll( " a_time = $cpTime " );
        // if($info){
        //     $house_id = array_column($info, 'id');
        //     $all_house_id = implode(",",$house_id);
        //     TencentAdIncomeModel::db()->delByIds($all_house_id);
        // }

        require_once(PLUGIN . "phpexcel/PHPExcel.php");
        require_once(PLUGIN . "phpexcel/PHPExcel/IOFactory.php");

        $tmp_files = $_FILES['file'];
        $file = $tmp_files['tmp_name'];
        $nameSuffix = substr($tmp_files['name'], strrpos($tmp_files['name'], '.') + 1);
        if(!in_array($nameSuffix, ['xls','xlsx','csv'])){
            echo '1003';exit();
        }
        // $file = iconv("utf-8", "gb2312", $file);

        if(empty($file) OR !file_exists($file)) {
            echo '1003';exit();
        }

        $objRead = new PHPExcel_Reader_Excel2007();
        if(!$objRead->canRead($file)){

            $objRead = new PHPExcel_Reader_Excel5();

            if(!$objRead->canRead($file)){
                if($nameSuffix == 'csv'){
                    $objRead = PHPExcel_IOFactory::createReader('CSV')
                                ->setDelimiter(',')
                                // ->setInputEncoding('GBK')
                                ->setEnclosure('"')
                                ->setLineEnding("\r\n")
                                ->setSheetIndex(0);
                }else{
                    echo '1003';exit();
                }
                
                
            }
        }

        $obj = $objRead->load($file);
        $data = $obj->getSheet(0)->toArray();

        // Remove the header;
        unset($data[0]);
        // Clear invalid data;
        foreach ($data as $kk => $vv){
            if(empty($vv[0])){
                unset($data[$kk]);
            }
        }
        $insertAll = array();
        // issue_games_income_summary.insert;
        $stat_datetime = "";
        foreach ($data as $key=>$value){
            $arr = explode('-', $value[1]);
            if(count($arr) == 2 && is_numeric($arr[0])){
                $stat_datetime = $value[0];
                $insertSingle = array(
                    'stat_datetime' => $value[0],
                    'ad_slot_id' => $arr[0],
                    'show' => str_replace(',','',$value[2]),
                    'click' => str_replace(',','',$value[3]),
                    'cost' => str_replace(',','',$value[4]),
                    'ecpm' => $value[5],
                    'click_rate' => substr($value[6], 0,strlen($value[6])-1)/100,
                    'a_time' => time(),
                );
                array_push($insertAll, $insertSingle);
            }
            
        }
        if(!$insertAll){
            echo '1004';exit();
        }
        if(TencentAdIncomeModel::db()->getCount("stat_datetime='".$stat_datetime."'")){
            echo '1005';exit();
        }
        $rs = TencentAdIncomeModel::db()->addAll($insertAll);
        if($rs){
            echo '1001';exit();
        }else{
            echo '1003';exit();
        }
    }
    
    private function aggregateByDay($day){
        // 判重
        $count = InnerAdDetailsByDayModel::db()->getCount("stat_datetime='".$day."'");
        if($count){
            echo $day."数据已存在\n";
            $r = InnerAdDetailsByDayModel::db()->delete("stat_datetime='".$day."' limit $count");
            if(!$r){
                echo $day."清除数据失败\n";
                return false;
            }
            echo $day."清除数据成功\n";
            LogLib::appWriteFileHash("========aggregateAD======$day 数据已存在 清除数据成功");
            // return false;
        }
        // 穿山甲广告 and 广点通
        $sql = "select b.uid,b.inner_ad_id,b.game_id,sum(a.click) click,sum(a.cost) cost,sum(a.`show`) `show` from (select ad_slot_id,cost,click,`show` from open_advertise_income where stat_datetime='".$day."' UNION ALL SELECT ad_slot_id,cost,click,`show` From tencent_ad_income WHERE stat_datetime='".$day."') a inner join (select am.inner_ad_id,am.outer_ad_id,oa.game_id,oa.uid from open_advertise oa left join ad_map am on oa.id=am.inner_ad_id where oa.status!=".OpenAdvertiseModel::$status_del." group by am.outer_ad_id) b on a.ad_slot_id=b.outer_ad_id group by b.inner_ad_id";
        $total = advertiseIncomeModel::db()->getAllBySQL($sql);
        if(empty($total)){
            echo $day."穿山甲和广点通无可聚合数据\n";
            LogLib::appWriteFileHash("========aggregateAD======$day 无可聚合数据");
            return false;
        }
        // 获取历史暗扣比例
        $yesterday = date("Y-m-d", strtotime($day." -1 day"));
        $gameIdsStr = implode(',', array_unique(array_column($total, 'game_id')));
        $historyData = InnerAdDetailsByDayModel::db()->getAllBySQL("select click_cut_p,cost_cut_p,show_cut_p,game_id from inner_ad_details_byday where stat_datetime='".$yesterday."' and game_id in ($gameIdsStr) group by game_id");
        $historyMap = [];
        foreach ($historyData as $v) {
            $historyMap[$v['game_id']] = $v;
        }
        // 组装今日暗扣比例
        $addData = [];
        $addTime = time();
        foreach ($total as $value) {
            $click_percent = 0;
            $cost_percent = 0;
            $show_percent = 0;
            if(isset($historyMap[$value['game_id']])){
                if(isset($historyMap[$value['game_id']]['click_cut_p'])){
                    $click_percent = $historyMap[$value['game_id']]['click_cut_p'];
                }

                if(isset($historyMap[$value['game_id']]['cost_cut_p'])){
                    $cost_percent = $historyMap[$value['game_id']]['cost_cut_p'];
                }

                if(isset($historyMap[$value['game_id']]['show_cut_p'])){
                    $show_percent = $historyMap[$value['game_id']]['show_cut_p'];
                }
                
            }
            $add = [
                'uid'=>$value['uid'],
                'inner_ad_id'=>$value['inner_ad_id'],
                'game_id'=>$value['game_id'],
                'cost'=>$value['cost'],
                'click'=>$value['click'],
                'show'=>$value['show'],
                'cut_cost'=>$value['cost']*(1-$cost_percent),
                'cut_click'=>$value['click']*(1-$click_percent),
                'cut_show'=>$value['show']*(1-$show_percent),
                'click_cut_p'=>$click_percent,
                'cost_cut_p'=>$cost_percent,
                'show_cut_p'=>$show_percent,
                'stat_datetime'=>$day,
                'a_time'=>$addTime
            ];
            $addData[] = $add;
        }

        if($addData){
            $res = InnerAdDetailsByDayModel::db()->addAll($addData);
            if($res){
                echo $day."聚合成功".count($addData)."数据";
                LogLib::appWriteFileHash("========aggregateAD======$day 聚合成功".count($addData)."数据");
                return true;
            }
        }
        
    }
}