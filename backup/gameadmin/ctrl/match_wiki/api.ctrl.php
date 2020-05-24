<?php
class ApiCtrl extends BaseCtrl{


    function login(){
        $this->show('login');
    }

    function push(){
        $this->show('push');
    }

    function gameMatch(){
        $this->show('gameMatch');
    }

    function index(){
        $this->show('index');
    }

    function pic(){
        $this->display("api_wiki/pic.html");
    }


    function show($module,$type = ""){

        $ApiLib = new ApiLib();
        $mapData = $ApiLib->mapArrApi($module,'game');

        $this->assign("map", $mapData['map'] );
        $this->assign("requestUrl",$mapData['requestUrl']);


//        $this->assign("j",0);

        $this->assign("moduleArr",$mapData['moduleArr']);
        $this->assign("module",$module);

        $this->display("api_wiki/show.html");
    }

    function desc(){
        $this->display("api_wiki/desc.html");
    }
}
