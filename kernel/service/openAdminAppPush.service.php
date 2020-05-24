<?php

class openAdminAppPushService
{

    public function pushToQueue(){
        // 获取当前时间
        // $current = date("Y-m-d H:i:00");
        $current = '2019-03-13 10:00:00';
        printf("Datetime: %s\n", $current);
        $userService = new UserService();
        $identifier = (ENV == "release") ? "200000" : "100000";
        $openAdminModel = new openAdminAppPushModel();
        $openAdminModel->queryBySendOfTime($current, 10000, function ($result, $page) use ($userService, $identifier) {
            foreach ($result as $item) {
                $comma_separated = explode(";", $item['developer_information']);
                $str_count = count($comma_separated);
                // $comma_separated = implode(",", $comma_separated);
                $str_count_type = (1 == $str_count)?'single':'all';
                // 以此参数作为判断单条发送或是多条发送的依据;
                // 调用腾迅-信鸽发送接口;
                $lib =  new PushXinGeLib();
                if('single' == $str_count_type){
                    // $lib->pushAndroidNotifyOneMsgByToken(111059, $item['send_title'], $item['send_content'], array('typeId'=>1000,'taskConfigId'=>1));
                }else{
                    // 多条发送暂时循环调用单次发送的方法;
                    foreach ($comma_separated as $value){
                        // $lib->pushAndroidNotifyOneMsgByToken($value, $item['send_title'], $item['send_content'], array('typeId'=>1000,'taskConfigId'=>1));
                    }
                }
            }
        });
    }
}
