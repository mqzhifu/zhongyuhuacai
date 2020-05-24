<?php
class InviteCtrl extends BaseCtrl {
    //填写别人的邀请码
    function setUserCode($code){
        if(!$code){
            return $this->out(8045,$GLOBALS['code'][8045]);
        }

        $row = UserDetailModel::db()->getRow(" invite_code = '$code'");
        if(!$row){
            return $this->out(1022,$GLOBALS['code'][1022]);
        }
        // 邀请人数限制为100 Modify By xuren 2019/06/20 Begin;
        $inviteNum = InviteModel::db()->getCount(" to_uid = ".$row['uid']);
        if($inviteNum > 100){
            return $this->out(8313,$GLOBALS['code'][8313]);
        }
        // 邀请人数限制为100 Modify By xuren 2019/06/20 End;

        $invite = InviteModel::db()->getRow(" uid = ".$this->uid);
        if($invite){
            return $this->out(8280,$GLOBALS['code'][8280]);
        }
        LogLib::appWriteFileHash(['setUserCode',$this->uinfo['invite_code'],$code]);
        if($this->uinfo['invite_code'] == $code){
            return $this->out(8273,$GLOBALS['code'][8273]);
        }

        // H5邀请获取不到device_code,现作兼容处理 Modify By XiaHB 2019/05/13 Begin;
        if(array_key_exists("deviceType", $this->clientInfo) && 'h5' != $this->clientInfo['deviceType']){
            if(PCK_AREA == 'cn'){
                if(!arrKeyIssetAndExist($this->clientInfo,'device_id')){
                    return $this->out(8055,$GLOBALS['code'][8055]);
                }
            }

        }
        // H5邀请获取不到device_code,现作兼容处理 Modify By XiaHB 2019/05/13   End;

        if(PCK_AREA == 'cn') {
            $deviceBind = InviteModel::db()->getRow(" device_id = '{$this->clientInfo['device_id']}'");
            if ($deviceBind) {
                return $this->out(8294, $GLOBALS['code'][8294]);
            }
        }

        if($this->uinfo['type'] == UserModel::$_type_guest){
            return $this->out(8296,$GLOBALS['code'][8296]);
        }


        $data = array(
            'uid'=>$this->uid,
            'to_uid'=>$row['uid'],
            'a_time'=>time(),
            'device_id'=>$this->clientInfo['device_id'],
        );

        $aid = InviteModel::db()->add($data);


        $data = array('invite_uid'=>$row['uid']);
        $this->userService->upUserDetailInfo($this->uid,$data);

        return $this->out(200,$aid);
    }
    //收益汇总
    function incomeTotal(){
        $rs = array('budget'=>0,'people'=>0,'income'=>0);
        $list = InviteModel::db()->getAll(" to_uid = ".$this->uid);
        if(!$list){
            return $this->out(200,$rs);
        }

        //好友贡献的收益
//        $sql = "select sum(num) as total from goldcoin_log where uid  = ".$this->uid." and type = ".GoldcoinLogModel::$_type_friend_play_game;
//        $income = GoldcoinLogModel::db()->getRowBySQL($sql);
        $lib =  new GamesService();
        $income = $lib->getFiendIncome($this->uid);

        $rs['people'] = count($list);
        // 海外APP展示金币;
        if(PCK_AREA == 'en'){
            $rs['income'] = $income;
            $rs['budget'] =  $this->feeHandle($rs['people'] * $GLOBALS['main']['friendIncomeMaxLimitOs']*100000);
        }else{
            $rs['income'] = round($income * $GLOBALS['main']['goldcoinExchangeRMB'],2);
            $rs['budget'] =   $rs['people'] * $GLOBALS['main']['friendIncomeMaxLimit'];
        }

        return $this->out(200,$rs);
    }
    //收益日志
    function incomeLog(){
        $list = InviteModel::db()->getAll(" to_uid = ".$this->uid);
        if(!$list){
            return $this->out(200,null);
        }
        $lib = new GamesService();
        $friendIncome = $lib->getFriendGiveGoldCoinList($this->uid);
        foreach($list as $k=>$v){
            $user = $this->userService->getUinfoById($v['uid']);
//            $sql = "select sum(num) as total from goldcoin_log where uid  = ".$this->uid." and type = ".GoldcoinLogModel::$_type_friend_play_game . " and memo = ".$v['uid'];
//            $income = GoldcoinLogModel::db()->getRowBySQL($sql);
            $friendIncome[$v['uid']] = (isset($friendIncome[$v['uid']]))?$friendIncome[$v['uid']]:0;// 四期新增对 $friendIncome[$v['uid']] 值是否存在进行判断;
            // 海外APP展示金币;
            if(PCK_AREA == 'en'){
                $list[$k]['income'] = $friendIncome[$v['uid']];
            }else{
                $list[$k]['income'] = round($friendIncome[$v['uid']] * $GLOBALS['main']['goldcoinExchangeRMB'] ,2);
            }
            $list[$k]['avatar'] = $user['avatar'];
            $list[$k]['nickname'] = $user['nickname'];
            $list[$k]['uid'] = $user['uid'];

            unset($list[$k]['id']);
            unset($list[$k]['to_uid']);
        }

        return $this->out(200,$list);
    }

    private function feeHandle($num){
        if(!is_numeric($num)){//首先对变量进行判断 看看是否是数字或数字字符串
            return false;
        }
        $num=explode('.',$num);//首先呢，利用小数点把数值分成整数和小数两个部分，并保存到$num变量里面
        $rl=$num[1];//我们都知道，小数部分是位于数值小数点之后，所以它的键值是1
        $j=strlen($num[0])%3;//接下来我们把整数部分除以三求余数 目的是为了确定最前面不足三位数的长度
        $sl=substr($num[0],0,$j);//然后 我们利用substr，从第0个数值（首个数字），向后取$j个数字，实际上相当于截断了最前面的$j个数字
        $sr=substr($num[0],$j);//下来吧 截取位置超过（包含）第三的部分，也就是整数部分，除去$sl部分剩下的段
        $i=0;
        $rvalue = '';
        while($i<=strlen($sr)){
            $rvalue = $rvalue.','.substr($sr, $i, 3);//三位三围地截断$sr部分，并用半角逗号连接它们
            $i=$i+3;
        }
        $rvalue=$sl.$rvalue;//接下来把不够三位的那部分和后面的部分结合
        $rvalue=substr($rvalue,0,strlen($rvalue)-1);//那整数最后面的一个半角逗号去掉
        $rvalue=explode(',',$rvalue);//然后把新生成 带逗号的数值字符串按逗号隔开 分解成数组
        if($rvalue[0]==0){
            array_shift($rvalue);//如果最前面的那段都是0 果断删掉
        }
        $rv=$rvalue[0];//前面不满三位的数
        for($i = 1; $i < count($rvalue); $i++){
            $rv = $rv.','.$rvalue[$i];
        }
        if(!empty($rl)){
            $rvalue = $rv.'.'.$rl;//小数不为空，整数和小数合并
        }else{
            $rvalue = $rv;//小数为空，只有整数
        }
        return $rvalue;
    }

    /**
     * 测试环境使用;
     * @return array
     */
    public function testInvite(){
        $code = _g('code');
        $sql = "SELECT * FROM user ORDER BY id ASC limit 200";
        $info = UserModel::db()->getAllBySQL($sql);
        $count = 0;
        foreach ($info as $k => $value){
            while ($count < 99) {
                $uid = $value['id'];
                $info = $this->setUserCodeBak($code, $uid);
                if($info){
                    $count ++;
                }else{
                    break;
                }
            }
        }
        return $this->out(200, ['msg'=>$count]);
    }

    /**
     * 测试环境使用;
     * @param $code
     * @param $uid
     * @return array|int
     */
    function setUserCodeBak($code, $uid){
        if(!$code){
            return $this->out(8045,$GLOBALS['code'][8045]);
        }
        $row = UserDetailModel::db()->getRow(" invite_code = '$code'");
        if(!$row){
            return 0;
        }
        $inviteNum = InviteModel::db()->getCount(" to_uid = ".$row['uid']);
        if($inviteNum > 100){
            return 0;

        }
        $invite = InviteModel::db()->getRow(" uid = ".$uid);
        if($invite){
            return 0;

        }
        LogLib::appWriteFileHash(['setUserCode',$this->uinfo['invite_code'],$code]);
        if($this->uinfo['invite_code'] == $code){
            return 0;

        }
        if(array_key_exists("deviceType", $this->clientInfo) && 'h5' != $this->clientInfo['deviceType']){
            if(PCK_AREA == 'cn'){
                if(!arrKeyIssetAndExist($this->clientInfo,'device_id')){
                    return $this->out(8055,$GLOBALS['code'][8055]);
                }
            }
        }
        if($uid != $row['uid']){
            $data = array(
                'uid'=>$uid,
                'to_uid'=>$row['uid'],
                'a_time'=>time(),
                'device_id'=>$this->clientInfo['device_id'],
            );
            $aid = InviteModel::db()->add($data);
        }else{
            return 0;
        }
        $data = array('invite_uid'=>$row['uid']);
        $this->userService->upUserDetailInfo($this->uid,$data);
        return $aid;
    }

}