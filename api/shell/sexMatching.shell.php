<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/4/1
 * Time: 9:45
 */

/**
 * 1.此脚本用于初始化access_log表的新增字段【sex】;
 * 2.user，关联字段id,uid，时间：当前时间节点;
 * Class sexMatching
 */

class sexMatching{
    /**
     * sexMatching constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
    }

    /**
     * access_log表【sex】初始化;
     * 0:未知;1:男;2:女;
     */
    public function run(){
        $this->getInfo(5000, function ($accessResult) {
            unset($accessResult[0], $accessResult[1]);
            if(!empty($accessResult) && is_array($accessResult)){
                foreach ($accessResult as $k=>$accessInfo) {
                    if($accessInfo['uid_new']){
                        $resSingle = userModel::db()->getById($accessInfo['uid_new']);
                        $counts = count($accessResult);
                        $count_success = 0;
                        $count_fail = 0;
                        if($resSingle){
                            $updateData["sex"] = $resSingle["sex"];
                            $rs = AccesslogModel::db()->update(array('sex'=>$updateData["sex"]), "uid = {$accessInfo['uid_new']} limit $counts");
                            if($rs){
                                $count_success ++;
                                $codeSign = '成功';
                            }else{
                                $count_fail ++;
                                $codeSign = '失败';
                            }
                            LogLib::writeGolgcoinHash('-------------------Begin------------------');
                            LogLib::writeGolgcoinHash("用户ID：{$accessInfo['uid_new']},性别：{$resSingle['sex']},初始化{$codeSign}！");
                            LogLib::writeGolgcoinHash('--------------------End-------------------');
                        }
                    }
                }
                exit(" SuccessLines: $count_success/500 FailLines:$count_fail/500");
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
            $result = AccesslogModel::db()->getAll("1 = 1 AND sex is null or sex = 0 group by uid limit {$count}", '', 'distinct(uid) as uid_new');
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

$test = new sexMatching();
$test->run();