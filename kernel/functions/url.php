<?php

function get_admin_project_name(){
    return "instantplayadmin";
}

function get_base_upload_url( $protocol = HTTP_PROTOCOL){
    $staticUrl = get_static_url($protocol);
    return $staticUrl . DS . "upload" . DS;
}

function get_admin_upload_url($tmpPath,$module, $protocol = HTTP_PROTOCOL){
    $base =  get_base_upload_url($protocol).get_admin_project_name() . DS . get_upload_cdn_evn() .  DS;
    return $base.$module.DS . $tmpPath;
}

function get_product_url( $tmpPath , $protocol = HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    if(substr($tmpPath,0,4) == "http"){
        return $tmpPath;
    }

    return get_admin_upload_url($tmpPath,"product",$protocol);
}

function get_agent_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"agent",$protocol);
}

function get_banner_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"banner",$protocol);
}

function get_category_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"category",$protocol);
}



function get_category_attr_para_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"category_attr_para",$protocol);
}

function get_avatar_url($tmpPath,$protocol = 'http'){
    if(!$tmpPath){
        return get_default_user_url(HTTP_PROTOCOL);
    }

    if(substr($tmpPath,0,4) == "http"){
        return $tmpPath;
    }

    return get_admin_upload_url($tmpPath,"avatar",$protocol);
}

function get_factory_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"factory",$protocol);
}

function get_feedback_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"feedback",$protocol);
}

function get_comment_url( $tmpPath , $protocol =HTTP_PROTOCOL){
    if(!$tmpPath){
        return false;
    }
    return get_admin_upload_url($tmpPath,"comment",$protocol);
}

function get_default_user_url($protocol = "http"){
    $staticUrl = get_static_url($protocol);
    return $staticUrl . "/nouser.png";
}


//function get_avatar_url_by_uid($uid){
//    $user = UserModel::db()->getById($uid);
//}






//以上是新的
function getUserAvatar($userInfo){

    if(!arrKeyIssetAndExist($userInfo,'avatar')){
        if(APP_NAME =='instantplay_new'){
            return get_static_url("https")   . "xyx/static/default_logo.jpg";
        }else{
            return get_static_url("https")   . "xyx/static/images/nouser.png";
        }
    }

    if($userInfo['robot'] == 1){
        return get_static_file_url_by_app('avatar', "rbt/". $userInfo['avatar'],IS_NAME );
    }

    if(substr($userInfo['avatar'],0,5) == "http:"){
        //兼容下HTTPS
        return substr($userInfo['avatar'],0,4)."s".substr($userInfo['avatar'],4);
    } elseif (substr($userInfo['avatar'],0,5) == "https") {
        return $userInfo['avatar'];
    }

    return get_static_file_url_by_app('avatar', "user/" . $userInfo['avatar'], IS_NAME);

}

function get_cdn_base_dir(){
    return BASE_DIR ."/www/".get_cdn_xyx_dir();
}

function get_cdn_xyx_dir(){
    return "xyxnew";
}

function get_upload_os_dir_by_app ($appName = APP_NAME,$module = '',$path = ''){
    $dir = get_cdn_base_dir();
    $appDir = $dir . "/".get_upload_cdn_evn()."/".$appName;

    if($module){
        $appDir .= "/$module/";
    }

    if($path){
        $appDir .= "/$path/";
    }

    return $appDir ;
}

function get_upload_cdn_evn(){
    if(ENV == 'dev' || ENV == 'local'){
        $appDir = "dev";
    }elseif(ENV == 'release'){
        $appDir = "pro";
    }elseif(ENV == 'pre'){
        $appDir = "dev";
    }else{
        exit(" EVN ERR");
    }

    return $appDir;
}

function get_static_file_url_by_app($module,$path,$appName = APP_NAME,$protocol = 'https'){
    $url = get_static_url($protocol) . "/".get_cdn_xyx_dir()."/".get_upload_cdn_evn(). "/$appName/$module/";

    if($path){
        $url .= $path ;
    }
    return $url;
}

function get_img_url_by_app($path,$appName = APP_NAME,$protocol = 'http'){
    if(!$path){
        return "";
    }
    return get_static_url($protocol)."upload/".$appName.DS.$path;
}

function get_img_url($path,$protocol = 'http'){
    return get_static_url($protocol)."upload/".$path;
}

function get_tmp_status_dir(){
    return  "xyxnew/".get_upload_cdn_evn()."/static/";
}

function get_domain_url($protocol = HTTP_PROTOCOL){
    return $protocol."://".DOMAIN_URL."/";
}

function get_static_url($protocol = 'http'){
    return $protocol."://".STATIC_URL."/";
}

function curl_get($url){
    $resultMap = [];
    $arr = explode("/", $url);
    $ctrl = $arr[3];
    $ac = $arr[4];
    if($ac == "logout")return;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, "YeRenChai_v1.0");

    $output = curl_exec($ch);
    if($output === false){
        echo "curl error:".curl_errno($ch);
        return;
    }


    if(curl_getinfo($ch,CURLINFO_HTTP_CODE) == '200'){
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $headerSize);
        $body = substr($output, $headerSize);
        // echo $headerSize;
    }
    curl_close($ch);
    // var_dump($output);
    $json = json_decode($output, true);
    // var_dump($json);
    
    // echo $url;
    // echo "    -----".checkJson($json['msg'], $ctrl, $ac)."----\n";
    // var_dump($json);
    $resultMap['url'] = $url;
    $resultMap['succ'] = checkJson($json['msg'], $ctrl, $ac);
    $resultMap['json'] = $output;
    return $resultMap;
}

function checkJson($msg, $ctrl, $ac){
    if(!isset($msg)){
        return false;
    }
    $arr = $GLOBALS['api'][$ctrl][$ac]['return'];

    foreach($arr as $k=>$v){
                    //标量
                    if($k =='scalar') {
                        if ($v['must']) {
                            if ($v['type'] == 'int') {
                                if(is_int($msg)){
                                    continue;   
                                }else{

                                    return false;
                                }
                            } elseif ($v['type'] == 'string') {
                                if(is_string($msg)){
                                    continue;   
                                }else{
                                    return false;   
                                }
                            }
                        } else {
                            // if( ! $msg ){
                            //     continue;
                            // }

                            if ($v['type'] == 'int') {
                                if(is_int($msg)){
                                    continue;   
                                }else{
                                    return false;   
                                }
                            } elseif ($v['type'] == 'string') {
                                if(is_string($msg)){
                                    continue;   
                                }else{
                                    return false;   
                                }
                            }
                        }
                        //判断当前KEY   是不是  一维数据
                    }elseif($k == 'array_key_number_one'){
                        if($v['must']){
                            if(is_array($msg)){
                                // exit("return value must have value.array_key_number_two");
                                if(count($msg)<=0){
                                    return false;
                                }
                            }else{
                                return false;
                            }
                        }

                        // foreach($msg as $k3=>$v3){
                        //     foreach($v['list'] as $k2=>$v2){
                        //         if($v2['must']){
                        //          if(isset($msg[$k2]))return false;
                        //             if($v2['type'] == 'int'){
                        //                 if(is_int($msg[$k2])){
                        //                  continue;
                              //        }else{
                              //            return false;   
                              //        }
                        //             }elseif($v2['type'] == 'string'){
                        //                 if(is_string($msg[$k2])){
                        //                  continue;
                              //        }else{
                              //            return false;   
                              //        }
                        //             }
                        //         }else{
                        //             if(isset($msg[$k2])){
                        //                 if($v2['type'] == 'int'){
                        //                     if(is_int($msg[$k2])){
                        //                      continue;
                                 //         }else{
                                 //             return false;   
                                 //         }
                        //                 }elseif($v2['type'] == 'string'){
                        //                     if(is_string($msg[$k2])){
                        //                      continue;
                                 //         }else{
                                 //             return false;   
                                 //         }
                        //                 }
                        //             }
                        //         }

                        //     }
                        // }
                        if(is_array($msg)){
                            foreach($msg as $k3=>$v3){
                                foreach ($v['list'] as $k2 => $v2) {
                                    if($v2['must']){
                                        if(!isset($msg[$k2])){
                                            return false;
                                        }
                                        if($v2['type'] == 'int'){
                                            if(is_int($msg[$k2])){
                                                continue;
                                            }else{
                                                return false;   
                                            }
                                        }elseif($v2['type'] == 'string'){
                                            if(is_string($msg[$k2])){
                                                continue;
                                            }else{
                                                return false;   
                                            }
                                        }
                                    }else{
                                        if(isset($msg[$k2])){
                                            if($v2['type'] == 'int'){
                                                if(is_int($msg[$k2])){
                                                    continue;
                                                }else{
                                                    return false;   
                                                }
                                            }elseif($v2['type'] == 'string'){
                                                if(is_string($msg[$k2])){
                                                    continue;
                                                }else{
                                                    return false;   
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    //是个 二维数组
                    }elseif($k == 'array_key_number_two'){
                        if($v['must']){
                            if(!$msg){
                                // exit("return value must have value.array_key_number_two");
                                return false;
                            }
                        }

                        if(is_array($msg)){
                            foreach($msg as $k3=>$v3){
                                foreach($v['list'] as $k2=>$v2){
                                    if($v2['type'] == 'int'){
                                        // $msg[$k3][$k2] = intval($msg[$k3][$k2]);
                                        if(is_int($msg[$k3][$k2])){
                                            continue;
                                        }else{
                                            return false;   
                                        }
                                    }elseif($v2['type'] == 'string'){
                                        // $msg[$k3][$k2] = (string)$msg[$k3][$k2];
                                        if(is_string($msg[$k3][$k2])){
                                            continue;
                                        }else{
                                            return false;   
                                        }
                                    }
                                }
                            }
                        }
                    }elseif($v['array_type'] =='array_key_number_one'){//array('pageInfo'=>$pageInfo,'list'=>$list);
                        // if($v['must']){
                        //     if(!isset($msg[$k])){
                        //         // exit("return value must have value.array_type array_key_number_one");
                        //         return false;
                        //     }
                        // }
                        if(!$v['must']){//不必须
                            if(!isset($msg[$k])){
                                continue;
                            }
                        }
                        if(is_array($msg[$k])){
                            foreach($msg[$k] as $k3=>$v3){//獲取histotylist
                                foreach($v['list'] as $k2=>$v2){
                                    if($v2['must']){
                                        if($v2['type'] == 'int'){
                                            // $msg[$k][$k3] = intval($msg[$k][$k3]);
                                            if(is_int($msg[$k][$k3])){
                                                continue;
                                            }else{
                                                return false;   
                                            }
                                        }elseif($v2['type'] == 'string'){
                                            // $msg[$k][$k3] = (string)$msg[$k][$k3];
                                            if(is_string($msg[$k][$k3])){
                                                continue;
                                            }else{
                                                return false;   
                                            }
                                        }
                                    }else{
                                        if(isset($msg[$k][$k3])){
                                            if($v2['type'] == 'int'){
                                                // $msg[$k][$k3] = intval($msg[$k][$k3]);
                                                if(is_int($msg[$k][$k3])){
                                                    continue;
                                                }else{
                                                    return false;   
                                                }
                                            }elseif($v2['type'] == 'string'){
                                                // $msg[$k][$k3] = (string)$msg[$k][$k3];
                                                if(is_string($msg[$k][$k3])){
                                                    continue;
                                                }else{
                                                    return false;   
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }elseif($v['array_type'] =='array_key_number_two'){


                        if(!$v['must']){//不必须
                            if(!isset($msg[$k])){
                                continue;
                            }
                        }

                        if(is_array($msg[$k])){
                            foreach($msg[$k] as $k3=>$v3){
                                foreach($v['list'] as $k2=>$v2){
                                    if($v2['type'] == 'int'){
                                        if(is_int($msg[$k][$k3][$k2])){
                                            continue;
                                        }else{
                                            return false;   
                                        }
                                        // $msg[$k][$k3][$k2] = intval($msg[$k][$k3][$k2]);
                                    }elseif($v2['type'] == 'string'){
                                        if(is_string($msg[$k][$k3][$k2])){
                                            continue;
                                        }else{
                                            return false;   
                                        }
                                        // $msg[$k][$k3][$k2] = (string)$msg[$k][$k3][$k2];
                                    }
                                }
                            }
                        }
                    }else{
                        // exit("api config return info err!");
                        return false;
                    }
                }

                return true;
}