<?php
class TaohuaShell{
    function curlGetHtmlContent ($url,$host,$isSSl = 0){
        return CurlLib::taohua($url,$host,$isSSl);
    }

    function parseTitleCategoryLink($content){
        $myPregRs = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);
        if($myPregRs){
            $myPregRs = myPregMatchAll('/>(.*)<\/a>/isU',$myPregRs,1,0);
        }
        $category = $myPregRs;

//        <a href="thread-2270290-1-1.html" onclick="atarget(this)" class="s xst">2021-01-18 The Road Less Travelled</a>
        $link = myPregMatchAll('/<a href="(.*)"(.*)>(.*)<\/a>/isU',$content,1,2);
        $title = myPregMatchAll('/<a href="(.*)"(.*)>(.*)<\/a>/isU',$content,3,2);

        $data = array('category'=>$category,'link'=>$link,'title'=>$title);
        return $data;
    }


    function parseCommentViews($content){

        $comment = myPregMatchAll('/<a(.*)>(.*)<\/a>/isU',$content,2,0);
        $view = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);

        $data = array('comment'=>$comment,'view'=>$view );
        return $data;

    }

    function parseAuthorDate($content){
        $myPregRs = myPregMatchAll('/<cite(.*)<\/cite>/isU',$content,1,0);
        if($myPregRs){
            $author = myPregMatchAll('/<a href="(.*)">(.*)<\/a>/isU',$myPregRs,2,0);
        }else{
            $author = "";
        }
        //日期 ，如果是7天左右 ，会显示，如：2个小时前，1天前，5天前等，所以  类似 前7天这种的，直接从title里取，后七天的就得另外再换个正则
        $date = myPregMatchAll('/title="(.*)"/isU',$content,1,0);
        if(!$date){
            $date = myPregMatchAll('/<em>(.*)<\/em>/isU',$content,1,0);
            if($date){
                $date = myPregMatchAll('/<span>(.*)<\/span>/isU',$content,1,0);
            }
        }
        $data = array('author'=>$author,'date'=>$date);
        return $data;
    }
    function parseOnePage($content,$forumConfig){
//        $content = file_get_contents("/data/www/zhongyuhuacai/spider/taohua_oumei.txt");
        $myPregRs = myPregMatchAll('/moderate(.*)<\/table>/isU',$content,1,0);

        preg_match_all('/<tbody(.*)<\/tbody>/isU',$myPregRs,$match);

        $list = null;
        foreach ($match[1] as $k=>$tbody){
            //赞助详情以及相关说明[2020年5月23日更新]
            if(strpos($tbody,'stickthread') !== false){
                continue;
            }
            //分隔符
            if(strpos($tbody,'separatorline') !== false){
                continue;
            }

            preg_match_all('/<td(.*)<\/td>/isU',$tbody,$matchTd);
            //TD一共是4个，第1个是ICON，第4个是最后评论信息，实际上TD需要处理的就2个

            $authorDate = $this->parseAuthorDate($matchTd[1][1]);
//            var_dump($authorDate);
            $commentViews = $this->parseCommentViews($matchTd[1][2]);
//            var_dump($commentViews);

            preg_match_all('/<th(.*)<\/th>/isU',$tbody,$matchTh);
            $titleCategoryLink = $this-> parseTitleCategoryLink($matchTh[1][0]);
//            var_dump($titleCategoryLink);exit;


            $data = array(
                'title'=>$titleCategoryLink['title'],
                'author'=>$authorDate['author'],
                'comment'=>$commentViews['comment'],
                'view'=>$commentViews['view'],
                'ori_time'=>$authorDate['date'],
                'category'=>$titleCategoryLink['category'],
//                'video_size'=>$videoSizeType['size'],
//                'video_type'=>$videoSizeType['type'],
//                'up'=>$authorUpTime['up'],
                'a_time'=>time(),
                "link"=>$titleCategoryLink['link'],
            );
            $list[] = $data;
        }
        return $list;
    }
}