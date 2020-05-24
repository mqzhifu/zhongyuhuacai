<?php
class SdkCtrl extends BaseCtrl {

    function getAccessToken($gameId){
        $tokenInfo = $this->AccessToken($gameId);
        return $this->out($tokenInfo['code'],$tokenInfo['msg']);
    }

    private function AccessToken($gameId){
        if(!$gameId){
            return out_pc(8027);
        }
        //2019-04-15修改为从缓存中获取game信息
        $game = GamesModel::db()->getById($gameId);
//        $gameInfo = RedisPHPLib::getServerConnFD()->hGet($this->getCommonParam(), $gameId);
//        $gameInfo = json_decode($gameInfo, true);

        if(empty($game)){
            return out_pc(1013);
        }

        if(!arrKeyIssetAndExist($game,'app_secret')){
            return out_pc(8051);
        }

        $accessToken = md5($this->uid . $game['app_secret']);
        return out_pc(200, $accessToken);
    }

    private function authAccessToken($accessToken,$gameId){
        if(!$accessToken){
            return out_pc(8050);
        }

        if(!$gameId){
            return out_pc(8027);
        }

        $sureAccessToken = $this->AccessToken($gameId);
        $sureAccessToken = $sureAccessToken['msg'];
        if($sureAccessToken != $sureAccessToken){
            return out_pc(8293);
        }

        return out_pc(200);
    }

    
    function getRankCount($accessToken,$gameId){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03 Begin;
        // 原代码注释;
        // $rs = GamesScoreModel::db()->getCount(" game_id = $gameId ");
        $num = str_split($gameId);
        $num_suffix = $num[count($num) - 1];
        $rs = GamesScoreMoreModel::getCount(" game_id = $gameId ", $num_suffix);
        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03   End;

        return $this->out(200,$rs);
    }
    
    function getRankList($accessToken,$gameId,$everyPageNum ,$offset ,$isDesc = 0 ){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        $everyPageNum = (int)$everyPageNum;
        if(!$everyPageNum || $offset < 0){
            $everyPageNum = 10;
        }

        $offset = (int)$offset;
        if(!$offset || $offset < 0 ){
            $offset = 0;
        }

        /*if(!$isDesc){
            $order= "desc";
        }else{
            $order = "asc";
        }*/
        // 前端反馈升降序没生效，现修复 Modify By XiaHB 2019/05/17;
        $order = ('false' == $isDesc)?'asc':'desc';

        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03 Begin;
        // 原代码注释;
        // $list = GamesScoreModel::db()->getAll("  game_id = $gameId order by score $order limit $offset,$everyPageNum");
        $num = str_split($gameId);
        $num_suffix = $num[count($num) - 1];
        $list = GamesScoreMoreModel::getAll("  game_id = $gameId order by score $order limit $offset,$everyPageNum", $num_suffix);
        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03   End;

        if(!$list){
            return $this->out(200,$list);
        }

        foreach($list as $k=>$v){
            $user = $this->userService->getUinfoById($v['uid']);
            $oid = TokenLib::create($v['uid']);
            $list[$k]['rank'] = $offset+1+$k;
            $list[$k]['oid'] = $oid;
            $list[$k]['name'] = $user['nickname'];

            $list[$k]['photo'] =  "";
            if(arrKeyIssetAndExist($user,'avatar')){
                $photo = $user['avatar'];
                $photo = str_replace('thirdqq.qlogo.cn','mgres.kaixin001.com.cn/wechat_image',$photo);
                $list[$k]['photo'] = $photo;
            }

        }
        return $this->out(200,$list);
    }

    function getUserRank($accessToken,$gameId,$isDesc = 0){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        if(!$isDesc){
            $order= "desc";
        }else{
            $order = "asc";
        }

        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03 Begin;
        // 原代码注释;
        // $list = GamesScoreModel::db()->getAll(" game_id = $gameId order by score $order ",null," uid,score ");
        $num = str_split($gameId);
        $num_suffix = $num[count($num) - 1];
        $list = GamesScoreMoreModel::getAll(" game_id = $gameId order by score $order ", $num_suffix);
        // 游戏分数表拆表逻辑 modify by XiaHB time:2019/07/03   End;

        $rs = array('score'=>0,'rank'=>0);
        if(!$list){
            return $this->out(200,$rs);
        }

//        LogLib::appWriteFileHash("sdk getUserRank list---------------");
//        LogLib::appWriteFileHash($list);
        foreach($list as $k=>$v){
            if($v['uid'] == $this->uid){
                $rs = array('score'=>$v['score'],'rank'=>$k+1);
                break;
            }
        }

        return $this->out(200,$rs);
    }

    function setRankListScore($accessToken,$gameId,$score){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        if(!$score){
            return $this->out(8052);
        }

        /*$log = GamesScoreModel::db()->getRow(" uid = ".$this->uid. "  and game_id = $gameId");
        if($log){
            $data = array(
                'score'=>$score,
//                'extra'=>$extra,
                'u_time'=>time(),
            );
            GamesScoreModel::db()->upById($log['id'],$data);
        }else{
            $data = array(
                'uid'=>$this->uid,
                'game_id'=>$gameId,
                'score'=>$score,
                'a_time'=>time(),
                'u_time'=>time(),
//                'extra'=>$extra,
            );
            GamesScoreModel::db()->add($data);
        }*/

        // 游戏分表games_score_游戏末尾数字 Add By XiaHB time:2019/07/01 Begin;
        $num1 = str_split($gameId);
        $num_suffix = $num1[count($num1) - 1];
        $playedLogSuffix = GamesScoreMoreModel::getRow(" uid = ".$this->uid. "  and game_id = $gameId", $num_suffix, 'id');
        if($playedLogSuffix){
            $upData = array(
                'score'=>$score,
                'u_time'=>time(),
            );
            GamesScoreMoreModel::upById($playedLogSuffix['id'], $upData, $num_suffix);
        }else{
            $insertData = array(
                'uid'=>$this->uid,
                'game_id'=>$gameId,
                'score'=>$score,
                'a_time'=>time(),
                'u_time'=>time(),
            );
            GamesScoreMoreModel::add($insertData, $num_suffix);
        }
        // 游戏分表games_score_游戏末尾数字 Add By XiaHB time:2019/07/01   End;

        return $this->out(200);

    }

    function setCacheData($accessToken,$gameId,$dataKey,$dataValue){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        if(!$dataKey){
            return $this->out(8053);
        }

        if(!$dataValue){
            if($dataValue !== 0 && $dataValue !== '0'){
                return $this->out(8054);
            }
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['sdkUserData']['key'],$this->uid);
//        $rs =RedisPHPLib::hset($key,$dataKey,$dataValue);

        $value = json_encode($dataValue);
        $rs = RedisPHPLib::getServerPoolConnFD('sdk')->hset($key,$dataKey,$value);


        return $this->out(200,$rs);
    }

    function getCacheData($accessToken,$gameId,$dataKey){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        if(!$dataKey){
            return $this->out(8053);
        }

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['sdkUserData']['key'],$this->uid);
//        $rs =RedisPHPLib::hget($key,$dataKey);

        $rs = RedisPHPLib::getServerPoolConnFD('sdk')->hget($key,$dataKey);
        if($rs){
            $rs = json_decode($rs,true);
        }


        if(!$rs){
            $rs = "space_string";
        }
        return $this->out(200,$rs);
    }

    function share($accessToken,$gameId,$type ,$platform,$toUid = 0, $platformMethod){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        if(!$type){
            return $this->out(8004);
        }

        if(!$platform){
            return $this->out(8046);
        }

        $rs = $this->systemService->share($this->uid,$type,$platform,$toUid,$gameId, $platformMethod);
        return $this->out(200,'OK');
    }
    //获取 商品 列表
    function getGoodsInfo($accessToken,$gameId){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        $rs = PropsPriceModel::db()->getAll(" game_id = $gameId");
        if($rs){
            foreach ($rs as $k=>$v) {
                $rs[$k]['money'] = $v['price'];
                $rs[$k]['name'] = $v['goods_name'];
                $rs[$k]['os_type'] = $v['iostype'];

                unset($rs[$k]['price']);
                unset($rs[$k]['goods_name']);
                unset($rs[$k]['iostype']);
                unset($rs[$k]['currency_type']);
            }
        }
        return $this->out(200,$rs);
    }

    /**
     * 统计游戏分享数据;
     * @param $gameId
     * @param $sharePath（分享路径74：微信好友，75：微信朋友圈，76：QQ好友，77：QQ空间）
     * @param $type（1：登陆量；2：点击量；3：下载量；）
     */
    function cntShareCount($accessToken, $gameId, $sharePath, $type){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }
        // 以时间维度更新数据;
        $nowTime = strtotime("now");
        $beginTime = strtotime(date("Y-m-d 00:00:00"));
        $EndTime = strtotime(date("Y-m-d 23:59:59"));
        if( empty($gameId) || empty($sharePath) || empty($type) ){
            $this->out('-1101', '参数缺失！');
        }
        if($sharePath != shareGameCntModel::$_type_h5_game_share_wechat_single && $sharePath != shareGameCntModel::$_type_h5_game_share_wechat_platform && $sharePath != shareGameCntModel::$_type_h5_game_share_qq_single && $sharePath != shareGameCntModel::$_type_h5_game_share_qq_platform){
            $this->out('-1102', '请求参数非法！');
        }
        $upField = $this->getFieldType($type);
        $shareCntInfo = ShareGameCntModel::db()->getRow( " game_id = $gameId AND share_path = $sharePath AND a_time >= '{$beginTime}' AND a_time <= '{$EndTime}' " );
        // update;
        if($shareCntInfo){
            $rs = ShareGameCntModel::db()->upById($shareCntInfo['id'], array("{$upField}"=> $shareCntInfo[$upField] + 1 ));
        }else{
            // insert;
            $insertArray = array(
                'game_id' => $gameId,
                'share_path' => $sharePath,
                "{$upField}" => 1,
                'a_time' => $nowTime
            );
            $rs = ShareGameCntModel::db()->add($insertArray);
        }
        if(!$rs){
            $this->out('-1103', '数据更新失败！');
        }
        $this->out(200);
    }

    //获取 未消耗 商品 列表
    function getUnwasteGoods($accessToken,$gameId){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }



        $list = GamesGoodsOrderModel::db()->getAll(" game_id  = $gameId and waste_time = 0 and status =  ".GamesGoodsOrderModel::$_status_ok);
        if($list){
            $game = GamesModel::db()->getById($list[0]['game_id']);

            foreach ($list as $k=>$v) {
                unset($list[$k]['wx_prepay_id']);
                unset($list[$k]['third_bank_info']);
                unset($list[$k]['out_trade_no']);
                unset($list[$k]['trade_type']);
                unset($list[$k]['wx_pre_order_back_info']);
                unset($list[$k]['wx_final_back_info']);
                unset($list[$k]['game_id']);
                unset($list[$k]['os_type']);


                $data = array('developerPayload'=>$v['developerPayload'],'productID'=>$v['goods_id'],'purchaseTime'=>$v['a_time'],'purchaseToken'=>$v['in_trade_no']);
                $data = json_encode($data);

                $list[$k]['signedRequest'] =base64_encode( hash_hmac("sha256", $data,$game['app_secret']) ) . "." . base64_encode($data);
            }
        }
        return $this->out(200,$list);
    }
    //消耗 商品
    function wasteGoods($accessToken,$gameId,$oid){
        $auth = $this->authAccessToken($accessToken,$gameId);
        if($auth['code'] != 200){
            return $this->out($auth['code'] ,$auth['msg'] );
        }

        $info = GamesGoodsOrderModel::db()->getRow(" in_trade_no = '$oid' ");
        if(!$info){
            return $this->out(7016);
        }

        if(arrKeyIssetAndExist($info,'waste_time')){
            return $this->out(7019);
        }

        if($info['status'] != GamesGoodsOrderModel::$_status_ok){
            return $this->out(7017);
        }

        $data = array('waste_time'=>time());
        GamesGoodsOrderModel::db()->upById($info['id'],$data);

        return $this->out(200,$info['goods_id']);
    }

    /**
     *
     * @param $request_payload
     * @param $player_id
     * @param $issued_at
     * @param $game_id
     * @return array
     */
    public function appAuthOrderStatus($request_payload, $player_id, $issued_at, $game_id){
        if( empty($request_payload) || empty($player_id) || empty($issued_at) || empty($game_id) ){
            $this->out('-1001', '参数缺失！');
        }
        $data = array('game_id'=>$game_id, 'issued_at'=>$issued_at, 'player_id'=>$player_id, 'request_payload'=>$request_payload);
        $data = json_encode($data);
        $game = GamesModel::db()->getById($game_id);
        if(empty($game) || !is_array($game)){
            $this->out('-1002', '游戏信息获取失败！');
        }else{
            $order['signature'] = base64_encode( hash_hmac("sha256", $data,$game['app_secret']) ) . "." . base64_encode($data);
            return $this->out(200,$order);
        }
    }

    /**
     * @param int $type
     * @return null|string
     */
    private function getFieldType($type = 0){
        $field = NULL;
        switch ($type){
            case 1:
                $field = 'login';
                break;
            case 2:
                $field = 'click';
                break;
            case 3:
                $field = 'download';
                break;
        }
        return $field;
    }

}