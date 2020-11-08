<?php
/**
 * Class splitTaskUserGroup
 */
class splitTaskUserGroup{
    /**
     * InitializationGoldValue constructor.
     * @param $c
     */
    public function __construct($c){
        $this->commands = $c;
    }

    /**
     * 按用户id取余拆分;
     * task_user_group;
     */
    public function run(){
        set_time_limit(0);
        $t1 = microtime(true);
        
        $list = TaskUserModel::db()->getAll("task_config_type = 2");
        if(!$list){
            exit(" no data");
        }

        echo "count data list:".count($list) ."\n" ;

        foreach ($list as $k=>$v) {
            $num_suffix = ($v['uid'] % 3);
            echo $v['uid'];
            $insert_data = array(
                'uid'=>$v['uid'],
                'a_time'=>$v['a_time'],
                'task_id'=>$v['task_id'],
                'step'=>$v['step'],
                'done_time'=>$v['done_time'],
                'goldcoin'=>$v['goldcoin'],
                'point'=>$v['point'],
                'reward_time'=>$v['reward_time'],
                'u_time'=>$v['u_time'],
                'task_config_type'=>$v['task_config_type'],
                'task_config_type_sub'=>$v['task_config_type_sub'],
                'total_step'=>$v['total_step'],
                'sort'=>$v['sort'],
            );
            $newId = TaskUserGroupMoreModel::add($insert_data, $num_suffix);
            if($newId){
                echo" rs:1";
            }else{
                echo "rs:0";
            }
            echo "\n";
        }
        $t2 = microtime(true);
        echo '耗时'.round($t2-$t1,3).'秒';echo "\n";
        echo "done\n";

        exit();
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

$test = new splitTaskUserGroup();
$test->run();