<?php
class TaohuaShell{
    function curlGetHtmlContent ($url,$host,$isSSl = 0,$cookid = "",$isFd = 0){
        return CurlLib::taohua($url,$host,$isSSl,$cookid,$isFd);
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
                'video_size'=>'',
                'video_type'=>'',
                'up'=>0,
                'a_time'=>time(),
                "link"=>$titleCategoryLink['link'],
            );
            $list[] = $data;
        }
        return $list;
    }

    function getDescValue($value){
        //全角 冒号
        $descWord = explode("：",$value);
        if(count($descWord) == 2){
            return $descWord[1];
        }
        $descWord = explode(":",$value);
        if(count($descWord) == 2){
            return $descWord[1];
        }elseif(count($descWord) > 2){
            $val = "";
            foreach ($descWord as $k=>$v){
                if(!$k)
                    continue;
                $val .= ":".$v;
            }
            return $val;
        }
        out(" getDescValue err: ".$value);
        return "";
    }

    function parseDetail($content){

        preg_match_all('/postmessage(.*)<\/table>/isU',$content,$match);
        if(!isset($match[1][0]) || !$match[1][0]){
            out(parseDetailErr("118"));
            exit;
        }

        $isOldPage = 0;
        if(strpos($match[1][0],'ignore_js_op') !== false){
            preg_match_all('/【(.*)<ignore_js_op/isU',$match[1][0],$descTmp);
        }else{
            $isOldPage = 1;
            $descTmp[1][0] = $match[1][0];
        }

//        var_dump($descTmp[1][0]);exit;
//        if(!isset($descTmp[1][0]) || !$descTmp[1][0]){
//            out(parseDetailErr("124"));
//            return rt(500);
//        }
//        var_dump($descTmp[1][0]);
        $descArr = explode("<br />",$descTmp[1][0]);
//        var_dump($descArr);
        $videoType = "";
        $video_duration = "";
        $videoSize = "";
        $actor = "";
        foreach ($descArr as $k=>$v){
            $v = trim($v);
            if(strpos($v,'格式') !== false){
                $videoType =  $this->getDescValue($v);
            }elseif(strpos($v,'大小') !== false){
                $videoSize = $this->getDescValue($v);
            }elseif(strpos($v,'時長') !== false){
                $video_duration = $this->getDescValue($v);
            }elseif(strpos($v,'演') !== false){
                $actor = $this->getDescValue($v);
            }
        }

        $data = array('video_duration'=>$video_duration,'video_type'=>$videoType,'video_size'=>$videoSize,'actor'=>$actor,);
        if(!$isOldPage){
            preg_match_all('/file="(.*)"/isU',$match[1][0],$imgs);
        }else{
            preg_match_all('/file="(.*)"/isU',$content,$imgs);
        }


        $imgsParse = "";
        foreach ($imgs[1] as $k=>$v){
            $imgsParse .= $v . ",";
        }
        $imgsParse = substr($imgsParse,0,strlen($imgsParse)-1);
        $data['imgs'] = $imgsParse;
        return rt(200,$data);
    }

    function getLinkUrl($host,$link){
        $link = explode("-",$link);
        $tid = $link[1];
        $url = $host . "forum.php?mod=viewthread&tid=" .$tid;
        return $url;
    }

    function getTid($link){
        $link = explode("-",$link);
        $tid = $link[1];
        return $tid;
    }
}

function rt($code,$data = []){
    return $data = array(
        'code'=>$code,'data'=>$data,
    );
}

function parseDetailErr($msg){
    return "parseDetail err: in line ".$msg;
}