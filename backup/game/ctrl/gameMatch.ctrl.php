<?php
//长连接 匹配相关
class GameMatchCtrl extends BaseCtrl {
    //报名匹配
    function userMatchSign($type,$userSex){
        $rs = $this->gameMatchService->userMatchSign($this->uid,$type,$userSex);
        return $this->out($rs['code'],$rs['msg']);
    }
    //取消匹配
    function cancelMatch($type){
        $rs = $this->gameMatchService->cancelSignMatch($this->uid,$type);
        return $this->out($rs['code'],$rs['msg']);
    }
    //游戏开始
    function startGame($roomId){
        $rs = $this->gameMatchService->startGame($this->uid,$roomId);
        return $this->out($rs['code'],$rs['msg']);
    }

    //实时同步游戏比分数据
    function realtimeRsyncMsg($roomId,$score){
        $rs = $this->gameMatchService->realtimeRsyncMsg($this->uid,$roomId,$score);
        return $this->out($rs['code'],$rs['msg']);
    }
    //游戏结算
    function gameEndTotal($roomId,$rs){
        $rs = $this->gameMatchService->gameEndTotal($this->uid,$roomId,$rs);
        return $this->out($rs['code'],$rs['msg']);
    }

    //-------------上面，是一套完整的 匹配流程------------------------

    //用户离开了房间
    function userLeaveGameRoom($roomId){
        $rs = $this->gameMatchService->userLeaveGameRoom($this->uid,$roomId);
        return $this->out($rs['code'],$rs['msg']);
    }
    //申请再来一局
    function againGameApply($roomId){
        $rs = $this->gameMatchService->againGameApply($this->uid,$roomId);
        return $this->out($rs['code'],$rs['msg']);
    }
    //同意再来一局
    function agreeAgainGameApply($roomId){
        $rs = $this->gameMatchService->agreeAgainGameApply($this->uid,$roomId);
        return $this->out($rs['code'],$rs['msg']);
    }


}