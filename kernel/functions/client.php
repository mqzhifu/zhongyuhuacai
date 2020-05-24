<?php
function get_client_info()
{
    if(RUN_ENV == "CLI"){

    }elseif(RUN_ENV == 'WEB'){
        return get_web_client_data();
    }elseif(RUN_ENV == 'WEBSOCKET'){

    }else{

    }
}

function get_web_client_data(){
    // 这种情况，不太可能出现，可能是 后台脚本，也可能是爬虫
    if ( !isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])  ){
        return -1;
    }
    //静态资源的请求，就没必要记录了
    if(STATIC_URL == $_SERVER['HTTP_HOST']){
        return -2;
    }

    $ip = get_client_ip();

    $is_spider = 0;//是否为爬虫
    $os = "";//操作系统
    $os_v = "";//操作系统版本
    $browser_model = "";//浏览器型号
    $browser_version = "";//浏览器版本
    $device_version = "";//移动设备版本
    $device_model = "";//移动设备型号

    $lon = "";//经度
    $lat = "";//纬度

    $app_v = "";//app版本号
    $sim_imsi = "";//sim 卡串号
    $cellphone = "";//手机号
    $dpi = "";//手机分辨率

    $deviceId = "";//设备号，因为有SIM卡、CDMA，同时有些手机会有双SIM卡，这个值安卓端暂定用 getDeviceId  函数，可能会有些不准
    $channel = "";//
    $f_channel = "";
    $cate = get_request_cate();//类型api wap pc
    if ( $cate =='pc'){
        $browser = get_useragent_browser();
        if( isset($browser['type']) && $browser['type'] == 'spider'){
            $is_spider = 1;
            $os = 'spider';
            $os_v = 0;
            $device_model = "spider";
        }else{
            $os_info = get_useragent_OS();
            $os = $os_info['os'];
            $os_v = $os_info['version'];
        }

        $browser_model = $browser['browser'];
        $browser_version = $browser['version'];
    }elseif($cate == 'wap'){
        $browser = get_useragent_browser();
        if( isset($browser['type']) && $browser['type'] == 'spider'){
            $is_spider = 1;
            $os = 'spider';
            $os_v = 0;
            $device_model = "spider";
        }else{
            $os_info = get_useragent_OS();
            $os = $os_info['os'];
            $os_v = $os_info['version'];

            $device_model = get_wap_phone_model();
        }

        $browser_model = $browser['browser'];
        $browser_version = $browser['version'];

        $device_version = 0;
    }elseif($cate == 'api') {
        $api_type = $_SERVER['HTTP-CLIENT-TYPE'];
        if($api_type == 1){//项目1  - APP
//客户端版本号|设备系统|设备系统版本|设备型号|设备型号版本|纬度|经度|sim_imsi|手机号|分辨率
            $data = explode( "|",$_SERVER['HTTP_CLIENT_DATA']);

            $app_v = $data[0];                // 1.1.1
            $os = $data[1];   // 设备类型  android/ios
            $os_v = $data[2];               // 10


            $device_model = $data[3] ;     // iphone,ipad
            $device_version = $data[4];    // X,XS
            $lat = $data[5];
            $lon = $data[6];
            $sim_imsi = $data[7];
            $cellphone = $data[8];          //135xxxx8907
            $dpi = $data[9];                //900x1000
            $imei = $data[10];
            $deviceId = $data[11];

            if(arrKeyIssetAndExist($data,12)){
                $channel = $data[12];
            }

            if(arrKeyIssetAndExist($data,13)){
                $f_channel = $data[13];
            }
        }elseif($api_type == 2){//项目2 - 微信 小程序

        }
    }else{
        exit("client info is error.");
    }

    $ref = "";
    if(isset($_SERVER['HTTP_REFERER']))
        $ref = urldecode($_SERVER['HTTP_REFERER']);


//    $uri = turn_unicode($uri);
//    $ref =turn_unicode($ref);

    $UA = "";
    if(arrKeyIssetAndExist($_SERVER,"HTTP_USER_AGENT")){
        $UA = strtolower($_SERVER["HTTP_USER_AGENT"]);
    }

    $info = array(
        'ip'=>$ip,
        'os'=>$os,
        'os_version'=>$os_v,
        'cate'=>$cate,
        'spider'=>$is_spider,
        'lon'=>$lon,
        'lat'=>$lat,
        'browser_model'=>$browser_model,
        'browser_version'=>$browser_version,
        'device_model'=>$device_model,
        'device_version'=>$device_version,
        'ref'=>$ref,
        'user_agent'=>$UA,
        'app_version'=>$app_v,
        'dpi'=>$dpi,
        'sim_imsi'=>$sim_imsi,
        'cellphone'=>$cellphone,
        'device_id'=>$deviceId,
        'channel'=>$channel,
        'f_channel'=>$f_channel,
    );
    return $info;


//    }else{
//        $info =
//            "{$execdate},,t:".time().",,method:$accept_method,,memory:{$p_mem},,exec:{$p_time},,host:$host,,url:$uri,,ctrl:".EXEC_CTRL.",,ac:".EXEC_AC.",,ip:$ip,,".
//            "os:$os,,os_v:$os_v,,cate:$cate,,spider:$is_spider,,lon:$lon,,lat:$lat,,".
//            "browser_model:$browser_model,,browser_version:$browser_version,,device_model:$device_model,,device_version:$device_version,,uid=".$uid.
//            ",,ref:$ref,,UA:".$UA ;
//
//        if (file_exists('/home/')) //判断是否为linux系统
//        {
//            $mylog = new Sys_Log();
//            $mylog->append('http_access', $info) ;
//        }
//    }

}

function get_client_data_struct(){
    $arr = array(
        0=>'APP版本号',
        1=>'操作系统,ios android',
        2=>'操作系统版本号',
        3=>'设备名称 xiaomi2 iphone ipad',
        4=>'设备版本号',
        5=>'纬度',
        6=>'经度',
        7=>'SIM卡唯一号',
        8=>'手机号',
        9=>'分辨率',
        9=>'imei',
        10=>'deviceID',
//        'ip'=>'IP',
//        'addr'=>'详细地址',
//        'cate'=>'h5 pc api(app不用传)',
//        'ref'=>'来源(app不用传)',
//        'user_agent'=>'浏览器C端信息(app不用传)',
//        'browser_model'=>'浏览器(app不用传)',
//        'browser_version'=>'浏览器版本(app不用传)',
    );

    return $arr;
}


// 获取客户端IP地址
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL) return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos =  array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip   =  trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

//判断是否是手机用户访问
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    //脑残法，判断手机发送的客户端标志,兼容性有待提高
    if ( arrKeyIssetAndExist($_SERVER,"HTTP_USER_AGENT") ) {
        $clientkeywords = array (
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * app 请求头专用字段分析
 */
function recordUthingData(){


    if (isset($_SERVER['HTTP_UTHING_DATA'])){
        $uthingData = explode(';', $_SERVER['HTTP_UTHING_DATA']);
        $uthing = trim($uthingData[0]) ;				// uthing
        $_device = strtolower(trim($uthingData[1])) ;	// 设备类型  android/ios
        $version = trim($uthingData[2]);				// 客户端版本号
        $pversion = trim($uthingData[3]);				// 操作系统版本号
        $model = trim($uthingData[4]);					// 手机型号

        $uid = getRequestInt('uid');
        if ($uid) {
            $device = 0 ;
            if ($_device == 'android')
                $device = Table_Push_User_Record::$_DEVICE_ANDROID;
            elseif ($_device == 'ios')
                $device = Table_Push_User_Record::$_DEVICE_IOS;
            Table_Push_User_Tag::inst()->updateJpushId($uid, '', $device);
            Table_msg_usertag::inst()->updateUserDeviceTag($uid, $device);
        }
    }
}

function get_wap_phone_model(){
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    $model = "unknow";
    $version = 0;
    if( stripos($agent, "iphone") !== false ){
        $model = "iphone";
    }elseif(stripos($agent, "ipod") !== false ){
        $model = "pod";
    }elseif(stripos($agent, "ipad") !== false ){
        $model = "pad";
    }elseif(stripos($agent, "android") !== false ){
        $info = get_wap_model_by_UA();
        if($info['browser'] != 'unknow'){
            $model = $info['browser'];
        }
    }

    return $model;
}

//操蛋的安卓，没脾气，完全不好取匹配
function get_wap_model_by_UA(){
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    $info = array('version'=>0,'browser'=>'unknow');
    if(strpos($agent,"meizu") || strpos($agent,"mz") ||  strpos($agent,"m355") || strpos($agent,"m578c")   ||  strpos($agent,"mx5")
        || strpos($agent,"mx3") || strpos($agent,"mx4")  || strpos($agent,"m2 note")    ){//魅族
        $version = 0;
        $info = array('version'=>$version,'browser'=>'meizu');
    }elseif(strpos($agent,"oppo") || strpos($agent,"r8") || strpos($agent,"r7") || strpos($agent,"r2017") || strpos($agent,"n5207") ){//oppo
        $version = 0;
        $info = array('version'=>$version,'browser'=>'oppo');
    }elseif(strpos($agent,"hisense")){//vivo
        $version = 0;
        $info = array('version'=>$version,'browser'=>'hisense');
    }elseif(strpos($agent,"vivo")){//vivo
        $version = 0;
        $info = array('version'=>$version,'browser'=>'vivo');
    }elseif(strpos($agent,"lenovo")){//联想
        $version = 0;
        $info = array('version'=>$version,'browser'=>'lenovo');
    }elseif(strpos($agent,"huawei") || strpos($agent,"che1") ){//华为
        $version = 0;
        $info = array('version'=>$version,'browser'=>'huawei');
    }elseif(strpos($agent,"honor") || strpos($agent,"h60")){//荣耀
        $version = 0;
        $info = array('version'=>$version,'browser'=>'honor');
    }elseif( strpos($agent,"zte") ){//中兴
        $version = 0;
        $info = array('version'=>$version,'browser'=>'zte');
    }elseif(strpos($agent,"htc")){//htc
        $version = 0;
        $info = array('version'=>$version,'browser'=>'htc');
    }elseif(strpos($agent,"mot") || strpos($agent,"xt") ){//moto
        $version = 0;
        $info = array('version'=>$version,'browser'=>'motorola');
    }elseif(strpos($agent,"coolpad")){//酷派
        $version = 0;
        $info = array('version'=>$version,'browser'=>'coolpad');
    }elseif(strpos($agent,"lg")){//LG
        $version = 0;
        $info = array('version'=>$version,'browser'=>'LG');
    }elseif(strpos($agent,"samsung") || strpos($agent,"sm") || strpos($agent,"gt") || strpos($agent,"sgh") || strpos($agent,"sch") ){//三星
        $version = 0;
        $info = array('version'=>$version,'browser'=>'samsung');
    }elseif(strpos($agent,"nexus")){//nexus
        $version = 0;
        $info = array('version'=>$version,'browser'=>'nexus');
    }elseif(strpos($agent,"nec")){//nec
        $version = 0;
        $info = array('version'=>$version,'browser'=>'nec');
    }elseif(strpos($agent,"philips")){//飞利浦
        $version = 0;
        $info = array('version'=>$version,'browser'=>'philips');
    }elseif(strpos($agent,"haier")){//海乐
        $version = 0;
        $info = array('version'=>$version,'browser'=>'haier');
    }elseif(strpos($agent,"nokia")){//诺基亚
        $version = 0;
        $info = array('version'=>$version,'browser'=>'nokia');
    }elseif(strpos($agent,"panasonic")){//松夏
        $version = 0;
        $info = array('version'=>$version,'browser'=>'panasonic');
    }elseif(strpos($agent,"blackberry")){//黑霉
        $version = 0;
        $info = array('version'=>$version,'browser'=>'blackberry');
    }elseif(strpos($agent,"sharp")){//夏普
        $version = 0;
        $info = array('version'=>$version,'browser'=>'sharp');
    }elseif(strpos($agent,"alcatel")){//阿尔卡特
        $version = 0;
        $info = array('version'=>$version,'browser'=>'alcatel');
    }elseif(strpos($agent,"tcl")){//TCL
        $version = 0;
        $info = array('version'=>$version,'browser'=>'tcl');
    }elseif(strpos($agent,"tianyu") || strpos($agent,"k-touch")){//天语
        $version = 0;
        $info = array('version'=>$version,'browser'=>'tianyu');
    }elseif(strpos($agent,"cmcc")  ){//移动
        $version = 0;
        $info = array('version'=>$version,'browser'=>'cmcc');
    }elseif(strpos($agent,"gionee")  ){//金立
        $version = 0;
        $info = array('version'=>$version,'browser'=>'gionee');
    }elseif(strpos($agent,"hm") ){//红米
        $version = 0;
        $info = array('version'=>$version,'browser'=>'hongmi');
    }elseif(strpos($agent,"X900")  || strpos($agent,"x600") ){//乐视
        $version = 0;
        $info = array('version'=>$version,'browser'=>'leshi');
    }elseif(strpos($agent,"nx") ){//努比亚
        $version = 0;
        $info = array('version'=>$version,'browser'=>'nubia');
    }elseif(strpos($agent,"mi 3w") || strpos($agent,"mi 4w") || strpos($agent,"mi 3") || strpos($agent,"mi 2") || strpos($agent,"mi 2s") || strpos($agent,"mi 4c")  || strpos($agent,"ktu84p") ){//小米  mi 3w  mi 4w
        $version = 0;
        $info = array('version'=>$version,'browser'=>'xiaomi');
    }elseif(strpos($agent,"mi note") ){//note 小米
        $version = 0;
        $info = array('version'=>$version,'browser'=>'mi note');
    }elseif(strpos($agent,"mi pad") ){//note 小米
        $version = 0;
        $info = array('version'=>$version,'browser'=>'mi pad');
    }else{
        preg_match ('/;\s*[A-Za-z0-9_-]+\s*[A-Za-z0-9_-]*\s*build\/[A-Za-z0-9_-]+/', $agent, $m );
        if($m){
            $str = trim( substr($m[0],1));
            $l = strpos($agent,"build");
            $device = trim(substr($str,0,$l));
            $version = explode("/",$str);
            $info = array('version'=>$version[1],'browser'=>$device);
        }
    }

    return $info;
}



function is_weixin(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}
function get_user_browser() {
    $browser = $_SERVER['HTTP_USER_AGENT'];
    if (stripos($browser, 'Firefox') !== false) {
        $curbrowser = 'Firefox';
    } elseif (stripos($browser, 'Chrome') !== false) {
        $curbrowser = 'Chrome';
    } elseif (stripos($browser, 'Safari') !== false) {
        $curbrowser = 'Safari';
    } elseif (strpos($browser, "NetCaptor")) {
        $curbrowser = "NetCaptor";
    } elseif (strpos($browser, "Netscape")) {
        $curbrowser = "Netscape";
    } elseif (strpos($browser, "Lynx")) {
        $curbrowser = "Lynx";
    } elseif (strpos($browser, "Opera")) {
        $curbrowser = "Opera";
    } elseif (strpos($browser, "Konqueror")) {
        $curbrowser = "Konqueror";
    } elseif (strpos($browser, "MSIE 9.0")) {
        $curbrowser = "IE9";
    } elseif (strpos($browser, "MSIE 8.0")) {
        $curbrowser = "IE8";
    } elseif (strpos($browser, "MSIE 7.0")) {
        $curbrowser = "IE7";
    } elseif (strpos($browser, "MSIE 6.0")) {
        $curbrowser = "IE6";
    } elseif (strpos($browser, "MSIE")) {
        $curbrowser = "IE";
    } else {
        $curbrowser = 'Other';
    }
    return $curbrowser;
}

function explode_http_agent($agent,$key){
    $arr = explode(" ",$agent);
    $info = "";
    foreach($arr as $k=>$v){
        if(   strpos( $v,$key)  !== false ){
            $tmp =explode("/",$v);
            $info = $tmp[1];
            break;
        }
    }
    return $info;
}

function get_useragent_browser(){
    $spider = get_useragent_spider();
    if($spider['version']){
        $spider['type'] = 'spider';
        return $spider;
    }

    if(!arrKeyIssetAndExist($_SERVER,'HTTP_USER_AGENT')){
        $info = array('version'=>0,'browser'=>'');
        return $info;
    }
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    if(strpos($agent,"maxthon")){//傲游
        $version = explode_http_agent($agent,'maxthon');
        $info = array('version'=>$version,'browser'=>'maxthon');
    }elseif( strpos($agent,"qq") !== false || strpos($agent,"tencent") !== false   ){//qq
        $version = explode_http_agent($agent,'qqbrowser');
        $info = array('version'=>$version,'browser'=>'qqbrowser');
    }elseif( strpos($agent,"micromessenger") ){//wap-微信
        $version = explode_http_agent($agent,'micromessenger');
        $info = array('version'=>$version,'browser'=>'micromessenger');
    }elseif( strpos($agent,"taobrowser") ){//淘宝
        $version = explode_http_agent($agent,'taobrowser');
        $info = array('version'=>$version,'browser'=>'taobrowser');
    }elseif( strpos($agent,"bidubrowser") || strpos($agent,"baiduhd") ){//百度
        //这个必须得在UC的上面，因为下面的也会满足条件
        $version = explode_http_agent($agent,'bidubrowser');
        $info = array('version'=>$version,'browser'=>'bidubrowser');
    }elseif( strpos($agent,"baiduboxapp") ){//百度APP
        $version = explode_http_agent($agent,'baiduboxapp');
        $info = array('version'=>$version,'browser'=>'baiduboxapp');
    }elseif( strpos($agent,"baiduwallet") ){//百度钱包
        $version = 0;
        $info = array('version'=>$version,'browser'=>'baiduwallet');
    }elseif( strpos($agent,"baidumap") ){//百度地图
        $version = 0;
        $info = array('version'=>$version,'browser'=>'baidumap');
    }elseif( strpos($agent,"ubrowser") ){//uc
        $version = explode_http_agent($agent,'ubrowser');
        $info = array('version'=>$version,'browser'=>'ubrowser');
    }elseif( strpos($agent,"ucbrowser") ){//uc
        $version = explode_http_agent($agent,'ucbrowser');
        $info = array('version'=>$version,'browser'=>'ucbrowser');
    }elseif( strpos($agent,"firefox") ){//火狐
        $version = explode_http_agent($agent,'firefox');
        $info = array('version'=>$version,'browser'=>'firefox');
    }elseif( strpos($agent,"lbbrowser") ){//猎豹
//        $version = explode_http_agent($agent,'lbbrowser');
        $info = array('version'=>0,'browser'=>'lbbrowser');
    }elseif( strpos($agent,"opr") || strpos($agent,"opera")  ){//opera
        $version = explode_http_agent($agent,'opr');
        //需要特殊处理一，因为和下面的这个都属于opera
        $info = array('version'=>$version,'browser'=>'opera');
    }elseif( strpos($agent,"douban") !== false  ){//opera
        $version = 0;
        $info = array('version'=>$version,'browser'=>'douban');
    }elseif( strpos($agent,"weibo") !== false  ){//opera
        $version = 0;
        $info = array('version'=>$version,'browser'=>'weibo');
    }elseif( strpos($agent,"metasr") ){//搜狗
        $version = explode_http_agent($agent,'metasr');
        $info = array('version'=>$version,'browser'=>'metasr');
    }elseif( strpos($agent,"alipay") ){//支付宝
        $version = explode_http_agent($agent,'alipay');
        $info = array('version'=>$version,'browser'=>'alipay');
    }elseif( strpos($agent,"safari")  && strpos($agent,"version") ){//苹果
        $version = explode_http_agent($agent,'safari');
        $info = array('version'=>$version,'browser'=>'safari');
    }elseif( strpos($agent,"msie 10.0") ){
        $info = array('version'=>"10.0",'browser'=>'ie');
    }elseif( strpos($agent,"msie 9.0") ){
        $info = array('version'=>"9.0",'browser'=>'ie');
    }elseif( strpos($agent,"msie 8.0") ){
        $info = array('version'=>"8.0",'browser'=>'ie');
    }elseif( strpos($agent,"msie 7.0") ){
        $info = array('version'=>"7.0",'browser'=>'ie');
    }elseif( strpos($agent,"msie 6.0") || strpos($agent,"msie 6.1") ){
        $info = array('version'=>"6.0",'browser'=>'ie');
    }elseif( strpos($agent,"msie 5.0") ||  strpos($agent,"msie 5.5") ){
        $info = array('version'=>"5.0",'browser'=>'ie');
    }elseif( strpos($agent,"rv:11") || strpos($agent,"msie 11.0") ){
        $info = array('version'=>"11",'browser'=>'ie');
    }elseif( strpos($agent,"chrome") ){//google
        //这个要放到最后判断，因为有很多浏览器都包含关键字：chrome
        $version = explode_http_agent($agent,'chrome');
        $info = array('version'=>$version,'browser'=>'chrome');
    }elseif( strpos($agent,"applewebkit") ){//苹果
        $version = 0;
        $info = array('version'=>$version,'browser'=>'safari');
    }else{
        //md,万恶的360，怕被百度、腾迅屏了，不在user_agent写标识
        $info = array('version'=>$agent,'browser'=>'unknow');
    }

    return $info;

}

function get_useragent_spider(){
    if(!arrKeyIssetAndExist($_SERVER,'HTTP_USER_AGENT')){
        return array('version'=>0,'browser'=>'');
    }
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
    $info = array('version'=>0,'browser'=>$agent);
    if (stripos($agent, "googlebot")) {
        $info = array('version'=>1,'browser'=>'google');
    }elseif(stripos($agent, "go 1.1 package") !== false){
        //我也没搞明白这个东西是哪里来的，隔一天抓一次，好像是GO语言的抓取程序
        $info = array('version'=>1,'browser'=>'go 1.1 package http');
    }elseif(stripos($agent, "alibaba") !== false){ //阿里云遁
        $info = array('version'=>1,'browser'=>'alibaba-security');
    }elseif(stripos($agent, "3g-portal-center") !== false){
        $info = array('version'=>1,'browser'=>'shouteng-3g-portal-center');
    }elseif(stripos($agent, "adsbot-google") !== false) {
        $info = array('version'=>1,'browser'=>'ads-google');
    }elseif(stripos($agent, "baiduspider") !== false) {
        $info = array('version'=>1,'browser'=>'baidu');
    }elseif(stripos($agent, "yahoo!") !== false) {
        $info = array('version'=>1,'browser'=>'yahoo');
    }elseif(stripos($agent, "bingbot")) {
        $info = array('version'=>1,'browser'=>'bing' );
    }elseif(stripos($agent, "sosospider") !== false) {
        $info = array('version'=>1,'browser'=>'soso' );
    }elseif(stripos($agent, "sosoimagespider") !== false) {
        $info = array('version'=>1,'browser'=>'soso_img' );
    }elseif(stripos($agent, "sogou") !== false) {
        $info = array('version'=>1,'browser'=>'sogou' );
    }elseif(stripos($agent, "360spider") !== false) {
        $info = array('version'=>1,'browser'=>'360' );
    }elseif(stripos($agent, "applebot") !== false) {
        $info = array('version'=>1,'browser'=>'apple' );
    }elseif(stripos($agent, "ia_archiver") !== false) {//alexa
        $info = array('version'=>1,'browser'=>'archiver_alexa');
    }elseif(stripos($agent, "spider") !== false || stripos($agent, "robot") !== false || stripos($agent, "bot") !== false) {
        $info = array('version'=>1,'browser'=>'spider|rebot|bot');
    }
//    elseif(stripos($agent, "yrspider")) {
//        $info = array('version'=>1,'browser'=>'yunrang' );
//    }
//    elseif(stripos($agent, "yeti") !== false) {
//        $info = array('version'=>1,'browser'=>'naver');
//    }
//    elseif(stripos($agent, "opensiteexplorer") !== false || stripos($agent, "shortlinktranslate") !== false ||  stripos($agent, "heritrix") !== false ) {
//        $info = array('version'=>1,'browser'=>'opensiteexplorer|shortlinktranslate|heritrix' );
//    }
//    elseif(stripos($agent, "dianjing_ad_spider") !== false) {
//        $info = array('version'=>1,'browser'=>'dianjing' );
//    }
//    elseif(stripos($agent, "nutch") !== false || stripos($agent, "python") !== false || stripos($agent, "scrapy") !== false ) {
//        $info = array('version'=>1,'browser'=>'nutch|py|scrapy' );
//    }
//    elseif(stripos($agent, "client") !== false || stripos($agent, "java") !== false || stripos($agent, "curl") !== false || stripos($agent, "perl") !== false  ) {
//        $info = array('version'=>1,'browser'=>'client|java|curl|perl' );
//    }
//    elseif(stripos($agent, "phantom") !== false || stripos($agent, "larbin") !== false ) {
//        $info = array('version'=>1,'browser'=>'phantomjs|larbin');
//    }
    return $info;
}

//获取用户请求的分类：wap pc api
function get_request_cate(){
    if(isset($_SERVER['HTTP-CLIENT-TYPE']) && $_SERVER['HTTP-CLIENT-TYPE']){
        $rs = 'api';
    }elseif (  isMobile() ){
        $rs = 'wap';
    }else{
        $rs = "pc";
    }

    return $rs;
}


function get_client_content_type(){
    $content_type = "";
    if(arrKeyIssetAndExist($_SERVER,'CONTENT_TYPE')){
        $content_type = $_SERVER['CONTENT_TYPE'];
        //这里做兼容，有时候会提交类似：application/json;charset=utf8
        $content_type = explode(";",$content_type);
        $content_type = trim($content_type[0]);
    }

    return $content_type;
}

function trun_app_header_info(){
    if( ! isset($_SERVER['HTTP_UTHING_DATA']))
        return 0;

    $rs = array();

    $uthingData = explode(';', $_SERVER['HTTP_UTHING_DATA']);
    $rs['os'] = strtolower(trim($uthingData[1])) ;	// 设备类型  android/ios
    $rs['os_version'] = trim($uthingData[3]);				// 操作系统版本号

    $rs['app_v'] = trim($uthingData[2]);				// 客户端版本号
    $rs['device_model'] = trim($uthingData[4]);		// 手机型号与版本
    if(isset($uthingData[5]))
        $rs['ref'] = trim($uthingData[5]);

    if(isset($uthingData[6]))
        $rs['lon'] = $uthingData[6];

    if(isset($uthingData[7]))
        $rs['lat'] = $uthingData[7];

    return $rs;
}

function get_useragent_OS(){
    if(!arrKeyIssetAndExist($_SERVER,'HTTP_USER_AGENT')){
        $info = array('os'=>"",'version'=>"");
        return $info;
    }
    $agent = strtolower($_SERVER["HTTP_USER_AGENT"]);


    $os = "unknow";
    $version = 0;
    $m = array();
    if( preg_match ('/windows nt ([0-9]+\.[0-9+])\)/', $agent, $m ) ){
        $os = "windows";
        $version = $m[1];
    }elseif( preg_match ('/windows nt ([0-9]+\.[0-9]+);/', $agent, $m ) ){
        $os = "windows";
        $version = $m[1];
    }elseif( strpos($agent,"windows xp") !== false ){
        $os = "windows";
        $version = "5.2";
    }elseif( strpos($agent,"symbian") !== false ){
        $os = "symbian";
        $version = "0";
    }elseif( strpos($agent,"windows nt;") !== false || strpos($agent,"windows nt)") !== false ){
        $os = "windows";
        $version = " nt5 ";
    }elseif( strpos($agent,"windows 98") !== false ){
        $os = "windows";
        $version = " 98 ";
    }elseif( strpos($agent,"windows 2000") !== false ){
        $os = "windows";
        $version = " 5.0";
    }elseif( preg_match ('/android\s+([0-9]+\.[0-9]+)([\.0-9]*)/', $agent, $m ) ){
        $os = "android";
        $version = $m[1];
        if($m[2]){
            $version .= $m[2];
        }
    }elseif( strpos($agent,"macintosh") !== false ) {
        $os = "macintosh";
        preg_match ('/intel mac os x \w+/', $agent, $m );
        if($m){
            $version = substr($m[0],15);
        }
    }elseif( strpos($agent,"mac os") !== false ){
        $os = "ios";

        preg_match ('/os \w+ like/', $agent, $m );
        if($m){
            $version = substr($m[0],3);
            $version = substr($version,0,strlen($version)-5);
        }
    }elseif( strpos($agent,"linux") !== false ){
        $os = "linux";
    }

    $info = array('os'=>$os,'version'=>$version);
    return $info;
}