<?php

$data = array(
    array(
    "labels"=>array(
        "alertname"=>"a_name_test",
        "job_name"=>"service_1",
        "instance"=>"127.0.0.1",
        "severity"=>"test",
    ),

    'annotations'=>array("summary"=>"php curl http push alert~")
    )
);
var_dump(json_encode($data));
$url = "http://127.0.0.1:9093/api/v1/alerts";
echo $url . "\n";
$r = http_curl($url,2,$data,null,true);
var_dump($r);

function http_curl($url,$type = 1,$postData = null,$ssl = null ,$isJson = false){
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
        if(!$isJson){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData) );
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode ($postData) );
        }
    }


    if($ssl == 1){
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }


    if($isJson){
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,

            array('Content-Type: application/json; charset=utf-8','Content-Length:' . strlen( json_encode ($postData)) )

        );
    }

    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return out_pc(5005,curl_errno($curl).":".curl_error($curl));
    }
    curl_close($curl);


    return out_pc(200,$data);
}

function out_pc($code = 200,$msg = '' ){
    return array('code'=>$code,'msg'=>$msg);
}