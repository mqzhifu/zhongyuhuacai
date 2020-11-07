<?php
class clearAllPic{
    public $_file_upload_dir = "";
    public function __construct($c){
        $this->commands = $c;
    }

    public function run(){
        $uploadService = new UploadService();
        $this->_file_upload_dir = $uploadService->getFileUploadDir();
        $getAllUploadImage =$uploadService->getAllUploadImage();
        $this->loop($getAllUploadImage);

    }

    function loop($data,$parent = "/"){
        foreach ($data as $k=>$v){
            if(is_array($v)){
                $this->loop($v,$parent . DS . $k);
            }else{
                $pathFile = $parent . DS . $v;
                $pathFile = substr($pathFile,1);
                $pathFile = $this->_file_upload_dir  . $pathFile;
                out("file:".$pathFile);
                if($v == 'desc.txt' or $v == 'demo.txt'){

                }else{
                    $rs = "true";
                    if (!unlink($pathFile)){
                        $rs = 'false';
                    }
                    out("exec commend del rs : $rs");
                }
            }
        }
    }

}