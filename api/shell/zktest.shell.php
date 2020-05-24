<?php
class zktest{

    public function run($attr){
        $service = new SproxyLib();
        $service->callMethod("test","GetUserOne",array("uid"=>1));
    }
}