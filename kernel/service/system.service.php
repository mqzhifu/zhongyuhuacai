<?php
class SystemService{
//    function getUVCntByDay(){
//        $today = dayStartEndUnixtime();
//        $cnt = LoginModel::db()->getCount(" a_time >= {$today['s_time']} and a_time <=  {$today['e_time']} group by uid");
//        return $cnt;
//    }

    function search($uid,$keyword ){
        if(!$keyword){
            return out_pc(8025);
        }
//        $keyword = array('a'=>12342343241);
//        $keyword = [];
//        echo "bbbb";
//var_dump((int)$keyword);exit;

        if( intval($keyword)){
            $userLib  =  new UserService();
            $user = $userLib->getUinfoById($keyword);
        }else{
            return out_pc(200,[]);
        }

        $friendService = new FriendService();
        $fansService = new FansService();

        $rs = [];
        $data = [];//这里定义成二维数组是为了以后兼容，搜索结果会出现多个的情况
        if($user){
            $isFriend = $friendService->isFriend($uid,$user['id']);
            $rs['isFriend'] = 1;
            if(!$isFriend){
                $rs['isFriend'] = 2;
            }

            $isFollow = $fansService->isFollow($uid,$user['id']);
            $rs['isFollow'] = 1;
            if(!$isFollow){
                $rs['isFollow'] = 2;
            }

            $rs['uid'] = $user['id'];
            $rs['nickname'] = $user['nickname'];
            $rs['avatar'] = $user['avatar'];
            $rs['id'] = $user['id'];
            $rs['sex'] = $user['sex'];

            $data[] = $rs;
        }
        return out_pc(200,$data);

    }

    function share($uid,$type,$platform,$toUid = 0,$gameId = 0,$platformMethod = 0){
        if(!$type){
            return out_pc(8004);
        }
        // 管理后台需展示APP分享详情数据需求，新增以下三个type值:$_type_game_share_add_friends;$_type_game_share_sdk_byapp;$_type_game_share_sdk_direct;$_type_game_share_toUid;
        if($type != GoldcoinLogModel::$_type_share_get_money && $type != GoldcoinLogModel::$_type_share_friend && $type != GoldcoinLogModel::$_type_share_income && $type != GoldcoinLogModel::$_type_rand_luck_box_share_wx && $type != GoldcoinLogModel::$_type_rand_luck_box_share_qq && $type != GoldcoinLogModel::$_type_share_sdk && $type != GoldcoinLogModel::$_type_game_share_add_friends && $type != GoldcoinLogModel::$_type_game_share_sdk_byapp && $type != GoldcoinLogModel::$_type_game_share_sdk_direct && $type != GoldcoinLogModel::$_type_game_share_toUid ){
            return out_pc(8210);
        }

        if($type == GoldcoinLogModel::$_type_rand_luck_box_share_wx || $type == GoldcoinLogModel::$_type_rand_luck_box_share_qq) {
            $info = RandomLuckBoxModel::db()->getRow(" uid = " .$uid . " order by id desc ");
            LogLib::appWriteFileHash($info);
            if ($info) {
                if (time() - $info['a_time'] < 4 * 60 * 60) {
                    LogLib::appWriteFileHash(["share if < 4 * 60 * 60 "]);
                    if($type == GoldcoinLogModel::$_type_rand_luck_box_share_wx){
                        if ($info['status'] == 1) {
                            $lib = new UserService();
                            $lib->addGoldcoin($uid, 40, GoldcoinLogModel::$_type_rand_luck_box_share_wx);
                        }

                        RandomLuckBoxModel::db()->upById($info['id'],array('status'=>2));
                    }else{
                        LogLib::appWriteFileHash(["share else "]);
                        if ($info['status'] == 2) {
                            $lib = new UserService();
                            $lib->addGoldcoin($uid, 40, GoldcoinLogModel::$_type_rand_luck_box_share_qq);
                        }
                        RandomLuckBoxModel::db()->upById($info['id'],array('status'=>3));
                    }
                }
            }
        }

        if(!$platform){
            return out_pc(8246);
        }

        if(!UserModel::keyInRegType($platform)){
            return out_pc(8281);
        }



        $data = array(
            'uid'=>$uid,
            'type'=>$type,
            'platform'=>$platform,
            'to_uid'=>$toUid,
            'content'=>'',
            'a_time'=>time(),
            'game_id'=>$gameId,
            'platform_method'=>$platformMethod,
        );

        $aid = ShareModel::db()->add($data);

        return out_pc(200,$aid);
    }

    function adStart($uid,$type){
        if(!$type){
            return out_pc(8004);
        }

        if(!GoldcoinLogModel::keyInType($type)){
            return out_pc(8210);
        }

        if(!$uid){
            return out_pc(8002);
        }

        // 四期项目新增，同一用户当天观看广告次数限制 add by XiaHB 20190409 Begin;
        define('TYPE_CODE', json_encode(array(24,25,26)));
        $type_code = json_decode(TYPE_CODE,true);
        if(in_array($type, $type_code)){
            $date_begin= strtotime(date('Y-m-d 00:00:00',time()));
            $date_end = strtotime(date('Y-m-d 23:59:59',time()));
            $nums = 40;
            $rs_num = AdLogModel::db()->getCount("uid = $uid AND a_time >= '{$date_begin}' AND  a_time <= '{$date_end}' AND type IN (24,25,26)");// 当前播放次数;
            if($rs_num){
                if($rs_num > $nums){
                    return out_pc(8059);// code码新增;
                }
            }
        }
        // 四期项目新增，同一用户当天观看广告次数限制 add by XiaHB 20190409   End;

        $data = array(
            'uid'=>$uid,'a_time'=>time(),'e_time'=>0,'type'=>$type
        );

        $aid = AdLogModel::db()->add($data);
        return out_pc(200,$aid);

    }

    function adEnd($uid,$id)
    {
        if (!$uid) {
            return out_pc(8002);
        }

        if (!$id) {
            return out_pc(8043);
        }

        $info = AdLogModel::db()->getById($id);
        if (!$info) {
            return out_pc(1021);
        }

        if ($info['e_time']) {
            return out_pc(8278);
        }

        LogLib::writeGolgcoinHash('====================Record Type Begin====================');

        if ($info['type'] == GoldcoinLogModel::$_type_ad_open_box) {
            $lib = new LotteryService();
            $rs = $lib->doBox($uid);
//            $rs[] = "";
        }elseif($info['type'] ==  GoldcoinLogModel::$_type_ad_addition_sign || $info['type'] ==  GoldcoinLogModel::$_type_ad_addition_playing_game || $info['type'] ==  GoldcoinLogModel::$_type_ad_addition_game_end){
            $gold = rand(40,90);
            if(PCK_AREA == 'en'){// 2019/06/12 海外金币有所上调 add by XiaHB;
                $gold = rand(50, 100);
            }
            $rs = array('code'=>200,'msg'=>array('rewardType'=>1,'rewardGold'=>$gold));
        }elseif ($info['type'] == GoldcoinLogModel::$_type_ad_24_sign) {
            $gold = 50;
            if(PCK_AREA == 'en'){// 2019/06/12 海外金币有所上调 add by XiaHB;
                $gold = 60;
            }
            $rs = array('code'=>200,'msg'=>array('rewardType'=>1,'rewardGold'=>$gold));
        }else{
            $rs = array('code'=>200,'msg'=>'ok');
        }
//        LogLib::writeGolgcoinHash("ad_log.id：{$id},type：{$info}");
        LogLib::writeGolgcoinHash('====================Record Type End=======================');


        LogLib::appWriteFileHash($rs);

        // 系统内E_WARNING处理，原因，抽奖的时候有可能返回类型3也就是，!= 1的情况，也有可能看广告不加金币，类型不在23,24,25,26,27里面，也就没有rewardType等字段,现做兼容处理 modify by XiaHB time:2019/07/04;
        // 原代码注释不删除：if($rs['code'] == 200 && $rs['msg']['rewardType'] == 1 && $rs['msg']['rewardGold'] ){modify by XiaHB time:2019/07/04;
        // type = 28,29,110,200,201造成了上述问题;
        if($rs['code'] == 200 && isset($rs['msg']['rewardGold']) && isset($rs['msg']['rewardType']) && $rs['msg']['rewardType'] == 1 && $rs['msg']['rewardGold'] ){
            $lib = new UserService();
            $rs2 = $lib->addGoldcoin($uid,$rs['msg']['rewardGold'],$info['type'],$id);
            LogLib::appWriteFileHash(['add goldcoin',$rs2]);
        }

        /*$data = array("e_time"=>time(),'reward_type'=>$rs['msg']['rewardType'],'reward_gold'=>$rs['msg']['rewardGold']);
        AdLogModel::db()->upById($id,$data);*/

        // 四期项目新增对 rewardType & rewardGold 字段值是否存在进行判断;
        if(isset($rs['msg']['rewardType']) && isset($rs['msg']['rewardGold'])){
            $data = array("e_time"=>time(),'reward_type'=>$rs['msg']['rewardType'],'reward_gold'=>$rs['msg']['rewardGold']);
            AdLogModel::db()->upById($id,$data);
        }else{
            $data = array("e_time"=>time(),);
            AdLogModel::db()->upById($id,$data);
        }

        return $rs;

    }

    function asynTask($ctrl,$ac,$data){
        $url = "http://".$GLOBALS['server']['http_bind_ip'].":".$GLOBALS['server']['http_port']."/?ctrl=$ctrl&ac=$ac";
        foreach ($data as $k=>$v) {
            $url .="&$k=$v";
        }

        $lib = new CurlLib();
        $lib->send($url);

    }
}