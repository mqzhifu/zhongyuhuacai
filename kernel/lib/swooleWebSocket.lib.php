<?php
$mem = null;
class SwooleWebSocketLib{
    private $_ip = "";
    private $_port = 0;
    private $_config = array(
        'dispatch_mode '=>2,//reactor寻找 WORK策略

        'daemonize'=>1,//以守护进程方式启动
//        'pid_file' => APP_DIR.'/swoole_master.pid',
        'pid_file' => '/var/run/swoole_' . SERVER_CONF_KEY .'.pid',
        'reactor_num'=>1,//最好和CPU数对应
        'task_worker_num'=>2,//全异步非阻塞：CPU数 1-4倍，同步阻塞100以上。如果此数小于reactor，默认调整和reactor相同
        'worker_num'=>4,
        'reactor_num'=>2,//调节poll线程的数量
        'backlog'=>128,//等待连接数
        'max_request'=>1000,//worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程.如果为0表示不需要自动重启。
        'max_conn'=>65535,//最大连接数，底层会根据此值分配内存空间保存所有connect,不要过大
        'log_file'=> LOG_APP_PATH.'/ws/swoole.log',
        'heartbeat_check_interval' =>30,//每隔多少秒检测一次，单位秒,Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
        'heartbeat_idle_time' => 60, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过

//        'ssl_cert_file'=>'/soft/nginx/conf/heyshell.crt',
//        'ssl_key_file'=>'/soft/nginx/conf/heyshell.key',
    );

    function __construct($ip =null ,$port = null,$config = null){

    }

    public function run(){
        $this->_ip = $GLOBALS['server'][SERVER_CONF_KEY]['bind_ip'];
        $this->_port = $GLOBALS['server'][SERVER_CONF_KEY]['port'];

        LogLib::wsWriteFileHash(['ip:'.$this->_ip,"port:".$this->_port]);


        //创建websocket服务器对象，监听0.0.0.0:9502端口
        //SWOOLE_PROCESS 多进程模式
        //TCP 类型的连接,IPV4
//        $server = new swoole_websocket_server($this->_ip, $this->_port ,SWOOLE_PROCESS,SWOOLE_TCP|SWOOLE_SSL);
        $server = new swoole_websocket_server($this->_ip, $this->_port ,SWOOLE_PROCESS,SWOOLE_TCP);

        //初始化配置信息
        $server->set( $this->_config );
        LogLib::wsWriteFileHash(['set config ',$this->_config]);


        //监听WebSocket连接打开事件.握手完成后，调用这个方法
        $server->on('open', 'onOpen');
        //监听WebSocket消息事件
        $server->on('message', 'onMessage' );
        //监听WebSocket连接关闭事件
        $server->on('close','onClose');

        $server->on('shutdown','onShutdown');

        $GLOBALS['ws_server'] = $server;

        //创建内存共享区
        $table = new swoole_table($this->_config['max_conn']);
        $table->column('fd', swoole_table::TYPE_INT, 4);
        $table->create();

        $GLOBALS['uid_fd_table'] = $table;

        $table = new swoole_table($this->_config['max_conn']);
        $table->column('uid', swoole_table::TYPE_INT, 4);
        $table->create();

        $GLOBALS['fd_uid_table'] = $table;



        $table = new swoole_table($this->_config['max_conn']);
        $table->column('mysql_id', swoole_table::TYPE_INT, 4);
        $table->create();

        $GLOBALS['mysql_id'] = $table;


        $server->addListener($GLOBALS['server'][SERVER_CONF_KEY]['http_bind_ip'],$GLOBALS['server'][SERVER_CONF_KEY]['http_port'],SWOOLE_SOCK_TCP);
        $server->on('request',"httpReceive");

        $server->on('Task','myOnTask');
        $server->on('Finish','myTaskFinish');

//        $server->on('Receive', 'httpReceive');

        LogLib::wsWriteFileHash(" start:swoole demon process....");
        LogLib::wsWriteFileHash(['new swoole_websocket_server:',$server]);

        swoole_set_process_name($GLOBALS['server'][SERVER_CONF_KEY]['process_name'] . "_" .ENV);

        $server->start();
    }

}

function myOnTask($serv,  $task_id,  $src_worker_id,  $data){
    LogLib::wsWriteFileHash(["in myOnTask",$serv,  $task_id,  $src_worker_id,  $data]);

    $lib = new DispathLib();
    $rs = $lib->authDispath($data['ctrl'],$data['ac']);
    LogLib::wsWriteFileHash($rs);
    $rs = $lib->action($data);
    LogLib::wsWriteFileHash($rs);

}

function myTaskFinish( $serv,  $task_id,  $data){
    LogLib::wsWriteFileHash(["in myTaskFinish",$serv,  $task_id,  $data]);
}

function httpReceive($request, $response){
    $data = $request->get;
    LogLib::wsWriteFileHash(['in httpReceive',$data]);

    $GLOBALS['ws_server']->task($data,0);

    $response->end("ok");
}

//建立SOCKET 成功后
function onOpen(){
    $argv = func_get_args();
    $server = $argv[0] ;
    $request = $argv[1];

    LogLib::wsWriteFileHash(["a new WS connect,fd:{$request->fd}"]);
    //$request->fd;
    //$request->head;
    //$request->server;
    //$request->data;
    //var_dump($request);

}
//关闭操作
function onClose(){
    $argv = func_get_args();
    $server = $argv[0] ;
    $fd = $argv[1];

    LogLib::wsWriteFileHash(["client {$fd} is closed"]);

    $class = new LoginCtrl($fd);
    $out_info = $class->onClose($fd);


//    if(IN_TYPE == 'PROTOBUF'){
//
//    }else{
//        $data = json_encode($out_info);
//        $server->push($fd, $data);
//    }

}

function onShutdown($server){
    $class = new LoginCtrl();
    $out_info = $class->wsShutdown($server);

    //清空 在线人数
    RedisOptLib::delOnlineUserTotal();

}


//C向S端发送数据
function onMessage(){
    //接口配置信息
    include_once APP_CONFIG.DS."/api.php";
    $argv = func_get_args();


    //主SERVER 进程 信息
    $server = $argv[0] ;
    //当前连接的用户 信息
    $frame = $argv[1];

    LogLib::wsWriteFileHash(["===receive from {$frame->fd}",$frame->data,"opcode:{$frame->opcode}","fin:{$frame->finish}"]);

    if(! $frame->data){
        return false;
    }

    $code = 0;

    //包含PROTOBUF-PHP 插件
    include_once PLUGIN."/protobuf/autoload.php";
    //开启protobuf
    if(IN_TYPE == 'PROTOBUF'){
        $code = unpack('N',substr( $frame->data,0,4));
        if(!$code){
            $msg = "PROTOBUF code is null\n";
            LogLib::wsWriteFileHash([$msg]);
            return -1;
        }
        $code = $code[1];
        LogLib::wsWriteFileHash(["unpack code:".$code]);
        $code = intval($code);
        if(!$code){
            $msg = "PROTOBUF code is not int\n";
            LogLib::wsWriteFileHash([$msg]);
            return -2;
        }

        if(strlen($code) != 4){

        }
    }elseif( IN_TYPE == 'JSON' ){
        $data = json_decode($frame->data,true);
        $code = $data['code'];
    }

    //根据消息头，消息号，映射对应的执行方法
    $config = [];
    foreach($GLOBALS['api'] as $module=>$method){
        foreach($method as $k=>$v){
            if($k =='title'){
                continue;
            }
            if($v['ws']['request_code'] == $code){
                $config = array('ctrl'=>$module,'ac'=>$k ,'method'=>$v );
                break;
            }
        }
    }

    if(!$config){
        $msg = "not found code config\n";
        LogLib::wsWriteFileHash([$msg]);
        $server->push($frame->fd,"not found code config");
        return false;
    }

    LogLib::wsWriteFileHash([$config['ctrl'],$config['ac'],$config['method']['ws']['request_code']]);

    $final_data = null;

    if(IN_TYPE == 'PROTOBUF'){
        //兼容没有参数的情况
        if($config['method']['request']){
            $className = $config['ac']."Request";

            $path = APP_CONFIG."/protobuf_class/". $className.".php" ;
            if(!file_exists($path)){
                $msg = "protobuf_class not exists:".$path;
                LogLib::wsWriteFileHash([$msg]);
                return -3;
            }

            $path2 = APP_CONFIG."/protobuf_class/GPBMetadata/".ucwords($config['ctrl']).".php";
            include_once $path2;

            $class = new $className();
            foreach($config['method']['request'] as $k=>$v){
                $acName = "get".ucwords($k);
                $value = $class->$acName();
                $final_data[$k] = $value;
            }
        }
    }else{
        $final_data = $data['data'];
    }

//    LogLib::wsWriteFileHash(['request para:',$final_data]);


    $class = new DispathLib($frame);
    $class->authDispath($config['ctrl'],$config['ac']);

    $out_info = null;
    try{
        $out_info = $class->action($final_data);
    }catch (Exception $e){
        ExceptionFrameLib::throwCatch($e);
        return -7;
    }

    LogLib::wsWriteFileHash(["out info:",$out_info]);

    if(!$out_info){
        LogLib::wsWriteFileHash(["error:out info is null"]);
        return -6;
    }



    if(OUT_TYPE == 'PROTOBUF'){
        $className = $config['ac']."Response";
        $path = APP_CONFIG."/protobuf_class/". $className.".php" ;
        if(!file_exists($path)){
            $msg = "protobuf_class not exists:".$path;
            LogLib::wsWriteFileHash([$msg]);
            return -4;
        }

        $path2 = APP_CONFIG."/protobuf_class/GPBMetadata/".ucwords($config['ctrl']).".php";
        include_once $path2;

        $class = new $className();
        if($out_info['code'] != 200){
            $class->setCode($out_info['code']);
//            $class->setCode($out_info['msg']);


            $returnData = $class->serializeToString();
            $server->push($frame->fd,$returnData);
        }else{
            foreach($config['method']['return'] as $k=>$v){
                $acName = "get".ucwords($k);
                $value = $class->$acName();
                $final_data[$k] = $value;
            }
        }
    }elseif(OUT_TYPE == 'JSON'){
        $d = array('code'=>$config['method']['ws']['response_code'],'msg'=>null);
        $d['msg'] = $out_info;
        $data = json_encode($d);

        $server->push($frame->fd, $data);

    }

}