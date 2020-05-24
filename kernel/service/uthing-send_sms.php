<?php
/**
 * crontab::product
 */
header("Content-type: text/html; charset=utf-8");
define("DEBUG_ACTION", 0);
include dirname(__file__)."/apps.php";

class Msg extends apps{

    public function www_init(){

    }

    public function defaultAction(){
        $this->destroyView();

        $start_time = time();
        echo "start_time:".date("Y-m-d H:i:s",$start_time ) . "\n";

        while(1){
            $sms = new Sms("http");
//            Sys_Redis::inst()->delete(get_SMS_redis_key());
//            for($i=10;$i<=19;$i++){
//                $sms->send('135225364'.$i,'hello'.$i);
//            }
            $sms->realSend();

            $end_time = time();
            $pro_time = $end_time - $start_time;

            echo "sleep 2...\n";
            sleep(2);
        }

        echo "end_time:".date("Y-m-d H:i:s",$end_time) . " process_time:{$pro_time}\n";

    }
}
new Msg();
