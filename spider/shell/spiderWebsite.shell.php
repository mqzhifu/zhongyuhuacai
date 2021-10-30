<?php
class spiderWebsite{
    public $config = null;

    public function __construct($c){
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME,"main");
        $this->config = $config['common'];
    }

    public function run($argc){
        $s_time = time();

        $website = $argc['website'];
        $forum = $argc['forum'];
        if (!$website){
            exit("website is null");
        }
        if (!arrKeyIssetAndExist($this->config,$website)){
            exit("website not in arr");
        }

        if (!$forum){
            exit("forum is null");
        }
        if (!arrKeyIssetAndExist($this->config[$website]['forum'],$forum)){
            exit("forum not in arr");
        }
        $this->out("website : $website , forum : $forum");

        $config = $this->config[$website];
        $forumConfig = $config["forum"][$forum];

        $protocol = "http";
//        if ($website == "sis_001"){
//            $protocol = "http";
//        }
        $host = $protocol."://". $config['host'][0]. "/";


        if ($website == HEJIDI){
            $hostForum = $host . "thread.php?fid-".$forumConfig['link_id'];
//            thread.php?fid-13-page-2.html
        }elseif ($website == "caoliu"){
            $hostForum = $host . "thread0806.php?fid=".$forumConfig['link_id'] . "&";
        }
        else{
            $hostForum = $host . "forum-".$forumConfig['link_id'];
        }

        out($hostForum);

        $provider = new $config['provider_class']();
        $totalPages = $forumConfig['loop_end'] - $forumConfig['loop_start'];
        $totalDataRecords = 0;
        for($page = $forumConfig['loop_start'];$page <= $forumConfig['loop_end'];$page++){
            if ($website == "hejidi"){
                $url = $hostForum . "-page-$page.html";
            }else if ($website == "caoliu"){
                $url = $hostForum . "page=$page";
            } else{
                $url = $hostForum . "-$page.html";
            }
            $this->out("page: $page , url: $url");
            $httpContent = $provider->curlGetHtmlContent($url,$host,0);
            $pageDataList = $provider->parseOnePage($httpContent['body'],$forumConfig,$page);
            $this->out("parseOnePage return data cnt:".count($pageDataList));
            $totalDataRecords += count($pageDataList);
            foreach ($pageDataList as $k=>$data){
                $data['status'] = 0;
                $id =$forumConfig['db_model_class']::db()->add($data);
                $this->out("k: ".$k ." , id: ".$id);
            }
            $sleepTime = 100000;
            echo "echo $sleepTime\n";
            usleep($sleepTime);
//            if ($page > 5 ){
//                exit(111);
//            }
        }

        $e_time = time() - $s_time;
        $this->out("total exec time : $e_time");
        $this->out("total pages : $totalPages");
        $this->out("total data records : $totalDataRecords");

    }

    function testHtmlContentPutFile($httpContent,$website,$forum,$page){
        //用于测试
        $fileName = $website ."_" . $forum ."_{$page}.txt";
        $this->putContentFile($fileName,$httpContent);
        $this->parseOneSis001("test");
    }


    function out($str,$ln = 1){
        if($ln){
            $str .= "\n";
        }
        echo $str;
    }

    function putContentFile($fileName,$content){
        $fd = fopen($fileName,"w+");
        fwrite($fd,$content);
    }
}

function myPregMatchAll($eg,$str,$indexOne,$index2){
    preg_match_all($eg,$str,$match);
//        var_dump($match);
    if(isset($match[$indexOne]) && $match[$indexOne]){
        if(isset($match[$indexOne][$index2]) && $match[$indexOne][$index2]){
            return $match[$indexOne][$index2];
        }
    }
    return "";
}
