<?php
//
function get_default_date($unixtime,$default = '--'){
    if(!$unixtime){
        return $default;
    }else{
        return date("Y-m-d H:i:s",$unixtime);
    }
}
//取得<周>相关信息
function get_week_info($gdate = ""){
    if(!$gdate) $gdate = date("Y-m-d");
    $w = date("w", strtotime($gdate));//取得一周的第几天,星期天开始0-6
    $dn = $w - 1;
// 	if(!$dn)$dn = 6;
    //本周开始日期
    $st = date("Y-m-d", strtotime("$gdate -".$dn." days"));
    //本周结束日期
    $en = date("Y-m-d", strtotime("$st +6 days"));
    //上周开始日期
    $last_st = date('Y-m-d',strtotime("$st - 7 days"));
    //上周结束日期
    $last_en = date('Y-m-d',strtotime("$st - 1 days"));
    return array('start_date'=>$st,'end_date'=> $en,'last_start_date'=> $last_st,'last_end_date'=>$last_en);//返回开始和结束日期
}

function get_year_list_range($start,$end){
    $list = null;
    for ($i=$start;$i<=$end;$i++){
        $list[] = $i;
    }
    return $list;
}

function get_month_list($zero = 0){
    $list = null;
    for ($i=1;$i<=12;$i++){
        if($i < 10 && $zero){
            $list[] = "0".$i;
        }else{
            $list[] = $i;
        }
    }
    return $list;
}

//获取一个月的最后一天
function get_month_last_day($year,$month){
    if(substr($month,0,1) === 0)
        $month = substr($month,1,1);

    $rs = 0;
    switch ($month){
        case 1:
            $rs = 31;break;
        case 2:
            if($year % 4 == 0)
                $rs = 28;
            else
                $rs = 29;
            break;
        case 3:
            $rs = 31;break;
        case 4:
            $rs = 30;break;
        case 5:
            $rs = 31;break;
        case 6:
            $rs = 30;break;
        case 7:
            $rs = 31;break;
        case 8:
            $rs = 31;break;
        case 9:
            $rs = 30;break;
        case 10:
            $rs = 31;break;
        case 11:
            $rs = 30;break;
        case 12:
            $rs = 31;break;
        default:

    }

    return $rs;
}

function date_week($unixtime)
{
    $_week = date('N', $unixtime);
    $week = '星期日';
    switch ($_week) {
        case 1:
            $week = '星期一';
            break;
        case 2:
            $week = '星期二';
            break;
        case 3:
            $week = '星期三';
            break;
        case 4:
            $week = '星期四';
            break;
        case 5:
            $week = '星期五';
            break;
        case 6:
            $week = '星期六';
            break;
        default:
            $week = '星期日';
            break;
    }

    return $week;
}


function get24HourOption(){
    $time_between = "<option '全天均可'>全天均可</option>";
    for($i=8;$i<=20;$i++){
        $start = $i;
        if($i < 10)
            $start = "0".$i;

        $end = $i + 1;
        if($end < 10)
            $end = "0".$end;

        $str =$start.":00 - ".$end . ":00";
        $time_between .= "<option value='$str'>$str</option>";
    }
    return $time_between;
}

//格式化当前时间
function time_format($time=0){
    $curtime=time();
    $diff = $curtime-$time;
    $str = '';
    if($diff<60){
        $str='刚刚';
    }elseif($diff<3600){
        $str=intval($diff/60).'分钟前';
    }elseif($diff<43200){
        $str=intval($diff/3600).'上午';
    }elseif($diff<86400){
        $str=intval($diff/3600).'下午';
    }else{
        $str=date("Y-m-d H:i",$time);
    }
    return $str;
}

function ymdTurnCn($time){
    if(!$time ){
        $time = time();
    }

    return date("Y",$time) . '年' . date("m",$time) . '月'. date("d",$time) . '日';
}

//获取当天的  开始时间  结束时间 的 unixtime
function dayStartEndUnixtime($day = null){
    if(!$day){
        $day = date("Y-m-d");
    }

    $day = date("Y-m-d",strtotime($day));

    $s_time = strtotime($day." 00:00:00");
    $e_time = $s_time + 24 * 60 *60 - 1;

    return array('s_time'=>$s_time,'e_time'=>$e_time);
}

//验证日期
function valid_date($date)
{
    //匹配日期格式
    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
    {
        //检测是否为日期,checkdate为月日年
        if(checkdate($parts[2],$parts[3],$parts[1]))
            return true;
        else
            return false;
    }
    else
        return false;
}