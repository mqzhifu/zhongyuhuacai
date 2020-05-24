<?php
//图片上传
class ImageUpLoadLib{
	//文件大小：2MB
    public $fileSize = 2;
    public $fileType = array('pjpeg','gif','bmp','png','jpeg','jpg','x-png');
    //文件上传路径
    public $path = APP_FILE_UPLOAD_DIR;
    //是否开始HASH随机文件名
    public $hash = true;
    public $postInputName = null;
    public $module = "";
    function __construct(){

    }

    function realUpLoadOneFile($postInputName,$path ,$allowFileTypes = array(),$useHash = 0){
        $errInfo = " upLoadOneFile ";
        if(!is_dir($path))
            exit($errInfo." path not dir");

        if(!$allowFileTypes)
            exit($errInfo." allowFileTypes path not dir");

        foreach ($allowFileTypes as $k=>$v) {
            if(!in_array($v,$this->fileType)){
                exit($errInfo ." allowFileTypes is err. $v");
            }
        }

        if(!$postInputName)
            exit($errInfo." postInputName path not dir");

        if(!isset($_FILES[$postInputName]))
            exit($errInfo.'$_FILES['.$postInputName .'] null notice: enctype="multipart/form-data"');


        $_FILE = $_FILES[$postInputName];
        $mark = file_mode_info($path);
        if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'){
            if( $mark < 7){
                exit($errInfo." dir not power");
            }
        }else{
            if( $mark != 15){
                exit($errInfo." dir not power");
            }
        }

        if(arrKeyIssetAndExist($_FILE,'error')){
            exit($errInfo.'$_FILES[$postInputName] have error');
        }


//        $fileName = $postInputName;
        if($_FILE['size']  > $this->fileSize  * 1024 * 1024)
            exit($errInfo." file size >  ".$this->fileSize);

        //判断文件类型(扩展)，共3步，1：文件名、2：PHP自带的函数、3：打开二进制文件


        //1判断文件名
        $extFileType = get_file_ext($_FILE["name"]);
        $extFileType = strtolower($extFileType);//转小写
        if(!in_array($extFileType, $this->fileType))
            exit($errInfo . " extFileType $extFileType ");


        //2PHP自带的函数
        $fileType = explode('/',$_FILE["type"]);
        $preType = $fileType[0];

        $extFileType = strtolower($fileType[1]);
        if(!in_array($extFileType, $this->fileType)){
            exit($errInfo . " explode fileType  $extFileType ");
        }

        if($extFileType == 'pjpeg' || $extFileType == 'jpeg'){
            $extFileType = 'jpg';
        }
        if($extFileType == 'x-png' || $extFileType == 'png'){
            $extFileType = 'png';
        }

        //这个验证就比较关键了，防止用户上传恶意文件~
        //但实际上黑客还是能攻击，但至少能防一些低级的攻击者
        $extFileType = $this->getFileType($_FILE["tmp_name"]);
        if(!in_array($extFileType,$this->fileType)){
            exit($errInfo . " tmp_name  type error");
        }

        $createFileName = date("YmdHis")."_" .rand(1000,9999);
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
            exit($errInfo . " move_uploaded_file error");
        }

        return out_pc(200,$fileDirName);
    }

	//开始上传一个文件
    //$path:文件上传路径
    //$fileType：文件类型|文件扩展名
    //$postNames:input name
    function upLoadOneFile($postInputName,$module ,$allowFileTypes = array(),$useHash = 0 ){
        $errInfo = " upLoadOneFile ";
        if(!$module){
            exit($errInfo." module is null");
        }



        $path = $this->path . DS . get_upload_cdn_evn() . DS .$module;
        return $this->realUpLoadOneFile($postInputName,$path,$allowFileTypes,$useHash);
    }
	//上传多文件
	function upLoad(){
		foreach($this->postNames as $k=>$fileName ){
			$this->upLoadFile($fileName);
		}

	}

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

