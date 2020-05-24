<?php
//异步任务
class AsynTaskCtrl {
    function sendEmail($email,$title,$content,$attachmentUrl = ""){
        LogLib::wsWriteFileHash(["in AsynTaskCtrl",$email,$title,$content]);
        $emailLib = new EmailLib();
        $rs = $emailLib->realSend($email,$title,$content,$attachmentUrl);
        return $rs;
    }
}