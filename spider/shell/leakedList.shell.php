<?php
class leakedList{
    public $config = null;

    public function __construct($c){
        $this->commands = $c;
        $config = ConfigCenter::get(APP_NAME,"main");
        $this->config = $config['common'];
    }

    public function run($argc){
        $s_time = time();

        $fileContent = file("./leaked_list.txt");
        if (!$fileContent){
            exit("fileContent is null");
        }

//         $sql = "truncate TABLE leaked_list";
//         LeakedListModel::db()->

        foreach ($fileContent as  $k=>$v){
            $line = str_replace("\n","",$v);
            $line = trim($line);
            $lineArr = explode("|",$line);
             foreach ($lineArr as  $k2=>$v2){
                $lineArr[$k2] = trim($v2);
            }
            $noType = "";
            $no = "";

            if ($lineArr[1]){
                $noInfoArr = explode("-",$lineArr[1]);
                if (count($noInfoArr) != 2){
                    var_dump($v);
                    exit("count($noInfoArr) != 2");
                }

                 $noType = $noInfoArr[0];
                 $no = $noInfoArr[1];
            }



            $data = array(
                'no_type'=>$noType,
                'no'=>$no,
                'girls'=>$lineArr[2],
                'title'=>"",
                'memo'=>$lineArr[3],
                'pre_size'=>$lineArr[4],
//                 'payload'=>$lineArr[6],
//                 'local'=>$lineArr[7],
                "a_time"=>time(),
            );
            var_dump($data);
            $rs = LeakedListModel::db()->add($data);
            var_dump($rs);
        }

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
