<?php

/**
 * 游戏相关类
 * Class gameCtrl
 */
class GameCtrl extends BaseCtrl
{
    public function index()
    {
        $this->display("gameEntrance.html",'new','isLogin');
    }

    /**
     * 游戏列表
     */
    public function show()
    {
        $this->addCss("assets/open/css/gameManage.css");
        $this->display("game/gameManage.html",'new','isLogin');
    }

    public function getGames()
    {
        $name = _g("name");
        if ($name != '') {
            $game = GamesModel::db()->getRow("uid = {$this->_uid} and name = '$name'");
            if (!$game) {
                $this->outputJson(0,"查询不到该游戏");
            }
            $games[0] = OpenGamesService::getUserGamesInfo($this->_uid,$game['id']);
        } else {
            $curPos = _g("curPos");
            $pageLength = _g("pageLength");
            $limit = '';
            if ($pageLength!='' && $curPos!='') {
                $limit = "limit $curPos,$pageLength";
            }
            $games = OpenGamesService::getUserGamesInfo($this->_uid,0,'',$limit);
        }

        $gameList=[];
        $statusDesc = GamesModel::getStatusDesc();
        $statusColor = GamesModel::getStatusColor();
        $staticUrl = $this->openGamesService->getOldStaticUrl();
        foreach ($games as $key=>$value) {
            $gameList[$key]['id'] = $value['id'];
            $gameList[$key]['name'] = $value['name'];
            $gameList[$key]['small_img']='';
            if ($value['small_img']) {
                $gameList[$key]['small_img'] = $this->getStaticFileUrl("games", $value['small_img'], "instantplayadmin");
            }
            if (isset($statusDesc[$value['status']]) && $value['status'] != 0) {
                if ($value['status'] == 8 && $value['is_online'] != 1) {
                    $value['status'] = 1;
                }
                $gameList[$key]['status_desc'] = $statusDesc[$value['status']];
                $gameList[$key]['status_color'] = $statusColor[$value['status']];
            }
        }
        $this->outputJson(200,"succ", $gameList);
    }


    /**
     * 添加游戏
     */
    public function add ()
    {
        $gameName = trim(_g('name', 'require'));
        // 三期迭代项目新增游戏名称校验功能 add by XiaHB 2019/03/23 Begin;
        if(isset($gameName) && !empty($gameName)){
            $result = $this->openGamesService->getGamesName($gameName);
            if(true === $result){
                $res['result'] = false;
                $res['code'] = '-1001';
                echo json_encode($res);return;
            }
        }
        // 三期迭代项目新增游戏名称校验功能 add by XiaHB 2019/03/23  End;
        $res = $this->openGamesService->addGame($this->_uid, $gameName);


        if ($res['result'] == true) {
            // 添加创建游戏日志
            ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_add_game, $res['game_id']);
            // 创建游戏时，生成分成比例数据 2019/05/28 by xuren
            // $count = FinanceModel::db()->getCount("game_id=".$res['game_id']);
            // if(!$count){
            //     FinanceModel::addOne($res['game_id'],FinanceModel::$type_ad);
            //     FinanceModel::addOne($res['game_id'],FinanceModel::$type_purchase);
            // }
        }

        echo json_encode($res);
        return;
    }

    /**
     * 更新游戏信息
     */
    public function update ()
    {
        $filter = $this->filterParam(['gid' => 'intval']);
        $gid = $filter['gid'];
        if (!$this->_uid || !$gid) {
            $res = [
                'result' => false
            ];
        } else {
            $fields = $this->openUpdateGameFields();
            $updateInfo = $this->filterParam($fields, true);
            if (!$updateInfo) {
                $res = [
                    'result' => false
                ];
            } else {
                $res = $this->openGamesService->updateGame($this->_uid, $gid, $updateInfo);
            }
        }
        var_dump($res);
    }

    /**
     * 删除游戏
     */
    public function delete ()
    {
        $filter = $this->filterParam(['gid' => 'intval']);
        $gid = $filter['gid'];
        if (!$this->_uid || !$gid) {
            $res = [
                'result' => false
            ];
        } else {
            $res = $this->openGamesService->deleteGame($this->_uid, $gid);
        }
        echo json_encode($res);
        return;
    }

    /**
     * open平台允许修改的字段
     * @return array
     */
    private function openUpdateGameFields ()
    {
        $updateFields = [
            'category' => 'intval',
            'name' => '',
            'small_img' => '',
            'list_img' => '',
            'index_reco_img' => '',
            'screen' => 'intval',
            'summary' => '',
            'background_color' => '',
        ];
        return $updateFields;
    }

    // 小游戏 - 公共 - 切换游戏状态
    public function commonChangeGameOnline ()
    {
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        $online = _g("online");
        if (!in_array($online, [0, 1])) {
            $this->outputJson(2, "参数错误", []);
        }
        $update["is_online"] = $online;

        // 执行游戏是否有线上版
        $checkProduction = GameHostingModel::hasProductionVersion($gameid);
        if (!$checkProduction) {
            $this->outputJson(3, "该游戏没有上线版，不能设置为上线状态", []);
        }

        // 修改游戏状态
        $result = $this->openGamesService->updateGame($this->_uid, $gameid, $update);
        if ($result["result"]) {

            // 添加切换游戏状态日志
            if ($online == 1)
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_set_mode_common, $gameid); elseif ($online == 0) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_set_mode_dev, $gameid);
            }

            $this->outputJson(0, "设置成功", []);
        } else {
            $this->outputJson(1, "设置失败", []);
        }
    }

    // 小游戏 - 公共 - 获取指定用户的游戏列表
    public function commonMyGames ()
    {
        $where = "1=1";

        $keyword = _g("keyword");
        if ($keyword != "") {
            $where .= " and name like '%" . $keyword . "%'";
        }

        $games = [];
        $result = $this->openGamesService->getUserGamesInfo($this->_uid, 0, $where);
        foreach ($result as $item) {
            $game = [];
            $game["id"] = $item["id"];
            $game["name"] = $item["name"];

            $game["icon_256_url"] = "";
            $game["icon_128_url"] = "";
            $game["startup_url"] = "";
            if ($item["list_img"] != "") {
                $game["icon_256_url"] = $this->getStaticFileUrl("games", $item['list_img'], "instantplayadmin");
            }
            if ($item["small_img"] != "") {
                $game["icon_128_url"] = $this->getStaticFileUrl("games", $item['small_img'], "instantplayadmin");
            }
            if ($item["index_reco_img"] != "") {
                $game["startup_url"] = $this->getStaticFileUrl("games", $item['index_reco_img'], "instantplayadmin");
            }
            $games[] = $game;
        }

        $this->outputJson(0, "游戏列表", $games);
    }

    // 小游戏 - 详情
    public function detail ()
    {
        // 检验游戏
        $this->checkGame();

        // 静态文件
        $this->addJs("assets/open/scripts/md5.js");
        // 控制显示外链
        $uid = $this->_uid;

        $res = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_link);
        $res2 = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_wechat);


        $this->assign("power_link", $res);
        $this->assign("power_wechat", $res2);
        $this->assign("wechat_program_desc", GamesModel::getWechatProgramDesc());

        // 获取游戏分类信息
        $gameCategory = $this->openGamesService->getGameCategory();

        $this->assign("gameCategory", $gameCategory);

        // 获取画风信息
        $paintStyleDesc = $this->openGamesService->getPaintStyleDesc();
        $this->assign("paintStyleDesc", $paintStyleDesc);

        $res3 = GamesModel::getUrlTypeDesc();
        if(!$res2){
            unset($res3[3]);
        }
        if(!$res){
            unset($res3[2]);
        }
        $this->assign("urlTypeDesc", $res3);

        $this->display("game/gameDetails.html","new","isLogin");
    }

    public function edit()
    {
        $this->checkGame();
        // 控制显示外链
        $uid = $this->_uid;
        $res = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_link);
        $res2 = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_wechat);

        $this->assign("power_link", $res);
        $this->assign("power_wechat", $res2);
        $this->assign("wechat_program_desc", GamesModel::getWechatProgramDesc());

        // 获取游戏分类信息
        $gameCategory = $this->openGamesService->getGameCategory();
        $this->assign("gameCategory", $gameCategory);

        // 获取画风信息
        $paintStyleDesc = $this->openGamesService->getPaintStyleDesc();
        $this->assign("paintStyleDesc", $paintStyleDesc);

        $res3 = GamesModel::getUrlTypeDesc();
        if(!$res2){
            unset($res3[3]);
        }
        if(!$res){
            unset($res3[2]);
        }
        $this->assign("urlTypeDesc", $res3);
        $this->addJs("/js/md5.js");
        $this->display("game/gameEdit.html","new","isLogin");
    }

    // 小游戏 - 详情 - 身份校验
    public function detailAuth ()
    {
        // 检验游戏
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        if (PCK_AREA == 'en') {
            $check = 1;
        } else {
            $password = _g("password");
            $check = $this->userService->checkPassword($this->_uid, $password);
        }
        if ($check == 1) {
            $this->outputJson(0, "验证成功", [
                "appSecret" => $gameInfo["app_secret"],
                "resetToken" => $this->openGamesService->generateResetKeyToken($this->_uid, $gameid),
            ]);
        } else {
            $this->outputJson(1, "验证失败", []);
        }
    }

    // 小游戏 - 详情 - 保存
    public function detailSave ()
    {
        // 检验游戏
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameid, 'game_manage')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>"; 
            exit(0);
        }

        $update = [];

        // 密钥
        $appSecret = _g("appSecret");
        // 密钥tokne
        $appSecretToken = _g("appSecretToken");
        if ($appSecret != "" && $this->openGamesService->verifyResetToken($this->_uid, $gameid, $appSecretToken)) {
            $update["app_secret"] = $appSecret;
        }

        // 分类
        $category = _g("category");
        if (is_numeric($category)) {
            $update["category"] = $category;
        }
        // 画风
        $paintStyle = _g("paintStyle");
        if (is_numeric($paintStyle)) {
            $update["paint_style"] = $paintStyle;
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
        // 游戏外链
        // $link_url = _g("link_url");
        $uid = $this->_uid;
        // $res = AppManagerModel::db()->getRow("uid=$uid");
        // if($res){
        //     $update['link_url'] = $link_url;
        // }
        $url_type = intval(_g('url_type'));
        if($url_type && GamesModel::checkURLType($url_type)){
            
            switch ($url_type) {
                case GamesModel::$url_type_inner :
                    break;
                case GamesModel::$url_type_link :
                    $hasPower = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_link);
                    if($hasPower){
                        $update['link_url'] = _g("link_url");
                    }
                    break;
                case GamesModel::$url_type_wx :
                    $hasPower = $this->openGamesService->hasLinkPower($uid, LinkGamePowerModel::$_power_wechat);
                    if($hasPower){
                        $update['wx_userName'] = _g("wx_userName");
                        $update['wx_path'] = _g("wx_path");
                        $update['wx_miniprogramType'] = _g("wx_miniprogramType");
                    }
                    break;
            }
            $update['url_type'] = $url_type;
        }
        

        // 背景颜色
        $backgroundColor = _g("backgroundColor");
        if ($backgroundColor != "") {
            $update["background_color"] = $backgroundColor;
        }
        // 创建图标保存目录
        // $gameUploadPath = "games" . DIRECTORY_SEPARATOR;
        // $gameImagePath = $this->openGamesService->getOldStaticPath() . $gameUploadPath;
        // $this->openGamesService->checkDirectory($gameImagePath);
        // // 应用图标256*256
        // if (isset($_FILES["icon_256"]) && is_uploaded_file($_FILES["icon_256"]["tmp_name"])) {
        //     $extension = $this->openGamesService->getFileExtension($_FILES["icon_256"]["name"]);
        //     $filename = uniqid("icon_256_") . $extension;
        //     $result = move_uploaded_file($_FILES["icon_256"]["tmp_name"], $gameImagePath . $filename);
        //     if ($result) {
        //         // 换成以前的地址
        //         $update["list_img"] = $gameUploadPath . $filename;
        //         $this->openGamesService->rsyncToServer();
        //     }
        //     unset($extension, $filename, $result);
        // }
        // // 应用图标128*128
        // if (isset($_FILES["icon_128"]) && is_uploaded_file($_FILES["icon_128"]["tmp_name"])) {
        //     $extension = $this->openGamesService->getFileExtension($_FILES["icon_128"]["name"]);
        //     $filename = uniqid("icon_128_") . $extension;
        //     $result = move_uploaded_file($_FILES["icon_128"]["tmp_name"], $gameImagePath . $filename);
        //     if ($result) {
        //         // 换成以前的地址
        //         $update["small_img"] = $gameUploadPath . $filename;
        //         $this->openGamesService->rsyncToServer();
        //     }
        //     unset($extension, $filename, $result);
        // }
        // // 游戏启动页
        // if (isset($_FILES["startup"]) && is_uploaded_file($_FILES["startup"]["tmp_name"])) {
        //     $extension = $this->openGamesService->getFileExtension($_FILES["startup"]["name"]);
        //     $filename = uniqid("startup_") . $extension;
        //     $result = move_uploaded_file($_FILES["startup"]["tmp_name"], $gameImagePath . $filename);
        //     if ($result) {
        //         // 换成以前的地址
        //         $update["index_reco_img"] = $gameUploadPath . $filename;
        //         $this->openGamesService->rsyncToServer();
        //     }
        //     unset($extension, $filename, $result);
        // }
        
        // 应用图标256*256
        if (isset($_FILES["icon_256"])) {
            $rs = $this->uploadService->uploadFileByApp("icon_256", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $update["list_img"] = $rs['msg'];
        }
        // 应用图标128*128
        if (isset($_FILES["icon_128"])) {
            $rs = $this->uploadService->uploadFileByApp("icon_128", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $update["small_img"] = $rs['msg'];
        }
        // 游戏启动页
        if (isset($_FILES["startup"])) {
            $rs = $this->uploadService->uploadFileByApp("startup", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $update["index_reco_img"] = $rs['msg'];
        }


        // 更新游戏信息
        $result = $this->openGamesService->updateGame($this->_uid, $gameid, $update);
        if ($result["result"]) {
            // 添加信息修改日志
            if (isset($update['category']) && $update['category'] != $gameInfo['category']) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_up_game_cate, $gameid, null, json_encode(['oldCategory'=>$gameInfo['category'], 'newCategory'=>$update['category']]));
            }
            if (isset($update['summary']) && $update['summary'] != $gameInfo['summary']) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_up_game_desc, $gameid, null, json_encode(['gameDesc'=>$update['summary']]));
            }
            if (isset($update["list_img"]) || isset($update["small_img"]) || isset($update["index_reco_img"])) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_up_game_pic, $gameid);
            }

            // 四期迭代新增添加修改游戏缓存信息逻辑 add by XiaHB time:2019/04/10 Begin;
            $this->updateGameCatch($gameid);// 通过主键，查询出全部的信息，直接替换掉;
            // 四期迭代新增添加修改游戏缓存信息逻辑 add by XiaHB time:2019/04/10   End;

            $this->outputJson(0, "更新成功", []);
        } else {
            $this->outputJson(1, "更新失败", []);
        }
    }

    // 小游戏 - 游戏托管 - 列表
    public function hosting ()
    {
        // 检验游戏信息
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameid, 'game_host')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>";
            exit(0);
        }

        
        $this->assign("gameid", $gameid);

//        $this->display("game/game_hosting.html");
        $this->display("game/game_hosting.html","new","isLogin");

    }

    public function hostingUpload ()
    {
        // set_time_limit(120);
        // 检验游戏信息
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameid, 'game_host')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>";
            exit(0);
        }

        // 获取附件信息
        $file = $_FILES["material"];
        // 生成版本号，累加
        $version = GameHostingModel::getVersion($gameid);

        // 备注
        $remark = _g("remark");
        // 校验
        if ($remark == "") {
            $this->outputJson(9, "备注不能为空", []);
        }

        // 附件，校验
        if (is_uploaded_file($file["tmp_name"])) {
            // 判断文件大小是否超过
            if ($this->openGamesService->sizeOverflow($file["size"])) {
                $this->outputJson(1, "文件大小超过限制", []);
            }
            // 查看附件类型
            $extension = strrchr($file["name"], ".");
            if ($extension != ".zip") {
                $this->outputJson(2, "文件类型需为.zip", []);
            }
            // 生成目标文件名
            $dstFilename = $this->openGamesService->generateSourcePath() . $gameid . "-" . $version . ".zip";
//            var_dump($dstFilename);
            // 移动文件到指定位置
            $result = move_uploaded_file($file["tmp_name"], $dstFilename);
            if (!$result) {
                $this->outputJson(3, "移动文件失败", []);
            }
            // 解压目标目录
            $dstPath = $this->openGamesService->generateDstPath($gameid, $version);
//            var_dump($dstPath);
            // 解压文件
            $zip = new ZipArchive();
            $result = $zip->open($dstFilename);
            if (!$result) {
                $this->outputJson(4, "素材文件解压失败", []);
            }
            $zip->extractTo($dstPath);
            $zip->close();

            // 解压文件处理
            $dirs = [];
            $handler = opendir($dstPath);
            while (($filename = readdir($handler)) != false) {
                if ($filename != "." && $filename != "..") {
                    // 删除苹果缓存目录
                    if ($filename == "__MACOSX") {
                        if (is_dir($dstPath . $filename)) {
                            $this->delDirAndFile($dstPath . $filename,true);
                        }
                    } else {
                        if (is_dir($dstPath . $filename)) {
                            $dirs[] = $dstPath . $filename;
                        }
                    }
                }
            }


            // 检验文件目录，是否存在引导文件index.html
            if (!file_exists($dstPath . "index.html") && count($dirs) == 1 && file_exists($dirs[0] . "/index.html")) {
                system("cp -a " . $dirs[0] . "/* " . dirname($dirs[0]));
                system("rm -rf " . $dirs[0]);
            }


            $ogs = new OpenGamesService();
            $ogs->rsyncToCDNServer(APP_NAME,'source',null,$gameid . "-" . $version . ".zip");
            $ogs->rsyncToCDNServer(APP_NAME,'games',null,$gameid . "/" . $version );
        } else {
            $this->outputJson(7, "上传失败", []);
        }

        // 添加数据到数据表
        $data['game_id'] = $gameid;
        $data['version'] = $version;
        $data['name'] = $file['name'];
        $data['size'] = $file['size'];
        $data['remark'] = $remark;
        $data['created_at'] = time();
        $data['status'] = GameHostingModel::STATUS_DEVELOPMENT;
        $data['uid'] = $this->_uid;
        $result = GameHostingModel::db()->add($data);
        if ($result) {
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            $data['id'] = $result;
            WZGameHostingModel::db()->add($data);
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
            // 添加游戏托管上传日志
            ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_upload_res, $gameid, $version);

            $this->outputJson(0, "上传成功", []);
        } else {
            $this->outputJson(8, "添加记录失败", []);
        }
    }

    // 小游戏 - 游戏托管 - 上传
    public function hostingUploadOld ()
    {
        // set_time_limit(120);
        // 检验游戏信息
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameid, 'game_host')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>";
            exit(0);
        }

        // 获取附件信息
        $file = $_FILES["material"];
        // 生成版本号，累加
        $version = GameHostingModel::getVersion($gameid);

        // 备注
        $remark = _g("remark");
        // 校验
        if ($remark == "") {
            $this->outputJson(9, "备注不能为空", []);
        }

        // 附件，校验
        if (is_uploaded_file($file["tmp_name"])) {
            // 判断文件大小是否超过
            if ($this->openGamesService->sizeOverflow($file["size"])) {
                $this->outputJson(1, "文件大小超过限制", []);
            }
            // 查看附件类型
            $extension = strrchr($file["name"], ".");
            if ($extension != ".zip") {
                $this->outputJson(2, "文件类型需为.zip", []);
            }
            // 生成目标文件名
            $dstFilename = $this->openGamesService->generateSourcePath() . $gameid . "-" . $version . ".zip";
            // 移动文件到指定位置
            $result = move_uploaded_file($file["tmp_name"], $dstFilename);
            if (!$result) {
                $this->outputJson(3, "移动文件失败", []);
            }
            // 解压目标目录
            $dstPath = $this->openGamesService->generateDstPath($gameid, $version);
            // 解压文件
            $zip = new ZipArchive();
            $result = $zip->open($dstFilename);
            if (!$result) {
                $this->outputJson(4, "素材文件解压失败", []);
            }
            $zip->extractTo($dstPath);
            $zip->close();

            // 解压文件处理
            $dirs = [];
            $handler = opendir($dstPath);
            while (($filename = readdir($handler)) != false) {
                if ($filename != "." && $filename != "..") {
                    // 删除苹果缓存目录
                    if ($filename == "__MACOSX") {
                        if (is_dir($dstPath . $filename)) {
                            $this->delDirAndFile($dstPath . $filename,true);
                        }
                    } else {
                        if (is_dir($dstPath . $filename)) {
                            $dirs[] = $dstPath . $filename;
                        }
                    }
                }
            }


            // 检验文件目录，是否存在引导文件index.html
            if (!file_exists($dstPath . "index.html") && count($dirs) == 1 && file_exists($dirs[0] . "/index.html")) {
                system("cp -a " . $dirs[0] . "/* " . dirname($dirs[0]));
                system("rm -rf " . $dirs[0]);
            }

            // 扫描木马
            // $clamavLog = $this->openGamesService->generateSourcePath() . $gameid . "-" . $version . "-clamav.log";
            // system("/usr/local/clamav/bin/clamscan -ir {$dstPath} > {$clamavLog}");
            // // 读取日志文件，判断是否存在木马
            // $safe = false;
            // $handle = fopen($clamavLog, "r");
            // if ($handle) {
            //     while (($line = fgets($handle)) !== false) {
            //         $line = trim($line);
            //         if (strpos($line, "Infected files: ") === 0) {
            //             $infectedFiles = intval(str_replace("Infected files: ", "", $line));
            //             if ($infectedFiles === 0) {
            //                 $safe = true;
            //             }
            //         }
            //     }
            //     fclose($handle);
            // } else {
            //     $this->outputJson(5, "扫描失败", []);
            // }
            // if (!$safe) {
            //     $this->outputJson(6, "存在木马程序", []);
            // }
            // 同步数据到CDN
            $rsyncPath = $this->openGamesService->getRsyncPath();
            $rsyncLog = $this->openGamesService->generateSourcePath() . $gameid . "-" . $version . "-rsync.log";
            system("/usr/bin/rsync -av {$rsyncPath} 10.10.7.223::XYX/ > {$rsyncLog}");
        } else {
            $this->outputJson(7, "上传失败", []);
        }

        // 添加数据到数据表
        $data['game_id'] = $gameid;
        $data['version'] = $version;
        $data['name'] = $file['name'];
        $data['size'] = $file['size'];
        $data['remark'] = $remark;
        $data['created_at'] = time();
        $data['status'] = GameHostingModel::STATUS_DEVELOPMENT;
        $data['uid'] = $this->_uid;
        $result = GameHostingModel::db()->add($data);
        if ($result) {
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            $data['id'] = $result;
            WZGameHostingModel::db()->add($data);
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

            // 添加游戏托管上传日志
            ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_upload_res, $gameid, $version);

            $this->outputJson(0, "上传成功", []);
        } else {
            $this->outputJson(8, "添加记录失败", []);
        }
    }

    public function delDirAndFile($path, $delDir = FALSE) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }

    // 小游戏 - 审核 - 操作页面
    public function audit ()
    {
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        $this->addCss("assets/open/css/gameReview.css");

        // 获取指定游戏的最后一个开发板
        $developments = $this->openGamesService->getAllDevelopmentVersion($gameid);
        $this->assign("developments", $developments);

        // 获取指定游戏的最后一个审核版
        $audit = $this->openGamesService->getLastAuditVersion($gameid);
        $this->assign("audit", $audit);

        // 获取指定游戏的上线版
        $production = $this->openGamesService->getLastProductionVersion($gameid);
        $this->assign("production", $production);

        // 可回退的版本
        $rollback = $this->openGamesService->getRollbackVersion($gameid);
        $this->assign("rollback", $rollback);

        $this->display("game/gameReview.html", "new", "isLogin");
    }

    // 小游戏 - 审核 - 修改审核状态
    public function auditChangeStatus ()
    {
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        // 类型
        $type = _g("type");
        // 游戏托管表主键信息
        $id = _g("gameHostingId");
        if(!$id || $id == "undefined"){
            $this->outputJson(2, "未选取版本", []);
        }
        // 执行结果
        $result = false;
        switch ($type) {
            case "test":
                // 将开发版设置为测试版
                $result = $this->openGamesService->setTestVersion($gameid, $id);

                if ($result) {
                    // 添加设置测试版日志
                    $version = GameHostingModel::db()->getRowById($id)['version'];
                    ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_set_test_ver, $gameid, $version);
                }

                break;
            case "development":
                // 将测试版设置为开发版
                $result = $this->openGamesService->setDevelopmentVersion($gameid, $id);

                if ($result) {
                    // 添加设置开发版日志
                    $version = GameHostingModel::db()->getRowById($id)['version'];
                    ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_set_dev_ver, $gameid, $version);
                }

                break;
            case "deleteDevelopment":
                // 删除开发版本
                $result = $this->openGamesService->deleteDevelopmentVersion($gameid, $id);
                break;
            case "production":
                // 设置为上线版
                // 校验密码是否正确
                if (PCK_AREA == 'en') {
                    $check = 1;
                } else {
                    $password = _g("password");
                    $check = $this->userService->checkPassword($this->_uid, $password);
                }
                
                if ($check == 1) {
                    $result = $this->openGamesService->setProductionVersion($gameid, $id);

                    if ($result) {
                        // 添加设置上线版日志
                        $version = GameHostingModel::db()->getRowById($id)['version'];
                        ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_set_pro_ver, $gameid, $version);
                    }

                }
                break;
        }

        if ($result) {
            $this->outputJson(0, "执行成功", []);
        } else {
            $this->outputJson(1, "执行失败", []);
        }
    }

    // 小游戏 - 审核 - 审核提交页面
    public function auditDetail ()
    {
        // 检验游戏
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo['id'];
        // $this->addCss("/assets/open/css/game-audit-submit-normalize.css");
        $this->addCss("/assets/open/css/reviewInfo.css");
        //获取为通过选项
        //身份证，和idcard是否齐全齐全则
        $uid = $this->_uid;
        $this->assign("developer_type", InformationModel::db()->getRow("uid = $uid")['type']);
        $where = "uid = $uid";
        // $res = InformationModel::db()->getRow($where);
        // if(!$res){
        //     $this->outputJson(0, "", []);
        // }

        $this->assign("softwareCopyright", 1);
        $this->assign("idcard", 1);
        $this->assign("business", 1);
        // if(!$res['idcard_img']||!$res['idcard2_img']){
        //     $this->assign("idcard", 1);
        // }else{
        //     $this->assign("idcard", 0);
        // }

        // if($res['type'] == InformationModel::TYPE_PERSON){
        //     $this->assign("business", 0);
        // }else {
        //     if($res['business']){
        //         $this->assign("business", 0);
        //     }else {
        //         $this->assign("business", 1);
        //     }
        // }


        $this->assign("softwareCopyright", 1);
        //软著是否上传过审核通过，没上传过则
        // if(GamesModel::checkCopyright($gameid)){
        //     $this->assign("softwareCopyright", 0);
        // }else{
        //     $this->assign("softwareCopyright", 1);
        // }
        //授权书可有可无
        $this->assign("authorization", 1);
        //获取游戏分类信息
        $gameCategory = $this->openGamesService->getGameCategory();

        $openUser = InformationModel::db()->getRow("uid=$uid");
        $otherImgs = [];
        $otherImgs['idcard_img'] = "";
        $otherImgs['idcard2_img'] = "";
        $otherImgs['business'] = "";
        $otherImgs['soft_copyright'] = "";
        $otherImgs['authorization'] = "";
        if ($openUser['idcard_img']) {
            $otherImgs['idcard_img'] = $this->getStaticFileUrl("idcard", $openUser['idcard_img']);
        }
        //身份证反面
        if ($openUser['idcard2_img']) {
            $otherImgs['idcard2_img'] = $this->getStaticFileUrl("idcard", $openUser['idcard2_img']);

        }
        //执照
        if ($openUser['business']) {
            $otherImgs['business'] = $this->getStaticFileUrl("business", $openUser['business']);
        }

        //软著
        if ($gameInfo['soft_copyright']) {
            $otherImgs['soft_copyright'] = $this->getStaticFileUrl("softcopyright", $gameInfo['soft_copyright']);
        }

        // 授权书
        $productionVersion = $this->openGamesService->getLastProductionVersion($gameid);
        if (isset($productionVersion) && $productionVersion) {
            $audit_info = json_decode($productionVersion['audit_info'], true);
            if (isset($audit_info['authorization']) && $audit_info['authorization']) {
                $otherImgs['authorization'] = $this->getStaticFileUrl("authorization", $gameInfo['authorization']);
            }
        }

        $this->assign("gameCategory", $gameCategory);
        // 画风
        $paintStyleDesc = $this->openGamesService->getPaintStyleDesc();
        $this->assign("paintStyleDesc", $paintStyleDesc);

        $this->assign("otherImgs", $otherImgs);

        $this->display("game/reviewInfo.html", "new", "isLogin");
    }

    //小游戏 - 审核 - 提交
    public function auditSubmit ()
    {
        $uid = $this->_uid;


        $gameInfo = $this->checkGame();
        //上传类型
        $imgtype = array('bmp', 'png', 'jpeg', 'jpg');

        $gameid = $gameInfo["id"];
        $id = _g("id");
        // $gameType = _g("gameType");
        // 画风
        // $paintStyle = _g("paintStyle");

        // $intro = _g("intro");
        // $screenDirection = _g("screenDirection");
        $backgroundColor = _g("backgroundColor");
        $versionUpdates = _g("versionUpdates");
        // $authorization = _g("authorization");
        // 控制访问
        $needIdCard1 = true;
        $needIdCard2 = true;
        $developerType = InformationModel::db()->getRow("uid = $uid")['type'];
        if ($developerType == InformationModel::TYPE_COMPANY) {
            $needBusiness = true;
        } else {
            $needBusiness = false;
        }
        
        $needSoftCoptyright = true;
        $needAuthorization = true;

        //如果上传了则不必须
        // $where = "uid = $uid";
        // $res = InformationModel::db()->getRow($where);
        // if($res['idcard_img'] && $res['idcard2_img']){
        //     $needIdCard1 = false;
        //     $needIdCard2 = false;
        // }
        // if($res['type'] == InformationModel::TYPE_PERSON){
        //     $needBusiness = false;
        // }else {
        //     if($res['business']){
        //         $needBusiness = false;
        //     }
        // }

        // if(InformationModel::checkIdcardAndBusiness($uid)){
        //     $needIdCard1 = false;
        //     $needIdCard2 = false;
        //     $needBusiness = false;
        // }

        // if(GamesModel::checkCopyright($gameid)){
        //     $needSoftCoptyright = false;
        // }

        if (!$id) {
            $this->outputJson(0, "上传失败，缺少id", []);
        }
        // if (!$gameType) {
        //     $this->outputJson(1, "上传失败，未选择类型", []);
        // }
        // if (!$paintStyle){
        //     $this->outputJson(13, "上传失败，未选择画风", []);
        // }
        // if (!$intro) {
        //     $this->outputJson(2, "上传失败，未添加介绍", []);
        // }
        // if (!$screenDirection) {
        //     $this->outputJson(3, "上传失败，未选择屏幕方向", []);
        // }
        // if (!$gameType) {
        //     $this->outputJson(4, "上传失败，未选择类型", []);
        // }
        //读取得到但是拿不出来
        if (!$backgroundColor) {
            $this->outputJson(12, "上传失败，未选择背景颜色", []);
        }
        $audit_info['background_color'] = $backgroundColor;
        $detail = GamesModel::db()->getRow("id=$gameid");
        if (!isset($_FILES["icon_256"]) || !is_uploaded_file($_FILES["icon_256"]["tmp_name"])) {
            if ($detail['list_img']) {
                $audit_info["list_img"] = $detail['list_img'];
            } else {
                $this->outputJson(5, "上传失败，应用图标（256 x 256）未上传", []);
            }

        }
        if (!isset($_FILES["icon_128"]) || !is_uploaded_file($_FILES["icon_128"]["tmp_name"])) {
            if ($detail['small_img']) {
                $audit_info["small_img"] = $detail['small_img'];
            } else {
                $this->outputJson(6, "上传失败，应用图标（128 x 128）未上传", []);
            }
        }
        if (!isset($_FILES["startup"]) || !is_uploaded_file($_FILES["startup"]["tmp_name"])) {
            if ($detail['index_reco_img']) {
                $audit_info["index_reco_img"] = $detail['index_reco_img'];
            } else {
                $this->outputJson(7, "上传失败，游戏启动页未上传", []);
            }
        }
        // 身份证执照判断
        $openUser = InformationModel::db()->getRow("uid=$uid");
        $data = [];
        if ($needIdCard1 && PCK_AREA != 'en') {
            if (!isset($_FILES["idcard1"]) || !is_uploaded_file($_FILES["idcard1"]["tmp_name"])) {
                if ($openUser['idcard_img']) {
                    $data['idcard_img'] = $openUser['idcard_img'];
                } else {
                    $this->outputJson(8, "上传失败，身份证正面未上传", []);
                }
            } else {
                $idcard1 = $this->uploadService->uploadFileByApp("idcard1", "idcard", "", 1);
                if ($idcard1['code'] != 200) {
                    $this->outputJson($idcard1['code'], $idcard1['msg']);
                }
                $data['idcard_img'] = $idcard1['msg'];
            }

        }

        if ($needIdCard2 && PCK_AREA != 'en') {
            if (!isset($_FILES["idcard2"]) || !is_uploaded_file($_FILES["idcard2"]["tmp_name"])) {
                if ($openUser['idcard2_img']) {
                    $data['idcard2_img'] = $openUser['idcard2_img'];
                } else {
                    $this->outputJson(9, "上传失败，身份证反面未上传", []);
                }
            } else {
                $idcard2 = $this->uploadService->uploadFileByApp("idcard2", "idcard", "", 1);
                if ($idcard2['code'] != 200) {
                    $this->outputJson($idcard2['code'], $idcard2['msg']);
                }
                $data['idcard2_img'] = $idcard2['msg'];
            }

        }

        if ($needBusiness && PCK_AREA != 'en') {
            if (!isset($_FILES["business"]) || !is_uploaded_file($_FILES["business"]["tmp_name"])) {
                if ($openUser['business']) {
                    $data['business'] = $openUser['business'];
                } else {
                    $this->outputJson(10, "上传失败，执照未上传", []);
                }
            } else {
                $business = $this->uploadService->uploadFileByApp("business", "business", "", 1);
                if ($business['code'] != 200) {
                    $this->outputJson($business['code'], $business['msg']);
                }
                $data['business'] = $business['msg'];
            }

        }

        // 软著判断
        if ($needSoftCoptyright && PCK_AREA != 'en') {
            // $productionVersion = $this->openGamesService->getLastProductionVersion($gameid);
            if (!isset($_FILES["softcopyright"]) || !is_uploaded_file($_FILES["softcopyright"]["tmp_name"])) {
                if (!$detail['soft_copyright']) {
                    $this->outputJson(11, "上传失败，软著未上传", []);
                }
            } else {

                $rs = $this->uploadService->uploadFileByApp("softcopyright", "softcopyright", "", 1);
                if ($rs['code'] != 200) {
                    $this->outputJson($rs['code'], $rs['msg']);
                }
                $games["soft_copyright"] = $rs['msg'];


                GamesModel::db()->upById($gameid, $games);
                // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                WZGamesModel::db()->upById($gameid, $games);
                // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
            }

        }

        // 应用图标256*256
        if (isset($_FILES["icon_256"])) {
            $rs = $this->uploadService->uploadFileByApp("icon_256", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $audit_info["list_img"] = $rs['msg'];
        }
        // 应用图标128*128
        if (isset($_FILES["icon_128"])) {
            $rs = $this->uploadService->uploadFileByApp("icon_128", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $audit_info["small_img"] = $rs['msg'];
        }
        // 游戏启动页
        if (isset($_FILES["startup"])) {
            $rs = $this->uploadService->uploadFileByApp("startup", "games", "", 1);
            if ($rs['code'] != 200) {
                $this->outputJson($rs['code'], $rs['msg']);
            }
            $audit_info["index_reco_img"] = $rs['msg'];
        }


        //授权书
        if (PCK_AREA != 'en') {
            if (isset($_FILES["authorization"]) && is_uploaded_file($_FILES["authorization"]["tmp_name"])) {
                $rs = $this->uploadService->uploadFileByApp("authorization", "authorization", "", 1);
                if ($rs['code'] != 200) {
                    $this->outputJson($rs['code'], $rs['msg']);
                }
                $audit_info["authorization"] = $rs['msg'];
            } else {
                $productionVersion = $this->openGamesService->getLastProductionVersion($gameid);
                if (!empty($productionVersion) && $productionVersion['audit_info'] != "") {
                    $arr = json_decode($productionVersion['audit_info'], true);
                    if (isset($arr['authorization'])) {
                        $audit_info['authorization'] = $arr['authorization'];
                    }
                }
            }
        }
        

        // 同步cdn
        $this->openGamesService->rsyncToServer();

        if (!empty($data)) {
            InformationModel::db()->update($data, " uid=" . $uid . " limit 1");
        }


        // $audit_info['gameType'] = $gameType;
        // $audit_info['paint_style'] = $paintStyle;
        // $audit_info['intro'] = $intro;
        // $audit_info['screenDirection'] = $screenDirection;
        $audit_info['versionUpdates'] = $versionUpdates;
        // $audit_info['softwareCopyrightImg'] = $softwareCopyright;
        // $audit_info['authorization'] = $authorization;
        $audit_info_josnStr = json_encode($audit_info);

        $update['audit_info'] = $audit_info_josnStr;
        $update['status'] = GameHostingModel::STATUS_AUDITING;
        $status = [
                    GameHostingModel::STATUS_AUDITING,
                    GameHostingModel::STATUS_AUDIT_SUCCESS,
                    GameHostingModel::STATUS_AUDIT_FAILURE,
                ];
        GameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_LOSE_EFFECT], "game_id='".$gameid."' AND status in (".implode(',', $status).") ORDER BY id DESC limit 1");
                //;
        GameHostingModel::db()->update($update, "id=$id limit 1");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_LOSE_EFFECT], "game_id='".$gameid."' AND status in (".implode(',', $status).") ORDER BY id DESC limit 1");
                //;
        WZGameHostingModel::db()->update($update, "id=$id limit 1");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        $this->openGamesService->setGamesStatus($gameid, GamesModel::$_status_3);
        // 添加提交审核日志
        $version = GameHostingModel::db()->getRowById($id)['version'];
        ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_submit_audit, $gameid, $version);

        $this->outputJson(200, "提审成功", "");
    }

    public function delOne(){
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];

        $id = _g('id');
        // $uid = $this->_uid;

        $res = $this->openGamesService->delOneVersion($gameid, $id);
        if(!$res){
            $this->outputJson(0, '删除失败', []);
        }

        $this->outputJson(200, 'succ', []);
    }

    public function download(){
        $gameInfo = $this->checkGame();
        $gameid = $gameInfo["id"];
        $id = _g('id');

        $res = GameHostingModel::db()->getRow(" game_id=$gameid and id=$id");
        if(!$res || !$res['version']){
            $this->outputJson(0, '不存在该版本', []);
        }

        $fileName = $this->openGamesService->getCDNSourcePath() . $gameid . "-" . $res['version'] . ".zip";

        LogLib::appWriteFileHash("FileDownloadUrl: ".$fileName);

        header( "Content-Disposition:  attachment;  filename=".basename($fileName)); //告诉浏览器通过附件形式来处理文件
        header('Content-Length: ' . filesize($fileName)); //下载文件大小
        readfile($fileName);  //读取文件内容
    }

    /**
     * 更新单个游戏的缓存信息;
     * @param $gameid
     */
    public function updateGameCatch($gameid){
        $gameCatchService = new gamesCatchService();
        $gameInfo = GamesModel::db()->getById($gameid);
        if(!empty($gameInfo)){
            $gameCatchService->addGameRow($gameid, $gameInfo);
        }
    }

    public function getHosting(){
        $length = 5;
        $page = _g("page");
        $gameid = _g("gameid");
        $start = ($page-1) * $length;
        $list = GameHostingModel::db()->getAll( "game_id = $gameid " );
        if (!$list) {
            $this->outputJson(200, "succ", ["totalPage"=>0, "list"=>[]]);
        }
        $count = count($list);
        $totalPage = ceil($count/$length);
        if ($page > $totalPage) {
            $list = [];
        }else{
            $list = GameHostingModel::db()->getAllBySql("
	    		SELECT id,game_id,version,name,size,remark,created_at,status,uid,audit_info FROM open_game_hosting WHERE game_id = {$gameid} ORDER BY created_at DESC LIMIT $start,$length;
			");
            foreach ($list as &$item) {
                $item["size_mb"] = number_format($item["size"] / pow(1024, 2), 2);
                $item["created_at_date"] = date("Y-m-d H:i", $item["created_at"]);
                switch ($item["status"]) {
                    case GameHostingModel::STATUS_DEVELOPMENT:
                        $item["status_msg"] = "开发版本";
                        break;
                    case GameHostingModel::STATUS_TEST:
                        $item["status_msg"] = "测试中";
                        break;
                    case GameHostingModel::STATUS_AUDIT_FAILURE:
                        $item["status_msg"] = "审核不通过";
                        break;
                    case GameHostingModel::STATUS_AUDIT_SUCCESS:
                        $item["status_msg"] = "审核已通过";
                        break;
                    case GameHostingModel::STATUS_AUDITING:
                        $item["status_msg"] = "审核中";
                        break;
                    case GameHostingModel::STATUS_PRODUCTION:
                        $item["status_msg"] = "线上版本";
                        break;
                    case GameHostingModel::STATUS_LOSE_EFFECT:
                        $item["status_msg"] = "失效";
                        break;
                    case GameHostingModel::STATUS_HAD_PRODUCTED:
                        $item['status_msg'] = "历史上线版本";
                        break;
                }
            }
        }
        $this->outputJson(200, "succ", ["totalPage"=>$totalPage, "list"=>$list]);
    }

}
