<?php
class Sis001Shell{

    function curlGetHtmlContent ($url,$host,$isSSl = 0){
        return CurlLib::sis001($url,$host,$isSSl);
    }

    function parseOnePage($content,$forumConfig){
//        $content = file_get_contents("/data/www/zhongyuhuacai/spider/sis_001_japan.txt");

//        $linkId = $forumConfig['link_id'];
//        $ge = '/summary="forum_'.$linkId.'"(.*)<\/table>/isU';
        $ge = '/版块主题(.*)<\/table>/isU';

        preg_match_all($ge,$content,$match);
        $tableListStr = $match[1][0];

        preg_match_all('/<tbody(.*)<\/tbody>/isU',$tableListStr,$match);
        $dataList = array();
        foreach ($match[1] as $k=>$tbody){
//            var_dump($v);
            preg_match_all('/<td(.*)<\/td>/isU',$tbody,$matchTd);
            //TD一共是6个，前2个是ICON，用不上，最后一个是：最后评论人/时间，也用不上，实际上TD需要处理的就3个

            //作者/点赞/时间
            $authorUpTime = $this->parseAuthorUpTime($matchTd[1][2]);
            //回复数/查看数
            $commentViews = $this->parseCommentViews($matchTd[1][3]);
            //视频:大小/类型
            $videoSizeType = $this->parseVideoSizeType($matchTd[1][4]);

            preg_match_all('/<th(.*)<\/th>/isU',$tbody,$matchTh);
            $titleCategoryLink = $this-> parseTitleCategoryLink($matchTh[1][0]);

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
            $dataList[] = $data;
        }
        return $dataList;
    }

    function parseCommentViews($content){
//        preg_match_all('/<strong>(.*)<\/strong>/isU',$content,$match);
//        $comment = $match[1][0];
//        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
//        $view = $match[1][0];

        $comment = myPregMatchAll('/<strong>(.*)<\/strong>/isU',$content,1,0);
        $view = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);

        $data = array('comment'=>$comment,'view'=>$view );
        return $data;

    }

    function parseVideoSizeType($content){
        $l = stripos($content,">");
        if ($l === false){
            $data = array('size'=>0,'type'=>0 );
        }else{
            $str = trim( substr($content,$l+1));
            $arr = explode("/",$str);
            $data = array('size'=>trim($arr[0]),'type'=>trim($arr[1]) );
        }

        return $data;

    }

    function parseAuthorUpTime($content){
//        preg_match_all('/<cite(.*)<\/cite>/isU',$content,$match);
//
//        preg_match_all('/<a href="(.*)">(.*)<\/a>/isU',$match[1][0],$matchAuthor);
//        $author = $matchAuthor[2][0];
//
//        preg_match_all('/<img(.*)>(.*)/',$match[1][0],$matchUp);
//        $up = trim($matchUp[2][0]);
//
//        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
//        $date = $match[1][0];

        $myPregRs = myPregMatchAll('/<cite(.*)<\/cite>/isU',$content,1,0);
        if($myPregRs){
            $author = myPregMatchAll('/<a href="(.*)">(.*)<\/a>/isU',$myPregRs,2,0);
            $up = trim( myPregMatchAll('/<img(.*)>(.*)/',$myPregRs,2,0));
        }else{
            $author = "";
            $up = "";
        }

        $date = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);


        $data = array('author'=>$author,'up'=>$up,'date'=>$date);
        return $data;
    }

    function parseTitleCategoryLink($content){

//        preg_match_all('/<em>(.*)<\/em>/isU',$content,$match);
//        preg_match_all('/>(.*)<\/a>/isU',$match[1][0],$match);
//        $category = $match[1][0];
        $myPregRs = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);
        if($myPregRs){
            $myPregRs = myPregMatchAll('/>(.*)<\/a>/isU',$myPregRs,1,0);
        }
        $category = $myPregRs;

//        preg_match_all('/<span(.*)<\/span>/isU',$content,$match);
        $myPregRs = myPregMatchAll('/<span(.*)<\/span>/isU',$content,1,0);
        if($myPregRs){
            preg_match_all('/<a href="(.*)"(.*)>(.*)<\/a>/isU',$myPregRs,$match);
            $link = $match[1][0];
            $title = $match[3][0];
        }else{
            $link = "";
            $title = "";
        }

        $data = array('category'=>$category,'link'=>$link,'title'=>$title);
        return $data;
    }
}