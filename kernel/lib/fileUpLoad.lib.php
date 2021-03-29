<?php
//文档上传
class FileUpLoadLib{
    const fileTypeCategoryImg = "img";
    const fileTypeCategoryVideo = "video";
    const fileTypeCategoryDoc = "doc";
    public $fileTypeCategory = array(
        "img"=>array('pjpeg','gif','bmp','png','jpeg','jpg','x-png'),//图片
        "video"=>array('mp4','avi','flv',"mkv",'rmvb','wmv','rm'),//视频
        "doc"=>array(
            "txt",
            'doc','docx',"dotx", "vnd.openxmlformats-officedocument.wordprocessingml.document",'msword',"vnd.openxmlformats-officedocument.wordprocessingml.template",
            "pptx","ppsx","vnd.ms-powerpoint",'application/vnd.openxmlformats-officedocument.presentationml.presentation',"application/vnd.openxmlformats-officedocument.presentationml.slideshow","application/vnd.openxmlformats-officedocument.presentationml.template",
            'xlsx',"x-xls","vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.openxmlformats-officedocument.spreadsheetml.template",

        ),//文档
    );
	//文件大小：2MB
    public $fileSize = 10;
    //文件上传路径
    public $basePath = APP_FILE_UPLOAD_DIR;
    //是否开始HASH随机文件名
    public $hash = true;
    public $postInputName = null;
    public $module = "";
    public $compress = 1;//开启压缩
    public $compressSize = 2048;//多大后，开启压缩，KB
    function __construct(){

    }

    function realUpLoadOneFileExitErr($msg){
        $errInfoPrefix = " upLoadOneFile :";
        exit($errInfoPrefix.$msg);
    }

    function realUpLoadOneFile($postInputName,$path ,$fileTypeCategory , $saveFileNewName = "" ,$allowFileTypeCategoryList = array(),$isBinary = 0,$useHash = 0,$compress = 0){
        if(!is_dir($path))
            $this->realUpLoadOneFileExitErr(" path not dir");

        if(!$fileTypeCategory)
            exit(" fileTypeCategory is null");

        if(!arrKeyIssetAndExist($this->fileTypeCategory,$fileTypeCategory)){
            $this->realUpLoadOneFileExitErr(" fileTypeCategory value error!");
        }


        if($allowFileTypeCategoryList){
            $fileTypeCategoryList = [];
            foreach ($allowFileTypeCategoryList as $k=>$v) {
                foreach ($fileTypeCategoryList as $k2=>$v2){
                    if($v != $v2){
                        $this->realUpLoadOneFileExitErr(" allowFileTypeCategoryList value error,<$v>!");
                    }
                }
                $fileTypeCategoryList[] = $v;
            }
        }else{
            $fileTypeCategoryList = $this->fileTypeCategory[$fileTypeCategory];
        }

        if(!$postInputName)
            $this->realUpLoadOneFileExitErr( " postInputName path not dir");

        if(!isset($_FILES[$postInputName]))
            $this->realUpLoadOneFileExitErr('$_FILES['.$postInputName .'] null notice: enctype="multipart/form-data"');

        $_FILE = $_FILES[$postInputName];
        $mark = file_mode_info($path);
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
            if( $mark < 7){
                $this->realUpLoadOneFileExitErr(" dir not power");
            }
        }else{
            if( $mark != 15){
                $this->realUpLoadOneFileExitErr(" dir not power");
            }
        }

        if(arrKeyIssetAndExist($_FILE,'error')){
            $this->realUpLoadOneFileExitErr('$_FILES[$postInputName] have error');
        }
        if($_FILE['size']  > $this->fileSize  * 1024 * 1024)
            $this->realUpLoadOneFileExitErr(" file size >  ".$this->fileSize);
        //压缩
        if($compress){
            if($fileTypeCategory != FileUpLoadLib::fileTypeCategoryImg){
                $this->realUpLoadOneFileExitErr(" 只有图片才支持压缩功能");
            }
            if($this->compressSize){
                if($_FILE['size'] > $this->compressSize  * 1024){

                }
            }
        }
        //判断文件类型(扩展)，共3步，1：文件名、2：PHP自带的函数、3：打开二进制文件


        //1判断文件名
        $extFileType = get_file_ext($_FILE["name"]);
        $extFileType = strtolower($extFileType);//转小写
        if(!in_array($extFileType, $fileTypeCategoryList))
            $this->realUpLoadOneFileExitErr(" extFileType $extFileType ");

        if(!$isBinary){//二进制可能 是： application/octet-stream
            //2PHP自带的函数
            $fileType = explode('/',$_FILE["type"]);
            $preType = $fileType[0];

//            $extFileType = strtolower($fileType[1]);
//            if($preType == 'text'){
//                $extFileType = "txt";
//            }elseif($preType == 'doc' || $preType == 'docx' || $preType == 'dotx'){
//                $extFileType = "doc";
//            }elseif($preType == 'pptx' || $preType == 'ppsx'  ){
//                $extFileType = "ppt";
//            }elseif($preType == 'msword' || $preType == 'xlsx'){
//                $extFileType = "xlsx";
//            }

            if(!in_array($extFileType, $fileTypeCategoryList)){
                $this->realUpLoadOneFileExitErr(" explode fileType  $extFileType ");
            }
        }else{
            $preType = $extFileType;
        }


        if($extFileType == 'pjpeg' || $extFileType == 'jpeg'){
            $extFileType = 'jpg';
        }
        if($extFileType == 'x-png' || $extFileType == 'png'){
            $extFileType = 'png';
        }

        if($fileTypeCategory == FileUpLoadLib::fileTypeCategoryImg){
            //这个验证就比较关键了，防止用户上传恶意文件~
            //但实际上黑客还是能攻击，但至少能防一些低级的攻击者
            $extFileType = $this->getFileType($_FILE["tmp_name"]);
            if(!in_array($extFileType,$this->fileType)){
                $this->realUpLoadOneFileExitErr(" tmp_name  type error");
            }
        }else{//视频和文档就不做校验了，权宜之计，视频先不做处理

        }

        $createFileName = date("YmdHis")."_" .rand(1000,9999);
        if($saveFileNewName){
            $createFileName = $saveFileNewName;
        }

        if($useHash){
            //年月日-小时分秒+4位随机数

            //获取 HASH 文件夹目录
            $hashDir = $this->mkdirHash();
            $fileDirName = $hashDir . "/" . $createFileName ."." .$extFileType;
        }else{
            $fileDirName = $createFileName."." .$extFileType;
        }

        $realFileDir = $path . DS .$fileDirName;
        //真正-开始-将用户上传的临时文件，转移至目录
        $rs = move_uploaded_file($_FILE["tmp_name"],$realFileDir);
        if(!$rs){
            $this->realUpLoadOneFileExitErr( " move_uploaded_file error");
        }

        return out_pc(200,$fileDirName);
    }

//    function upLoadOneFileByBinary($postInputName,$module){
//        $errInfo = " upLoadOneFile ";
//        if(!$module){
//            exit($errInfo." module is null");
//        }
//
//        $path = $this->path . DS . get_upload_cdn_evn() . DS .$module;
//        if(!is_dir($path))
//            exit($errInfo." path not dir");
//
//        if(!$postInputName)
//            exit($errInfo." postInputName path not dir");
//
//        if(!isset($_FILES[$postInputName]))
//            exit($errInfo.'$_FILES['.$postInputName .'] null notice: enctype="multipart/form-data"');
//
//        $_FILE = $_FILES[$postInputName];
//        $mark = file_mode_info($path);
//        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
//            if( $mark < 7){
//                exit($errInfo." dir not power");
//            }
//        }else{
//            if( $mark != 15){
//                exit($errInfo." dir not power");
//            }
//        }
//
//        if(arrKeyIssetAndExist($_FILE,'error')){
//            exit($errInfo.'$_FILES[$postInputName] have error');
//        }
//
//        if($_FILE['size']  > $this->fileSize  * 1024 * 1024)
//            exit($errInfo." file size >  ".$this->fileSize);
//
//    }

	//开始上传一个文件
    //$path:文件上传路径
    //$fileType：文件类型|文件扩展名
    //$postNames:input name
    function upLoadOneFile($postInputName,$module ,$fileTypeCategory ,$saveFileNewName = "",$allowFileTypeCategoryList = array(),$isBinary = 0,$useHash = 0,$compress = 0){
        $errInfo = " upLoadOneFile ";
        if(!$module){
            exit($errInfo." module is null");
        }

        $path = $this->path . DS . get_upload_cdn_evn() . DS .$module;
        return $this->realUpLoadOneFile($postInputName,$path,$fileTypeCategory,$saveFileNewName,$allowFileTypeCategoryList,$isBinary,$useHash,$compress);
    }
//	//上传多文件
//	function upLoadMulti(){
//		foreach($this->postNames as $k=>$fileName ){
//			$this->upLoadFile($fileName);
//		}
//
//	}

	function mkdirHash(){
		$dirName = date("Ymd");
		$dir = $this->path . "/" . $dirName;
		if(!is_dir($dir)){
			mkdir( $dir );
		}

		return $dirName;
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

}

