<?php
class matchUser{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr)
    {
        $matchUserService = new GameMatchService();
        $para = array(1,1);
        while(1){
            $rs = $matchUserService->matchRealUser($para);

            LogLib::matchUserWriteFileHash($rs);
            sleep(1);
        }
    }

}