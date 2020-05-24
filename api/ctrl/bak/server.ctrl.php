<?php
class Servcer{
    function getServer(){
        include_once APP_CONFIG."/server.php";
        return $this->out(200,$GLOBALS['server']['ws']);
    }

    function heartbeat(){
        return $this->out(200);
    }
}