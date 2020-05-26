<?php
class SignService{

    //获取一个用户，连续签到的天数
    function getJunction7Day($uid){
        $list = UserSignModel::db()->getAll(" uid = {$uid} order by id desc limit 7 ");
        if(!$list){
            return [];
        }

        $today = dayStartEndUnixtime();
        if($list[0]['day_start_time'] == $today['s_time']){//判断今天是否签到了
            $isToday = 1;
        }elseif($list[0]['day_start_time'] == $today['s_time'] - 24 * 60 * 60){//判断昨天是否签到了
            $isToday = 0;
        }else{
            return [];
        }

        $sign = [];
        $end = count($list) ;
        for($i=0;$i < $end;$i++){
            if($i == $end - 1){//证明是最后一个
                $sign[] = $list[$i];
            }else{
                //如果当天跟前一天，相隔24小时，证明断了~非连续签到
                if($list[$i]['day_start_time'] - $list[$i+1]['day_start_time'] > 24 * 60 * 60){
                    $sign[] = $list[$i];
                    break;
                }else{
                    $sign[] = $list[$i];
                }
            }
        }

        return $sign;

    }

    function getUser24List($uid){
        $list = SignModel::getUserLisByDay($uid);
        $list24 = [];

//        if(!$list){
//            for($i = 0;$i<24 ;$i++){
//                $list24[] = 0;
//            }
//        }else{
            $today = dayStartEndUnixtime();
            for($i = 0;$i<24 ;$i++){
                $f = 2;
                $hoursStartTime = $today['s_time'] + $i * 60 *60 ;
                $hoursEndTime = $today['s_time'] + ($i + 1) * 60 *60 ;
                if($list){
                    foreach ($list as $k=>$v) {
                        if($v['a_time'] >= $hoursStartTime and $v['a_time'] <= $hoursEndTime){
                            $f = 1;
                            break;
                        }
                    }
                }

                $arr = array('hour'=>$i,'isSign'=>$f,'current_hour'=>date("H"));
                $list24[] = $arr;
            }
//        }
        return $list24;
    }

    //签到列表 - 以 月/周 为一个大维度
    function getWeekList($uid){
        $reward = $GLOBALS['main']['signReward'];
        $today = dayStartEndUnixtime();

        $list = UserSignModel::db()->getAll(" uid = {$uid} order by id desc limit 7 ");
//        $list = '[{"id":"23626","uid":"300017","a_time":"1554185125","reward":"106","day_start_time":"1554134400","sign_time":"1"},{"id":"16264","uid":"300017","a_time":"1553936866","reward":"106","day_start_time":"1553875200","sign_time":"1"},{"id":"16261","uid":"300017","a_time":"1553936683","reward":"106","day_start_time":"1553875200","sign_time":"1"},{"id":"12755","uid":"300017","a_time":"1553739950","reward":"278","day_start_time":"1553702400","sign_time":"4"},{"id":"11891","uid":"300017","a_time":"1553650563","reward":"486","day_start_time":"1553616000","sign_time":"3"},{"id":"11071","uid":"300017","a_time":"1553566451","reward":"128","day_start_time":"1553529600","sign_time":"2"},{"id":"10577","uid":"300017","a_time":"1553499792","reward":"106","day_start_time":"1553443200","sign_time":"1"}]';
//        $list = '[{"id":"23031","uid":"300561","a_time":"1554173137","reward":"1388","day_start_time":"1554134400","sign_time":"7"},{"id":"20499","uid":"300561","a_time":"1554101922","reward":"1388","day_start_time":"1554048000","sign_time":"7"},{"id":"18407","uid":"300561","a_time":"1554004664","reward":"1388","day_start_time":"1553961600","sign_time":"7"},{"id":"15166","uid":"300561","a_time":"1553901641","reward":"1388","day_start_time":"1553875200","sign_time":"7"},{"id":"13514","uid":"300561","a_time":"1553826290","reward":"1388","day_start_time":"1553788800","sign_time":"7"},{"id":"12647","uid":"300561","a_time":"1553730058","reward":"1388","day_start_time":"1553702400","sign_time":"7"},{"id":"12110","uid":"300561","a_time":"1553672159","reward":"1388","day_start_time":"1553616000","sign_time":"7"}]';
//        $list = (json_decode($list,true));

        LogLib::appWriteFileHash($list);
        if(!$list){
            //此人过去6天，没有签到过
            foreach($reward as $k=>$v){
                $v['isSign'] = 0;
                $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
                $list[] = $v;
            }

            return out_pc(200,$list);
        }
        LogLib::appWriteFileHash($list[0]);



        //计算出连续签到的天数，如果今天和昨天没有签到，就证明，已经断了，从第一天开始就行了
        if($list[0]['day_start_time'] == $today['s_time']){//判断今天是否签到了
            LogLib::appWriteFileHash(" in today");
            $isToday = 1;
        }elseif($list[0]['day_start_time'] == $today['s_time'] - 24 * 60 * 60){//判断昨天是否签到了
            $isToday = 0;
            LogLib::appWriteFileHash(" in yesterday");
        }else{
            LogLib::appWriteFileHash(" in no day ");
            $list = null;
            foreach($reward as $k=>$v){
                $v['isSign'] = 0;
                $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
                $list[] = $v;
            }

            return out_pc(200,$list);
        }
        $sign = [];
        $end = count($list) ;

        for($i=0;$i < $end;$i++){
            if($i == $end - 1){//证明是最后一个
                $sign[] = $list[$i];
            }else{
                //如果当天跟前一天，相隔24小时，证明断了~非连续签到
                if($list[$i]['day_start_time'] - $list[$i+1]['day_start_time'] > 24 * 60 * 60){
                    $sign[] = $list[$i];
                    break;
                }else{
                    $sign[] = $list[$i];
                }
            }
        }
        //倒序
        $sign = array_reverse($sign);
        //1234567
        //2345671
        //3456712
        //4567123
        //5671234
        //6712345
        //7123456

        if($isToday == 1){
            if(count($sign) == 7){
                //证明是连续签到7天，有2种可能
                //1，就是正常的从第1天到第7天
                if($sign[0]['sign_time']  == 1){

                }else{
                    //2，可能是之前连续签了7三，接着又连续签到2天
                    $e =  7 - $sign[0]['sign_time'];
                    for($i=0;$i<=$e;$i++){
                        unset($sign[$i]);
                    }
                    $sign = array_values($sign);
                }
            }
        }else{
            if(count($sign) == 7){
                //证明是连续签到7天，有2种可能
                //1，就是正常的从第1天到第7天，所以今天再签到，得重新轮回来了
                if($sign[0]['sign_time']  == 1){
                    $list = null;
                    foreach($reward as $k=>$v){
                        $v['isSign'] = 0;
                        $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
                        $list[] = $v;
                    }

                    return out_pc(200,$list);
                }else{
                    $e =  7 - $sign[0]['sign_time'];
                    for($i=0;$i<=$e;$i++){
//                    echo $i;
                        unset($sign[$i]);
                    }

                    $sign = array_values($sign);
                }
            }
        }
        LogLib::appWriteFileHash($sign);

        //这里做个容错 ，之前的数据错误，得修复
        foreach ($sign as $k=>$v) {
            if(arrKeyIssetAndExist($v,'sign_time')){

            }
        }

        $rs = array();
        $dayStartTime = 0;
        foreach($reward as $k=>$v){
            $dayStartTime +=   24 * 60 * 60;
            $row = $v;
            if(arrKeyIssetAndExist($sign,$k)){//证明这天已经领取过了
                $row['isSign'] = 1;
                $row['dayStartTime'] = $sign[$k]['day_start_time'];
            }else{
                $row['isSign'] = 0;
                if(!$k){
                    $row['dayStartTime'] = $today['s_time'] ;
                }else{
                    $row['dayStartTime'] = $rs[$k-1]['dayStartTime'] + (  24 * 60 * 60);
                }
//                LogLib::appWriteFileHash(['bbbbbbb',$k,$row['dayStartTime']]);
            }
            $rs[] = $row;
        }

        return out_pc(200,$rs);








        //==========================================================================



//        //先找出过去7天，用户的签到情况
//        $before7day = $today['s_time'] - 6 * 24 * 60 * 60;
//        $list = SignModel::db()->getAll(" uid = {$uid} and day_start_time >= $before7day order by day_start_time desc limit 7 ");
//        if(!$list){
//            //此人过去6天，没有签到过
//            foreach($reward as $k=>$v){
//                $v['isSign'] = 0;
//                $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
//                $list[] = $v;
//            }
//
//            return out_pc(200,$list);
//        }
//
//        //计算出连续签到的天数，如果今天和昨天没有签到，就证明，已经断了，从第一天开始就行了
//        if($list[0]['day_start_time'] == $today['s_time']){//判断今天是否签到了
//        }elseif($list[0]['day_start_time'] == $today['s_time'] - 24 * 60 * 60){//判断昨天是否签到了
//        }else{
//            $list = null;
//            foreach($reward as $k=>$v){
//                $v['isSign'] = 0;
//                $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
//                $list[] = $v;
//            }
//
//            return out_pc(200,$list);
//        }
//        //先取出用户连续的签到天数
//        $sign = [];
//        $end = count($list) ;
//
//        for($i=0;$i < $end;$i++){
//            if($i == $end - 1){//证明是最后一个
//                $sign[] = $list[$i];
//            }else{
//                //如果当天跟前一天，相隔24小时，证明断了~非连续签到
//                if($list[$i]['day_start_time'] - $list[$i+1]['day_start_time'] > 24 * 60 * 60){
//                    break;
//                }else{
//                    $sign[] = $list[$i];
//                }
//            }
//        }
//        //倒序
//        $sign = array_reverse($sign);
//
//        //证明该用户，连续7天签到()
//        //1直到昨天，一直是连续7天
//        //2直到今天，一直连续是7天
//        if(count($sign) == 7){
//
//            if( $sign[0]['sign_time'] != 1 && $sign[6]['day_start_time'] != $today['s_time']){
//                $e =  6 - $sign[0]['sign_time'];
//                for($i=0;$i<$e;$i++){
////                    echo $i;
//                    unset($sign[$i]);
//                }
//
//                $sign = array_values($sign);
//            }else{
//                $end = 7 - $sign[0]['sign_time'];
//                for($i=0;$i<= $end ;$i++){
////                    echo $i;
//                    unset($sign[$i]);
//                }
//
//                $sign = array_values($sign);
//            }
//        }elseif(count($sign) == 6){
//            if($sign[0]['sign_time'] == 2 && $sign[5]['sign_time'] == 7 ){
//                //证明该用户，连续7天签到（一直到昨天）
//                //因为取7天最近的记录，是以今天为维度，过去连续7天，只能取出6天
//
//                $list = null;
//                foreach($reward as $k=>$v){
//                    $v['isSign'] = 0;
//                    $v['dayStartTime'] = $today['s_time'] + $k *  24 * 60 * 60;
//                    $list[] = $v;
//                }
//
//                return out_pc(200,$list);
//            }elseif( $sign[0]['sign_time'] != 1){
//                var_dump(6,"aaaa");exit;
////                $e = 5 - $sign[0]['sign_time'];
////                for($i=0;$i< $e ;$i++){
////                    unset($sign[$i]);
////                }
////                $sign = array_values($sign);
//            }
//        }
//
//        $rs = array();
//        $dayStartTime = 0;
//        foreach($reward as $k=>$v){
//            $dayStartTime +=   24 * 60 * 60;
//            $row = $v;
//            if(arrKeyIssetAndExist($sign,$k)){//证明这天已经领取过了
//                $row['isSign'] = 1;
//                $row['dayStartTime'] = $sign[$k]['day_start_time'];
////                echo "aa";var_dump($k);
//            }else{
////                echo "bb";var_dump($k);
//                $row['isSign'] = 0;
//                if(!$k){
//                    $row['dayStartTime'] = $today['s_time'] ;
//                }else{
//                    $row['dayStartTime'] = $rs[$k-1]['dayStartTime'] + (  24 * 60 * 60);
//                }
//
////                LogLib::appWriteFileHash(['bbbbbbb',$k,$row['dayStartTime']]);
//            }
//            $rs[] = $row;
//        }
//
//        return out_pc(200,$rs);
    }


}