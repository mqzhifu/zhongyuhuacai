<?php
//麻将游戏
class majiangLib{
    private $_record = array();
    private $_init_record = array();
    private $_dice = array();
    private $_room_max = 100;//服务器可同时支持最大房间数
    private $_user_room_max = 5;//一个用户可 创建的房间最大数
    private $_user_timeout = 10;//10分钟
    private $_uid = 0;
    private $_room_id= 0;
    private $_group_id= 0;
    private $_dir = array(1=>'(东)',2=>'(南)',3=>'(西)',4=>'(北)');

#######################################################
    //用户进入主页面，初始化，需要判定状态，根据状态-返回不同的值
    function userInit($uid,$room_id = 0){
        $filet_user = UserModel::filterById($uid);
        if($filet_user['code'] != 200)
            return $filet_user;

        $data = "";
        //查询是否已经存在了(非结束状态)
        $group_one_user = GroupUserModel::db()->getRow(" uid = $uid and status != 3");
        if(!$group_one_user) {
            //可以报名
            $status = -1;
        }elseif($group_one_user['status'] == 0){
            //准备中,等待其它用户均准备后,系统触发，初始化牌局，开始
            $status = 0 ;
        }elseif($group_one_user['status'] == 1){
            //系统初始化完成，已开局，等待打骰子
            $status = 1;

            $data = $this->getDealerInfo($group_one_user['room_id'],$uid,1);
            $data = $data['msg'];
        }elseif($group_one_user['status'] == 2){
            //进行中
            $status = 2;
            $data = $this->userRecover($uid);
            $data = $data['msg'];
        }else{
            $status = 999;
        }

        return out_ok(array('status'=>$status,'data'=>$data));

    }
    //用户进入房间，点击-准备-按钮
    function userReady($uid,$room_id = 0){
        LogLib::app(" lib func:userReady:======start=============");
        //判断UID合法
        $filet_user = UserModel::filterById($uid);
        if($filet_user['code'] != 200)
            return $filet_user;

        //查询是否已经存在了
        $group_user = GroupUserModel::db()->getRow(" uid = $uid and status != 3");
        if($group_user){
            if($group_user['status'] == 1 ){
                return out_err('牌局已生成，等待打骰子');
            }elseif( $group_user['status'] == 2){
                return out_err('牌局进行中，不能报名');
            }elseif( ! $group_user['status'] || $group_user['status'] == 0){
                return out_err('不能重复报名');
            }
        }

        if($room_id){
            LogLib::app("room_id:".$room_id);
            $room = RoomModel::filterById($room_id);
            if($room['code'] != 200)
                return $room;

            if($room['msg']['status'] == 1 ){
                return out_err('房间进行中');
            }elseif( $room['msg']['status'] == 2){
                return out_err('房间已结束');
            }

            $room_user = GroupUserModel::getByRoomId($room_id);
            if($room_user && count($room_user) >= 4){
                return out_err('房间已有4人准备中，不能再报名了');
            }
        }

        $data = array('uid'=>$uid,'status'=>0,'room_id'=>$room_id,'timeout'=>time() + $this->_user_timeout * 60);
        $gu_id = GroupUserModel::add($data);

        LogLib::app("new group_user id:$gu_id");

        $rs  = $this->makeGroup($room_id);
        LogLib::app($rs);

        LogLib::app(" lib func:userReady:======end=============");
        return out_ok("ok");
    }
    //用户取消准备-离开
    function userCancelReady($uid){//查询是否已经存在了

        $group_user = GroupUserModel::db()->getRow(" uid = $uid and status = 0");
        if($group_user){
            $rs = GroupUserModel::db()->delById($group_user['id']);
            if($rs)
                return out_ok('取消成功');
            else
                return out_err('取消失败，请联系管理员');
        }else{
            return out_err('用户并没有报名或者已经开局，不能取消...');
        }

        //回收用户创建的房间-没有人的房间
//        $this->recoveryRoom();

    }
    //用户扔骰子-个共两次，第一次是按照做庄-东南西北，第二次是由第一次结果而定
    function userDice($uid){
        LogLib::app(" user dice start##################");
        $user = UserModel::filterById($uid);
        if($user['code'] != 200)
            return $user;

        $userGroup = GroupUserModel::db()->getRow(" uid = ".$uid);

        $group = GroupModel::db()->getById($userGroup['group_id']);

        $is_first = 0;
        if($group['dices']){
            $dices = explode(",",$group['dices']);
            $is_first = count($dices);
            if($is_first >= 2)
                out_err('数据错误，骰子扔了3次');
        }

        LogLib::app(" dice time:".$is_first);
        //判断第几次
        if($is_first == 1){
            $dealer = $this->getDiceDealer($group['room_id']);
            if($dealer['code']!= 200)
                return $dealer;

            if($uid != $dealer['msg']){
                return out_err(" 抱歉庄家打出的是: ".$dices[0].",不是您第二次打骰!");
            }
            $rand = rand(2,12);
            LogLib::app(" dice num:$rand");
            $data = array('dices'=>$group['dices'] . ",".$rand);
            $rs = GroupModel::upById($data,$group['id']);

            LogLib::app(" up group dice num:".$rs);

            return $this->gameStart($group['id']);
        }else{

            $dealer = $this->getDiceDealer($group['room_id']);
            if($dealer['code'] != 200)
                return $dealer;

            if($dealer['msg'] != $uid)
                return out_err("抱歉您不是庄家");

            $rand = rand(2,12);
            LogLib::app(" dice num:$rand");
            $data = array('dices'=>$rand);

            $rs = GroupModel::upById($data,$group['id']);

            LogLib::app(" up group dice num:".$rs);

            return out_ok('ok-dices_num:'.$rand."请找下一个打骰子的人~");
        }

    }
    //用户摸一张牌
    function userGetOneRecord($group_id,$uid){
        $group_user = GroupUserModel::db()->getOne(" group_id = $group_id and $uid = $uid ");
        if(!$group_user)
            return out_err("抱歉，您不在这个组里");

        $group = GroupModel::filterById($group_id);
        if($group['code']!= 200){
            return $group;
        }

        if($group['status'] != 2)
            return("状态不是<进行中>2");

        //其实group表的状态就可以了，但是保持数据的一致性
        if($group['current_record_num'] >= 136 - 2 * 8){
            return out_err('所有牌均以抓完');
        }


        if($group['current_throw_uid']){
            return out_err('该用户抓牌后，还没有打牌，请等待用户打出一张牌');
        }

        //找出摸牌的顺序的人
        $uids = split_arr($group['uids'],'uid');
        $next_uid = $this->getDealerUid($uids,$uid,1);
        if($next_uid != $uid)
            return out_err("抱歉您不是下一步摸牌的人");




        //上一张牌的数字+1，计算出下一张，也就是当前牌的ID
        $next_record = $group['current_record_num'] + 1;
        $record = GroupRecordModel::db()->getOne(" status = 0 and group_id = $group_id and no = ".$next_record);


        GroupModel::upById(array('current_record_num'=>$next_record),$group['id']);
        GroupRecordModel::upById(array('current_uid'=>$next_uid),$record['id']);
        //已抓完了
        if($group['current_record_num'] >= 136 - 2 * 8){
            $this->groupEnd($group_id);
        }

        return out_ok("ok");
    }
    //用户打一张牌
    function userTakeOneRecord($uid,$take_record_num){
        $record = GroupRecordModel::db()->getById($take_record_num);
        if($record['uid'] != $uid)
            return out_err('该张牌不是您的');


        $user_group = GroupUserModel::db()->getOne(" uid = $uid and status = 2");
//        $group = GroupModel::db()->getById($user_group['group_id']);

        GroupRecordModel::upById(array('status'=>2),$record['id']);
        GroupModel::upById(array('current_throw_uid'=>0),$user_group['group_id']);

    }
    //吃 碰 杠 胡
    //ac=chi ,peng,ming_gang,an_gang,hu
    //$take_record_num:用户打出的一牌
    //use_hand_record:array() 用户手里的牌组成  action
    function userChangeRecord($group_id,$uid,$from_uid,$take_record_num,$action,$use_hand_record){
        $record_ids = "";
        if('hu' == $action){
            $this->groupEnd($group_id);
        }elseif('chi' == $action){

            if(count($use_hand_record) !=2 )
                out_err("吃的牌，用户得提供2张牌的ID");


            $group = GroupModel::db()->getById($group_id);
            $uids = $uids = explode(',',$group['uids']);

            $current_uid = $this->getDealerUid($uids,$group['current_uid'],1);
        }elseif('peng' == $action ){

            if(count($use_hand_record) !=2 )
                out_err("吃的牌，用户得提供2张牌的ID");


            $record_ids = split_arr($use_hand_record);
            $record_ids = $take_record_num . ",".$record_ids;

            $group = GroupModel::db()->getById($group_id);
            $uids = $uids = explode(',',$group['uids']);

            $current_uid = $this->getDealerUid($uids,$from_uid);


        }elseif('gang' == $action || 'an_gang' == $action){

            if(count($use_hand_record) !=3 )
                out_err("吃的牌，用户得提供2张牌的ID");

            $record_ids = split_arr($use_hand_record);
            $record_ids = $take_record_num . ",".$record_ids;


            $group = GroupModel::db()->getById($group_id);
            $uids = $uids = explode(',',$group['uids']);

            $current_uid = $this->getDealerUid($uids,$from_uid);


            //从最后一张牌开始抓
            $recode = GroupRecordModel::db()->getOne(" status = 3 order by no asc  ");
            GroupRecordModel::upById(array('uid'=>$uid),$recode['id']);

        }

        //更新-下一个打牌人的状态
        $data =array('current_uid'=>$current_uid);
        GroupModel::upById($data,$group_id);


        $data = array(
            'record_ids'=>$record_ids,
            'type'=>$action,
            'group_id'=>$group_id,
            'uid'=>$uid,
            'from_uid'=>$from_uid,
            'from_record_id'=>$take_record_num,
        );

        $change_id = RecordChangeModel::add($data);
        //更新牌的状态
        GroupRecordModel::upById(array('uid'=>$uid,'status'=>1,'change_type'=>$action,'change_from_uid'=>$from_uid,'change_id'=>$change_id),$take_record_num);
        foreach($use_hand_record as $k=>$v){
            GroupRecordModel::upById(array('change_type'=>$action,'change_id'=>$change_id),$v);
        }


    }

    function paintUserDir($group_id){
        $group = GroupModel::db()->getById($group_id);
        $group_user = explode(',',$group['uids']);
        $user_dir = array();//UID=>东南西北
        foreach($group_user as $k=>$v){
            $r = $k+1;
            $user_dir[$v] = $this->_dir[$r];
        }

        return array('user_dir'=>$user_dir,'group_user'=>$group_user);
    }

    //当用户掉线后，重新渲染页面
    function userRecover($uid){
        $user_status = GroupUserModel::db()->getRow(" status !=3 and uid = $uid ");
        if(!$user_status)
            return out_err(" 没有进行中的牌局，不需要恢复 ");

        if($user_status['status'] == 0) {//准备中
            return out_err("还在准备中，没有牌可供初始化");
        }elseif($user_status['status'] == 1){//准备中
            return out_err("还在-等待庄家打骰子");
        }elseif($user_status['status'] == 2){//进行中
            $room = RoomModel::db()->getById($user_status['room_id']);

            $user_group_info = $this->paintUserDir($user_status['group_id']);
            $group_user = $user_group_info['group_user'];
            $user_dir = $user_group_info['user_dir'];


            $group_info = GroupModel::db()->getById($user_status['group_id']);

            //废弃的牌
            $trash = GroupRecordModel::db()->getAll(" status = 2 and group_id = ".$user_status['group_id'],'','title');
            //不能抓的牌-且可以查看的2张牌
            $no_record = GroupRecordModel::db()->getAll(" status = 3 and is_show = 1  and group_id = ".$user_status['group_id'] . " order by no ",'','title');
            //用户手里的牌
            $user_record = array();
            foreach($group_user as $k=>$v){
                $user_record[$v] = GroupRecordModel::db()->getAll(" status = 1 and uid = $v and group_id = ".$user_status['group_id'] . " order by title " ,'','title');
                //吃 碰 杠
                $record_change = RecordChangeModel::db()->getAll(" group_id = ".$user_status['group_id']  . " and  uid =  ".$v);
                if(!$record_change)
                    continue;

                //格式化-顺序
                $sort_rs = array();
                foreach($record_change as $k2=>$v2){
                    $record_ids = explode(",",$v['record_ids']);
                    foreach($record_ids as $k3=>$v3){
                        foreach($user_record[$v] as $k4=>$v4){
                            if($v3 == $v4['id']){
                                $sort_rs[] = $v4;
                                break;
                            }
                        }
                    }
                }
                $user_record[$v] = $sort_rs;
            }
            //用户的顺序（自己的牌永远在下方显示，按照：东南西北，依次排开）
            $user_sort = $this->getGroupUserOrderBySelf($group_user,$uid);

            $rs = array(
                //弃牌
                'trash'=>$trash,
                //用户手牌
                'user_record'=>$user_record,
                'no_record_show'=>$no_record,
                'group_user'=>$group_user,
                'dealer_uid'=>$room['dealer_uid'],
                'user_dir_desc'=>$user_dir,
                'user_sort'=>$user_sort,
                'current_catch_uid'=>$group_info['current_catch_uid'],
            );

            return out_ok($rs);

        }else{
            return out_err('用户状态错误，请联系管理员');
        }
    }


############################################################
    //4个人都准备后，建立一个组
    function makeGroup($room_id = 0){
        LogLib::app(" make group start....");
        $where = " status = 0";
        if($room_id){
            LogLib::app("room id :".$room_id);
            $where .= " and room_id = $room_id ";
        }

        $where .= "  order by id limit 4 ";

        $group_user = GroupUserModel::db()->getAll($where,'','group_id,id,uid');
        if(!$group_user)
            return out_ok(' no 4 user record');

        if(count($group_user) < 4)
            return out_ok(' less 4 user record');

        if(count($group_user) > 4)
            return out_ok(' more 4 user record');

        $uids = split_arr($group_user,'uid');
        $ids = split_arr($group_user,'id');

        $room = $this->selectRoom($room_id);
        if($room['code'] != 200)
            return $room;


        $data = array('uids'=>$uids,'status'=>1,'room_id'=>$room['msg'],'current_record_num'=>1,'current_uid'=>0);
        $gid = GroupModel::add($data);

        LogLib::app("new group_id:$gid ");
        //更新报名用户的状态
        $data= array('status'=>1,'group_id'=>$gid,'room_id'=>$room['msg']);
        $rs = GroupUserModel::update($data," id in ( $ids ) limit 4 ");

        LogLib::app("up group_user status:1,rs= ".$rs);

        LogLib::app(" make group end....");

        $this->gameInit($gid);
        //计算当前做庄的UID
        $rs = $this->makeRoomDealerUid($room['msg']);

        return out_ok("ok");
    }
    //4张都已准备好，且已生成房间-组信息，开始生成136张牌，初始化
    function gameInit($gid){
        LogLib::app(" lib func:game init=======start=========");
        $init_record = $this->makeAllRecord();
        LogLib::app("gid:$gid, add record...start");
        foreach($init_record as $k=>$v){
            $data = array('title'=>$v,'group_id'=>$gid);
            $rs = GroupRecordModel::add($data);
            LogLib::app($k."=".$rs ." ",2,2);
        }
        //换行
        LogLib::app("");
        LogLib::app("136 card insert db finish.");


        LogLib::app(" lib func:game init=====end===========");
    }
    //支持一个，跟多个
    function gameStart($group_id = 0){
        if(!$group_id)
            $group = GroupModel::db()->getAll(" status = 1");
        else
            $group = array(array('id'=>$group_id));

        foreach($group as $k=>$v){
            $this->initAllRecord($v['id']);
            $this->sysSendRecord($v['id']);
        }

    }
    //系统初始化发送13张牌
    function sysSendRecord($group_id){
        //共4人，发3次，每次4张然后，每人再一张
        $max = 4 * 4 * 3 + 4;

        $group = GroupModel::db()->getById($group_id);
        $uids = explode(',',$group['uids']);

        LogLib::app(" uids:".$group['uids']);

        //计算抓牌的顺序,第一次要根据庄家的排序计算
        $room = RoomModel::db()->getById($group['room_id']);
        $uids_no = middle_sort_arr($uids,$room['dealer_no']);

        $record = GroupRecordModel::db()->getAll(" 1  order by no limit $max ");
        //每人4张牌
        $z = 0;
        for($i=1;$i <= $max -4;$i=$i+4){
            if($z >3)
                $z = 0;

            $user = $uids_no[$z];
            LogLib::app("uid-".$user.":",2,2);
            $ids = $record[$i-1]['id'] .",".  $record[$i]['id'] .",". $record[$i+1]['id'] .",". $record[$i+2]['id'] ;
            LogLib::app($ids." ",2,2);
            $data = array('uid'=>$user,'status'=>1);
            $rs = GroupRecordModel::update($data," id in ($ids) limit 4 ");

            $z++;

            LogLib::app(" up group record status:".$rs);

        }

        //最后4张
        $z = 0;
        for($j=$i;$j<=$max;$j++){
            if($z >3)
                $z = 0;

            $user = $uids_no[$z];
            LogLib::app("uid-".$user.":",2,2);
            $id = $record[$j-1]['id'] ;
            LogLib::app($id." ",2,2);
            $data = array('uid'=>$user,'status'=>1);
            $rs = GroupRecordModel::upById($data,$id);
            LogLib::app(" up group record status:".$rs);
            $z++;
        }
        //更新组信息
        $data = array('current_record_num'=>$max,'current_uid'=>$uids_no[0],'status'=>2);
        $rs  = GroupModel::upById($data,$group_id);

        LogLib::app(" up group info:".$rs);
        //更新用户信息
        $rs = GroupUserModel::update(array('status'=>2) ," group_id = $group_id limit 4");
        LogLib::app(" up group_user status=2:".$rs);

        return out_ok("ok");

    }
    //更新，当前房间，做庄的UID字段
    function makeRoomDealerUid($room_id){
        $room_filter = RoomModel::filterById($room_id);
        if($room_filter['code'] != 200)
            return out_err($room_filter);

        $room = $room_filter['msg'];

        $group = GroupModel::db()->getRow(" room_id = $room_id ");
        //该组下的-用户ID
        $group_user = explode(',',$group['uids']);
        $dealer_uid = "";//当前庄家
        foreach($group_user as $k=>$v){
            $r = $k+1;
            if($r == $room['dealer_no']){
                $dealer_uid = $v;
                break;
            }
        }

        $d = array('dealer_uid'=>$dealer_uid);
        RoomModel::upById($d,$room_id);

        return out_ok('ok');

    }
    //胡牌后的，收尾结算处理
    function groupEnd($group_id){
        $group = GroupModel::db()->getById($group_id);

        $data = array('status'=>1);
        RoomModel::upById($data,$group['room_id']);

        $data = array('status'=>3);
        GroupModel::upById($data,$group_id);

        $data = array('status'=>3);
        GroupUserModel::upById($data," id in ({$group['uids']}) ");

        $room = RoomModel::db()->getById($group['room_id']);
        //庄家自动+1
        $dealer_no = $room['dealer_no'] + 1;
        if($dealer_no > 4)
            $dealer_no = 1;

        $rs = RoomModel::upById(array('dealer_no'=>$dealer_no),$group['room_id']);
        LogLib::app(" up room dealer:".$rs);

    }
    //开始格式化，每张牌的顺序及状态
    function initAllRecord($group_id){
        $group = GroupModel::db()->getById($group_id);
        $dices = explode(",",$group['dices']);
//        $dices = array(2,5);
        //已庄家两次打骰子的数字和，开始抓的牌,麻将是两张撂在一起的
        $dice_total_num = $dices[0] + $dices[1];
        $start = $dice_total_num * 2;
        LogLib::app("dice tow time total:".$start);

        $record = GroupRecordModel::db()->getAll(" group_id = ".$group_id );
        //正常可抓的牌一共就是136-2*8=120,最后8撂是不能抓的，其中4 8 撂第一张是要翻开的
        $z = 1;
        $end = $start + 120;
        $j = 0;
        for($i = $start;$i < $end;$i++){
            $data = array('no'=>$z);
            if($i > 135){
                LogLib::app( $j . " ",0,0);
                GroupRecordModel::upById($data,$record[$j]['id']);
                $j++;
            }else{
                LogLib::app( $i . " ",0,0);
                GroupRecordModel::upById($data,$record[$i]['id']);
            }
            $z++;
        }

        $st = $i;
        if($i > 135){//
            $st = $j;
        }
        //最后8撂是不能抓的，其中4 8 撂第一张是要翻开的
        for($i = 0 ;$i < 2*8;$i++){
            //其中这8撂的，第4列第一张 明牌，第8列第一张明牌
            if($i== 7 || $i == 15)
                $data['is_show'] = 1;
            if($st > 135){
                LogLib::app( $j . " ",0,0);
                $data = array('no'=>$z,'status'=>3);
                GroupRecordModel::upById($data,$record[$j]['id']);
                $j++;
            }else{
                LogLib::app( $st . " ",0,0);
                $data = array('no'=>$z,'status'=>3);
                GroupRecordModel::upById($data,$record[$st]['id']);
                $st++;
            }

            $z++;

        }
    }
    //开一个房间
    //uid:-1 为系统
    //type:0 用户，1 系统
    function createRoom($uid,$ps = 0,$type = 0){
        $room_num  = RoomModel::db()->getCount(" status != 2 ");
        if($room_num > $this->_room_max)
            return out_err("房间已超过最大值");

        if($type != 1){
            $user_room = RoomModel::db()->getCount(" status !=2 and uid = $uid  ");
            if($user_room > $this->_user_room_max)
                return out_err("用户房间已超过最大值");
        }

        $data = array('uid'=>$uid,'ps'=>$ps,'type'=>$type);
        $room_id = RoomModel::add($data);
        LogLib::app("new room id:$room_id");
        return out_ok($room_id);
    }

    //随机生成136张牌
    function makeAllRecord(){
        $col = 4;
        $arr = array(
            'b'=>array('desc'=>'筒子','row'=>9),
            't'=>array('desc'=>'条子','row'=>9),
            'w'=>array('desc'=>'万子','row'=>9),
            'f'=>array('desc'=>'东南西北','row'=>4),
            'zfb'=>array('desc'=>'中发白','row'=>3)
        );
        $rs = array();
        foreach($arr as $k=>$v){
            for($i=1;$i<=$v['row'];$i++){
                //生成相同的4张
                for($j=1;$j<=$col;$j++){
//                    echo $k . $i ."<br/>";
                    $rs[] = $k . $i;
                }
            }
        }

        LogLib::app("make 136 cards finish");

        return $rs;
    }


    function selectRoom($room_id = 0){
        if($room_id){
            $room = RoomModel::filterById($room_id);
            if($room['status'])
                return out_err("该房间状态为：非等待状态，不能处理");
        }else{
            $room = RoomModel::db()->getRow(" status = 0 ");
            if(!$room){
                $room = $this->createRoom(-1,'',1);
                if($room['code'] != 200)
                    return $room;
                $room_id = $room['msg'];
            }else{
                $room_id = $room['id'];
            }
        }

        LogLib::app(" select room id :$room_id ");

        $data = array('status'=>1);
        RoomModel::upById($data,$room_id);

        LogLib::app(" up room status:1");

        return out_ok($room_id);
    }
    //获取庄家及另外3家信息
    function getDealerInfo($room_id , $uid , $get_dice_dealer_uid = 0){
        $data = array();
        if($get_dice_dealer_uid){
            $dealer = $this->getDiceDealer($room_id,$uid);
            //投骰子的庄家
            $data['dice_dealer_uid'] = $dealer['msg'];
        }

        $room = RoomModel::db()->getById($room_id);

        $user = GroupUserModel::db()->getRow(" uid = ".$uid);
        $user_group_info = $this->paintUserDir($user['group_id']);
        $user_sort = $this->getGroupUserOrderBySelf($user_group_info['group_user'],$uid);

        $data['user_sort'] = $user_sort;
        $data['dealer_uid'] = $room['dealer_uid'];
        $data['user_dir'] = $user_group_info['user_dir'];
        $data['group_user'] = $user_group_info['group_user'];

        return out_ok($data);
    }

    //找庄家-第一次骰子的UID，第二次骨子的UID
    function getDiceDealer($room_id = 0 , $uid = 0){
        if(!$room_id && !$uid)
            return out_err(' room_id and uid both null');

        if(!$room_id && $uid){
            $group_one_user = GroupUserModel::db()->getRow(" uid = $uid and status = 1");
            $room = RoomModel::db()->getById($group_one_user['group_id']);
        }else{
            $room = RoomModel::filterById($room_id);
            if($room['code'] != 200)
                return $room;

            $room = $room['msg'];
        }

        $group = GroupModel::db()->getRow(" room_id = {$room['id']} and status = 1 ");
        if(!$group)
            return out_err("该房间下没有建立组");

        $dices_times = 0;
        if($group['dices']){
            if($group['dices']){
                $dices_times = 1;
                $dices = explode(",",$group['dices']);
                if(count($dices) >= 2)
                    return out_err('数据错误，骰子扔了3次');

                $dices_times = 2;
            }
        }

//        if($group['status'] != 1)
//            out_err('状态为 系统初始化完成，才可以扔骰子');

        if(! $dices_times ){
            return out_ok($room['dealer_uid']);
        }else{
            $group_user = GroupUserModel::db()->getAll(" group_id = {$group['id']} order by id asc ");
            $uids = split_arr($group_user,'uid');
            //第二次要根据第一的结果计算出来
            if($dices[0] == 5 || $dices[0] == 9 ){
                //庄家再打一次
                $uid = $room['dealer_uid'];
            }elseif($dices[0] == 3 || $dices[0] == 7 || $dices[0] == 11 ){
                //对家
                $uid = $this->getDealerUid($uids,$room['dealer_uid'],2);
            }elseif($dices[0] == 2 || $dices[0] == 6 || $dices[0] == 10 ){
                //下家
                $uid = $this->getDealerUid($uids,$room['dealer_uid'],1);
            }elseif($dices[0] == 4 || $dices[0] == 8 || $dices[0] == 12 ){
                //上家
                $uid = $this->getDealerUid($uids,$room['dealer_uid'],-1);
            }

            return out_ok($uid);
        }

    }


    function getDealerUid($uids,$uid,$step = 0){
        if(!is_array($uids))
            $uids = explode(",",$uids);

        $key = 0;
        foreach($uids as $k=>$v){
            if($v == $uid){
                $key = $k;
            }
        }

        $key += $step;
        if($key == -1)
            $key = count($uids)-1;

        $rs = $key + 1 % 4;
        return $uids[$rs - 1];
    }

    function getGroupUserOrderBySelf($uids,$self_uid){
        if(!is_array($uids))
            $uids = explode(",",$uids);

        $key = 0;
        foreach($uids as $k=>$v){
            if($v == $self_uid){
                $key = $k ;
                break;
            }
        }

        $rs = array();
        while(1){
            $rs[] = $uids[$key];
            if(count($rs) >= count($uids)){
                break;
            }

            $key++;
            if($key >= count($uids)){
                $key = 0;
            }
        }

        return $rs;


    }

###################################################
    //回收报名后，失效的用户
    function recoveryUser(){
        GroupUserModel::db()->delete(" status = 0 and ".time()." > timeout");
    }
    //回收用户创建的房间
    function recoveryRoom(){
        $room = RoomModel::db()->getAll(" status = 0");
        foreach($room as $k=>$v){
            $room_user = GroupUserModel::db()->getAll(" status != 3 and room_id = ".$v['id']);
            if(!$room_user){
                RoomModel::upById(array('status'=>2),$v['id']);
            }
        }
    }
    //系统 计算应该谁抓牌了
    function sysGetOneRecord(){

    }
    //创建组-防止有遗漏掉的，系统再次建组
    function sysMakeGroup(){
        $this->makeGroup();
    }
    //发牌-防止有遗漏掉的，系统再次初始化
    function sysGameStart(){
        $this->gameStart();
    }


}