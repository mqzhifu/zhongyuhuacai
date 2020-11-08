<?php
class WsProtobufCtrl
{
    public $request = null;

    public $_api = null;
    public $_err_code = null;
    public $_main = null;
    public $_redis_key = null;

    function __construct($request)
    {
        $this->request = $request;
        if (!arrKeyIssetAndExist($request, 'code')) {
            exit("auth code is null");
        }

        if ($request['code'] != 'mqzhifu') {
            exit("auth code is err.");
        }
        //接口配置信息
        $this->_api = ConfigCenter::get(APP_NAME, "api");
        //所有错误码
        $this->_err_code = ConfigCenter::get(APP_NAME, "err_code");
        //主-配置文件
        $this->_main = ConfigCenter::get(APP_NAME, "main");
        //redis所有key的配置文件
        $this->_redis_key = ConfigCenter::get(APP_NAME, "rediskey");
    }

    function index()
    {
        echo "im index";
        exit;
    }


    function createProtoBuf()
    {
        include_once APP_CONFIG . DS . "api.php";
        $api = $GLOBALS['api'];

        $header = "syntax = \"proto3\";\n\n";
        foreach ($api as $k => $module) {

            if ($k != 'user') {
                continue;
            }
            $content = $header;
            foreach ($module as $k2 => $method) {
                if ($k2 == 'title') {
                    continue;
                }

                $content .= "//" . $method['ws']['request_code'] . " - " . $method['title'] . "\nmessage {$k2}Request {\n";
                $i = 1;
                foreach ($method['request'] as $k3 => $v3) {
                    $type = $v3['type'];
                    if ($v3['type'] == 'int') {
                        $type = 'int32';
                    }

                    if ($v3['type'] == 'boolean') {
                        $type = 'bool';
                    }

                    $content .= "\t{$type} $k3=$i;\n";
                    $i++;
                }

                $content .= "}\n\n";
//                $content .= "//".$method['ws']['response_code']." - ".$method['title']."\nmessage {$k2}Response {\n";
//                $i = 1;
//                foreach($method['return'] as $k3=>$v3){
//                    $type = $v3['type'];
//                    if($v3['type'] == 'int'){
//                        $type = 'int32';
//                    }
//
//                    if($v3['type'] == 'boolean'){
//                        $type = 'bool';
//                    }
//
//                    $content .= "\t{$type} $k3=$i;\n";
//                    $i++;
//                }
//
//                $content .="}\n\n";


            }
            $fileName = $k . ".proto";
            $path = APP_CONFIG . "protobuf_config/" . $fileName;
            echo $path . "<br/>";
            $fd = fopen($path, "w+");
            fwrite($fd, $content);


        }
        var_dump($content);
        exit;
    }

    function compileProtoBuf()
    {
        $path = APP_CONFIG . "protobuf_config";

        $fd = opendir($path);

        $targetPath = APP_CONFIG . "protobuf_class";
        while ($file = readdir($fd)) {
            if ($file == "." || $file == '..') {
                continue;
            }
            $commend = PLUGIN . "/protoc.exe -I=" . $path . " --php_out=" . $targetPath . " $file";
            echo $commend . "\n";
            exec($commend, $out, $status);
//            var_dump($out);var_dump($status);
//            exit;
        }

    }


    function webSocketJson()
    {
        $client = new WsclientLib();
        $ip = "49.51.252.214";
        $port = "9500";
        $client->connect($ip, $port, '/');

        $data = array('code' => 2003, 'data' => array('token' => 11111),);
        $data = json_encode($data);
        $rs = $client->sendData($data);
        var_dump($rs);
        exit;
    }

    function grpc($c, $a)
    {
        include_once PLUGIN . "/protobuf/autoload.php";

        include_once APP_CONFIG . "/protobuf_class/GPBMetadata/{$c}.php";
        include_once APP_CONFIG . "/protobuf_class/{$a}Request.php";

        $class = "{$a}Request";
//        var_dump($class);exit;
        $obj = new $class();
    }

    function wsProtobuf($c, $a)
    {
        include_once APP_CONFIG . "/server.php";

        include_once PLUGIN . "/protobuf/autoload.php";

        include_once APP_CONFIG . "/protobuf_class/GPBMetadata/{$c}.php";
        include_once APP_CONFIG . "/protobuf_class/{$a}Request.php";

        $class = "{$a}Request";
//        var_dump($class);exit;
        $obj = new $class();
//        $obj->setUserId(1);
//        $obj->setNum(2);
//        $obj->setSessionKey("aaaaaaa");


        $rs = $obj->serializeToString();

        $client = new WsclientLib();
        $client->connect($GLOBALS['server']['ip'], $GLOBALS['server']['port'], '/');


        $data = pack("N", 1003) . $rs;

//        $len = strlen($rs)+8;

//        $data = pack("n",$len).pack("N",18003).pack("N",1212).$rs;
//        $payload = json_encode(array(
//            'code' => 'xxx',
//            'id' => '1'
//        ));
        $rs = $client->sendData($data);

        if ($rs !== true) {
            echo "sendData error...\n";
        } else {
            echo "ok\n";
        }


//        $ws = new WebSocketClientLib();
//        $ws->connect("127.0.0.1","59001");

//
    }

    //有些接口，必须是登陆后，才能访问~有些不需要
    function loginAPIExcept($ctrl = "", $ac = "")
    {
        if (!$ctrl && !$ac) {
            $ctrl = $this->ctrl;
            $ac = $this->ac;
        }

        $arr = $this->_main['loginAPIExcept'];
        foreach ($arr as $k => $v) {
            if ($v[0] == $ctrl && $v[1] == $ac) {
                return 1;
            }
        }

        return 0;
    }

    function uidTransferToken()
    {
        $uid = $this->request['uid'];
        if (!$uid) {
            exit("uid 为空");
        }
        $user = UserModel::db()->getById($uid);
        if (!$user) {
            exit("uid not in db");
        }
        $service = new UserService();
        $token = $service->createToken($uid);
        echo $token;
    }

    function tokenTransferUid($token)
    {
        if (!$token) {
            exit("token 为空");
        }
        $uuid = TokenLib::getUid($token);
        var_dump($uuid);
        exit;
        $uid = $uuid['msg'];

        $user = UserModel::db()->getById($uid);
        var_dump($user);

        var_dump($uid);
        exit;
    }

    function receiveJson()
    {
        $data = file_get_contents("php://input");
        var_dump($data);
        exit;
    }

    function testSendCURLJson()
    {
        echo 4;

        $url = "http://local.sanguo.com/login/WXGame/";
        $json = json_encode(array('code' => '11111'));
        $rs = CurlLib::send($url, 2, $json, 0, 1);
        var_dump($rs);
        exit;
    }
}