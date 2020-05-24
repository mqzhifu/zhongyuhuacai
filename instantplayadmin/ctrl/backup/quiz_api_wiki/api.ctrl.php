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
        $this->show('sign');
    }

    function advertise(){
        $this->show('advertise');
    }







    function show($module,$func = ""){
        include_once CONFIG_DIR ."quiz/api.php";
        include_once CONFIG_DIR . DS ."/quiz/main.php";
        $domain = "http://is-test.feidou.com/";

//        var_dump($GLOBALS['api']);exit;

        $moduleArr = $GLOBALS['api'][$module];
        if(!$moduleArr){
            exit("参数 值 错误");
        }
        unset($moduleArr['title']);


        $map = array();
        $requestUrl = array();
        $i = 0;

        foreach($moduleArr as $k=>$v){
            if( ! $this->loginAPIExcept($module,$k,$func)){
                $moduleArr[$k]['request']['token'] = array('type'=>'string','must'=>1,'default'=>'sre6Yn94stiGuZzbfbWt2LN2ua5_yXRx','title'=>'token');
            }
            $map[$i] = $k;
            $requestUrl[$i] = $domain .$module."/".$k."/";

            if($moduleArr[$k]['request']){
                foreach($moduleArr[$k]['request'] as $k2=>$v2){
                    $requestUrl[$i] .= $k2 ."={$v2['default']}&";
                }
            }

            $i++;
        }


        $this->assign("map",$map);
        $this->assign("requestUrl",$requestUrl);


//        $this->assign("j",0);

        $this->assign("moduleArr",$moduleArr);
        $this->assign("module",$module);

        $this->display("api_wiki/show.html");
    }

    function loginAPIExcept($ctrl = "",$ac = "",$func = ""){
        if(!$ctrl && !$ac ){
            $ctrl = $this->ctrl;
            $ac = $this->ac;
        }

        $arr = $GLOBALS['main']['loginAPIExcept'];

        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 1;
            }
        }

        return 0;
    }

    function desc(){
        $this->display("api_wiki/desc.html");
    }

    function doc(){
        $this->display("api_wiki/doc.html");
    }
}
