<?php
class ToolsCtrl {

    function __construct(){
        //接口配置信息
        include_once CONFIG_DIR.DS.IS_NAME."/api.php";
    }

    function index(){echo "im index";}


    function createProtoBuf(){
        include_once APP_CONFIG.DS."api.php";
        $api =$GLOBALS['api'];

        $header = "syntax = \"proto3\";\n\n";
        foreach($api as $k=>$module){

            $content = $header;
            foreach($module as $k2=>$method){
                if($k2 =='title'){
                    continue;
                }

                $content .= "//".$method['ws']['request_code']." - ".$method['title']."\nmessage {$k2}Request {\n";
                $i = 1;
                foreach($method['request'] as $k3=>$v3){
                    $type = $v3['type'];
                    if($v3['type'] == 'int'){
                        $type = 'int32';
                    }

                    if($v3['type'] == 'boolean'){
                        $type = 'bool';
                    }




                    $content .= "\t{$type} $k3=$i;\n";
                    $i++;
                }

                $content .="}\n\n";
                $content .= "//".$method['ws']['response_code']." - ".$method['title']."\nmessage {$k2}Response {\n";
                $i = 1;
                foreach($method['return'] as $k3=>$v3){
                    $type = $v3['type'];
                    if($v3['type'] == 'int'){
                        $type = 'int32';
                    }

                    if($v3['type'] == 'boolean'){
                        $type = 'bool';
                    }

                    $content .= "\t{$type} $k3=$i;\n";
                    $i++;
                }

                $content .="}\n\n";


            }
            $fileName = $k.".proto";
            $path = APP_CONFIG."protobuf_config/".$fileName;
            echo $path."<br/>";
            $fd = fopen($path,"w+");
            fwrite($fd,$content);


        }
        var_dump($content);exit;
    }

    function compileProtoBuf(){
        $path = APP_CONFIG."protobuf_config";

        $fd = opendir($path);

        $targetPath = APP_CONFIG."protobuf_class";
        while ($file = readdir($fd)){
            if($file == "." || $file == '..'){
                continue;
            }
            $commend = PLUGIN."/protoc.exe -I=".$path." --php_out=".$targetPath." $file";
            echo $commend."\n";
            exec($commend,$out,$status);
//            var_dump($out);var_dump($status);
//            exit;
        }

    }


    function wsjson(){
        $client = new WsclientLib();
        $ip = "49.51.252.214";
        $port = "9500";
        $client->connect($ip,$port , '/');

        $data = array('code'=>2003,'data'=>array('token'=>11111),);
        $data = json_encode($data);
        $rs = $client->sendData($data);
        var_dump($rs);exit;
    }

    function wsProtobuf($c,$a){
        include_once APP_CONFIG."/server.php";

        include_once PLUGIN . "/protobuf/autoload.php";

        include_once APP_CONFIG."/protobuf_class/GPBMetadata/{$c}.php";
        include_once APP_CONFIG."/protobuf_class/{$a}Request.php";

        $class = "{$a}Request";
//        var_dump($class);exit;
        $obj = new $class();
//        $obj->setUserId(1);
//        $obj->setNum(2);
//        $obj->setSessionKey("aaaaaaa");


        $rs = $obj->serializeToString();

        $client = new WsclientLib();
        $client->connect($GLOBALS['server']['ip'], $GLOBALS['server']['port'], '/');


        $data = pack("N",1003).$rs;

//        $len = strlen($rs)+8;

//        $data = pack("n",$len).pack("N",18003).pack("N",1212).$rs;
//        $payload = json_encode(array(
//            'code' => 'xxx',
//            'id' => '1'
//        ));
        $rs = $client->sendData($data);

        if( $rs !== true ){
            echo "sendData error...\n";
        }else{
            echo "ok\n";
        }





//        $ws = new WebSocketClientLib();
//        $ws->connect("127.0.0.1","59001");

//
    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl = "",$ac = ""){
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

    function apilist($code = 0){
        if(!$code){
            exit("code err1");
        }

        if($code != 'mqzhifu'){
            exit("code err2");
        }

        $st = getAppSmarty();

        $api =$GLOBALS['api'];

        $moduleCnt = 0;
        $methodCnt = 0;
        foreach($api as $k=>$module){
            $moduleCnt++;
//            echo $module['title']."</br>";
            foreach($module as $k2=>$method){
                $methodCnt++;
                if($k2 =='title'){
                    continue;
                }
                if( $this->loginAPIExcept($k,$k2)){
                    $api[$k][$k2]['is_login'] = "否";
                }else{
                    $api[$k][$k2]['is_login'] = "是";
                }
            }

        }
        $index_html = $st->compile("apilist.html");

        include $index_html;
    }

    function tdd($module = ''){
        $api =$GLOBALS['api'];
        $token = "sre6YYF4rpaHqYaWf7W5lg";
        if($module){
            $moduleArr = $api[$module];
            if(!$api){
                exit("module 参数 错误");
            }

            $this->tddModule($module,$moduleArr,$token);
            exit;
        }





        $i = 0;
        foreach($api as $k=>$moduleArr){
            $i++;
            if($k != 'game'){
                continue;
            }
//            echo $module['title']."</br>";

            $this->tddModule($module,$moduleArr,$token);

//            if($i == 5){
//                exit;
//            }
        }
    }

    function tddModule($module,$moduleArr,$token){
        foreach($moduleArr as $k2=>$method){
            if($k2 =='title'){
                continue;
            }
            if(! $this->loginAPIExcept($module,$k2)){
                $method['request']['token'] = array('type'=>'string','must'=>1,'default'=>$token,'title'=>'token');
            }

            $url = get_domain_url() .$module."/".$k2."/";

            if($method['request']){
                foreach($method['request'] as $k3=>$v4){
                    $url .= $k3 ."={$v4['default']}&";
                }
            }

            if($k2 == 'onClose' || $k2 == 'logout'){
                echo "清TOKEN的方法不能执行<br/>";
                continue;
            }

            $rs = CurlLib::send($url);
            echo $url."<br/>";
            echo $rs['msg']."<br/><br/>";
        }
    }

    function apitest($c,$a,$code){
        if(!$code){
            exit("code err1");
        }

        if($code != 'mqzhifu'){
            exit("code err2");
        }

        $this->userService = new UserService();
        include_once APP_CONFIG.DS."api.php";
//        $token = _g('token');

        $token = "sre6YYCuxJSHz4qVfdup2g";
        $tokenInfo = TokenLib::getUid($token);
        $info = $this->userService->getUinfoById($tokenInfo['uid']);
        $api = $GLOBALS['api'];
        $method = null;
        foreach($api as $k=>$module){
            if($c != $k){
                continue;
            }
            foreach($module as $k=>$v){
                if($k == $a){
                    $v['module'] = $module['title'];
                    $method = $v;
                }
            }
        }
        if(!$method){
            exit("未找到方法，请检查参数，或者检查API配置文件");
        }

        $is_login = 1;
        if( $this->loginAPIExcept($c,$a)){
            $is_login = 0;
        }
        $para = array();
        if(arrKeyIssetAndExist($method,'request')){
            foreach($method['request'] as $k=>$v){
                if($v['must'] == 1){
                    $v['must'] = '是';

                }else{
                    $v['must'] = '否';
                }

                $v['name'] = $k;
                $para[] = $v;
//                $para[$k]['default'] = "";
//                if($v['name'] == 'uniqueCode'){
//                    $para[$k]['default'] = '123';
//                }
            }
        }


        //需要登陆的接口
        if($is_login == 1){
            $para[] =array('name'=>'token',"title"=>'token','must'=>'必填','default'=>$token,'info'=>$info,'title'=>'token');
        }



        $st = getAppSmarty();
        $index_html = $st->compile("apitest.html");
        include $index_html;
    }


    function uidTransferToken($uid){
        if(!$uid){
            exit("uid 为空");
        }
        $user = UserModel::db()->getById($uid);
        var_dump($user);

        $token = TokenLib::create(UserModel::$_type_wechat.$uid);
        echo $token;
    }

    function tokenTransferUid($token){
        if(!$token){
            exit("token 为空");
        }
        $uuid = TokenLib::getUid($token);
        var_dump($uuid);exit;
        $uid = $uuid['msg'];

        $user = UserModel::db()->getById($uid);
        var_dump($user);

        var_dump($uid);exit;
    }

    function addUserTodayTask($uid){
        echo 22;
        $taskClass = new TaskLib();
        $rs = $taskClass->addUserDailyTask($uid);
        var_dump($rs);exit;
    }

    function receiveJson(){
        $data = file_get_contents("php://input");
        var_dump($data);exit;
    }

    function testSendCURLJson(){
        echo 4;

        $url = "http://local.sanguo.com/login/WXGame/";
        $json = json_encode(array('code'=>'11111') );
        $rs = CurlLib::send($url,2,$json,0,1);
        var_dump($rs);exit;
    }

    function getCodeDesc(){
        $code = $GLOBALS['code'];
        $st = getAppSmarty();
        $index_html = $st->compile("code.html");
        include $index_html;
    }
}