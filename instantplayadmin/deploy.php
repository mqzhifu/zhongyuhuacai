<?php

class Deployment {

    public $serect = 'zzzzxiao'; //webhooks中配置的密钥
    public $logPath = "/home/www/zhongyuhuacai/storage/deploy";
    public $wwwDir = "/home/www/";
    public $projectName = "zhongyuhuacai";
    public $projectStaticName = "zhongyuhuacai_static";

    public $projectDir = "";
    public $storeDir = "";
    public $uploadDir = "";
    public $staticDir = "";
    public $payload = null;

    function __construct(){
        $this->projectDir = $this->wwwDir . $this->projectName . "/";
        $this->storeDir = $this->projectDir."storage/";
        $this->staticDir = $this->projectDir . "static/";
        $this->uploadDir = $this->staticDir."upload/";
    }

    public function parser()
    {
        $requestBody = file_get_contents('php://input'); //每次推送的时候，会接收到post过来的数据。
        //$this->write_log($requestBody);
        $requestBodyUrlDecode = urldecode($requestBody);
        //$this->write_log($requestBodyUrlDecode);
        $payload = substr($requestBodyUrlDecode,8);
        //$this->write_log($payload);
        $payload = json_decode($payload, true);    //将数据转成数组，方便取值。
        $this->write_log($payload);
        $this->payload = $payload;

        //if(empty($payload)){
        //写日志
        //      $this->write_log('send fail from github is empty');exit;
        //}else{
        //获取github推送代码时经过哈希加密密钥的值
            $this->write_log("HTTP_X_HUB_SIGNATURE:".$_SERVER['HTTP_X_HUB_SIGNATURE']);
            $this->write_log("HTTP_X_GITHUB_EVENT:".$_SERVER['HTTP_X_GITHUB_EVENT']);
            $this->write_log("HTTP_X_GITHUB_DELIVERY:".$_SERVER['HTTP_X_GITHUB_DELIVERY']);
              $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        //}


        $res_log = '['.$payload['commits'][0]['author']['name'] . ']' . '向[' . $payload['repository']['name'] . ']项目的' .
        $payload['ref'] . '分支'.$_SERVER['HTTP_X_GITHUB_EVENT'].'了代码。commit信息是：'.$payload['commits']['message'].'。详细信息如下：';

        $this->write_log($res_log);

//        if ($signature && strlen($signature) > 8 && $this->isFromGithub($requestBody,$signature)) {
        //验证密钥是否正确，如果正确执行命令。

        //}else{
        //      $this->write_log('git 提交失败！');
        //      abort(403);
//        }
    }

    public function isFromGithub($payload,$signature)
    {
        //$hash是github的密钥。然后与本地的密钥做对比。
        list($algo, $hash) = explode("=", $signature, 2);
        return $hash === hash_hmac($algo, $payload, $this->serect);
    }

    public function write_log($data,$jsonEncode = 0)
    {
        if(is_array($data)){
            $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        }

        $fd = fopen($this->logPath."/".date("Ymd").".log","a+");
        fwrite($fd,date("H:i:s") . " ". $data . "\n");
        // 此处加载日志类，用来记录git push信息，可以自行写。
    }

    function execShell($cmd){
        $output = null;
        $this->write_log("exec shell commend:$cmd");
        exec($cmd.' 2>&1',$output,$status);
//        $out = shell_exec($shell);
        if(is_array($output)){
            foreach ($output as $k=>$v){
                $this->write_log("out:".json_encode($v) . " status:$status" );
            }
        }else{
            if(!$output){
                $out = "null";
            }
            $this->write_log("out:".($out)  . " status:$status");
        }
    }

    function shell(){
        $this->write_log("start shell:");
        $this->execShell("cd {$this->projectDir} && git pull");
        $this->execShell("chmod 777 -R {$this->storeDir} ");
        $this->staticSoftLink();
        $this->execShell("chmod 777 -R {$this->uploadDir} ");
        $this->execShell("php {$this->projectDir}init_env.php pre");
         


//        $this->execShell("chown -R www:www {$this->projectDir} ");
//        $res = shell_exec("cd /home/www/instantplay && git pull 2>&1");
    }

    function staticSoftLink(){
        if(!is_dir($this->staticDir)){
            $this->execShell("ln -s {$this->wwwDir}{$this->projectStaticName}   ".substr($this->staticDir , 0 ,strlen($this->staticDir) - 2));
        }
    }
}

$deploy = new Deployment();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //触发此代码的时候，git是以post方式触发
    $signature = $deploy->parser();
    $deploy->shell();
    http_response_code(200);
}else{
    $deploy->write_log("err: not POST request.");
}