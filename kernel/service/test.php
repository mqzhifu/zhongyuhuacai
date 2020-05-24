<?php
class Test{
    static $_service_name = 'Test';
    static $_inst = null;

    function inst(){
        if(self::$_inst){
            return self::$_inst;
        }

        self::$_inst = SproxyLib::ins();
        self::$_inst->setServiceName(self::$_service_name);

        return self::$_inst;
    }


    static function GetOne(){
        $c = self::inst()->GetUserOne();

    }
}