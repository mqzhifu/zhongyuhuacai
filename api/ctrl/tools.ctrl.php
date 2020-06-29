<?php
class ToolsCtrl {
    public $request = null;

    public $_api = null;
    public $_err_code = null;
    public $_main = null;
    public $_redis_key = null;
    function __construct($request){
        $this->request = $request;
        if(!arrKeyIssetAndExist($request,'code')){
            exit("auth code is null");
        }

        if($request['code'] != 'mqzhifu'){
            exit("auth code is err.");
        }
        //接口配置信息
        $this->_api = ConfigCenter::get(APP_NAME,"api");
        //所有错误码
        $this->_err_code = ConfigCenter::get(APP_NAME,"err_code");
        //主-配置文件
        $this->_main = ConfigCenter::get(APP_NAME,"main");
        //redis所有key的配置文件
        $this->_redis_key = ConfigCenter::get(APP_NAME,"rediskey");
    }

    function index(){
        echo "im index";exit;
    }
    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl = "",$ac = ""){
        if(!$ctrl && !$ac ){
            $ctrl = $this->ctrl;
            $ac = $this->ac;
        }

        $arr = $this->_main['loginAPIExcept'];
        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 1;
            }
        }

        return 0;
    }

    function apilist($request){
        $st = getAppSmarty();

        $api = $this->_api;
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
        exit;
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

    function apitest($request){
        $c = $request['c'];
        $a = $request['a'];

        $this->userService = new UserService();
        $token = _g('token');
        if(!$token){
            $token = 'sre6ZoCIspaIqaCUfbWp2bN2vWp_2XRx';
        }
        $tokenInfo = TokenLib::getDecode($token);
//        var_dump($tokenInfo);exit;
        $info = $this->userService->getUinfoById($tokenInfo['uid']);
        $info = $info['msg'];
        $api = $this->_api;
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
        exit;
    }


    function uidTransferToken(){
        $uid = $this->request['uid'];
        if(!$uid){
            exit("uid 为空");
        }
        $user = UserModel::db()->getById($uid);
        if(!$user){
            exit("uid not in db");
        }
        $service = new UserService();
        $token = $service->createToken($uid);
        echo $token;
        exit;
    }

    function tokenTransferUid($request){
        if(!$request['token']){
            exit("token 为空");
        }
        $uuid = TokenLib::getDecode($request['token']);
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
        $code = $this->_err_code;
        $st = getAppSmarty();
        $index_html = $st->compile("code.html");

        $client_data_struct = get_client_data_struct();
//        var_dump($client_data_struct);exit;
//        $typeDesc = GoldcoinLogModel::getTypeDesc();
//        var_dump($typeDesc);exit;
//        $typeDescTitle = GoldcoinLogModel::getTypeTitle();


        $UserTypeDesc = UserModel::getTypeDesc();



        include $index_html;
    }

    function getTestApiResult(){
        $urls = generateApiDefaultUrls();
        $resultArr = [];
        foreach ($urls as $url) {
            $resultArr[] = curl_get($url);
        }
        // $resultArr = [['url'=>'dsadddddddddddddddddddddd','succ'=>1,'json'=>'dsads']];
        $st = getAppSmarty();
        $index_html = $st->compile("testApi.html");
        include $index_html;
    }

}