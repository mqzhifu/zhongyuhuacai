<?php

/**
 * @Author: Kir
 * @Date:   2019-05-07 18:39:31
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-06 15:22:00
 */


class PublishCtrl extends BaseCtrl
{
    function home()
    {
        $this->addCss('assets/mg/css/home.css');
        $this->addCss('assets/open/css/bootstrap.min.css');
        $this->addJs('assets/open/scripts/bootstrap.min.js');
        $this->display("publish/home.html",'new');
    }
	/**
     * 发行游戏列表
     */
    function index()
    {
    	$os = _g('os');
    	if (!$os) {
    		jump("/game/index/");
    		exit(0);
    	}
    	$this->assign("os",$os);
        $this->display("publish/manager.html","new",'isLogin');
    }

    // 检验游戏信息，若不存在跳转到指定页面
    function check()
    {
        $gameid = _g("gameid");
        $gameInfo = PublishGamesModel::db()->getRow("uid={$this->_uid} and id = $gameid");
        if (!$gameInfo) {
            if (isAjax()) {
                $this->outputJson(99, "无此游戏权限", []);
            } else {
                jump("/publish/index/os=$os");
                exit(0);
            }
        }

        if ($gameInfo['icon_256']) {
            $gameInfo['icon_256'] = $this->openGamesService->getOldStaticUrl().$gameInfo['icon_256'];
        }

        $this->assign("gameid", $gameInfo["id"]);
        $this->assign("gameInfo", $gameInfo);
        $this->assign("os",$gameInfo['os']);

        return $gameInfo;
    }

       // 发行游戏
    function getGames()
    {
    	$os = _g('os');
        if ($name = _g("name")) {
            $game = PublishGamesModel::db()->getRow(" uid={$this->_uid} and name = '$name' and os=$os");
            if (!$game) {
                $this->outputJson(0,"查询不到该游戏");
            }
            $games[0] = $game;
        } else {
            $curPos = _g("curPos");
            $pageLength = _g("pageLength");
            $limit = '';
            if ($pageLength!='' && $curPos!='') {
                $limit = "limit $curPos,$pageLength";
            }
            $games = PublishGamesModel::db()->getAll(" uid={$this->_uid} and os=$os $limit");
        }

        $staticUrl = $this->openGamesService->getOldStaticUrl();
        foreach ($games as &$g) {
            if ($g['icon_256']) {
                $g['icon_256'] = $staticUrl.$g['icon_256'];
            }
            $g['os'] = PublishGamesModel::$typeDesc[$g['os']]; 
        }
        $this->outputJson(200,"succ", $games);
    }


    /**
     * 添加发行游戏
     */
    function addGame()
    {
        $name = trim(_g('name', 'require'));
        $os = trim(_g('os', 'require'));
        if(isset($name) && !empty($name)){
            if (PublishGamesModel::db()->getRow(" name='$name'")) {
                $this->outputJson(0,"游戏名称已存在");
            }
        }
        $game = [
            'name' => $name,
            'os' => $os,
            'app_secret' => $this->openGamesService->getSecret(),
            'uid' => $this->_uid,
            'a_time' => time(),
            'u_time' => time(),

        ];
        PublishGamesModel::db()->add($game);

        $res = PublishGamesModel::db()->getRow(" name='$name'");

        $this->outputJson(200,"succ",$res);
    }

    // 小游戏 - 详情
    function detail()
    {
        // 检验游戏
        $this->check();

        // 获取游戏分类信息
        $gameCategory = $this->openGamesService->getGameCategory();

        $this->assign("gameCategory", $gameCategory);

        $this->display("publish/gameDetails.html","new","isLogin");
    }

    function edit()
    {
        $this->check();

        // 获取游戏分类信息
        $gameCategory = $this->openGamesService->getGameCategory();

        $this->assign("gameCategory", $gameCategory);

        $this->display("publish/gameEdit.html","new","isLogin");
    }

    // 小游戏 - 详情 - 身份校验
    function auth()
    {
        $gameInfo = $this->check();

        $password = _g("password");
        $check = $this->userService->checkPassword($this->_uid, $password);
        if ($check == 1) {
            $this->outputJson(0, "验证成功", ['appSecret'=>$gameInfo['app_secret']]);
        } else {
            $this->outputJson(1, "验证失败", []);
        }
    }

    // 小游戏 - 详情 - 保存
    function save()
    {
        // 检验游戏
        $gameInfo = $this->check();
        $gameid = $gameInfo["id"];

        $update = [];

        // 分类
        $category = _g("category");
        if (is_numeric($category)) {
            $update["category"] = $category;
        }
        // 简介
        $summary = _g("summary");
        if ($summary !== "") {
            $update["summary"] = $summary;
        }
        // 屏幕方向
        $screen = _g("screen");
        if (in_array($screen, [0, 1, 2])) {
            $update["screen"] = $screen;
        }

        // 创建图标保存目录
        $gameUploadPath = "publish_games" . DIRECTORY_SEPARATOR;
        $gameImagePath = $this->openGamesService->getOldStaticPath() . $gameUploadPath;
        $this->openGamesService->checkDirectory($gameImagePath);
        // 应用图标256*256
        if (isset($_FILES["icon_256"]) && is_uploaded_file($_FILES["icon_256"]["tmp_name"])) {
            $extension = $this->openGamesService->getFileExtension($_FILES["icon_256"]["name"]);
            $filename = uniqid("icon_256_") . $extension;
            $result = move_uploaded_file($_FILES["icon_256"]["tmp_name"], $gameImagePath . $filename);
            if ($result) {
                // 换成以前的地址
                $update["icon_256"] = $gameUploadPath . $filename;
                $this->openGamesService->rsyncToServer();
            }
            unset($extension, $filename, $result);
        }

        // 更新游戏信息
        $update['u_time'] = time();
        $res = PublishGamesModel::db()->upById($gameid, $update);
        if ($res) {
            $this->outputJson(0, "更新成功", []);
        } else {
            $this->outputJson(1, "更新失败", []);
        }
    }


    // 游戏收入
    function income()
    {
    	$this->check();
    	$this->addJs('assets/open/scripts/laydate/laydate.js');
    	$this->display("publish/income.html","new","isLogin");
    }

    function getIncomeList()
    {
    	$length = 10;
    	$gameid = _g("gameid");
    	$page = _g("page");
    	$start = ($page-1) * $length;

    	$where = " game_id = $gameid ";
    	$startDate = _g("startDate");
    	$endDate = _g("endDate");
    	if ($startDate) {
    		$startDate = strtotime($startDate);
    		$where .= " and a_time >= $startDate ";
    	}
    	if ($endDate) {
    		$endDate = strtotime("+1 day $endDate");
    		$where .= " and a_time < $endDate ";
    	}
    	$count = issueGamesIncomeSummaryModel::db()->getCount($where);
    	$totalPage = ceil($count/$length);
    	if ($page > $totalPage) {
    		$list = [];
    	} else {
    		$list = issueGamesIncomeSummaryModel::db()->getAll("$where order by a_time desc limit $start,$length");
    	}
    	
    	foreach ($list as &$income) {
    		$income['a_time'] = date("Y-m-d",$income['a_time']);
    	}

    	$this->outputJson(200, "succ", ["totalPage"=>$totalPage, "list"=>$list]);
    }


    // 收入汇总
    function summary()
    {
    	$os = _g('os');
    	if (!$os) {
    		jump("/game/index/");
    		exit(0);
    	}
    	$this->assign("os",$os);
    	$this->addJs('assets/open/scripts/laydate/laydate.js');
    	$this->display("publish/incomeSummary.html","new","isLogin");
    }


    function getIncomeSummary()
    {
    	$length = 10;
    	$os = _g("os");
    	$page = _g("page");
    	$start = ($page-1) * $length;

    	$where = "1";

    	$startDate = _g("startDate");
    	$endDate = _g("endDate");
    	if ($startDate) {
    		$startDate = strtotime($startDate);
    		$where .= " and a_time >= $startDate ";
    	}
    	if ($endDate) {
    		$endDate = strtotime("+1 day $endDate");
    		$where .= " and a_time < $endDate ";
    	}

    	$games = PublishGamesModel::db()->getAll(" uid={$this->_uid} and os=$os");
    	if (!$games) {
    		$this->outputJson(200, "succ", ["totalPage"=>0, "list"=>[]]);
    	}

    	$gameIds = array_column($games, 'id');
    	$gameIds = implode(',', $gameIds);
    	$where .= " and game_id in ($gameIds) ";

    	$count = issueGamesIncomeSummaryModel::db()->getRowBySql("
    		SELECT
				count(*) AS cnt
			FROM
				( SELECT FROM_UNIXTIME( a_time, '%Y-%m-%d' ) AS date FROM issue_games_income_summary WHERE $where GROUP BY a_time ) AS c
    	")['cnt'];
    	
    	$totalPage = ceil($count/$length);
    	if ($page > $totalPage) {
    		$list = [];
    	} else {
    		$list = issueGamesIncomeSummaryModel::db()->getAllBySql("
	    		SELECT
					FROM_UNIXTIME( a_time, '%Y-%m-%d' ) AS date,
					sum( total_installed ) AS total_installed,
					sum( total_overall ) AS total_overall,
					sum( avg_cost ) AS avg_cost,
					sum( ad_income ) AS ad_income,
					sum( inside_income ) AS inside_income,
					sum( total_income ) AS total_income,
					sum( profit ) AS profit 
				FROM
					issue_games_income_summary 
				WHERE $where
				GROUP BY
					a_time
				LIMIT $start,$length;
			");
    	}

    	$this->outputJson(200, "succ", ["totalPage"=>$totalPage, "list"=>$list]);
    }


    /**
     * 展示某一个游戏的广告
     */
    public function Ad()
    {
        $this->check();

        $this->display('publish/adManagement.html', 'new', 'isLogin');
    }

    public function getAds()
    {
        $this->check();

        $length = 10;
        $gameid = _g("gameid");
        $page = _g("page");
        $start = ($page-1) * $length;

        $where = " game_id = $gameid and status <> 4 ";
        $count = PublishAdModel::db()->getCount($where);

        $totalPage = ceil($count/$length);
        if ($page > $totalPage) {
            $list = [];
        } else {
            $list = PublishAdModel::db()->getAll("$where order by u_time desc limit $start,$length");
        }

        $AdvertiseTypeDesc = PublishAdModel::getAdvertiseTypeDesc();
        $directionDesc = PublishAdModel::getAdDirectionDesc();

        foreach ($list as $k => $value) {
            $value['no'] = $i;
            if (isset($AdvertiseTypeDesc[$value['advertise_type']])) {
                $value['advertise_type_desc'] = $AdvertiseTypeDesc[$value['advertise_type']];
            } else {
                $value['advertise_type_desc'] = '未知';
            }
            if (isset($directionDesc[$value['direction']])) {
                $value['direction_desc'] = $directionDesc[$value['direction']];
            } else {
                $value['direction_desc'] = '未知';
            }
            $list[$k] = $value;
        }

        $this->outputJson(200, "succ", ['totalPage'=>$totalPage,'list'=>$list]);
    }


    /**
     * 给某个游戏添加广告
     */
    public function addAd()
    {
        $this->check();

        $this->assign('adType', PublishAdModel::getAdvertiseTypeDesc());
        $this->assign('direction', PublishAdModel::getAdDirectionDesc());
        $this->display('publish/adCreation.html', 'new', 'isLogin');

    }


    public function addAdSubmit()
    {

        $gameinfo = $this->check();
        $gid = $gameinfo["id"];

        if (!$this->_uid || !$gid) {
            $res = [
                'result' => false
            ];
        } else {
            $base = [
                'game_id' => $gid,
                'uid' => $this->_uid,
                'status' => 1,
                'a_time' => time(),
                'u_time' => time(),
            ];
            $fields = $this->openUpdateAdFields('add');
            $addInfo = $this->filterParam($fields, true);
            if (count(array_intersect_key($addInfo, $fields)) != count($fields)) {
                $res = [
                    'result' => false,
                    'msg' => '参数不足！',
                ];
            } else {
                $addInfo = array_merge($base, $addInfo);
                $aid = PublishAdModel::db()->add($addInfo);
                if ($aid) {
                    $res['title'] = $addInfo['title'];
                    $res['game_id'] = $gid;
                    $res['aid'] = $aid;
                    $res['result'] = true;
                }
            }
        }
        echo json_encode($res);
        return;
    }

    // 广告开关状态
    public function switchAd()
    {
        $gameinfo = $this->check();
        $gid = $gameinfo["id"];
        $aid = _g("aid");
        $status = _g("status");
        if ($status != 2 && $status != 3) {
            $this->outputJson(0, "参数错误");
        }
        $updateInfo = [
            'status'=>$status
        ];
        $updateInfo['u_time'] = time();
        PublishAdModel::db()->upById($aid, $updateInfo);
        $this->outputJson(200, "succ");
    }


    /**
     * 更新某一个游戏的信息
     */
    public function updateAd ()
    {
        $filter = $this->filterParam(['gameid' => 'intval', 'aid' => 'intval']);
        $gid = $filter['gameid'];
        $aid = $filter['aid'];
        if (!$this->_uid || !$gid || !$aid) {
            $res = [
                'result' => false
            ];
        } else {
            $updateInfo = $this->filterParam($this->openUpdateAdFields('update'), true);
            if (!$updateInfo) {
                $res = [
                    'result' => false
                ];
            } else {
                $updateInfo['u_time'] = time();
                if (PublishAdModel::db()->upById($aid, $updateInfo)) {
                    $res = [
                        'result' => true
                    ];
                }
            }

        }
        echo json_encode($res);
        return;
    }

    /**
     * 软删除某一个游戏的广告信息
     */
    public function deleteAd ()
    {
        $filter = $this->filterParam(['gameid' => 'intval', 'aid' => 'intval']);
        $gid = $filter['gameid'];
        $aid = $filter['aid'];
        if (!$this->_uid || !$gid || !$aid) {
            $res = [
                'result' => false
            ];
        } else {
            $updateInfo['status'] = 4;
            $updateInfo['u_time'] = time();
            if (PublishAdModel::db()->upById($aid, $updateInfo)) {
                $res = [
                    'result' => true
                ];
            }
        }
        echo json_encode($res);
        return;
    }

    private function openUpdateAdFields ($opeartion = 'add')
    {
        $updateFields = [
            'title' => '',
        ];
        if ($opeartion == 'add') {
            $updateFields['advertise_type'] = 'intval';
            $updateFields['direction'] = 'intval';
        }
        return $updateFields;
    }

}