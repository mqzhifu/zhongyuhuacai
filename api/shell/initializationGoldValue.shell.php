<?php
/**
 * Created by PhpStorm.
 * User: XiaHB.
 * Date: 2019/3/29
 * Time: 9:36
 */

/**
 * 1.此脚本用于初始化user表的新增字段【goldcoin_sum,goldcoin_sum_less】;
 * 2.数据来源goldcoin_log，关联字段uid，时间：当前时间节点;
 * Class InitializationGoldValue
 */
class initializationGoldValue{
    /**
     * InitializationGoldValue constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
    }

    /**
     * user表【goldcoin_sum & goldcoin_sum_less】初始化;
     */
    public function run(){
        set_time_limit(0);

        $f = 1;
        if($f == 1){
            $opt = 1;
            $filed = "goldcoin_sum";
        }else{
            $opt = 2;
            $filed = "goldcoin_sum_less";
        }


        $sql = "select sum(num) as sum,uid from goldcoin_log where opt= $opt group by uid";
        $list = GoldcoinLogModel::db()->getAllBySQL($sql);
        if(!$list){
            exit(" no data");
        }

        echo "count data list:".count($list) ."\n" ;

        $service = new UserService();
        foreach ($list as $k=>$v) {
            echo $v['uid']." ".$v['sum'];
            $data = array($filed=>$v['sum']);
            $rs = $service->upUserInfo($v['uid'],$data);
            if($rs['msg']){
                echo" rs:1";
            }else{
                echo "rs:0";
            }

            echo "\n";
        }

        echo "done\n";

        exit;

        $userService = new UserService();
        $this->getInfo(5000, function ($userResult) use ($userService) {
            if(!empty($userResult) && is_array($userResult)){
                $count_success = 0;
                foreach ($userResult as $k=>$userInfo) {
                    $resSingle = GoldcoinLogModel::getByUid($userInfo['id']);
                    $count_fail = 0;
                    if($resSingle){
                        $updateData['goldcoin_sum'] = $resSingle['positive_num'];// 获取数;
                        $updateData['goldcoin_sum_less'] = $resSingle['negative_num'];// 消耗数;
                        $rs = UserModel::db()->upById($userInfo['id'], $updateData);
                        if(1 == $rs){
                            // 更新userService缓存操作（只更新goldcoin_sum字段）;
                            $userService->upUserInfo($userInfo['id'], array('goldcoin_sum'=>$updateData['goldcoin_sum']));
                            $count_success ++;
                            $codeSign = '成功';
                        }else{
                            $count_fail ++;
                            $codeSign = '失败';
                        }
                        LogLib::writeGolgcoinHash('-------------------Begin------------------');
                        LogLib::writeGolgcoinHash("用户ID：{$userInfo['id']},金币总数：{$resSingle['positive_num']},消耗数：{$resSingle['negative_num']},初始化{$codeSign}！");
                        LogLib::writeGolgcoinHash('--------------------End-------------------');

                    }
                }
                exit(" SuccessLines: $count_success/5000 FailLines:$count_fail/5000");
            }else{
                exit(" No User Result Back !");
            }
        });
    }

    /**
     * @param $str
     */
    function o($str){
        if(PHP_OS == 'WINNT'){
            $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
        }
        echo $str."\n";
    }

    /**
     * @param $count
     * @param $callback
     * @return bool
     */
    public function getInfo($count, $callback)
    {
        do {
            $result = UserModel::db()->getAll("1 = 1 AND goldcoin_sum is null or goldcoin_sum = 0 limit {$count}", '', 'id, goldcoin_sum, goldcoin_sum_less');
            $countResult = count($result);
            if ($countResult == 0) {
                break;
            }
            if ($callback($result) === false) {
                return false;
            }
            unset($result);
        } while ($countResult == $count);
        return true;
    }
}

$test = new initializationGoldValue();
$test->run();