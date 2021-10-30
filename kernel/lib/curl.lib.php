<?php
//请求第3方工具
class CurlLib{
    static $_inc = 1;
    //type:1  get
    //type:2  post
    static function send($url,$type = 1,$postData = null,$ssl = null ,$isJson = false){
        $curl = curl_init();

        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_REFERER, "https://baidu.com");
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($type == 2){
            //设置为POST
            curl_setopt($curl, CURLOPT_POST, 1);
            //设置POST数据
            if(!$isJson){
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData) );
            }else{
                curl_setopt($curl, CURLOPT_POSTFIELDS, ($postData) );
            }
        }


        if($ssl == 1){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }


        if($isJson){
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER,

                array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen($postData) )

            );
        }

        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return out_pc(5005,curl_errno($curl).":".curl_error($curl));
        }
        curl_close($curl);


        return out_pc(200,$data);
    }


    static function sis001($url,$REFERER , $ssl = null ){
        $stime = time();
        $curl = curl_init();
        out("curl sis001 url : $url stime:".date("Y-m-d H:i:s",$stime));
        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36";
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt($curl, CURLOPT_REFERER, $REFERER);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if($ssl == 1){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        //response header
        curl_setopt($curl, CURLOPT_HEADER, 1);

//        echo $url."\n";

        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            out("curl err : ".curl_errno($curl).":".curl_error($curl));
        }
        curl_close($curl);


        $l = stripos($content,"\r\n\r\n");
        $body = substr($content,$l+ 4);
        $header = substr($content,0,$l);
        $header = self::parseSrtHeaderToArr($header);
        $data = array(
            'error'=>"",
            'body'=>$body,
            "header"=>$header,
        );

        $execTime = time() - $stime;

        out("curl sis001 exec time:".$execTime);
        return $data;
    }

    static function hejidi($url,$REFERER , $ssl = null ){
        $stime = time();
        $curl = curl_init();
        out("curl sis001 url : $url stime:".date("Y-m-d H:i:s",$stime));
        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        $r = rand(0,9);
        //$ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36";
//        $ua = "Mozilla/$r.0 (Macintosh; Intel Mac OS X 10_15_$r) AppleWebKit/537.3$r (KHTML, like Gecko) Chrome/87.0.4280.14$r Safari/537.3$r";
        $ua = randUa();
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt($curl, CURLOPT_REFERER, $REFERER);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.Rand_IP(), 'CLIENT-IP:'.Rand_IP()));
//追踪返回302状态码，继续抓取
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if($ssl == 1){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        //response header
        curl_setopt($curl, CURLOPT_HEADER, 1);

//        echo $url."\n";

        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            out("curl err : ".curl_errno($curl).":".curl_error($curl));
        }
        curl_close($curl);


        $l = stripos($content,"\r\n\r\n");
        $body = substr($content,$l+ 4);
        $header = substr($content,0,$l);
        $header = self::parseSrtHeaderToArr($header);
        $data = array(
            'error'=>"",
            'body'=>$body,
            "header"=>$header,
        );

        $execTime = time() - $stime;

        out("curl sis001 exec time:".$execTime);
        return $data;
    }

    static function kankan($url,$REFERER , $ssl = null ){
        $stime = time();
        $curl = curl_init();
        out("curl sis001 url : $url stime:".date("Y-m-d H:i:s",$stime));
        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36";
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt($curl, CURLOPT_REFERER, $REFERER);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if($ssl == 1){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        //response header
        curl_setopt($curl, CURLOPT_HEADER, 1);

//        echo $url."\n";

        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            out("curl err : ".curl_errno($curl).":".curl_error($curl));
        }
        curl_close($curl);


        $l = stripos($content,"\r\n\r\n");
        $body = substr($content,$l+ 4);
        $header = substr($content,0,$l);
        $header = self::parseSrtHeaderToArr($header);
        $data = array(
            'error'=>"",
            'body'=>$body,
            "header"=>$header,
        );

        $execTime = time() - $stime;

        out("curl sis001 exec time:".$execTime);
        return $data;
    }


    static $_taohua_curl_fd = null;
    static function getTaohuaCurlFd(){
        if (self::$_taohua_curl_fd){
            out("curl fd has exist ...");
            return self::$_taohua_curl_fd;
        }
        self::$_taohua_curl_fd = curl_init();
        return self::$_taohua_curl_fd;
    }
    static function taohua($url,$REFERER , $ssl = null ,$cookie = 0,$useFd = 0){
        $stime = microtime(true);
        out("curl sis001 url : $url stime:".date("Y-m-d H:i:s",$stime));

        if($useFd){
            $curl = self::getTaohuaCurlFd();
        }else{
            out(" new curl_init");
            $curl = curl_init();
//            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//                'Connection: Keep-Alive',
//                'Keep-Alive: 300',
//            ));
        }

        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        $r1 = rand(1,10);
        $r2 = rand(1,10);
        $r3 = rand(1,10);
//        $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36";
        $ua = randUa();
        curl_setopt($curl, CURLOPT_USERAGENT, $ua);
        curl_setopt($curl, CURLOPT_REFERER, $REFERER);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //response header
        curl_setopt($curl, CURLOPT_HEADER, 1);

        if($ssl == 1){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if($useFd){
            curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
        }

//        out("set cookie:".$cookie);
//        curl_setopt ($curl, CURLOPT_COOKIE , $cookie );
//        WMwh_2132_viewid=tid_1310474; path=/

        $rip1 = Rand_IP();
        $rip2 = Rand_IP();
        out("rip1 : $rip1 , rip2 : $rip2 ， REFERER : $REFERER");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.$rip1, 'CLIENT-IP:'.$rip2));

        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            out("curl err : ".curl_errno($curl).":".curl_error($curl));
        }
        if(!$useFd){
            curl_close($curl);
            out("close : curl");
        }

        $l = stripos($content,"\r\n\r\n");
        $body = substr($content,$l+ 4);
        $header = substr($content,0,$l);
        $header = self::parseSrtHeaderToArr($header);
//        var_dump($header);
        $data = array(
            'error'=>"",
            'body'=>$body,
            "header"=>$header,
        );

        $execTime = microtime(true) - $stime;
        echo number_format($execTime, 4, '.', '')." seconds \n";

        return $data;
    }

    //专门给tang98的 ，因为不同网站，防爬虫的策略不一样
    static function tang98($url,$REFERER = "",$cookie = ""){
        $curl = curl_init();

        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        //301 302 转移的情况 ，直接跟踪，不过试了下，header里的location是有死循环的可能，主要是防爬虫
//        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
        $ua = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36";
        curl_setopt($curl, CURLOPT_USERAGENT,$ua);
        curl_setopt($curl, CURLOPT_REFERER, $REFERER);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //response header
        curl_setopt($curl, CURLOPT_HEADER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        if($cookie){
            out("set cookie:".$cookie);
            curl_setopt ($curl, CURLOPT_COOKIE , $cookie );
        }

        out("curl:".$url);
        $content = curl_exec($curl);
        if (curl_errno($curl)) {
            return out_pc(5005,curl_errno($curl).":".curl_error($curl));
        }
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
//        var_dump($content);
        $tmpContent = explode("\r\n\r\n",$content);
        $header = self::parseSrtHeaderToArr( $tmpContent[0]);
        //这里是操蛋的点，也是防爬虫，他在body第一行后面，又加了\r\n\r\n，导致后面的数据读取不出来
        //只能使用最严防的方式获取body字符串了
        $l = stripos($content,"\r\n\r\n");
        $body = substr($content,$l+ 4);

//        if(self::$_inc > 2){
//            out("in dead loop...");
//            exit();
//        }
        //出现  301 302 ,就证明，对方 要求验证:是否爬虫
//        if($httpCode == 301 || $httpCode == 302){
//            var_dump($header);
//            out("in 301 302");
//            self::$_inc++;
//            $cookie = $header['set-cookie'];
//            return self::tang98($header['location'],$REFERER, $cookie );
//        }

        $data = array(
            'body'=>$body,
            "header"=>$header,
            "httpCode"=>$httpCode,
        );
        return out_pc(200,$data);
    }

    static function parseSrtHeaderToArr($strHeader){
        $headerArr = explode("\r\n",$strHeader);
        $rs = null;
        foreach ($headerArr as $k=>$v){
            if(  substr($v,0,4) == "HTTP"){
                $row = explode(" ",$v);
                $rs['code'] = $row[1];
                continue;
            }
            $row = explode(": ",$v);
            $key = trim($row[0]);
            $content = trim($row[1]);
            $rs[$key] = $content;
        }
        return $rs;
    }
}

//随机IP
function Rand_IP(){

    $ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
    $ip3id= round(rand(600000, 2550000) / 10000);
    $ip4id= round(rand(600000, 2550000) / 10000);
    //下面是第二种方法，在以下数据中随机抽取
    $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
    $randarr= mt_rand(0,count($arr_1)-1);
    $ip1id = $arr_1[$randarr];
    return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
}

function randUa(){
    $arr = array(
    "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
    "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
    "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
    "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; .NET4.0C; .NET4.0E; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; InfoPath.3; rv:11.0) like Gecko",
    "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
    "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
    "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
    "Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
    "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
    "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Maxthon 2.0)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; TencentTraveler 4.0)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; The World)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; 360SE)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SE 2.X MetaSr 1.0; SE 2.X MetaSr 1.0; .NET CLR 2.0.50727; SE 2.X MetaSr 1.0)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Avant Browser)",
    "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36",
    );
    $r = rand(0,count($arr ) - 1);
    return $arr[$r];
}
