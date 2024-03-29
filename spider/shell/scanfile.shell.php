<?php


// Stream #0:0(eng): Video: h264 (High) (avc1 / 0x31637661), yuv420p(tv, bt709), 1920x1080 [SAR 1:1 DAR 16:9], 4996 kb/s, 29.97 fps, 29.97 tbr, 30k tbn, 59.94 tbc (default)
// Stream #0:0(eng): Video: h264 (High) (avc1 / 0x31637661), yuv420p, 1920x1080 [SAR 1:1 DAR 16:9], 11996 kb/s, 59.94 fps, 59.94 tbr, 60k tbn, 59.94 tbc (default)
// Stream #0:0(und): Video: h264 (Constrained Baseline) (avc1 / 0x31637661), yuv420p(tv, bt709), 1920x1080, 16004 kb/s, 30 fps, 30 tbr, 1000k tbn, 2000k tbc (default)

class scanfile{
    public $config = null;

    public function __construct($c){
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME,"main");
        $this->config = $config['common'];
    }

    public function run($argc){
        $s_time = time();
        //mac 硬盘
//         $baseDir = "/Volumes/My Passport/新流出/";
        $baseDir = "/Volumes/My Passport/新流出/";
        //mac 本地
//         $baseDir = "/Users/wangdongyan/Downloads/film/流出/";
        //win硬盘
//         $baseDir = "/Volumes/新加卷/流出/";
        $baseDir = "/Volumes/Elements/新流出/";
        $fileList = my_dir($baseDir);
//         var_dump($fileList);
        $infoList = array();
        foreach( $fileList as $k=>$v){
            if ($v == '.DS_Store'){
                continue;
            }
            if (substr($v,0,1) == '.'){
                continue;
            }
            if (substr($v,0,9) == 'MX3DS-006'){//MX3DS-006_ましろ杏_3D.mpeg
                continue;
            }

//             var_dump($v);
            $fileName = $baseDir . $v;
            $shellCommand = "ffmpeg -i '$fileName'  2>&1 |grep Duration";
            echo $shellCommand . "\n";
            $rs = exec($shellCommand);
//             var_dump($rs);
            $commandArr = explode(",",trim($rs));
            $DurationArr = explode(":",trim($commandArr[0]));
            $hour = trim($DurationArr[1]);
            $minutes = trim($DurationArr[2]);
            $secondArr = explode(".", trim($DurationArr[3]));
            $second = $secondArr[0];
            $duration = $hour . ":" . $minutes . ":" .$second;
            echo "$k " . $v . " ~ " . $duration ;


            $shellCommand = "ffmpeg -i '$fileName'  2>&1 |grep Stream|head -1";
//             echo $shellCommand . "\n";
            $rs = exec($shellCommand);
            $stream = explode(",",$rs);
            if (count($stream) < 2 ){
            //avi 格式有点问题
                $dpi = 0;
                $fps = 0;
            }else{
                 $tmpStr = substr($stream[1],-1,1);
                 if ( $tmpStr  == 'v'){
                     $dpi = trim($stream [3]);
                     $fps = trim($stream[5]);
                 }else{
                     $dpi = trim($stream [2]);
                     $fps = trim($stream[4]);
                 }
            }

            echo " " . $dpi . " ". $fps;

            $shellCommand = "du -m '$fileName'|awk '{print $1}'";
//             echo $shellCommand;
            $fileSizeInfo = exec($shellCommand);
//             var_dump($fileSizeInfo);
            $fileSize = $fileSizeInfo / 1024;
            $fileSizeNumber = round($fileSize,3);
            echo " "  . $fileSizeNumber . "G \n";

            $row = array(
                'payload'=>"local",
                "dpi"=>$dpi,
                'fps'=>$fps,
                'size'=>$fileSizeNumber,
                'duration'=>$duration,
                'name'=>$v,
            );


            $infoList[] = $row;

        }



        $infoList[] = array("name" =>"MXGS-888",'佐野あおい','dpi'=>0,"size"=>0,'duration'=>"02:00:00",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"MXGS-791",'香純ゆい','dpi'=>0,"size"=>"6.02G",'duration'=>"02:20:25",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"STKO-004",'紺野ひかる','dpi'=>0,"size"=>"2.22G",'duration'=>"00:59:09",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"SDNT-008",'加瀬ななほ','dpi'=>0,"size"=>"5.69G",'duration'=>"02:20:37",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"STKO-005",'加藤ももか','dpi'=>0,"size"=>"2.53G",'duration'=>"01:01:07",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"SVDVD-794",'一條みお&悠月リアナ&東條蒼&月宮ねね','dpi'=>0,"size"=>"402.5+47.5",'duration'=>"00:10:50",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"STKO-013",'南梨央奈','dpi'=>0,"size"=>"2.81G",'duration'=>"01:07:56",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"STARS-573",'松岡ちな','dpi'=>0,"size"=>"4.22G",'duration'=>" ",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"TIGR-007",'君色花音','dpi'=>0,"size"=>"2.58G",'duration'=>"01:03:47",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"SDAM-046",'広瀬結香&小鳥遊ももえ&鈴木真夕','dpi'=>0,"size"=>"5.7G",'duration'=>"02:20:47",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"STARS-173",'西野翔','dpi'=>0,"size"=>"6.6G",'duration'=>"00:00:00",'fps'=>29,'payload'=>"del");
        $infoList[] = array("name" =>"MX3DS-006",'ましろ杏','dpi'=>0,"size"=>"15.6G",'duration'=>"00:00:00",'fps'=>29,'payload'=>"del");


        foreach( $infoList as $k=>$v){
            $nameInfo = explode("_",$v['name']);
            $noInfo = explode( "-", trim($nameInfo[0]));
//             var_dump($noInfo);
            $no_type = trim($noInfo[0]);
            $no = trim($noInfo[1]);
            $where = " no = '$no' and no_type = '$no_type'";
            $row = LeakedListModel::db()->getRow($where);
            if (!$row){
                exit("no search : $where");
            }
            $payload = "local";
            if (isset($v['payload'])){
                $payload = $v['payload'];
            }
            $data = array(
                'dpi' =>$v['dpi'],
                'size' =>$v['size'],
                'duration' =>$v['duration'],
                'fps' =>$v['fps'],
                'payload'=>$payload,
                'payload'=>"local",
            );
            $rs = LeakedListModel::db()->upById($row['id'],$data);
            $upRs = "fail";
            if ($upRs){
                $upRs = "true";
            }
            echo $k . "  where:". $where . "up one :".$upRs . "\n";
        }


//         $fileContent = file("./leaked_list.txt");
//         if (!$fileContent){
//             exit("fileContent is null");
//         }
//             var_dump($data);
//             $rs = LeakedListModel::db()->add($data);
//             var_dump($rs);
//         }

        $e_time = time() - $s_time;
        $this->out("total exec time : $e_time");

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
