<?php
//将分散的 ws 日志，汇总到一张表里面
class MakeWsLogCnt{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        ini_set('display_errors','On');

        $addTime = time();
        $day = date("Ymd",strtotime(date("Ymd"))- 24 * 60 * 60 );
        $ym = date("Ym",strtotime(date("Ymd"))- 24 * 60 * 60);



        echo "脚本执行时间：".date("Y-m-d H:i:s", $addTime)."\n";
        // 排重
        $item = WsCntModel::getLatestItem($ym);
        $lastInsertDay = date("Ymd", $item['a_time']);
        $nowDay = date("Ymd", $addTime);
        if($lastInsertDay == $nowDay){
            exit("$lastInsertDay 重复导入，导入失败！\n");
        }

        // $data = WsLogModel::countCalcDayData($day);
        $data = WsLogModel::countCalcDayData2($day);
        $countData = count($data);
        if(!$data){
            exit("no data");
        }
        echo "总数据".$countData."条\n";
        $addData = [];
        echo "数据重整中……\n";
        foreach ($data as $k=>$v) {
            echo $k . " ";
            // $s_time_str = explode(',',$v['a_times']);
            // $e_time_str = explode(',',$v['e_times']);

            // $total_time = 0;
            // for($i=0;$i<count($s_time_str);$i++){
            //     $one_time = 0;
            //     if(arrKeyIssetAndExist($e_time_str,$i)){
            //         $one_time = $e_time_str[$i] - $s_time_str[$i];
            //     }
            //     $total_time += $one_time;
            //     // if($one_time > 10000) echo $e_time_str[$i]."  ".$s_time_str[$i];
            // }
// 
            $insert = array(
                'ip'=>$v['ip'],
                'device_id'=>$v['device_id'],
                'uid'=>$v['uid'],
                'login_times'=>$v['cnt'],
                'total_time'=>$v['total'],
                // 'start_time'=>$v['a_time'],
                // 'end_time'=>$v['e_time'],
                'a_time'=>$addTime,
            );
            $addData[] = $insert;

        }
        echo "\n";
        $countAddData = count($addData);
        echo "重整数据".$countAddData."条\n";
        // 获取昨天的表中最新一条数据
        $item = WsCntModel::getLatestItem($ym);
        $lastInsertDay = date("Ymd", $item['a_time']);
        $nowDay = date("Ymd", $addTime);
        // 如果今天未添加过
        if($lastInsertDay != $nowDay){
            if($addTime > $item['a_time']){
                $res = WsCntModel::db()->addAll($addData,"ws_cnt_".$ym);
                if($res){
                    echo "导入成功!(".$countAddData."/".$countData.")\n";
                }else{
                    echo "数据库导入失败！\n";
                }
            }
        }else{
            echo "导入失败，数据已存在！\n";
        }


//        if(!arrKeyIssetAndExist($attr,'ac')){
//            exit("please ac=xxx ,wsLog . \n");
//        }
//
//        $ac = $attr['ac'];
//        $this->$ac();
    }

}



