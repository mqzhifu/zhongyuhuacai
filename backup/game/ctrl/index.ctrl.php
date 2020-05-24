<?php
class IndexCtrl extends BaseCtrl  {

    function index(){
        echo "welcome!";
        exit;
    }

    function getServer(){
        include_once APP_CONFIG."/server.php";
        return $this->out(200,$GLOBALS['server']['gameMatch']);
    }

    function heartbeat(){
        return $this->out(200);

    }

    function getUinfo(){
        $uinfo = $this->matchService->getUinfoByUid($this->uid);
        return $this->out(200,$uinfo);
    }

    function getRoomInfo($roomId){
        $info  = $this->matchService->getRoomById($roomId);
        return $this->out(200,$info);
    }

    function getRoomRsyncMsg($roomId){
        $info  = GameRsyncLogModel::db()->getAll(" room_id = '$roomId' ",null,' uid,a_time,score');
        if($info){
            foreach ($info as $k=>$v) {
                $info[$k]['aTime'] = $v['a_time'];
            }
        }
        return $this->out(200,$info);
    }

    function clearUserInfoRoomInfo(){
        $uinfo = $this->matchService->getUinfoByUid($this->uid);
        if(!$uinfo){
            Loglib::wsWriteFileHash ('uinfo is null,error');
            return true;
        }

        if($uinfo['status'] == UserModel::$_status_normal){
            Loglib::wsWriteFileHash('uinfo status no need clear,error');
            return true;
        }

        if($uinfo['roomId']){
            $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['rsyncMsg']['key'],$uinfo['roomId']);
            RedisPHPLib::getServerConnFD()->del($key);
        }

        $type = 1;
        $this->matchService->delMatchedRealUser($this->uid,$type);
        $this->matchService->delUserSign($this->uid,$type);
        $this->matchService->delUserSignPoolByUid($type,$this->uid);

        if($uinfo['status'] == UserModel::$_status_playing){
            RoomModel::db()->delete(" room_id =  '{$uinfo['roomId']}' ");
        }

        $rs = $this->matchService->upUinfoByField($this->uid,array('status'=>UserModel::$_status_normal));
        return out_pc(200,$this->uid." ".$rs);
    }

}