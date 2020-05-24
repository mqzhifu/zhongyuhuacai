<?php

/**
 * @Author: xuren
 * @Date:   2019-03-15 10:50:11
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-20 18:22:35
 */
class GameCtrl extends BaseCtrl{

	function online(){
		$this->addCss("/assets/open/css/game-detail.css?1");
// $this->addCss("/assets/open/css/developer-set-style.css");
		if(_g("getOnlineList")){
            $this->getOnlineList();
        }

        $this->assign('i', 0);
		$this->assign('gameTagsMap', TagsDetailModel::getGameTagsDesc());
        $this->assign('customTagsMap', TagsDetailModel::getCustomTagsDesc());

        $openGamesService = new OpenGamesService();
        $this->assign('gameCategory', $openGamesService->getGameCategory());
        $this->assign('paintStyleDesc', $openGamesService->getPaintStyleDesc());
        $this->assign('hideDesc', $this->getHideDesc());

		$onlineDesc = ['6'=>"上线",'9'=>"下线"];
		$this->assign('onlineDesc', $onlineDesc);
		$this->display("game_online.html");
	}
	function getOnlineList(){
        $this->getList([6]);
    }

	function audit(){
		$this->addCss("/assets/open/css/game-detail.css?1");
		if(_g("getAudit")){
            $this->getAudit();
        }
        $this->assign('i', 0);
        $this->assign('gameTagsMap', TagsDetailModel::getGameTagsDesc());
        $this->assign('customTagsMap', TagsDetailModel::getCustomTagsDesc());

        $openGamesService = new OpenGamesService();
        $this->assign('gameCategory', $openGamesService->getGameCategory());
        $this->assign('paintStyleDesc', $openGamesService->getPaintStyleDesc());
        $this->assign('hideDesc', $this->getHideDesc());

        $onlineDesc = ['5'=>"审核中",'3'=>"审核不通过",'4'=>"审核通过"];
		$this->assign('onlineDesc', $onlineDesc);
		$this->display("game_audit.html");
	}
	function getAudit(){
		$this->getList([5]);
	}

	function wait(){
		$this->addCss("/assets/open/css/game-detail.css?1");
		if(_g("getWait")){
            $this->getWait();
        }
        $this->assign('i', 0);
        $this->assign('gameTagsMap', TagsDetailModel::getGameTagsDesc());
        $this->assign('customTagsMap', TagsDetailModel::getCustomTagsDesc());

        $openGamesService = new OpenGamesService();
        $this->assign('gameCategory', $openGamesService->getGameCategory());
        $this->assign('paintStyleDesc', $openGamesService->getPaintStyleDesc());
        $this->assign('hideDesc', $this->getHideDesc());

        $onlineDesc = ['1'=>"开发中",'2'=>"测试中",'3'=>"审核不通过",'4'=>"审核通过"];
		$this->assign('onlineDesc', $onlineDesc);
		$this->display("game_wait.html");
	}
	function getWait(){
		$this->getList([1,2,3,4]);//1234
	}

	function offline(){
		$this->addCss("/assets/open/css/game-detail.css?1");
		if(_g("getOffline")){
            $this->getOffline();
        }
        $this->assign('i', 0);
        $this->assign('gameTagsMap', TagsDetailModel::getGameTagsDesc());
        $this->assign('customTagsMap', TagsDetailModel::getCustomTagsDesc());

        $openGamesService = new OpenGamesService();
        $this->assign('gameCategory', $openGamesService->getGameCategory());
        $this->assign('paintStyleDesc', $openGamesService->getPaintStyleDesc());
        $this->assign('hideDesc', $this->getHideDesc());

        $onlineDesc = ['6'=>"上线",'9'=>"下线"];
		$this->assign('onlineDesc', $onlineDesc);


		$this->display("game_offline.html");
	}
	function getOffline(){
		$this->getList([9]);
	}
	// function getOffline(){
	// 	$records = array();
 //        $records["data"] = array();
 //        $sEcho = intval($_REQUEST['draw']);

 //        $where = $this->getWhere();
        
	//     $where .= " and a.`status`=2";

        
        
 //        $sql = "select count(*) as cnt from open_game_hosting as where $where";
        

 //        $cntSql = UserModel::db()->getRowBySQL($sql);
 //        // var_dump($cntSql);
 //        // exit;
 //        if(arrKeyIssetAndExist($cntSql,'cnt')){
 //            $cnt = $cntSql['cnt'];
 //        }

 //        $iTotalRecords = $cnt;//DB中总记录数
 //        if ($iTotalRecords){
 //            $order_sort = _g("order");

 //            $order_column = $order_sort[0]['column'] ?: 0;
 //            $order_dir = $order_sort[0]['dir'] ?: "asc";


 //            $sort = array(
 //                'game_id',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //                '',
 //            );
 //            $order = $sort[$order_column]." ".$order_dir;

 //            $iDisplayLength = intval($_REQUEST['length']);//每页多少条记录
 //            if(999999 == $iDisplayLength){
 //                $iDisplayLength = $iTotalRecords;
 //            }else{
 //                $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
 //            }

 //            $iDisplayStart = intval($_REQUEST['start']);//limit 起始


 //            $end = $iDisplayStart + $iDisplayLength;
 //            $end = $end > $iTotalRecords ? $iTotalRecords : $end;

 //            $sql = "select o.id,o.game_id,o.status,g.name,o.audit_info,g.category,g.soft_copyright,g.u_time,ou.type,ou.company,ou.contact,ou.phone,ou.business,ou.idcard_img,ou.idcard2_img from (open_game_hosting as o left join games as g on o.game_id=g.id) left join open_user as ou on o.uid=ou.uid  where $where GROUP BY o.game_id order by o.$order limit $iDisplayStart,$end ";

            
 //            $data = UserModel::db()->getAllBySQL($sql);
            
 //            foreach($data as $k=>$v){
                
 //                $backup="";
 //                if($v["soft_copyright"]){
 //                	$backup .= '<a style="color: #245085;" href="'.$this->getStaticBaseUrl().$v["soft_copyright"].'" target="_blank">软著 </a>';
 //                }
 //                $audit_info = json_decode($v['audit_info'], true);
 //                if($audit_info['authorization']){
 //                	$backup .= '<a style="color: #245085;" href="'.$this->getStaticBaseUrl().$audit_info['authorization'].'" target="_blank">授权证书</a>';
 //                }

 //                $certification = "";
 //                if($v['business']){
 //                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticBaseUrl().$v["business"].'" target="_blank">执照 </a>';
 //                }
 //                if($v['idcard_img']){
 //                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticBaseUrl().$v["idcard_img"].'" target="_blank">身份证正 </a>';
 //                }
 //                if($v['idcard2_img']){
 //                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticBaseUrl().$v["idcard2_img"].'" target="_blank">身份证反 </a>';
 //                }
 //                $records["data"][] = array(
 //                    $v['game_id'],
 //                    $v['id'],
 //                    $v['name'],
 //                    $v['category'],
 //                    $this->getDescByStatus($v['status']),
 //                    $v['type'],
 //                    $v['company'],
 //                    $v['contact'],
 //                    $v['phone'],
 //                    $backup,
 //                    $certification,
 //                    get_default_date($v['u_time']),
 //                    '<a href="/game/no/game/goodsIndex/?game_id='.$v['game_id'].'">'.$v['game_id'].'</a>',
 //                   '<a href="#" class="btn btn-xs default red delone" data-toggle="modal" data-target="#myModal" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 查看</a>',
 //                );
 //            }
 //        }

 //        $records["draw"] = $sEcho;
 //        $records["recordsTotal"] = $iTotalRecords;
 //        $records["recordsFiltered"] = $iTotalRecords;

 //        echo json_encode($records);
 //        exit;
	// }

	function getWhere(){
        $where = " 1 ";
        if($game_id = _g("game_id")){
        	$where .= " and o.game_id=$game_id";
        }
        if($name = _g("name")){
        	$where .= " and g.name like '%$name%'";
        }
        if($type = _g("type")){
        	$where .= " and ou.type=$type";
        }
        if($contact = _g("contact")){
        	$where .= " and ou.contact like '%$contact%'";
        }
        if($phone = _g("phone")){
        	$where .= " and ou.phone like '%$phone%'";
        }

        if($a_time_from = _g("a_time_from")){
            $where .= " and g.a_time >= ".strtotime($a_time_from)."";
        }

        if($a_time_to = _g("a_time_to")){
            $where .= " and g.a_time <= ".strtotime("$a_time_to +1 day")."";
        }

        if($u_time_from = _g("u_time_from")){
            $where .= " and g.u_time >= ".strtotime($u_time_from)."";
        }

        if($u_time_to = _g("u_time_to")){
            $where .= " and g.u_time <= ".strtotime("$u_time_to +1 day")."";
        }

        return $where;
    }

    public function goodsIndex(){
    	$gameid = _g("game_id");
    		
    	$sql = "select * from open_props_price where game_id= $gameid";
    	$list2 = UserModel::db()->getAllBySQL($sql);

    	$this->assign("list2", $list2);

    	$this->display("goods_id.html");
    }

    public function getGoodsList(){

    }

    public function getStaticBaseUrl(){
        $baseUrl = "http://mgres.kaixin001.com.cn/xyx";
        if(ENV == 'release'){
            $baseUrl .= DIRECTORY_SEPARATOR."pro".DIRECTORY_SEPARATOR;
        } else {
            $baseUrl .= DIRECTORY_SEPARATOR."dev".DIRECTORY_SEPARATOR;
        }

        $baseUrl .= "upload".DIRECTORY_SEPARATOR.'open';
        return $baseUrl;
    }

    private function getDescByStatus($num){
    	$arr = ['1'=>'开发中','2'=>'测试','3'=>'审核不通过','4'=>'审核通过','5'=>'审核中','6'=>'上线','7'=>'历史版本','8'=>'失效','9'=>"下线"];
    	return $arr[$num];
    }
    private function getScreenDesc($val){
    	$arr = ['1'=>'横', '2'=>'竖'];
    	return $arr[$val];
    }

    private function getList($statusArr){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();
        for($i=0; $i<count($statusArr); $i++){
        	if($i >= 1){
	        	$where .= " or o.`status`=".$statusArr[$i];
	        }else{
                $where .= " and (o.`status`=".$statusArr[$i];
            }

        }
        $where .= ")";
        
        $sql = "select count(*) as cnt from (open_game_hosting as o left join games as g on o.game_id=g.id) left join open_user as ou on g.uid=ou.uid where $where";


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
                'game_id',
                '',
                'sort',
                '',
                '',
                '',
                '',
                '',
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


            
            $sql = "select g.sort,o.id,o.game_id,o.status,o.created_at,o.version,g.name,o.audit_info,g.category,g.soft_copyright,g.u_time,g.a_time,ou.type,ou.company,ou.account_holder,ou.contact,ou.phone,ou.business,ou.idcard_img,ou.idcard2_img from (open_game_hosting as o left join games as g on o.game_id=g.id) left join open_user as ou on g.uid=ou.uid  where $where order by $order limit $iDisplayStart,$iDisplayLength ";

            
            $data = UserModel::db()->getAllBySQL($sql);
            // 获取游戏类型
            $category = GamesCategoryModel::db()->getAll();
            $categoryDesc = [];
            foreach ($category as $value) {
                $categoryDesc[$value['id']] = $value['name_cn'];
            }

            foreach($data as $k=>$v){
                
                $backup="";
                if($v["soft_copyright"]){
                	$backup .= '<a style="color: #245085;" href="'.$this->getStaticFileUrl('softcopyright', $v['soft_copyright'], "open").'" target="_blank">软著 </a>';
                }
                $audit_info = json_decode($v['audit_info'], true);
                if($audit_info['authorization']){
                	$backup .= '<a style="color: #245085;" href="'.$this->getStaticFileUrl('authorization', $audit_info['authorization'], "open").'" target="_blank">授权证书</a>';
                }

                $certification = "";
                if($v['business']){
                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticFileUrl('business', $v['business'], "open").'" target="_blank">执照 </a>';
                }
                if($v['idcard_img']){
                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticFileUrl('idcard', $v['idcard_img'], "open").'" target="_blank">身份证正 </a>';
                }
                if($v['idcard2_img']){
                	$certification .= '<a style="color: #245085;" href="'.$this->getStaticFileUrl('idcard', $v['idcard2_img'], "open").'" target="_blank">身份证反 </a>';
                }

                $records["data"][] = array(
                    $v['game_id'],
                    $v['version'],
                    $v['sort'],
                    $v['name'],
                    $categoryDesc[$v['category']],
                    $this->getDescByStatus($v['status']),
                    OpenUserModel::getAccountDescs()[$v['type']],
                    $this->getDeveloperName($v),
                    $v['contact'],
                    $v['phone'],
                    $backup,
                    $certification,
                    get_default_date($v['a_time']),
                    get_default_date($v['u_time']),
                    '<a class="btn btn-xs default red delone" href="/game/no/game/goodsIndex/game_id='.$v['game_id'].'" target="_blank"><i class="fa fa-file-text"></i>'.'查看'.'</a>',
                   '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'" game-id="'.$v['game_id'].'" onclick="getDetail(this)"><i class="fa fa-file-text"></i> 操作</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function getDeveloperName($dp){
        if($dp['type'] == OpenUserModel::TYPE_PERSON){
            return $dp['account_holder'];
        }

        if($dp['type'] == OpenUserModel::TYPE_COMPANY){
            return $dp['company'];
        }
        return "";
    }

    public function getDetails(){
    	$id = _g('id');
    	if($id=="undefined"||!$id){
    		$this->outputJson(0, "id不存在");
    	}


    	$sql = "select o.game_id,o.status,g.base_played_num,g.sort,g.summary,g.name,g.category,g.paint_style,o.audit_info,g.is_online,g.soft_copyright,g.list_img,g.small_img,g.index_reco_img,g.app_secret,g.hide,ou.business,ou.idcard_img,ou.idcard2_img from (open_game_hosting as o left join games as g on o.game_id=g.id) left join open_user as ou on o.uid=ou.uid  where `o`.`id`=$id";
        
        $data = UserModel::db()->getRowBySQL($sql);
        if(!$data){
        	$this->outputJson(1, "信息不存在");
        }
		
		$jsonArr = json_decode($data['audit_info'], true);

		$returnData = [];
		$returnData['game_id'] = $data['game_id'];
		$returnData['game_key'] = $data['app_secret'];
		$returnData['game_name'] = $data['name'];
        $returnData['background_color'] = $jsonArr['background_color'] ? $jsonArr['background_color'] : $data['background_color'];
		$returnData['status'] = $data['status'];
		$returnData['is_online'] = $data['is_online'];
		$returnData['game_screen'] = isset($jsonArr['screenDirection']) ? $this->getScreenDesc($jsonArr['screenDirection']) : "未知";
		$returnData['game_intro'] = isset($jsonArr['intro']) ? $jsonArr['intro'] : "无介绍";
		$returnData['game_updates'] = isset($jsonArr['versionUpdates']) ? $jsonArr['versionUpdates'] : "无更新描述";
        $returnData['soft_copyright'] = $this->getStaticFileUrl('softcopyright', $data['soft_copyright'], "open");
        $returnData['business'] = $this->getStaticFileUrl('business', $data['business'], "open");
        $returnData['idcard_img'] = $this->getStaticFileUrl('idcard', $data['idcard_img'], "open");
        $returnData['idcard2_img'] = $this->getStaticFileUrl('idcard', $data['idcard2_img'], "open");


		$returnData['authorization'] = isset($jsonArr['authorization']) ? $this->getStaticFileUrl('authorization', $jsonArr['authorization'], "open") : "";
		$openGamesService = new OpenGamesService();
        if(_g('online')){
            $returnData['icon_256'] = $this->getStaticFileUrl('games', $data['list_img'], "open", true);
            $returnData['icon_128'] = $this->getStaticFileUrl('games', $data['small_img'], "open", true);
            $returnData['startup'] = $this->getStaticFileUrl('games', $data['index_reco_img'], "open", true);
        }else{
            $returnData['icon_256'] = $this->getStaticFileUrl('games', $jsonArr['list_img'] ? $jsonArr['list_img'] : $data['list_img'], "open", true);
            $returnData['icon_128'] = $this->getStaticFileUrl('games', $jsonArr['small_img'] ? $jsonArr['small_img'] : $data['small_img'], "open", true);
            $returnData['startup'] = $this->getStaticFileUrl('games', $jsonArr['index_reco_img'] ? $jsonArr['index_reco_img'] : $data['index_reco_img'], "open", true);
        }
        
        $returnData['sort'] = $data['sort'];
		$returnData['base_played_num'] = $data['base_played_num'];

        // 画风 and 分类
        $returnData['category'] = $data['category'];
        $returnData['paint_style'] = $data['paint_style'];
        // 标签列表
        $returnData['tagList'] = $this->gamesService->getGameTags($data['game_id']);
        // app getlist是否显示
        $returnData['hide'] = $data['hide'];
		$this->outputJson(200, "成功", $returnData);
    }

    function save(){
    	$ot = _g('operationType');
    	switch ($ot) {
    		case '1':
    			$is_online = _g('is_online');
    			$gameid = _g('gameid');
    			if(!$is_online || $is_online == 'undefined' || !$this->checkIsOnline($is_online) ||  !$gameid || $gameid == 'undefined'){
    				$this->outputJson(0,'非法参数');
    			}
    			$res = GamesModel::db()->update(['is_online'=>$is_online],"id=$gameid limit 1");
    			if(!$res){
    				$this->outputJson(1,'操作失败');
    			}
                // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                WZGamesModel::db()->update(['is_online'=>$is_online],"id=$gameid limit 1");
                // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
    			break;
    		case '2':
    			$status = _g('status');
    			$id = _g('id');
    			$gameid = _g('gameid');
    			$examine_content = trim(_g('reasonTxt'));
    			if(!$status || $status == 'undefined' || !$this->checkStatus($status) || !$id || $id == 'undefined'){
    				$this->outputJson(0,'非法参数');
    			}

                $gameHostingInfo = GameHostingModel::db()->getRowById($id);
                if ($gameHostingInfo['status'] != $status && $status == 6) {
                    
                    $this->setProduction($gameid, $id);
                } else {
                    $up['status'] = $status;
                }
                $version_detail = json_decode($gameHostingInfo['audit_info'], true);
                $background_color = _g("background_color");
                if ($background_color) {
                    $version_detail['background_color'] = $background_color;
                }
                $up['audit_info'] = json_encode($version_detail);

                // 审核表改状态
    			$res = GameHostingModel::db()->upById($gameHostingInfo['id'],$up);
                // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                WZGameHostingModel::db()->upById($gameHostingInfo['id'],$up);
                // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
    			if($status == 9){//下线
                    $updates = [];
                    $updates['is_online'] = GamesModel::$_online_false;
                    $sort = _g('sort');
                    if($sort != 'undefined'){
                        $updates['sort'] = intval($sort);
                    }
                    $base_played_num = _g('base_played_num');
                    if($base_played_num != 'undefined'){
                        $updates['base_played_num'] = intval($base_played_num);
                    }
                    // 隐藏
                    $hide = _g("hide");
                    if($hide != 'undefined'){
                        $updates['hide'] = intval($hide);
                    }
                    // 画风和分类
                    $category = _g("category");
                    if($category && $category != 'undefined'){
                        $updates['category'] = intval($category);
                    }
                    $paintStyle = _g("paintStyle");
                    if($paintStyle && $paintStyle != 'undefined'){
                        $updates['paint_style'] = intval($paintStyle);
                    }
                    $updates['status'] = GamesModel::$_status_7;
    				GamesModel::db()->update($updates, "id=$gameid limit 1");
                    // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    WZGamesModel::db()->update($updates, "id=$gameid limit 1");
                    // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

    			}
    			// 三期新增 Begin
                if($status == 3){//不通过
                    $row = UserModel::db()->getRowById($gameid, 'id','games');

                    $updates = [];
                    // 隐藏
                    $hide = _g("hide");
                    if($hide != 'undefined'){
                        $updates['hide'] = intval($hide);
                    }
                    // 画风和分类
                    $category = _g("category");
                    if($category && $category != 'undefined'){
                        $updates['category'] = intval($category);
                    }
                    $paintStyle = _g("paintStyle");
                    if($paintStyle && $paintStyle != 'undefined'){
                        $updates['paint_style'] = intval($paintStyle);
                    }

                    $updates['status'] = GamesModel::$_status_6;
                    GamesModel::db()->update($updates,"id=$gameid limit 1");
                    // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    WZGamesModel::db()->update($updates,"id=$gameid limit 1");
                    // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    $notificationService = new openNotificationService();
                    $notificationService->sendNotifyMsg($row['uid'],1,"代码发布审核结果","开发者你好，经平台审核，您的小游戏《{$row['name']}》未通过审核，具体原因如下：$examine_content");
                }
                if($status == 4){//通过
                    $row = UserModel::db()->getRowById($gameid, 'id','games');

                    $updates = [];
                    // 隐藏
                    $hide = _g("hide");
                    if($hide != 'undefined'){
                        $updates['hide'] = intval($hide);
                    }
                    // 画风和分类
                    $category = _g("category");
                    if($category && $category != 'undefined'){
                        $updates['category'] = intval($category);
                    }
                    $paintStyle = _g("paintStyle");
                    if($paintStyle && $paintStyle != 'undefined'){
                        $updates['paint_style'] = intval($paintStyle);
                    }

                    $updates['status'] = GamesModel::$_status_5;
                    GamesModel::db()->update($updates,"id=$gameid limit 1");
                    // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    WZGamesModel::db()->update($updates,"id=$gameid limit 1");
                    // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

                    // 通过则添加一条分成比例记录 2019-05-27
                    // FinanceModel::db()->add(['game_id'=>$gameid,'a_time'=>time()]);

                    $notificationService = new openNotificationService();
                    $notificationService->sendNotifyMsg($row['uid'],1,"代码发布审核结果","开发者你好，经平台审核，您的小游戏《{$row['name']}》已通过审核。");
                }
                if($status == 5){//审核中
                    $row = UserModel::db()->getRowById($gameid, 'id','games');

                    $updates = [];
                    // 隐藏
                    $hide = _g("hide");
                    if($hide != 'undefined'){
                        $updates['hide'] = intval($hide);
                    }
                    // 画风和分类
                    $category = _g("category");
                    if($category && $category != 'undefined'){
                        $updates['category'] = intval($category);
                    }
                    $paintStyle = _g("paintStyle");
                    if($paintStyle && $paintStyle != 'undefined'){
                        $updates['paint_style'] = intval($paintStyle);
                    }
                    $updates['status'] = GamesModel::$_status_3;// games表得审核中状态
                    GamesModel::db()->update($updates,"id=$gameid limit 1");
                    // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    WZGamesModel::db()->update($updates,"id=$gameid limit 1");
                    // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

                }
                // 三期新增   End
    			if($status == 6){//上线
                    // $item = GamesModel::db()->getRow("id=$id",'open_game_hosting');
                    // $audit_info = json_decode($item['audit_info'],true);
                    $updates = [];
                    // $updates['status'] = 8;//已經上綫
                    // $updates['is_online'] = GamesModel::$_online_true; 
                    // if(isset($audit_info['gameType']) && $audit_info['gameType']){
                    //     $updates['category'] = $audit_info['gameType'];
                    // }
                    // if(isset($audit_info['small_img']) && $audit_info['small_img']){
                    //     $updates['small_img'] = $audit_info['small_img'];
                    // }
                    // if(isset($audit_info['list_img']) && $audit_info['list_img']){
                    //     $updates['list_img'] = $audit_info['list_img'];
                    // }
                    // if(isset($audit_info['index_reco_img']) && $audit_info['index_reco_img']){
                    //     $updates['index_reco_img'] = $audit_info['index_reco_img'];
                    // }
                    // if(isset($audit_info['screenDirection']) && $audit_info['screenDirection']){
                    //     $updates['screen'] = $audit_info['screenDirection'];
                    // }
                    // if(isset($audit_info['intro']) && $audit_info['intro']){
                    //     $updates['summary'] = $audit_info['intro'];
                    // }
                    
                    $updates['u_time'] = time();
                    
                    $sort = _g('sort');
                    if($sort != 'undefined'){
                        $updates['sort'] = intval($sort);
                    }
                    $base_played_num = _g('base_played_num');
                    if($base_played_num != 'undefined'){
                        $updates['base_played_num'] = intval($base_played_num);
                    }
                    // 隐藏
                    $hide = _g("hide");
                    if($hide != 'undefined'){
                        $updates['hide'] = intval($hide);
                    }
                    // 画风和分类
                    $category = _g("category");
                    if($category && $category != 'undefined'){
                        $updates['category'] = intval($category);
                    }
                    $paintStyle = _g("paintStyle");
                    if($paintStyle && $paintStyle != 'undefined'){
                        $updates['paint_style'] = intval($paintStyle);
                    }
					$background_color = _g("background_color");
                    if($background_color && $background_color != 'undefined'){
                        $updates['background_color'] = $background_color;
                    }
					$updates['status'] = GamesModel::$_status_8;                    // $openGamesService = new OpenGamesService();
                    // $updates['play_url'] = $openGamesService->getGameUrl($item['game_id'],$item['version']);
                    //并且将数据覆盖

                    // 后台更新信息优先于上线覆盖
                    GamesModel::db()->update($updates,"id=$gameid limit 1");
                    // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                    WZGamesModel::db()->update($updates,"id=$gameid limit 1");
                    // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
    			}
    			// if(!$res){
    			// 	$this->outputJson(1,'操作失败');
    			// }
    			// if($status == '4'){
    				
    			// }
    			break;
    		default:
    			
    			break;
    	}
        // 更新指定游戏的缓存信息 add by XiaHB time:2019/04/11 Begin;
        $gameId = _g('gameid');
        $gameCatchService = new gamesCatchService();
        $gameInfo = GamesModel::db()->getById($gameId);
        if(!empty($gameInfo)){
            $gameCatchService->updateGameRow($gameId, $gameInfo);
        }
        // 更新指定游戏的缓存信息 add by XiaHB time:2019/04/11   End;
    	$this->outputJson(200, "操作成功");


    }

    private function checkIsOnline($is_online){
    	$arr = [GamesModel::$_online_true,GamesModel::$_online_false];
    	return in_array($is_online, $arr);
    }

    private function checkStatus($status){
    	$arr = ['1'=>'开发中','2'=>'测试','3'=>'审核不通过','4'=>'审核通过','5'=>'审核中','6'=>'上线','7'=>'历史版本','8'=>'失效','9'=>'下线'];
    	return array_key_exists($status, $arr);
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

    public function auditPass(){
        $this->addCss("/assets/open/css/game-detail.css?1");
        if(_g("getAuditPass")){
            $this->getAuditPass();
        }

        $this->assign('i', 0);
        $this->assign('gameTagsMap', TagsDetailModel::getGameTagsDesc());
        $this->assign('customTagsMap', TagsDetailModel::getCustomTagsDesc());

        $openGamesService = new OpenGamesService();
        $this->assign('gameCategory', $openGamesService->getGameCategory());
        $this->assign('paintStyleDesc', $openGamesService->getPaintStyleDesc());
        $this->assign('hideDesc', $this->getHideDesc());

        $onlineDesc = ['1'=>"开发中",'2'=>"测试中",'3'=>"审核不通过",'4'=>"审核通过",'6'=>"上线"];
        $this->assign('onlineDesc', $onlineDesc);
        $this->display("game_audit_pass.html");
    }

    function getAuditPass(){
        $this->getList([4]);//12346
    }

    function addTag(){
        $gameid = intval(_g("gameid"));
        $tagid = intval(_g("tagid"));
        $res = $this->gamesService->addTag($gameid, $tagid);
        if(!$res){
            $this->outputJson(1, '添加标签失败');
        }
        $this->outputJson(200, 'succ');
    }

    function removeTag(){
        $gameid = intval(_g("gameid"));
        $tagid = intval(_g("tagid"));

        $res = $this->gamesService->removeTag($gameid, $tagid);
        if(!$res){
            $this->outputJson(1, '删除标签失败');
        }
        $this->outputJson(200, 'succ');
    }

    private function getHideDesc(){
        return [0=>"显示",1=>"隐藏"];
    }

    // 将游戏版本设置为上线版
    public function setProduction ($gameid, $id)
    {
        
        $db = GameHostingModel::db();
        
        $db->update(['status'=>GameHostingModel::STATUS_HAD_PRODUCTED], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_PRODUCTION . "' limit 1 ");
        // 设置指定版本上线版
        
        $db->update(['status'=>GameHostingModel::STATUS_PRODUCTION], " id='" . $id . "' AND game_id='" . $gameid . "'  limit 1"); 
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_HAD_PRODUCTED], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_PRODUCTION . "' limit 1 ");
        WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_PRODUCTION], " id='" . $id . "' AND game_id='" . $gameid . "'  limit 1"); 
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        // 获取主键的版本号
        $hosting = GameHostingModel::db()->getById($id);
        $audit_info = json_decode($hosting['audit_info'], true);

        $updates = [];
        $updates['status'] = 8;//已上綫
        $updates['is_online'] = GamesModel::$_online_true;
        $openGamesService = new OpenGamesService();
        $updates['play_url'] = $openGamesService->getGameUrl($gameid, $hosting["version"]);
        
        if(isset($audit_info['small_img']) && $audit_info['small_img']){
            $updates['small_img'] = $audit_info['small_img'];
        }
        if(isset($audit_info['list_img']) && $audit_info['list_img']){
            $updates['list_img'] =  $audit_info['list_img'];
        }
        if(isset($audit_info['index_reco_img']) && $audit_info['index_reco_img']){
            $updates['index_reco_img'] = $audit_info['index_reco_img'];
        }
        
        GamesModel::db()->update($updates, " id='" . $gameid . "' limit 1");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGamesModel::db()->update($updates, " id='" . $gameid . "' limit 1");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        // 执行事务
        try {
            $result = true;
            //设置游戏状态 games表
            $openGamesService->setGamesStatus($gameid, GamesModel::$_status_8);
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }
}