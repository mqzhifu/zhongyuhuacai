<?php

/**
 * @Author: Kir
 * @Date:   2019-03-13 10:38:36
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-05-31 17:53:45
 */


/**
 * 
 */
class MemberCtrl extends BaseCtrl
{
	
	/**
     * 成员管理
     */
    public function show ()
    {
        $gameInfo = $this->checkGame();
        $gameId = $gameInfo['id'];
        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameId, 'game_manage')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>"; 
            exit(0);
        }
        $this->addCss("/assets/open/css/member.css");

        $this->display("member.html", "new", "isLogin");
    }


    public function getMembers ()
    {

        $gameInfo = $this->checkGame();
        $gameId = $gameInfo['id'];
        $members = UserGamesModel::db()->getAllBySql("select oug.*,u.nickname,u.avatar from (select * from open_user_games where game_id = $gameId ) as oug left join user as u on oug.uid = u.id ");

        if (!$members) {
            $this->outputJson(200, "succ", []);
        }

        $adminUsers = [];
        $devUsers = [];
        $testUsers = [];

        foreach ($members as $us) {
            $us['avatar'] = getUserAvatar($us);

            switch ($us['role']) {
                case UserGamesModel::$_user_admin:
                    $adminUsers[] = $us;
                    break;
                case UserGamesModel::$_user_dev:
                    $devUsers[] = $us;
                    break;
                case UserGamesModel::$_user_test:
                    $testUsers[] = $us;
                    break;
                default:
                    # code...
                    break;
            }
        }

        $this->outputJson(200, "succ", [
            // [剩余可添加人数，成员列表]
            'adminUsers'=>[0,$adminUsers], 
            'devUsers'=>[50-count($devUsers),$devUsers], 
            'testUsers'=>[100-count($testUsers),$testUsers]
        ]);
    }


    public function addMember()
    {
        $gameId = _g("gameid");
        $addUid = _g('addUid');
        $role = _g('role');

        // 权限检查
        if (!$this->openGamesService->hasAuthority($this->_uid, $gameId, 'game_manage')) {
            echo"<script>alert('您没有该权限');history.go(-1);</script>"; 
            exit(0);
        }
        
        $roles = array_keys(UserGamesModel::getAuths());

        if (!$addUid || !$gameId || !in_array($role, $roles)) {
            $this->outputJson(0, "参数有误", []);
        }

        if (!UserModel::db()->getRowById($addUid)) {
            $this->outputJson(0, "搜索不到该用户，请输入正确的ID号", []);
        }

        if (UserGamesModel::db()->getRow(" game_id = $gameId and uid=$addUid ")) {
            $this->outputJson(0, "该用户是本项目成员，无需重复绑定", []);
        }

        $member = [
            'game_id' => $gameId,
            'uid' => $addUid,
            'role' => $role,
            'a_time' => time()
        ];

        if (UserGamesModel::db()->add($member)) {

            // 添加成员日志
            if ($role == UserGamesModel::$_user_dev) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_add_dev_user, $gameId, null, json_encode(['operateUid'=>$addUid]));
            } elseif ($role == UserGamesModel::$_user_test) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_add_test_user, $gameId, null, json_encode(['operateUid'=>$addUid]));
            }
            
            $this->outputJson(1, "添加成功", []);
        }

    }

    public function delMember()
    {
        $gameId = _g("gameid");
        $delUid = _g('delUid');
        $uid=$this->_uid;
        $user = UserGamesModel::db()->getRow(" game_id = $gameId and uid=$uid ");

        if ($user['role'] != UserGamesModel::$_user_admin) {
            $this->outputJson(0, "您不是管理员，没有该权限", []);
        }

        if (!UserModel::db()->getRowById($delUid)) {
            $this->outputJson(0, "用户不存在", []);
        }

        if (!$delUser = UserGamesModel::db()->getRow(" game_id = $gameId and uid=$delUid ")) {
            $this->outputJson(0, "该用户不是本项目成员", []);
        }

        if (UserGamesModel::db()->delete(" game_id = $gameId and uid=$delUid limit 1")) {
            // 删除成员日志
            if ($delUser['role'] == UserGamesModel::$_user_dev) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_del_dev_user, $gameId, null, json_encode(['operateUid'=>$delUid]));
            } elseif ($delUser['role'] == UserGamesModel::$_user_test) {
                ActivityLogModel::addLog($this->_uid, ActivityLogModel::$_event_del_test_user, $gameId, null, json_encode(['operateUid'=>$delUid]));
            }

            $this->outputJson(1, "删除成功！", []);
        }

    }


    public function searchUser()
    {
        $gameId = _g("gameid");
        $addUid = _g('addUid');

        if (!$user = UserModel::db()->getRowById($addUid)) {
            $this->outputJson(0, "搜索不到该用户，请输入正确的ID号", []);
        }

        $ret = [
            'nickname' => $user['nickname'],
            'avatar' => getUserAvatar($user)
        ];

        if (UserGamesModel::db()->getRow(" game_id = $gameId and uid=$addUid ")) {
            $this->outputJson(0, "该用户是本项目成员，无需重复绑定", $ret);
        }

        $this->outputJson(1, "", $ret);
    }

}