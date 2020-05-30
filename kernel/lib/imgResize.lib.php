<?php
// 将图像等比放大或缩小
class imgResizeLib{

    private $src;
    private $image;
    private $width;
    private $height;
    private $imageType;
    private $imageResize;
    private $newWidth;
    private $newHeight;

    // 文件路径名，期待文件的宽度
    public function __construct($fileName, $newWidth){

        $this->src = $fileName;
        $this->newWidth = $newWidth;
        $this->imageType = exif_imagetype($fileName);
        $this->image = $this->openImage($this->src);
        if($this->image){
            $this->width = imagesx($this->image);
            $this->height = imagesy($this->image);
        }


    }


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


$img1 = new resize('GD/img/logo.png',50);
$img1->resizeImage();


?>