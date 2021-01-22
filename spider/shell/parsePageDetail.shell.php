<?php
class ParsePageDetail{
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

        $host = "http://". $config['host'][0]. "/";
//        $hostForum = $host . "forum-".$forumConfig['link_id'];
//        out($hostForum);


        $model = $forumConfig['db_model_class'];

        $where = " detail_status = 0 ";
        $listCnt = $model::db()->getCount( $where );
        $this->out("count : ".$listCnt);
        if(!$listCnt || $listCnt<= 0){
            exit(" no need.");
        }

        $everyTime = 100;
        $max =(int)( $listCnt / $everyTime ) + 1;
        $provider = new $config['provider_class']();
        $limit = " order by id asc limit  0, 100 ";
        $inc = 0;
        for($i = 0;$i < $max;$i++){
            $dataList = $model::db()->getAll( $where .$limit ,null, " id,link " );
            if(!$dataList){
                exit(" inner loop get mysql data is null");
            }
            foreach ($dataList as $k=>$v){
//                if($inc > 20){
//                    break;
//                }
                $inc++;
                $this->out($inc . " i=$i k=$k dbId : {$v['id']} link: {$v['link']}");
                $link = $provider->getLinkUrl($host ,$v['link']);
                $cookie = "WMwh_2132_viewid=tid_".$provider->getTid($v['link'] ."");
                $httpContent = $provider->curlGetHtmlContent($link,$host,0,$cookie,1);
                if($httpContent['header']['code'] == 302){
                    $this->out("302 continue");
                    continue;
                }
                if(!$httpContent['body']){
                    var_dump($httpContent);exit;
                }
                $parseDataRs = $provider->parseDetail($httpContent['body'],$forumConfig);
//                $data = array('video_duration'=>$video_duration,'video_type'=>$videoType,'video_size'=>$videoSize,'actor'=>$actor,);
                if($parseDataRs['code'] != 200){
                    $this->out("err one");
                    $upData = array("detail_status"=>2,);
                }else{
                    $parseData = $parseDataRs['data'];
                    $upData = array(
                        //vedio_duration 这个单词中的vedio是错的，要修改DB，麻烦先不动了
                        "vedio_duration"=>$parseData['video_duration'],
                        "video_type"=>$parseData['video_type'],
                        "video_size"=>$parseData['video_size'],
                        "actor"=>$parseData['actor'],
                        "img"=>$parseData['imgs'],
                        "detail_status"=>1,
                    );
                }
                var_dump($upData);
                $upRs = $model::db()->upById($v['id'],$upData);
                $this->out("ok~ok~ok~: ".$upRs ."");
            }
        }
        $e_time = time() - $s_time;
        $this->out("total exec time : $e_time");
//        $this->out("total pages : $totalPages");
//        $this->out("total data records : $totalDataRecords");

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