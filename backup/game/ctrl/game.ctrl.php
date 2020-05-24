<?php
//短连接，游戏数据获取相关
class GameCtrl extends BaseCtrl {
    //当前用户排名
    function getGameRank(){
        $rs = $this->gameMatchService->getGameRank($this->uid);
        return $this->out($rs['code'],$rs['msg']);
    }

    function getUserGameTotalInfo($gameId){
        $rs = $this->gameMatchService->getUserGameTotalInfo($this->uid,$gameId);
        return $this->out($rs['code'],$rs['msg']);
    }


}