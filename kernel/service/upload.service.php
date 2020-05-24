<?php

class UploadService
{
//    public $fileSize = 2;
//    public $fileType = array('pjpeg','gif','bmp','png','jpeg','jpg','x-png');
//    public $path = IMG_UPLOAD;
//    public $hash = true;
//    public $postInputName = null;
//    public $module = "";
    // public $urlPrefix = get_static_url('https')."upload/".APP_NAME;

    function product($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'product',array('png','jpg','bmp'),0);
        return $rs;
    }

    function agent($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'agent',array('png','jpg','bmp'),0);
        return $rs;
    }

    function avatar($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'avatar',array('png','jpg','bmp'),0);
        return $rs;
    }
    function banner($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'banner',array('png','jpg','bmp'),0);
        return $rs;
    }

    function categoryAttrPara($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'category_attr_para',array('png','jpg','bmp'),0);
        return $rs;
    }

    function feedback($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'feedback',array('png','jpg','bmp'),0);
        return $rs;
    }

    function comment($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'comment',array('png','jpg','bmp'),0);
        return $rs;
    }

    function factory($postInputName){
        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputName,'factory',array('png','jpg','bmp'),0);
        return $rs;
    }






    function upAvatar($uid=0, $avatar=''){
        $lib = new ImageUpLoadLib();

        $rs = $lib->upLoadOneFile('avatar',USER_AVATAR_IMG_UPLOAD);
        if($rs['code'] != 200){
            return $rs;
        }

        $this->rsyncToCDNServer();
        $data = array('avatar'=>$rs['msg']);

        $userService = new UserService();
        $rs = $userService->upUserInfo($uid,$data);
        $user = $userService->getUinfoById($uid);
        $avatarImgUrl = $user['avatar'];

        return out_pc(200, $avatarImgUrl);

    }

    //type:1 图片 2文件
    function uploadFileByApp($postInputNmae,$module,$path ,$type,$appName = APP_NAME,$uid = 0){
        $osDir = get_upload_os_dir_by_app($appName,$module ,$path);
        LogLib::appWriteFileHash($osDir);

        $lib = new ImageUpLoadLib();
        $rs = $lib->upLoadOneFile($postInputNmae,$osDir);
        if($rs['code'] != 200){
            return $rs;
        }

        $data = array('avatar'=>$rs['msg']);
        LogLib::appWriteFileHash($data);

        if ($uid && $module == 'avatar'){
            $userService = new UserService();
            $rs2 = $userService->upUserInfo($uid,$data);
            $user = $userService->getUinfoById($uid);

//            $uinfo = $userService->getUinfoById($uid);
//            $url = getUserAvatar($uinfo);

            $avatarImgUrl = $user['avatar'];
        }else{
            $avatarImgUrl = $rs['msg'];
        }

        $openGamesService = new OpenGamesService();
        $openGamesService->rsyncToCDNServer($appName,$module,$path,$rs['msg']);

        return out_pc(200, $avatarImgUrl);
    }

    public function rsyncToCDNServer ($appName = APP_NAME,$module,$path,$avatarImgUrl)
    {
        $shell = "/usr/bin/rsync -av /kpool/apps/instantplay/www/avatar/user/* 10.10.7.223::XYX/static/avatar/user";
        exec($shell);
        // 同步数据到CDN
//        $rsyncPath = get_upload_os_dir_by_app($appName,$module , $path)  ;
//        chmod($rsyncPath ."/$avatarImgUrl",0744);
//        $comm = "/usr/bin/rsync -av {$rsyncPath} 10.10.7.223::XYX/".get_upload_cdn_evn()."/$appName/$module/$path/ > /dev/null";

        $os_dir = get_cdn_base_dir();

        $ar1 = get_upload_cdn_evn() . "/$appName/$module/$path/$avatarImgUrl";
        $comm = BASE_DIR . "/www/".get_cdn_xyx_dir()."/rsync_to_cdn.sh $ar1 $os_dir > /dev/null";
//        $comm = BASE_DIR . "/www/".get_cdn_xyx_dir()."/rsync_to_cdn.sh $ar1 $os_dir ";
//        echo $comm;
//        exit;
        system($comm);
    }

    /**
     * 上传图片open下 例如xxx/open/{$path}/{$filePurposeMark}_20190226093346_8258.jpg
     * 默认大小2M
     * @param  [type]  $inputName       [description]
     * @param  [type]  $path            /开头
     * @param  integer $size            [description]
     * @param  [type]  $imgtype         [description]
     * @param  string  $filePurposeMark [description]
     * @return string  /avatar/20190226140942_1569.jpg
     */
    function imageUpLoad($inputName, $path=null, $size = 0, $imgtype=null, $filePurposeMark = "")
    {
        if($size != 0){
            $this->fileSize = $size;
        }
        if($path != null){
            $_path = $this->getBasePath().$path;
        }else{
            $_path = $this->getBasePath();
        }
        $rs = $this->upLoadOneFile($inputName, $_path, $imgtype, $filePurposeMark);
        // if($rs['code'] != 200&&$rs['code'] != 8018){
        //     return out_pc($rs['code'],$GLOBALS['code'][$rs['code']]);
        // }
        if($rs['code'] == 8018){
            return "";
        }
        if($rs['code'] != 200){
            return "";
        }
        $avatar = $rs['msg'];
        $path2 = $path.'/'. $avatar;// /avatar/100/xxx.png

        $openGamesService = new OpenGamesService();
        $openGamesService->rsyncToServer();

        return $path2;
    }

    public function getBasePath()
    {
        $path = BASE_DIR.DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."xyx".DIRECTORY_SEPARATOR;
        if (ENV == 'release') {
            // 正式版本
            $path .= "pro".DIRECTORY_SEPARATOR;
        } else {
            // 开发版
            $path .= "dev".DIRECTORY_SEPARATOR;
        }
        if('mgopen' == APP_NAME){
            $path .= "upload".DIRECTORY_SEPARATOR.'open';
        }else{
            $path .= "upload".DIRECTORY_SEPARATOR.APP_NAME;
        }
        return $path;
    }

    public function getStaticBaseUrl(){
        $baseUrl = "https://mgres.kaixin001.com.cn/xyx";
        if(ENV == 'release'){
            $baseUrl .= DIRECTORY_SEPARATOR."pro".DIRECTORY_SEPARATOR;
        } else {
            $baseUrl .= DIRECTORY_SEPARATOR."dev".DIRECTORY_SEPARATOR;
        }

        $baseUrl .= "upload".DIRECTORY_SEPARATOR.APP_NAME;
        return $baseUrl;
    }
}