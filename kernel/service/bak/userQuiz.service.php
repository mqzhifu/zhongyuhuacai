<?php
/**
 * Class UserQuizService
 */
class UserQuizService{
    /**
     * Create user toke
     * @param $uid
     * @return array|mixed
     */
    public function createToken($uid){
        $token = TokenLib::create($uid);
        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['token']['key'],$uid);
        RedisPHPLib::set($key,$token,$GLOBALS['rediskey']['token']['expire']);
        return $token;
    }

    /**
     * @param $uid
     * @param $coins
     * @param $type
     * @param int $opt
     * @return bool
     */
    public function updateUserGoldCoins($uid, $coins, $type, $opt = 1){
        $userInfo = UserModel::db()->getById($uid);
        $goldcoin_now = $userInfo['goldcoin'];
        $updateData = [];
        if(1 == $opt){
            $updateData['goldcoin'] = $goldcoin_now + $coins;
        }elseif (2 == $opt){
            $updateData['goldcoin'] = $goldcoin_now - $coins;
        }
        $updateData['u_time'] = time();
        $result = UserModel::db()->upById($uid, $updateData);
        if($result){
            $this->addGoldCoinsLog($uid, $coins, $opt, $type);
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $uid
     * @param $num
     * @param int $opt
     * @param $title
     */
    public function addGoldCoinsLog($uid, $num, $opt = 1, $title){
        $insertData = [];
        $insertData['uid'] = $uid;
        $insertData['num'] = $num;
        $insertData['opt'] = $opt;
        $insertData['title'] = $title;
        $insertData['a_time'] = time();
        $insertData['u_time'] = time();
        GoldcoinLogModel::db()->add($insertData);
    }

    /**
     * @param $uid
     * @param $balance
     */
    public function updateUserBalance($uid, $balance){
        $userInfo = UserModel::db()->getById($uid);
        if($userInfo && isset($userInfo['goldcoin']) && isset($userInfo['balance']) && isset($userInfo['gift_card'])){
            $balance_now = $userInfo['balance'];
            $gift_card = $userInfo['gift_card'];
        }else{
            $balance_now = 0;
            $gift_card = 0;
        }
        $updateData['balance'] = $balance_now + $balance;
        $updateData['gift_card'] = $gift_card + $balance;
        $updateData['u_time'] = time();
        UserModel::db()->upById($uid, $updateData);
    }

    /**
     * @param $uid
     * @param $weaponType
     */
    public static function addProps($uid, $weaponType, $nums = 0){
        $rs = userWeaponModel::db()->getRow(" uid = $uid AND weapon_id = $weaponType ");
        $nums_now = $rs['nums'];
        $upData = array(
            'nums' => $nums_now + $nums,
            'u_time' => time()
        );
        userWeaponModel::db()->update($upData, "uid=$uid and weapon_id = $weaponType limit 1");
    }
}