<?php

// * 获取地区信息，根据-访问才的IP，请求第3方接口
class AreaLib  {

    //API控制台申请得到的ak（此处ak值仅供验证参考使用）
    private static $ak = 'tvqtHaI627qxTanojvpTMV0Kzx6uGxpC';

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

    static function getByIp(){
//        $json = "{\"address\":\"CN|\u5317\u4eac|\u5317\u4eac|None|UNICOM|0|0\",\"content\":{\"address\":\"\u5317\u4eac\u5e02\",\"address_detail\":{\"city\":\"\u5317\u4eac\u5e02\",\"city_code\":131,\"district\":\"\",\"province\":\"\u5317\u4eac\u5e02\",\"street\":\"\",\"street_number\":\"\"},\"point\":{\"x\":\"116.40387397\",\"y\":\"39.91488908\"}},\"status\":0}";
//        $json  = json_decode($json,true);
//        $rs = self::formatAPIData($json);
//        var_dump($rs);exit;
        $ip = get_client_ip();
        $ip = "111.200.52.34";

        $url = self::request($ip);
//        echo $url;
        $res = @file_get_contents($url);
        //解析json
        $arr = json_decode($res,true);
        if (!empty($arr['status'])) {
            return false;
        }

        $data = self::formatAPIData($arr);
        $data['IP'] = $ip;
        return $data;
    }

    static function getAllCountry(){
        $data = AreaModel::db()->getAll(1);
        return $data;
    }

    static function getAllProvince(){
        $data = AreaModel::db()->getAll(1);
        return $data;
    }
    //获取一个省下面的所有城市
    static function getCityByProvince($provinceId){
        $data = AreaModel::db()->getAll(" pid = $provinceId");
        return $data;
    }



    static function request($ip){


        //构造请求串数组
        $querystring_arrays = array (
            'ip' => $ip,
            'ak' => self::$ak,
            'coor' => 'bd09ll',
        );

        $sn = self::caculateAKSN(self::$ak, self::$sk, self::$uri, $querystring_arrays);
        return self::$url."?ip=".$ip."&ak=".self::$ak."&sn={$sn}&coor=bd09ll";
    }

    static  function caculateAKSN ($ak, $sk, $url, $querystring_arrays, $method = 'GET') {
        if ($method === 'POST') {
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }

//    protected function locationInfo() {
//        $ip = $this->realIp();
//        $ip = '111.200.52.34';
//        $ip = '14.196.122.25';
//        $ip = '60.255.144.255';
//        $ip = '61.139.12.25';

//        //构造请求串数组
//        $querystring_arrays = array (
//            'ip' => $ip,
//            'ak' => $this->ak,
//            'coor' => 'bd09ll',
//        );

        //调用sn计算函数，默认get请求
//        $sn = $this->caculateAKSN($this->ak, $this->sk, $this->uri, $querystring_arrays);

        //输出完整请求的url（仅供参考验证，故不能正常访问服务）
//        return $this->url."?ip=".$ip."&ak={$this->ak}&sn={$sn}&coor=bd09ll";
//    }



//    public function getIpInfo() {


        /**
         * heyroc editor
         */

//        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
//        Mod_Log_useraction::login("getIpInfo($ip) :",$res);
//        if (empty($res)) {
//            return false;
//        }
//        $jsonMatches = array();
//        preg_match('#\{.+?\}#', $res, $jsonMatches);
//        if (!isset($jsonMatches[0])) {
//            return false;
//        }
//        $json = json_decode($jsonMatches[0], true);
//        if (isset($json['ret']) && $json['ret'] == 1) {
//            $json['ip'] = $ip;
//            unset($json['ret']);
//        } else {
//            return false;
//        }
//        return $json;
//    }

//    public function realIp() {
//
//        //return '59.39.145.178';
//
//        if (isset($_SERVER)) {
//            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
//                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//
//                //取X-Forwarded-for中第一个非unknown的有效Ip字符串
//                foreach ($arr as $ip) {
//                    $ip = trim($ip);
//                    if ($ip != 'unknown') {
//                        $realip = $ip;
//                        break;
//                    }
//                }
//            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
//                $realip = $_SERVER['HTTP_CLIENT_IP'];
//            } else {
//                if (isset($_SERVER['REMOTE_ADDR'])) {
//                    $realip = $_SERVER['REMOTE_ADDR'];
//                } else {
//                    $realip = '0.0.0.0';
//                }
//            }
//        } else {
//            if (getenv('HTTP_X_FORWARDED_FOR')) {
//                $realip = getenv('HTTP_X_FORWARDED_FOR');
//            } elseif (getenv('HTTP_CLIENT_IP')) {
//                $realip = getenv('HTTP_CLIENT_IP');
//            } else {
//                $realip = getenv('REMOTE_ADDR');
//            }
//        }
//
//        $onlineip = null;
//        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
//        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
//        return $realip;
//    }

}
