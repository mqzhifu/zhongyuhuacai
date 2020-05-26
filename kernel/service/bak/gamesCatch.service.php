<?php

/**
 * 游戏缓存处理;
 * Class gamesCatchService
 */
class gamesCatchService{

    /**
     * @var array|string
     */
    public $commonParam = [];

    /**
     * gamesCatchService constructor.
     */
    public function __construct ()
    {
        $this->commonParam = $this->getCommonParam();
    }

    /**
     * @return string
     */
    public function getCommonParam ()
    {
        // 测试数据：$key = 'instantplay_game_admin';
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['gameList']['key'], 'admin');// open_gameList_admin;
        return $key;
    }

    /**
     * 获取所有游戏列表;
     * @return array
     */
    public function getGamesList(){
        $info = RedisPHPLib::getServerConnFD()->hGetAll($this->getCommonParam());
        $gamesList = [];
        if(!empty($info)){
            foreach ($info as $k => $value){
                $gameRow = json_decode($value, true);
                array_push($gamesList, $gameRow);
            }
        }
        return $gamesList;
    }

    /**
     * 获取单条游戏信息;
     * @param $game_id
     * @return array|mixed
     */
    public function getGameRow($game_id){
        $gameInfo = RedisPHPLib::getServerConnFD()->hGet($this->getCommonParam(), $game_id);
        if(!empty($gameInfo)){
            return json_decode($gameInfo, true);
        }else{
            return [];
        }
    }

    /**
     * 添加单条游戏信息;
     * @param $game_id
     * @param $content
     * @return int
     */
    public function addGameRow($game_id, $content){
        $code = 0;
        $content = json_encode($content);
        $rs = RedisPHPLib::getServerConnFD()->hSet($this->getCommonParam(),$game_id, $content);
        if($rs){
            $code = 1;
            return $code;
        }
        return $code;
    }

    /**
     * 修改单条游戏信息;
     * @param $game_id
     * @param $content
     * @return int
     */
    public function updateGameRow($game_id, $content){
        $code = 0;
        $rs = RedisPHPLib::getServerConnFD()->hSet($this->getCommonParam(),$game_id, $content);
        if($rs){
            $code = 1;
            return $code;
        }else{
            return $code;
        }
    }

    /**
     * 这个方法暂时注释掉，现在没有删除逻辑了,既然写了就留着吧，有备无患;
     * 删除单条游戏信息;
     * @param $game_id
     * @return int
     */
    /*public function delGameRow($game_id){
        $code = 0;
        $rs = RedisPHPLib::getServerConnFD()->hDel($this->getCommonParam(), $game_id);
        if($rs){
            $code = 1;
            return $code;
        }else{
            return $code;
        }
    }*/
}