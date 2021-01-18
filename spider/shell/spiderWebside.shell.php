<?php
class spiderWebside{
    const TANG_98 ="tang_98";
    const SIS001 ="sis_001";
    const JAPAN_ORI_UNCODE ="japan";
    const OUMEI ="oumei";

    public $config = array(
        self::TANG_98=>
            array(
                "host"=>array(
                    "98skdjseq2wshop.xyz",
                    "98wsaweassd.xyz",
                    "98asedwwq.xyz"
                ),
                "forum"=>array(
                    self::OUMEI=>"forum-229-3.html",
                    self::JAPAN_ORI_UNCODE=>"forum-36-1.html",
                )
            ),
        self::SIS001=>
            array(
                "host"=>array(
                    "38.103.161.16/bbs",
                    "666.diyihuisuo.com/bbs",
                    "cn.1huisuo.com/forum",
                    "666.1huisuo.net/bbs"
                ),
                "forum"=>array(
                    self::OUMEI=>array(
                        "link_id"=> "229",
                        "loop_start"=>1,
                        "loop_end"=>2341,
                        "db_model_class"=>"Sis001OumeiModel"
                    ),
                    self::JAPAN_ORI_UNCODE=>array(
                        "link_id"=> "143",
                        "loop_start"=>1,
                        "loop_end"=>2696,
                        "db_model_class"=>"Sis001JapanModel"
                    ),
                )
            )

    );


    public $mysql = array(
        "create database spider charset=utf8mb4;",
    );
    public $table = array(
        self::TANG_98=>array(
            self::JAPAN_ORI_UNCODE=>self::TANG_98 ."_japan"
        )
    );

    public function __construct($c){
        $this->commands = $c;
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

    function parseOne($content){

    }


    function parseOneSis001CommentViews($content){
        preg_match_all('/<strong>(.*)<\/strong>/isU',$content,$match);
        $comment = $match[1][0];
        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
        $view = $match[1][0];

        $data = array('comment'=>$comment,'view'=>$view );
        return $data;

    }

    function parseOneSis001VideoSizeType($content){
        $l = stripos($content,">");
        $str = trim( substr($content,$l+1));
        $arr = explode("/",$str);
        $data = array('size'=>trim($arr[0]),'type'=>trim($arr[1]) );
        return $data;

    }

    function parseOneSis001AuthorUpTime($content){
        preg_match_all('/<cite(.*)<\/cite>/isU',$content,$match);

//        var_dump($match[1][0]);
        preg_match_all('/<a href="(.*)">(.*)<\/a>/isU',$match[1][0],$matchAuthor);
        $author = $matchAuthor[2][0];

        preg_match_all('/<img(.*)>(.*)/',$match[1][0],$matchUp);
        $up = trim($matchUp[2][0]);

        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
        $date = $match[1][0];

        $data = array('author'=>$author,'up'=>$up,'date'=>$date);
        return $data;
    }

    function parseOneSis001TitleCategoryLink($content){
        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
        preg_match_all('/>(.*)<\/a>/isU',$match[1][0],$match);
        $category = $match[1][0];

        preg_match_all('/<span(.*)<\/span>/isU',$content,$match);
        preg_match_all('/<a href="(.*)"(.*)>(.*)<\/a>/isU',$match[1][0],$match);
//        var_dump($match);

        $link = $match[1][0];
        $title = $match[3][0];
        $data = array('category'=>$category,'link'=>$link,'title'=>$title);
        return $data;
    }

    function parseOneSis001($content,$forumConfig){
        //mainbox threadlist
//        $content = file_get_contents("/data/www/zhongyuhuacai/spider/sis_001_japan.txt");

//        $linkId = $forumConfig['link_id'];
//        $ge = '/summary="forum_'.$linkId.'"(.*)<\/table>/isU';
        $ge = '/版块主题(.*)<\/table>/isU';

        preg_match_all($ge,$content,$match);
//        var_dump($match);exit;
        $tableListStr = $match[1][0];

        preg_match_all('/<tbody(.*)<\/tbody>/isU',$tableListStr,$match);
        foreach ($match[1] as $k=>$tbody){
//            var_dump($v);
            preg_match_all('/<td(.*)<\/td>/isU',$tbody,$matchTd);
            //TD一共是6个，前2个是ICON，用不上，最后一个是：最后评论人/时间，也用不上，实际上TD需要处理的就3个

            //作者/点赞/时间
            $authorUpTime = $this->parseOneSis001AuthorUpTime($matchTd[1][2]);
            //回复数/查看数
            $commentViews = $this->parseOneSis001CommentViews($matchTd[1][3]);
            //视频:大小/类型
            $videoSizeType = $this->parseOneSis001VideoSizeType($matchTd[1][4]);

            preg_match_all('/<th(.*)<\/th>/isU',$tbody,$matchTh);
            $titleCategoryLink = $this-> parseOneSis001TitleCategoryLink($matchTh[1][0]);

            $data = array(
                'title'=>$titleCategoryLink['title'],
                'author'=>$authorUpTime['author'],
                'comment'=>$commentViews['comment'],
                'view'=>$commentViews['view'],
                'ori_time'=>$authorUpTime['date'],
                'category'=>$titleCategoryLink['category'],
                'video_size'=>$videoSizeType['size'],
                'video_type'=>$videoSizeType['type'],
                'up'=>$authorUpTime['up'],
                'a_time'=>time(),
                "link"=>$titleCategoryLink['link'],
            );
            $id = $this->dbOptByAdd($forumConfig['db_model_class'],$data);
//            $id = Sis001JapanModel::db()->add($data);
            $this->out("k: ".$k ." , id: ".$id);
        }
    }

    function tang98(){
//        $firstHtmlContent = CurlLib::tang98($hostForum,$host)['msg'];
//        $firstCurlCookie = $firstHtmlContent['header']['set-cookie'];
//        $this->out("firstHtmlContent response ,code : ".$firstHtmlContent['httpCode'] . " cookie : ".$firstCurlCookie);
//
//        $firstCurlCookie = "cnsllc=cns60.10.194.45";//这里是先方便测试，忽略上面3行代码
//        $fileName = $key ."_" . $hostForum ." .txt";
//        for($i=0;$i<1;$i++){
//            $htmlContent = CurlLib::tang98($hostForum,$host,$firstCurlCookie)['msg'];
//            $this->putContentFile($fileName,$htmlContent['body']);
//            $this->parseOne($htmlContent['body']);
//            var_dump($htmlContent['body']);exit;
//        }
//        var_dump(-100);exit;
    }
    public function run($argc){
        $s_time = time();

        $webside = $argc['webside'];
        $forum = $argc['forum'];
        if (!$webside){
            exit("webside is null");
        }
        if (!arrKeyIssetAndExist($this->config,$webside)){
            exit("webside not in arr");
        }

        if (!$forum){
            exit("forum is null");
        }
        if (!arrKeyIssetAndExist($this->config[$webside]['forum'],$forum)){
            exit("forum not in arr");
        }

//        $key = self::TANG_98;
//        $key = self::SIS001;
//        $forumKey = self::JAPAN_ORI_UNCODE;


        $config = $this->config[$webside];
        $forumConfig = $config["forum"][$forum];


        $host = "http://". $config['host'][0]. "/";

        $hostForum = $host . "forum-".$forumConfig['link_id'];
        out($hostForum);


        for($page = $forumConfig['loop_start'];$page <= $forumConfig['loop_end'];$page++){
            $url = $hostForum . "-$page.html";
            $this->out("page: $page , url: $url");
            $httpContent = CurlLib::sis001($url,$host,0);
//        $fileName = $key ."_" . $forumKey .".txt";
//        $this->putContentFile($fileName,$httpContent['body']);
//        $this->parseOneSis001("test");
            $this->parseOneSis001($httpContent['body'],$forumConfig);
        }

        $e_time = time() - $s_time;
        $this->out("total exec time : $e_time");
    }

    function dbOptByAdd($model,$data){
        if($model == 'Sis001OumeiModel'){
            $id = Sis001OumeiModel::db()->add($data);
        }elseif($model == 'Sis001JapanModel'){
            $id = Sis001JapanModel::db()->add($data);
        }
        return $id;
    }

}