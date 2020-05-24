<?php
/**
 * Created by PhpStorm.
 * User: XiaHB.
 * Date: 2019/3/29
 * Time: 13:54
 */

/**
 * 通过access_log表中的ip地址，去ip表中找到对应的省市,并将数值回写到access_log表中的province&city字段;
 * Class ipMatching
 */
class ipMatching{
    /**
     * InitializationGoldValue constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
    }

    /**
     * access_log表【province,city字段】初始化;
     * ip表【province,city】;
     */
    public function run(){
        $selectSql = " SELECT DISTINCT(ip) FROM access_log WHERE province IS NULL;";
        $select_sql = " SELECT province, city,MIN(start_ip) AS start_ip, MAX(end_ip) AS end_ip FROM ip GROUP BY city ; ";
        $accessResult = AccesslogModel::db()->query($selectSql);
        $counts = count($accessResult);
        $ipResult = AccesslogModel::db()->query($select_sql);
        $count_success = 0;
        $count_fail = 0;
        if(!empty($accessResult) && !empty($ipResult)){
            foreach ($accessResult as $value){
                foreach ($ipResult as $v){
                    if($v['start_ip'] <= $value['ip'] && $value['ip']<= $v['end_ip']){
                        $updateData['province'] = $v['province'];
                        $updateData['city'] = $v['city'];
                        $rs = AccesslogModel::db()->upById($value['id'], $updateData);
                        if(1 == $rs){
                            $count_success ++;
                            $codeSign = '成功';
                        }else{
                            $count_fail ++;
                            $codeSign = '失败';
                        }
                        LogLib::writeGolgcoinHash('-------------------Begin------------------');
                        LogLib::writeGolgcoinHash("access_log表主键ID：{$v['id']},省：{$v['province']},市：{$v['city']},初始化{$codeSign}！");
                        LogLib::writeGolgcoinHash('--------------------End-------------------');
                    }
                }
            }
            exit(" SuccessLines: $count_success/$counts FailLines:$count_fail/$counts");
        }else{
            exit(" No User Result Back !");
        }
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
}

$test = new ipMatching();
$test->run();