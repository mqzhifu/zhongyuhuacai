<?php
//请求第3方工具
class CurlLib{
    //type:1  get
    //type:2  post
    static function send($url,$type = 1,$postData = null,$ssl = null ,$isJson = false){
        $curl = curl_init();

        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_REFERER, "http://www.egret.com");
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
}

