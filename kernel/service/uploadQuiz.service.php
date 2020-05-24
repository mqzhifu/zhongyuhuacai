<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/4/24
 * Time: 10:45
 */

/**
 * Class uploadQuizService
 */
class uploadQuizService
{
    public $fileSize = 2;
    public $fileType = array('pjpeg','gif','bmp','png','jpeg','jpg','x-png');
    public $path = IMG_UPLOAD;
    public $hash = true;
    public $postInputName = null;
    public $module = "";
    // public $urlPrefix = get_static_url('https')."upload/".APP_NAME;

    function upAvatar($uid=0, $avatar=''){
        LogLib::appWriteFileHash(["im upavatar,", $avatar]);
        $lib = new ImageUpLoadLib();

        LogLib::appWriteFileHash(USER_AVATAR_IMG_UPLOAD);

        $rs = $lib->upLoadOneFile('avatar',USER_AVATAR_IMG_UPLOAD);
        if($rs['code'] != 200){
            return $rs;
        }

        if(ENV == 'release'){//正式环境 上传的图片要执行一下同步到CDN
//          $shell = "/usr/bin/rsync -av /kpool/apps/instantplay/www/xyx/instantplay/avatar/user/* 10.10.7.223::XYX/instantplay/avatar/user";
            $shell = "/usr/bin/rsync -av /kpool/apps/instantplay/www/avatar/user/* 10.10.7.223::XYX/static/avatar/user";
            exec($shell);
//            LogLib::appWriteFileHash($rs);

        }

        $data = array('avatar'=>$rs['msg']);
        LogLib::appWriteFileHash([$rs['msg'],"im msg avatart"]);

//        if ($uid){
        $userService = new UserService();
        $rs = $userService->upUserInfo($uid,$data);
        $user = $userService->getUinfoById($uid);
//        }
//        else {
//            $user = ['avatar'=>$data['avatar'], 'robot'=>0];
//        }
        $avatarImgUrl = $user['avatar'];
//        LogLib::appWriteFileHash($avatarImgUrl);

        return out_pc(200, $avatarImgUrl);

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

    private function upLoadOneFile($postInputName, $path = null, $fileType = null, $filePurposeMark = ""){
        if($path){

            if(!is_dir($path))

                mkdir($path);
            //     echo $path;
            // exit;
            // return out_pc(8112);
            $this->path = $path;
        }else{
            if(!is_dir($this->path))
                return out_pc(8112);
        }

        if($fileType)
            $this->fileType = $fileType;

        if(!$postInputName){
            return out_pc(8017);
        }

        if(!isset($_FILES[$postInputName]))
            return out_pc(8018,'$_FILES['.$postInputName .'] null notice: enctype="multipart/form-data"');

        $mark = file_mode_info($this->path);
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
            if( $mark < 7){
                return out_pc(8113);
            }
        }else{
            if( $mark != 15){
                return out_pc(8113);
            }
        }

        if(arrKeyIssetAndExist($_FILES[$postInputName],'error')){
            return out_pc(8118);
        }


//        $this->postInputName = $postInputName;
        $fileName = $postInputName;
        if( $_FILES[$fileName]['size']  > $this->fileSize  * 1024 * 1024)
            return out_pc(8114);



        $fileType = get_file_ext($_FILES[$fileName]["name"]);

        $fileType = strtolower($fileType);
        if(!in_array($fileType, $this->fileType))
            return out_pc(8115);




        LogLib::appWriteFileHash("..............");
        LogLib::appWriteFileHash($_FILES[$fileName]);


        $fileType = explode('/', $_FILES[$fileName]["type"]);
        $fileType[1] = strtolower($fileType[1]);
        if(!in_array($fileType[1], $this->fileType)){
            return out_pc(8115);
        }

        if($fileType[1] == 'pjpeg' || $fileType[1] == 'jpeg'){
            $fileType[1] = 'jpg';
        }
        if($fileType[1] == 'x-png' || $fileType[1] == 'png'){
            $fileType[1] = 'png';
        }

        $type = $this->getFileType($_FILES[$fileName]["tmp_name"]);
        if(!in_array($type,$this->fileType)){
            return out_pc(8116);
        }

        $createFileName = date("YmdHis")."_" .rand(1000,9999);

        if($filePurposeMark && is_string($filePurposeMark)){
            $createFileName = $filePurposeMark."_".$createFileName;
        }

        $fileDirName = $createFileName."." . $fileType[1];


        $finalFileDirName =  $this->path . "/" .  $fileDirName;

        // var_dump($finalFileDirName);

        $rs = move_uploaded_file($_FILES[$fileName]["tmp_name"],$finalFileDirName);

        if(!$rs){
            return out_pc(8017);
        }

        $rs = $fileDirName;

        return out_pc(200,$rs);
    }

    function getFileType($filename){
        //打开文件
        $file = fopen($filename, "rb");
        //读前两个字节
        $bin = fread($file, 2);
        fclose($file);
        //二进制转十进制
        $strInfo = @unpack("C2chars", $bin);
        //连接两个字符
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        switch ($typeCode){
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 8075:
                $fileType = 'zip';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            default:
                $fileType = 'unknown: '.$typeCode;
        }

        //Fix
        if ($strInfo['chars1']=='-1' AND $strInfo['chars2']=='-40' ) return 'jpg';
        if ($strInfo['chars1']=='-119' AND $strInfo['chars2']=='80' ) return 'png';

        return $fileType;
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
        $path .= "upload".DIRECTORY_SEPARATOR.APP_NAME;
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