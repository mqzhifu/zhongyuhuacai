<?php

class OpenGamesService
{
    // 重置密钥token key
    const RESET_TOKEN_KEY = "CHdr0KuD50jFfGxvVoAi49ubACLwNsJ7";

    // cdn地址
    const CDN_URL = 'mgres.kaixin001.com.cn';

    /**
     * 获取用户名下游戏信息
     * @param $uid
     * @param $gid
     * @param $otherWhere
     * @return array
     */
    public function getUserGamesInfo ($uid, $gid = 0, $otherWhere = '', $limit='')
    {

        $info = [];
        if ($uid) {
            $whereUserGame = "`uid`={$uid} AND `role`<3 $limit";
            $data = UserGamesModel::db()->getAll($whereUserGame);

            if ($data) {
                foreach ($data as $k => $v) {
                    $allGid[] = intval($v['game_id']);
                }
            }
            if (!isset($allGid)) {
                return [];
            }

            $where = " `status`<>4 ";
            if ($otherWhere) {
                $where .= ' AND ' . $otherWhere;
            }

            $gid = intval($gid);
            //如果gid有值，则获取单个游戏信息
            if ($gid) {
                if (!in_array($gid, $allGid, true)) {
                    return [];
                }
                $where = " `id`={$gid} AND " . $where . " LIMIT 1 ";
                $func = 'getRow';
            } else {
                $in = implode(',', $allGid);
                $where .= " AND `id` IN({$in}) ORDER BY id asc ";
                $func = 'getAll';
            }
            $info = GamesModel::db()->$func($where);
        }
        return $info;
    }

    /**
     * 添加新游戏
     * @param $uid
     * @param $gameName
     * @return array
     */
    public function addGame ($uid, $gameName)
    {
        $info['result'] = false;
        if ($uid && $gameName) {
            $time = time();
            $addInfo = [
                'name' => $gameName,
                'app_secret' => $this->getSecret(),
                'uid' => $uid,
                'status' => 1,//添加时默认为开发中
                'is_online' => 0,//添加时默认为不上线
                'a_time' => $time,
                'u_time' => $time,
            ];
            $gid = GamesModel::db()->add($addInfo);
            if ($gid) {
                // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                $addInfo['id'] = $gid;
                WZGamesModel::db()->add($addInfo);
                // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
                $addInfo1 = [
                    'game_id' => $gid,
                    'uid' => $uid,
                    'role' => UserGamesModel::$_user_admin,
                    'a_time' => $time,
                ];
                UserGamesModel::db()->add($addInfo1);
                $info['game_name'] = $gameName;
                $info['game_id'] = $gid;
                $info['result'] = true;

                // 四期迭代，新添加的游戏写入缓存中 add by XiaHB 2019/04/10  Begin;
                $gameCatchService = new gamesCatchService();// 需要通过主键ID反查games表取出对应游戏信息;
                $gameInfo = GamesModel::db()->getById($gid);
                if( !empty($gameInfo) && is_array($gameInfo) ){
                    $gameCatchService->addGameRow($gid, $gameInfo);// 添加成功的时候返回值1，暂不对结果进行判断;
                }
                // 四期迭代，新添加的游戏写入缓存中 add by XiaHB 2019/04/10    End;
            }
        } else {
            $info['msg'] = '游戏名无效或用户未登录！';
        }
        return $info;
    }

    public function getSecret ()
    {
        $str = md5(substr(str_shuffle('abcdefghigklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), 0, 10));
        return md5($str . str_replace(' ', '', microtime()));
    }

    /**
     * 更新
     * @param $uid
     * @param $gid
     * @param $updateInfo
     * @return mixed
     */
    public function updateGame ($uid, $gid, $updateInfo)
    {
        $info['result'] = false;
        $have = $this->getUserGamesInfo($uid, $gid);
        if ($have) {
            $updateInfo['u_time'] = time();
            $r = GamesModel::db()->upById($gid, $updateInfo);
            if ($r) {
                $info['result'] = true;
                // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
                WZGamesModel::db()->upById($gid, $updateInfo);
                // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
            }
        }
        return $info;
    }

    /**
     * 软删除 将status置为4即可
     * @param $uid
     * @param $gid
     * @return mixed
     */
    public function deleteGame ($uid, $gid)
    {
        $updateInfo['status'] = 4;
        return $this->updateGame($uid, $gid, $updateInfo);
    }

    // 获取素材目录
    public function getMaterialPath ()
    {
        $path = get_cdn_base_dir() . "/" . get_upload_cdn_evn() . "/open/";

        $this->checkDirectory($path);
        return $path;
    }

    // 获取素材目录
    public function getMaterialPathOld ()
    {
        $path = BASE_DIR . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "xyx" . DIRECTORY_SEPARATOR;
        if (ENV == 'release') {
            // 正式版本
            $path .= "pro" . DIRECTORY_SEPARATOR;
        } else {
            // 开发版
            $path .= "dev" . DIRECTORY_SEPARATOR;
        }
        $this->checkDirectory($path);
        return $path;
    }

    // 获取游戏源码目录
    public function getGamesPath ()
    {
        $path = $this->getMaterialPath() . "games" . DIRECTORY_SEPARATOR;
        $this->checkDirectory($path);
        return $path;
    }

    // 生成目标目录
    public function generateDstPath ($gameid, $version)
    {
        $path = $this->getGamesPath() . $gameid . DIRECTORY_SEPARATOR . $version . DIRECTORY_SEPARATOR;
        $this->checkDirectory($path);
        return $path;
    }

    // 生成源文件目录
    public function generateSourcePath ()
    {
        $path = $this->getMaterialPath() . "source" . DIRECTORY_SEPARATOR;
        $this->checkDirectory($path);
        return $path;
    }

    public function getCDNSourcePath()
    {
        $path = $this->getCDNUrl() . APP_NAME . DIRECTORY_SEPARATOR . "source" . DIRECTORY_SEPARATOR;
        $this->checkDirectory($path);
        return $path;
    }

    // 获取同步目录
    public function getRsyncPath ()
    {
        $path = BASE_DIR . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "xyx" . DIRECTORY_SEPARATOR;
        if (ENV == 'release') {
            // 正式版本
            $path .= "pro";
        } else {
            // 开发版
            $path .= "dev";
        }
        return $path;
    }

    // 同步资源到CDN
    public function rsyncToServer ()
    {
        // 同步数据到CDN
        $rsyncPath = $this->getRsyncPath();
        // echo "/usr/bin/rsync -av {$rsyncPath} 10.10.7.223::XYX/ > /dev/null";
        system("/usr/bin/rsync -av {$rsyncPath} 10.10.7.223::XYX/ > /dev/null");
    }

    // 检查目录，若不存在则建立目录
    public function checkDirectory ($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    // 判断文件大小是否溢出
    public function sizeOverflow ($size)
    {
        $limit = 52428800; // 50 * 1024 * 1024 = 50m
        return $size > $limit ? true : false;
    }

    // 获取游戏分类信息
    public function getGameCategory ()
    {
        $category = GameCategoryModel::getCategory();
        return array_column($category, 'name_cn', 'id');
        // $map = [];
        // $tree = [];
        // foreach ($category as &$item) {
        //     $map[$item["id"]] = &$item;
        // }
        // foreach ($category as &$item) {
        //     $parent = &$map[$item["pid"]];
        //     if ($parent) {
        //         $parent["child"][] = &$item;
        //     } else {
        //         $tree[] = &$item;
        //     }
        // }

        // return $tree;
        // $category = GamesCategoryNewModel::getCategory();
        // if($category && isset($category[0]['category_id']) && isset($category[0]['name_cn'])){
        //     return array_column($category, 'name_cn', 'category_id');
        // }
        // return [];

    }

    // 检验用户是否指定游戏权限
    public function getGameInfo ($uid, $gameid)
    {
        if (empty($gameid)) {
            return [];
        }

        return $this->getUserGamesInfo($uid, $gameid);
    }

    // 生成重置key的token
    public function generateResetKeyToken ($uid, $gameid)
    {
        $time = time();
        $token = md5($time . self::RESET_TOKEN_KEY);
        $expire = 600;      // 10分钟
        $key = "reset_token_" . $uid . "_" . $gameid;

        RedisPHPLib::set($key, $token, $expire);

        return $token;
    }

    // 检验重置key的token是否有效
    public function verifyResetToken ($uid, $gameid, $token)
    {
        $key = "reset_token_" . $uid . "_" . $gameid;
        $checkToken = RedisPHPLib::get($key);
        if ($checkToken != "" && $checkToken == $token) {
            return true;
        }
        return false;
    }

    // 获取文件的后缀名
    public function getFileExtension ($filename)
    {
        return strrchr($filename, ".");
    }

    // 获取开放平台静态文件地址URL
    public function getStaticUrl ()
    {
        return $this->getCDNUrl() . "open/";
    }

    // 获取开放平台静态文件地址URL
    public function getStaticUrlOld ()
    {
        return $this->getCDNUrl() . "upload/open/";
    }

    // 旧 - 平台文件地址访问
    public function getOldStaticUrl ()
    {
        return $this->getCDNUrl() . "upload/instantplayadmin/";
    }


    // 旧图片访问地址，作老版兼容
    public function getOldStaticImageUrl ($module,$path,$appName = APP_NAME,$protocol = 'https')
    {
        return get_static_url($protocol) . "xyx/" . get_upload_cdn_evn() . "/upload/$appName/$path";
    }


    // 获取app需要的图片地址
    public function getAppStaticImageUrl ($uri)
    {
        if(strpos($uri, 'games') !== false && strpos($uri, 'icon') !== false ){
            return $this->getCDNUrlOld() . "upload/instantplayadmin/".$uri;
        }elseif(strpos($uri, 'games') !== false && strpos($uri, 'startup') !== false ){
            return $this->getCDNUrlOld() . "upload/instantplayadmin/".$uri;
        }else{
            $url = get_static_file_url_by_app('games',$uri,'open');
//            var_dump($url);exit;
            return $url;
        }
//            return $this->getOldStaticUrl() . $uri;
    }

    // 开放平台静态文件目录
    public function getStaticPath ()
    {
        return $this->getMaterialPath() . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR . "open" . DIRECTORY_SEPARATOR;
    }

    // 旧 - 平台文件地址
    public function getOldStaticPath ()
    {
        return $this->getMaterialPath() . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR . "instantplayadmin" . DIRECTORY_SEPARATOR;
    }

    // 获取游戏版本号对应的游戏地址
    public function getGameUrl ($gameid, $version)
    {
        $url = get_static_url("https") .get_cdn_xyx_dir() . "/" . get_upload_cdn_evn() .   "/open/games/" . $gameid . "/" . $version . "/";
        return $url;
    }

    // 获取游戏版本号对应的游戏地址
    public function getGameUrlOld ($gameid, $version)
    {
        $url = "https://mgres.kaixin001.com.cn/xyx/";
        if (ENV == "release") {
            $url .= "pro/";
        } else {
            $url .= "dev/";
        }
        $url .= "games/" . $gameid . "/" . $version . "/";
        return $url;
    }

    // 通过指定UID获取所拥有的游戏
    public function getGameByDeveloper ($uid)
    {
        // 自己创建的游戏
        $result = $this->getUserGamesInfo($uid);
        // 获取游戏ID列表
        $games = [];
        foreach ($result as $item) {
            $games[$item["id"]] = $item;
        }
        $gameids = array_keys($games);
        // 获取版本信息
        $list = GameHostingModel::getGameUrl($gameids, [
            GameHostingModel::STATUS_PRODUCTION,
            GameHostingModel::STATUS_TEST,
        ]);
        // 设置返回数据
        $data = [];
        foreach ($list as $value) {
            if (!isset($games[$value["game_id"]])) {
                continue;
            }
            $game = $games[$value["game_id"]];
            $item = [];
            $item["id"] = $game["id"];
            $item["name"] = $game["name"];
            $item["icon_256"] = $game["icon_256"];
            $item["background_color"] = $game["background_color"];
            $item["test"] = ($value["status"] == GameHostingModel::STATUS_TEST) ? 1 : 0;
            $item["pay_url"] = $value["pay_url"];
            $data[] = $item;
        }

        return $data;
    }

    // 通过指定UID获取所拥有的待审核游戏
    public function getGameByOfficialAdmin ($uid)
    {
        // 判断用户是否为官方管理员 TODO

        // 获取所有待审核的游戏信息
        $list = GameHostingModel::getGameUrl(0, [
            GameHostingModel::STATUS_AUDITING,
        ]);
        // 获取游戏信息
        $gameids = array_column($list, "game_id");
        // 获取游戏信息
        $result = GamesModel::db()->getByIds(implode(", ", $gameids));
        $games = [];
        foreach ($result as $item) {
            $games[$item["id"]] = $item;
        }
        // 设置返回值
        $gamesService = new GamesService();
        $data = [];
        foreach ($list as $value) {
            if (!isset($games[$value["game_id"]])) {
                continue;
            }
            $game = $games[$value["game_id"]];
            $item = [];
            $item["id"] = $game["id"];
            $item["name"] = $game["name"];
            $item["icon_256"] = $game["icon_256"];
            $item["background_color"] = $game["background_color"];
            $item["played_num"] = $gamesService->getPlayedNum($game["id"]);
            $item["pay_url"] = $value["pay_url"];
            $data[] = $item;
        }

        return $data;
    }

    // 获取所有已上线的游戏
    public function getGameByOnline ()
    {
        // 查询所有已上线的游戏信息
        $where = "is_online=1";
        $result = GamesModel::db()->getAll($where);
        $games = [];
        foreach ($result as $item) {
            $games[$item["id"]] = $item;
        }
        $gameids = array_keys($games);

        // 查询有上线版本的游戏
        $list = GameHostingModel::getGameUrl(0, [
            GameHostingModel::STATUS_PRODUCTION,
        ]);

        // 返回数据
        $gamesService = new GamesService();
        $data = [];
        foreach ($list as $value) {
            if (!isset($games[$value["game_id"]])) {
                continue;
            }
            $game = $games[$value["game_id"]];
            $item = [];
            $item["id"] = $game["id"];
            $item["name"] = $game["name"];
            $item["icon_256"] = $game["icon_256"];
            $item["background_color"] = $game["background_color"];
            $item["played_num"] = $gamesService->getPlayedNum($game["id"]);
            $item["pay_url"] = $value["pay_url"];
            $data[] = $item;
        }

        return $data;
    }

    // 获取指定游戏的最后一个开发版本信息
    public function getLastDevelopmentVersion ($gameid)
    {
        $data = GameHostingModel::getLastDevelopmentVersion($gameid);
        $data = $this->foramtVersion($data);
        return $data;
    }

    public function getAllDevelopmentVersion ($gameid)
    {
        $datas = GameHostingModel::getAllDevelopmentVersion($gameid);
        foreach ($datas as $key => $data) {
            $data = $this->foramtVersion($data);
            $datas[$key] = $data;
        }

        return $datas;
    }

    // 获取指定游戏的最后一个审核版本信息
    public function getLastAuditVersion ($gameid)
    {
        $data = GameHostingModel::getLastAuditVersion($gameid);
        $data = $this->foramtVersion($data);
        return $data;
    }

    // 获取指定游戏的最后一个上线版本
    public function getLastProductionVersion ($gameid)
    {
        $data = GameHostingModel::getLastProductionVersion($gameid);
        $data = $this->foramtVersion($data);
        return $data;
    }

    // 指定游戏可回退的3个版本
    public function getRollbackVersion ($gameid)
    {
        $data = GameHostingModel::getRollbackVersion($gameid);
        foreach ($data as $key => $value) {
            $data[$key] = $this->foramtVersion($value);
        }
        return $data;
    }

    // 添加版本的相关信息
    private function foramtVersion ($data)
    {
        if (!empty($data)) {
            $data["created_at_date"] = date("Y-m-d H:i", $data["created_at"]);
            $data["created_at_date2"] = date("Y/m/d H:i", $data["created_at"]);
            $userService = new UserService();
            $uinfo = $userService->getUinfoById($data["uid"]);
            // 用户昵称
            $data["uname"] = $uinfo["nickname"];
        }

        return $data;
    }

    // 获取CDN地址
    public function getCDNUrl ()
    {
        $url = get_static_url("https").get_cdn_xyx_dir()."/".get_upload_cdn_evn() . "/";
        return $url;
    }

    public function getCDNUrlOld ()
    {
        $url = "https://" . self::CDN_URL . '/xyx/';
        if (ENV == "release") {
            $url .= "pro/";
        } else {
            $url .= "dev/";
        }
        return $url;
    }

    // 将游戏版本设置为测试版
    public function setTestVersion ($gameid, $id)
    {
        
        $db = GameHostingModel::db();
        // 将指定游戏的历史测试版本设置为开发版

        $db->update(["status"=>GameHostingModel::STATUS_DEVELOPMENT], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_TEST . "' limit 1");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        $data['id'] = $result;
        WZGameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_DEVELOPMENT], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_TEST . "' limit 1");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        // 将指定的版本设置为测试版
        $db->update(["status"=>GameHostingModel::STATUS_TEST], "game_id='" . $gameid . "' AND id='" . $id . "' limit 1");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_TEST], "game_id='" . $gameid . "' AND id='" . $id . "' limit 1");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        // 执行事务
        try {
            // $transaction->commit();
            
            $result = true;
        } catch (Exception $exception) {
            $result = false;
        }
        if ($result == 1) {
            //设置游戏状态 games表
            $this->setGamesStatus($gameid, GamesModel::$_status_2);
        }
        return $result;
    }

    // 将游戏版本设置为开发版
    public function setDevelopmentVersion ($gameid, $id)
    {
        

        $result = GameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_DEVELOPMENT], "game_id='" . $gameid . "' AND id='" . $id . "' limit 1 ");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_DEVELOPMENT], "game_id='" . $gameid . "' AND id='" . $id . "' limit 1 ");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        
        if ($result == 1) {
            //设置游戏状态 games表
            $this->setGamesStatus($gameid, GamesModel::$_status_1);
        }
        return $result;
    }

    // 删除开发版本
    public function deleteDevelopmentVersion ($gameid, $id)
    {
        // 使用开心网数据库中间层事务功能
        // $db = \DKXI_Database::factory('apps');
        // 删除指定游戏的指定版本号
        $status = [
            GameHostingModel::STATUS_DEVELOPMENT,
            GameHostingModel::STATUS_TEST,
        ];
        // $sql = "DELETE FROM " . GameHostingModel::TABLE . " WHERE id='" . $id . "' AND game_id='" . $gameid . "' AND status IN (" . implode(", ", $status) . ") LIMIT 1";
        // $result = $db->execute(0, $sql);
        $result = GameHostingModel::db()->delete(" id='" . $id . "' AND game_id='" . $gameid . "' AND status IN (" . implode(", ", $status) . ") limit 1" , GameHostingModel::TABLE);
        if ($result == 1) {
            //设置游戏状态 games表
            $this->setGamesStatus($gameid, GamesModel::$_status_1);
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            WZGameHostingModel::db()->delete(" id='" . $id . "' AND game_id='" . $gameid . "' AND status IN (" . implode(", ", $status) . ") limit 1" , GameHostingModel::TABLE);
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        }
        return $result == 1 ? true : false;
    }

    // 将游戏版本设置为审核版
    public function setAuditVersion ($gameid, $id)
    {
        // 使用开心网数据库中间层事务功能
        // $db = \DKXI_Database::factory('apps');
        // 判断当前游戏是否有审核版
        // $sql = "SELECT COUNT(1) AS `count` FROM " . GameHostingModel::TABLE . " WHERE game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_AUDITING . "'";
        // $result = $db->query(0, $sql);
        $result = GameHostingModel::db()->getAll(" game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_AUDITING . "'");
        $result = $result->toArray();
        $count = $result["rows"][0][0];
        if ($count != 0) {
            return false;
        }
        // $sql = "UPDATE " . GameHostingModel::TABLE . " SET status='" . GameHostingModel::STATUS_AUDITING . "' WHERE id='" . $id . "' AND game_id='" . $gameid . "' LIMIT 1";
        // $result = $db->execute(0, $sql);
        $result = GameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_AUDITING], "id='" . $id . "' AND game_id='" . $gameid . "' limit 1");
        if ($result == 1) {
            //设置游戏状态 games表
            $this->setGamesStatus($gameid, GamesModel::$_status_3);
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            WZGameHostingModel::db()->update(["status"=>GameHostingModel::STATUS_AUDITING], "id='" . $id . "' AND game_id='" . $gameid . "' limit 1");
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        }
        return $result == 1 ? true : false;
    }

    // 将游戏版本设置为上线版
    public function setProductionVersion ($gameid, $id)
    {
        
        $db = GameHostingModel::db();
        // 将指定游戏线上版设置为审核成功
        
        // $transaction->queue($sql);
        $db->update(['status'=>GameHostingModel::STATUS_HAD_PRODUCTED], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_PRODUCTION . "' limit 1 ");
        // 设置指定版本上线版
        
        $db->update(['status'=>GameHostingModel::STATUS_PRODUCTION], " id='" . $id . "' AND game_id='" . $gameid . "' AND (status='" . GameHostingModel::STATUS_AUDIT_SUCCESS . "' or status='" . GameHostingModel::STATUS_HAD_PRODUCTED . "') limit 1"); 

        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_HAD_PRODUCTED], "game_id='" . $gameid . "' AND status='" . GameHostingModel::STATUS_PRODUCTION . "' limit 1 ");
        WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_PRODUCTION], " id='" . $id . "' AND game_id='" . $gameid . "' AND (status='" . GameHostingModel::STATUS_AUDIT_SUCCESS . "' or status='" . GameHostingModel::STATUS_HAD_PRODUCTED . "') limit 1"); 
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里

        // 获取主键的版本号
        $hosting = GameHostingModel::db()->getById($id);
        $audit_info = json_decode($hosting['audit_info'], true);

        $updates = [];
        $updates['status'] = 8;//已上綫
        $updates['is_online'] = GamesModel::$_online_true;
        $updates['play_url'] = $this->getGameUrl($gameid, $hosting["version"]);
        // if(isset($audit_info['gameType']) && $audit_info['gameType']){
        //     $updates['category'] = $audit_info['gameType'];
        // }
        // 画风
        // if(isset($audit_info['paint_style']) && $audit_info['paint_style']){
        //     $updates['paint_style'] = $audit_info['paint_style'];
        // }
        // $updates['status'] =
        if(isset($audit_info['small_img']) && $audit_info['small_img']){
            $updates['small_img'] = $audit_info['small_img'];
        }
        if(isset($audit_info['list_img']) && $audit_info['list_img']){
            $updates['list_img'] =  $audit_info['list_img'];
        }
        if(isset($audit_info['index_reco_img']) && $audit_info['index_reco_img']){
            $updates['index_reco_img'] = $audit_info['index_reco_img'];
        }
        // if(isset($audit_info['screenDirection']) && $audit_info['screenDirection']){
        //     $updates['screen'] = $audit_info['screenDirection'];
        // }
        // if(isset($audit_info['intro']) && $audit_info['intro']){
        //     $updates['summary'] =  $audit_info['intro'];
        // }
        // $updates['soft_copyright'] = 
        // 修改游戏中的play_url
        // $sql = "UPDATE games SET play_url='" . $this->getGameUrl($gameid, $hosting["version"]) . "' WHERE id='" . $gameid . "' LIMIT 1";
        // $transaction->queue($sql);
        GamesModel::db()->update($updates, " id='" . $gameid . "' limit 1");
        // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
        WZGamesModel::db()->update($updates, " id='" . $gameid . "' limit 1");
        // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        
        // 执行事务
        try {
            $result = true;
            //设置游戏状态 games表
            $this->setGamesStatus($gameid, GamesModel::$_status_8);
        } catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }


    public function getUserGameSoftwareCopyright ($uid, $gameId)
    {

    }

    /**
     * 判断用户是否有某项功能权限
     * @param  [int]  $uid
     * @param  [int]  $gameId
     * @param  [string]  $funcName  [功能名]
     * @return boolean
     */
    public function hasAuthority ($uid, $gameId, $funcName)
    {
        $user = UserGamesModel::db()->getRow(" game_id=$gameId and uid=$uid ");
        if (!$user) {
            # 检查games表是否有该用户，有则将其加入user_games表
            if (!$us = GamesModel::db()->getRow(" id=$gameId and uid=$uid ")) {
                return false;
            }
            $user = [
                'game_id' => $gameId,
                'uid' => $uid,
                'role' => UserGamesModel::$_user_admin,
                'a_time' => time()
            ];
            UserGamesModel::db()->add($user);
        }
        $role = $user['role'];
        $auths = UserGamesModel::getAuths();

        if (in_array($funcName, $auths[$role])) {
            return true;
        }
        return false;
    }


    /**
     * 设置状态
     */
    public function setGamesStatus ($gid, $status)
    {
        $onlineStatus = GamesModel::$_status_8;
        $h = GamesModel::db()->getOne("`id`={$gid} AND `status`={$onlineStatus} LIMIT 1", "", "id");

        if (!$h) {
            $updateInfo['u_time'] = time();
            $updateInfo['status'] = $status;
            GamesModel::db()->upById($gid, $updateInfo);
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            WZGamesModel::db()->upById($gid, $updateInfo);
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        }
    }

    /**
     * 获取游戏名称;
     * @param $name
     * @return bool
     */
    public function getGamesName ($name)
    {
        $sql = "select * from games where name = '{$name}' limit 1";
        $h = GamesModel::db()->query($sql);
        if ($h) {
            return true;
        }else{
            return false;
        }
    }

    public function getReadStatus($uid){
        $sql = "select * from open_notification where uid = '{$uid}' and is_read != 1 ;";
        $h = GamesModel::db()->query($sql);
        if ($h) {
            return true;
        }else{
            return false;
        }
    }
    public function getUnreadCount($uid){
        $cnt = NotificationModel::db()->getCount("uid = $uid and is_read != 1");
        return $cnt;
    }

    public function delOneVersion($game_id,$id){
        $result = GameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_LOSE_EFFECT], "game_id='" . $game_id. "' and id='".$id."' limit 1 ");
        if ($result == 1) {
            //设置游戏状态 games表
            $this->setGamesStatus($game_id, GamesModel::$_status_1);
            // start----------------2019-07-25 xuren 同步到wzgame小游戏库里
            WZGameHostingModel::db()->update(['status'=>GameHostingModel::STATUS_LOSE_EFFECT], "game_id='" . $game_id. "' and id='".$id."' limit 1 ");
            // end----------------2019-07-25 xuren 同步到wzgame小游戏库里
        }
        return $result;
    }


    public function hasLinkPower($uid, $power)
    {
        if ($user = LinkGamePowerModel::db()->getRow("uid = $uid")) {
            $powers = explode(',',$user['role']);
            if (in_array($power, $powers)) {
                return true;
            }
        }

        return false;
    }

    // 通过uid获取游戏id数组
    public function getOnLineGameidsByUid($uid){
        $res = GamesModel::db()->getAllBySQL("select id from games where uid=$uid and status!=4 and is_online=1 ");
        return array_column($res, "id");
    }

    // 获取所有游戏画风描述
    public function getPaintStyleDesc ()
    {
        $items = GamePaintStyleModel::getPaintStyle();

        if($items && isset($items[0]['style_id']) && isset($items[0]['name_cn'])){
            return array_column($items, 'name_cn', 'style_id');
        }
        return [];
    }
}