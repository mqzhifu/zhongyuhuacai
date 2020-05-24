<?php
//异步任务
class AsynTaskCtrl
{
    function sendEmail($email, $title, $content, $attachmentUrl = "")
    {
        LogLib::wsWriteFileHash(["in AsynTaskCtrl", $email, $title, $content]);
        $emailLib = new EmailLib();
        $rs = $emailLib->realSend($email, $title, $content, $attachmentUrl);
        return $rs;
    }

    function roomWriteDB($data){
        $id = RoomModel::db()->add($data);
        LogLib::wsWriteFileHash(["roomWriteDB :",$data,$id]);
//        return $id;
    }

    function roomUpWriteDb($data,$roomId,$fromUid){
        $id = RoomModel::db()->update($data," room_id = '$roomId' and from_uid = '$fromUid' limit 1");
        LogLib::wsWriteFileHash(["roomUpWriteDb :",$roomId,$fromUid,$data,$id]);
//        return $id;
    }

    function roomUpWriteDbByRoomId($data,$roomId){
        $rs = RoomModel::db()->update($data," room_id = '$roomId' limit 2");
        LogLib::wsWriteFileHash(["roomUpWriteDbByRoomId :",$roomId,$data,$rs]);
//        return $rs;
    }

    function rsyncMsgWriteDB($data){
        $data['a_time'] = time();
        $id = GameRsyncLogModel::db()->add($data);
        LogLib::wsWriteFileHash(["rsyncMsgWriteDB id :",$id]);
//        return $id;
    }

    function writeLogin($data){
        $data['a_time'] = time();
        $id = LoginModel::db()->add($data);
//        return $id;
    }
}