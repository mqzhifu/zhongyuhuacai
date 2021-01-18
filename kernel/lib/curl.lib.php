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

