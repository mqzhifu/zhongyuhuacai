<?php
// 将图像等比放大或缩小
class ImgResizeLib{

    private $src;
    private $image;
    private $width;
    private $height;
    private $imageType;
    private $imageResize;
    private $newWidth;
    private $newHeight;


    public function __construct($oldPathFile, $newPathFile , $newWidth , $newHeight){
        list($oldWidth,$oldHeidht,$oldExt) = getimagesize($oldPathFile);
        if($newWidth >= $oldWidth ){
            exit("new width >= old width");
        }
        if($newHeight >= $oldHeidht ){
            exit("new width >= old width");
        }

        switch($oldExt){
            case 1:
                //生成gIF格式画布
                $oldImg = imagecreatefromgif($oldPathFile);
                break;
            case 2:
                //生成jpg格式画布
                $oldImg = imagecreatefromjpeg($oldPathFile);
                break;
            case 3:
                //生成png格式画布
                $oldImg = imagecreatefrompng($oldPathFile);
                break;
            default:
                exit("ext type is err.");
        }

        if($oldWidth > $oldHeidht){
            $calcNewWidth = $oldWidth/$newWidth;
            $newW = $oldWidth/$calcNewWidth;
            $newH = $oldHeidht/$calcNewWidth;
        }else{
            $b = $oldHeidht/$newHeight;
            $newW = $oldWidth/$b;
            $newH = $oldHeidht/$b;;
        }

        $newImg = imagecreatetruecolor($newW,$newH);
        imagecopyresampLED($newImg,$oldImg,0,0,0,0,$newW,$newH,$oldWidth,$oldHeidht);


        switch($oldExt){
            case 1:
                //生成gIF格式画布
                imagegif($newImg,$newPathFile . ".gif");
                break;
            case 2:
                //生成jpg格式画布
                imagejpeg($newImg,$newPathFile . ".jpg");
                break;
            case 3:
                //生成png格式画布
                imagepng($newImg,$newPathFile. ".png");
                break;
            default:
                exit("ext type is err.");
        }

        imagedestroy($oldImg);
        imagedestroy($newImg);
    }
    // 文件路径名，期待文件的宽度
//    public function __construct($fileName, $newWidth){
//
//        $this->src = $fileName;
//        $this->newWidth = $newWidth;
//        $this->imageType = exif_imagetype($fileName);
//        $this->image = $this->openImage($this->src);
//        if($this->image){
//            $this->width = imagesx($this->image);
//            $this->height = imagesy($this->image);
//        }
//    }


    private function openImage($file){

        switch ($this->imageType) {
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif($file);
                break;

            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($file);
                break;

            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($file);
                break;
        }

        return $img;
    }


    private function saveImage(){

        switch ($this->imageType) {
            case IMAGETYPE_GIF:
                header("Content-type: " . image_type_to_mime_type(IMAGETYPE_GIF));
                imagegif($this->imageResize);
                break;

            case IMAGETYPE_JPEG:
                header("Content-type: " . image_type_to_mime_type(IMAGETYPE_JPEG));
                imagejpeg($this->imageResize);
                break;

            case IMAGETYPE_PNG:
                header("Content-type: " . image_type_to_mime_type(IMAGETYPE_PNG));
                imagepng($this->imageResize);
                break;
        }

        imagedestroy($this->image);
        imagedestroy($this->imageResize);
    }


    public function resizeImage(){

        $ratio = $this->height/$this->width;
        $this->newHeight = $this->newWidth*$ratio;
        $this->imageResize = imagecreatetruecolor($this->newWidth, $this->newHeight);
        imagecopyresampled($this->imageResize, $this->image, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
        $this->saveImage();
    }

}