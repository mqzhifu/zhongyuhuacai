<?php
class grpc{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        $protoPath = APP_CONFIG ."protobuf_config/";
        $out = APP_CONFIG."protobuf_class/";
        $grpc_php_plugin = PLUGIN . "/grpc/grpc_php_plugin";
        $shell = "protoc --proto_path=$protoPath   --php_out=$out   --grpc_out=$out   --plugin=protoc-gen-grpc=$grpc_php_plugin   test.proto";
        var_dump($shell);exit;
    }

}