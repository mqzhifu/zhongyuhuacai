<?php
/**
 * Created by PhpStorm.
 * User: XiaHB
 * Date: 2019/3/23
 * Time: 10:33
 */

/**
 * @param:开放平台通知消息封装公共方法;
 * @param:Class openNotificationService
 */
class openNotificationService{
    /**
     * @param $to_uid接收者
     * @param $type类型（1:1对1；2：1对多）
     * @param $title（标题）
     * @param $content（内容）
     * @return array
     */
    public function sendNotifyMsg($to_uid, $type, $title, $content){
        $returnArray = [];
        $returnArray['code'] = 0;
        // 对4个必要参数进行判断;
        if(empty($to_uid)){
            $returnArray['msg'] = '缺失接收者ID';
            return $returnArray;
        }
        if(empty($type)){
            $returnArray['msg'] = 'type参数缺失';
            return $returnArray;
        }
        if(empty($title)){
            $returnArray['msg'] = '缺失发送标题';
            return $returnArray;
        }
        if(empty($content)){
            $returnArray['msg'] = '缺失发送内容';
            return $returnArray;
        }
        // 落表数据处理;
        $insertData = [
            'uid'=>$to_uid,
            'title'=>$title,
            'type'=>$type,
            'content'=>$content,
            'a_time'=>time()
        ];
        $insertData['is_read'] = 2;
        $insertData['to_del'] = 2;
        $insertData['from_del'] = 2;
        $res = openNotificationModel::db()->add($insertData);
        if(isset($res) && !empty($res)){
            $returnArray['code'] = 1;
            $returnArray['msg'] = '成功';
            return $returnArray;
        }else{
            $returnArray['msg'] = '数据写入失败';
            return $returnArray;
        }
    }
}