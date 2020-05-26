<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class UserCtrl extends BaseCtrl{
    function index(){
        if(_g("getlist")){
            $this->getList();
        }

        $this->assign("sexDesc",UserModel::getSexDesc());
        $this->assign("onlineDesc",UserModel::getOnlineDesc());
        $this->assign("typeDesc",UserModel::getTypeDesc());

        $this->display("user/user.html");

    }


    function addUserGoldcoin($uid){
        $rs = $this->userService->addGoldcoin($uid,10000,1);
        var_dump($rs);exit;
//        110887
    }

    function getList(){
        $records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $where = $this->getWhere();


//         $sql = "select count(*) as cnt from  (
//                     SELECT a.id,a.nickname,a.avatar,a.a_time,a.sex,a.cellphone,a.goldcoin,a.type,a.goldcoin_sum,a.goldcoin_sum_less,b.invite_code FROM user AS a LEFT JOIN user_detail AS b  ON a.id = b.uid where $where) as c";

//         $cntSql = UserModel::db()->getRowBySQL($sql);

// //        echo $sql;
// //        var_dump($cntSql);exit;

//         $cnt = 0;
//         if(arrKeyIssetAndExist($cntSql,'cnt')){
//             $cnt = $cntSql['cnt'];
//         }

        // $iTotalRecords = $cnt;//DB中总记录数

        // 不搜索invite_code字段时，获取记录数使用以下方法,不联表
        $iTotalRecords = UserModel::db()->getCount();

        if ($iTotalRecords){
            $order_sort = _g("order");

            $order_column = $order_sort[0]['column'] ?: 0;
            $order_dir = $order_sort[0]['dir'] ?: "asc";


            $sort = array(
                '',
                'id',
                '',
                '',
                'a_time',
                '',
                '',
                'goldcoin',
                '',
                '',
                'goldcoin_sum',
                'goldcoin_sum',
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

            // $sql = "SELECT a.id,a.nickname,a.avatar,a.a_time,a.sex,a.cellphone,a.goldcoin,a.type,a.goldcoin_sum,a.goldcoin_sum_less,b.invite_code FROM user AS a LEFT JOIN user_detail AS b  ON a.id = b.uid where $where order by $order limit $iDisplayStart,$end ";
            
            $sql = "SELECT id,nickname,avatar,a_time,sex,cellphone,goldcoin,type,goldcoin_sum,goldcoin_sum_less,robot FROM user where $where order by $order limit $iDisplayStart,$iDisplayLength ";

//            echo $sql;exit;
            $data = UserModel::db()->getAllBySQL($sql);

            $uids = array_column($data, 'id');
            $uids = implode(',',$uids);

            $userDetails = [];
            if ($uids) {
                $sql = "SELECT id,uid,invite_code FROM user_detail where uid in ($uids) order by uid";
                $userDetails = UserDetailModel::db()->getAllBySQL($sql);
            }

            foreach($data as $k=>$v){
                $inviteCode = '';
                foreach ($userDetails as $key=>$detail) {
                    if ($v['id'] == $detail['uid']) {
                        $inviteCode = $detail['invite_code'];
                        unset($userDetails[$key]);
                        break;
                    }
                }
                $avatarImg = "";
                if(arrKeyIssetAndExist($v,'avatar')){
                    $avatarImg = "<img width='50' height='50' src='". getUserAvatar($v) ."   ' />";
                }

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'.$v['id'].'">',
                    $v['id'],
                    $v['nickname'],
                    $avatarImg,
                    get_default_date($v['a_time']),
                    UserModel::getSexDescByKey($v['sex']),
                    $v['cellphone'],
                    $v['goldcoin'],
                    UserModel::getTypeDescByKey($v['type']),
                    $inviteCode,
                    $v['goldcoin_sum'],
                    $v['goldcoin_sum_less'],
//                    '<a href="#" class="btn btn-xs default red delone" data-id="'.$v['id'].'"><i class="fa fa-trash-o"></i> 删除</a>'.
                    '<a href="/user/no/user/showDetail/uid='.$v['id'].'" class="btn btn-xs default yellow" data-id="'.$v['id'].'" target="_blank"><i class="fa fa-file-text"></i> 详情</a>',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
    }

    function makeRedisToken($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $token = TokenLib::create($uid);
        $rs = RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['token']['expire']);
        var_dump($rs);
        echo "-ok";
    }

    function clearUserMatchStatus($uid){
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['serverMatching']['key'],$uid,'game');
        $userStatus = RedisPHPLib::getServerConnFD()->del($key);


        echo "ok";
    }

    function searchAllDel(){
        $where = $this->getWhere();

        if($where == ' 1 ')
            out_err("没有搜索条件，那么就是清空全表，禁止这样操作!",500,'ajax');

        $type = _g("type");
        if(!$type || (  $type != 'in' && $type != 'out' ) ){
            out_err("para type:error....!",500,'ajax');
        }

        if($type == 'in'){
            $cnt = Sms_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from sms_log where ".$where . "  limit 100000";
            Sms_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');
        }else{
            $cnt = Send_logModel::db()->getCount($where);
            if(!$cnt)
                out_ok('删除了0条记录',200,'ajax');

            $sql = "delete from send_log where ".$where. "  limit 100000";
            Send_logModel::db()->execute($sql);
            out_ok("删除了{$cnt}条记录",200,'ajax');

        }
    }

    function delOne($uid){
        if(!(int)$uid){
            exit("uid is null");
        }

        $user = UserModel::db()->getById($uid);
        if(!$user){
            exit("uid not in db ");
        }
        e_d_f($user);
        //删除表数据

        //访问日志
        //广告日志
        //点击，统计日志
        //每日排行日志
        //发送邮件日志
        //粉丝
        //粉丝拉黑
        //粉丝免打扰
        //粉丝互相关注
        //首次支付日志

        //游戏收集
        //游戏购买订单
        //金币日志
        //sdk游戏成绩排行
        //欢乐抽奖
        //ID验证
        //IM消息
        //im会话
        //邀请
        //登陆日志
        //抽奖lottery
        //幸运宝箱
        //提现日志
        //玩过的游戏日志
        //推送日志


        //随机宝箱
        //举报日志
        //分享日志
        //分享游戏
        //短信日志
        //日常任务
        //成长任务

        //用户
        //用户详情
        //签到
        //用户黑名单
        //优惠卷
        //验证码




        //任务
//        $delRs = TaskUserModel::db()->delete(" uid = $uid limit 1000");
//        echo "del task rs :".$delRs. "</br>";

        //用户
        $delRs = UserModel::db()->delById($uid);
        echo "del user rs :".$delRs. "</br>";
        //用户详细表
        $delRs = UserDetailModel::db()->delById($uid);
        echo "del user_detail rs :".$delRs. "</br>";

        //金币日志
        $delRs = GoldcoinLogModel::db()->delete(" uid = $uid limit 1000");
        echo "del gold coin log  rs :".$delRs. "</br>";
        //登陆日志
        $delRs = LoginModel::db()->delete(" uid = $uid limit 1000");
        echo "del login  rs :".$delRs. "</br>";


        //删除缓存

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['userinfo']['key'],$uid,IS_NAME);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);

        echo "clear uinfo cache rs :".$cacheRs. "</br>";



        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $cacheRs = RedisPHPLib::getServerConnFD()->del($key);
        echo "clear token cache rs :".$cacheRs. "</br>";

        echo "end";exit;



    }

    function showDetail($uid){

        $user = $this->userService->getUinfoById($uid);
        $this->assign("user",$user);

//        $task = TaskUserModel::db()->getAll(" uid = $uid");
        $task = null;
//        echo "<br/>任务:<br/>";
        $this->assign("task",$task);
//
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,IS_NAME);
        $cacheToken = RedisPHPLib::get($key);
        $this->assign("cacheToken",$cacheToken);

        $token = TokenLib::create($uid);
        $this->assign("token",$token);


//        echo "token cache rs :".$cacheRs. "</br>";
//
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['heartbeat']['key'],$uid,IS_NAME);
        $heartBeat = RedisPHPLib::getServerConnFD()->get($key);
        $this->assign("heartBeat",$heartBeat);
//        echo "心跳 heartbeat rs :".$cacheRs. "</br>";


        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['serverMatching']['key'],$uid,'game');
        $userStatus = RedisPHPLib::getServerConnFD()->get($key);
        $this->assign("userStatus",$userStatus);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid,'game');
        $cacheToken = RedisPHPLib::get($key);
        $this->assign("gameCacheToken",$cacheToken);



        $goldLog = GoldcoinLogModel::db()->getAll(" uid = $uid");
        $this->assign("goldLog",$goldLog);

        $service = new GamesService();
        $playedGameHistoryList = PlayedGamesModel::db()->getAll(" uid = $uid ");

        $this->assign("playedGameHistoryList",$playedGameHistoryList);

        $this->display("user/show_detail.html");
        exit;


    }

    function delBat(){
        $ids = _g("ids");
        if(!$ids)
            return 0;

        $ids = explode(",",$ids);
        $type = _g("type");
        $str_ids = "";
        foreach($ids as $k=>$id){
            if($id){
                if($type == 'in'){
                    $info = Sms_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Sms_logModel::db()->delById($id);
                    }
                }else{
                    $info = Send_logModel::db()->getById($id);
                    if($info){
                        $str_ids .= $info['message_id'].",";
                        $rs = Send_logModel::db()->delById($id);
                    }
                }
            }
        }

        if($type == 'in'){
            $str = "收件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'inbox_del');
        }else{
            $str = "发件箱批量删除:".$str_ids;
            $uid = $this->_sess->getValue('id');
            admin_db_log_writer($str,$uid,'sendbox_del');
        }

    }

    function getWhere(){
        $where = " 1 ";
        if($id = _g("id"))
            $where .= " and id=$id";

        if($nickname = _g("nickname"))
            $where .= " and nickname like '%$nickname%'";

        if($from = _g("from")){
            $where .= " and a_time >= '".strtotime($from)."'";
        }

        if($to = _g("to")){
            $where .= " and a_time <= '".strtotime("$to +1 day")."'";
        }


        if($sex = _g("sex"))
            $where .= " and sex=$sex";


        if($cellphone = _g("cellphone"))
            $where .= " and cellphone='$cellphone'";

        if($type = _g("type"))
            $where .= " and type=$type";

        return $where;
    }


}