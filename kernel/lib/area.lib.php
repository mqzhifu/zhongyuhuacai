<?php

// * 获取地区信息，根据-访问才的IP，请求第3方接口
class AreaLib  {

    //百度 - API控制台申请得到的ak - 属于 server 浏览器 类型
    private static $ak = 'B6zZCrUZ6bSSFPKcCirUGeRPWC9HIxsy';

    //应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看（此处sk值仅供验证参考使用）
    private static $sk = 'zdFPS4zBY0BVfzeOEtZnsbtLvMqOcMuv';

    //以Geocoding服务为例，地理编码的请求url，参数待填
    private static $url = "http://api.map.baidu.com/location/ip";

    //get请求uri前缀
    private static $uri = '/location/ip';

    //    //解析从百度API请求回来的数据信息
    static function formatAPIData($data){
        $channel = "";
        $country = "";
        if($data['address']){
            $addr = explode("|",$data['address']);
            $channel = $addr[4];
            $country = $addr[0];
        }

        $rs = array(
            'country'=>$country,
            'channel'=>$channel,
            'province'=>$data['content']['address_detail']['province'],
            'city'=>$data['content']['address_detail']['city'],
            'lat'=>$data['content']['point']['y'],
            'lng'=>$data['content']['point']['x'],
            //下面3个值，基本上没有，但也写上吧，万一有呢
            'district'=>$data['content']['address_detail']['district'],//区县
            'street'=>$data['content']['address_detail']['street'],//街道
            'street_number'=>$data['content']['address_detail']['street_number'],//门牌号
        );

        return $rs;

    }

    static function getByGPS($latitude,$longitude){
        if(!$latitude || !$longitude){
            return "空";
        }

        $url = "http://api.map.baidu.com/reverse_geocoding/v3/?ak=".self::$ak."&output=json&coordtype=wgs84ll&location=$latitude,$longitude";
        $res = @file_get_contents($url);
        //解析json
        $arr = json_decode($res,true);
        if(!$arr){
            return "百度解析IP失败(-1)-$latitude,$longitude";
        }

        if($arr['status'] !== 0){
            return "百度解析IP失败(-2)-$latitude,$longitude";
        }

        $arr['result']['formatted_address'];

        return  $arr['result']['formatted_address'];
//        $arr['result']['business'];
//         $arr['addressComponent']['province'];
//         $arr['addressComponent']['city'];
//         $arr['addressComponent']['town'];
//         $arr['addressComponent']['street']

    }

    static function getByIp($ip = ""){
//        $json = "{\"address\":\"CN|\u5317\u4eac|\u5317\u4eac|None|UNICOM|0|0\",\"content\":{\"address\":\"\u5317\u4eac\u5e02\",\"address_detail\":{\"city\":\"\u5317\u4eac\u5e02\",\"city_code\":131,\"district\":\"\",\"province\":\"\u5317\u4eac\u5e02\",\"street\":\"\",\"street_number\":\"\"},\"point\":{\"x\":\"116.40387397\",\"y\":\"39.91488908\"}},\"status\":0}";
//        $json  = json_decode($json,true);
//        $rs = self::formatAPIData($json);
//        var_dump($rs);exit;
        if(!$ip){
            $ip = get_client_ip();
        }else{
            $ip = trim($ip);
        }

        if($ip == '127.0.0.1'){
            return "本地/本机";
        }

        if(substr($ip,0,3) == '192'){
            return "内网";
        }

        $url = self::request($ip);
//        echo $url;
        $res = @file_get_contents($url);
        //解析json
        $arr = json_decode($res,true);
        if (!empty($arr['status'])) {
            return "百度解析IP失败-$ip";
        }

        $data = self::formatAPIData($arr);
        $data['IP'] = $ip;
        return $data;
    }

    static function request($ip){
        //构造请求串数组
//        $querystring_arrays = array (
//            'ip' => $ip,
//            'ak' => self::$ak,
//            'coor' => 'bd09ll',
//        );

//        $sn = self::caculateAKSN(self::$ak, self::$sk, self::$uri, $querystring_arrays);
        return self::$url."?ip=".$ip."&ak=".self::$ak."&coor=bd09ll";
    }

//    static  function caculateAKSN ($ak, $sk, $url, $querystring_arrays, $method = 'GET') {
//        if ($method === 'POST') {
//            ksort($querystring_arrays);
//        }
//        $querystring = http_build_query($querystring_arrays);
//        return md5(urlencode($url.'?'.$querystring.$sk));
//    }


}
