<?php
class ApiCtrl extends BaseCtrl{

    function bank(){
        $this->show('bank');
    }

    function game(){
        $this->show('game');
    }

    function userSafe(){
        $this->show('userSafe');
    }

    function user(){
        $this->show('user');
    }

    function im(){
        $this->show('im');
    }

    function push(){
        $this->show('push');
    }

    function fans(){
        $this->show('fans');
    }

    function system(){
        $this->show('system');
    }

    function invite(){
        $this->show('invite');
    }

    function task(){
        $this->show('task');
    }

    function sign(){
        $this->show('sign');
    }

    function lottery(){
        $this->show('lottery');
    }

    function sdk(){
        $this->show('sdk');
    }

    function login(){
        $this->show('login');
    }

    function advertise(){
        $this->show('advertise');
    }

    function index(){
        $this->show('index');
    }







    function show($module,$type = ""){

        $ApiLib = new ApiLib();
        $mapData = $ApiLib->mapArrApi($module,$type);

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

    function doc(){
        $this->display("api_wiki/doc.html");
    }
}
