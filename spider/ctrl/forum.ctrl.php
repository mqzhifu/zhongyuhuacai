<?php

class ForumCtrl extends BaseCtrl {
    function index($request){
        $host = $this->_config[$request['website']]['host'][1];
        $config = $this->_config[$request['website']]['forum'][$request['category']];

        echo $this->_config[$request['website']]['name']."<br/>";
        echo $config['name']."<br/>";

        $keywordName = "无";
        $keyword = "";
        $where = 1;
        if(arrKeyIssetAndExist($request,'keyword')){
            $keyword = $request['keyword'];
            $keywordName = $keyword;
            $where = " title like '%$keyword%' or author like '%$keyword%' ";
        }

        echo "搜索关键字：".$keywordName ."<br/>";

        $count = $config['db_model_class']::db()->getCount($where);
        echo "总记录数:$count";


        $html = "<table><form method='post' action='/forum/index/?website={$request['website']}&category={$request['category']}'>";

        $html .= "<tr><td><input name='keyword' type='text' /></td><td><input type='submit' value='搜索' ></td></tr>";

        $html .= "</form></table>";
        if($keyword){
            $data = $config['db_model_class']::db()->getAll($where);
            if($data){
                $html .= "<table>";
                $html .=  "<tr><td>id</td><td>分类</td><td>标题</td><td>作者</td><td>评论</td><td>阅读数</td><td>添加时间</td><td>原始时间</td><td>视频大小</td><td>视频类型</td><td>点赞</td></tr>";
                foreach ($data as $k=>$v){
                    $url = "https://".$host . "/".$v['link'];
                    $html .=  "<tr>";
                    $html .= "<td>{$v['id']}</td>";
                    $html .= "<td>{$v['category']}</td>";
                    $html .= "<td><a href='$url' target='_blank'>{$v['title']}</a></td>";
                    $html .= "<td>{$v['author']}</td>";
                    $html .= "<td>{$v['comment']}</td>";
                    $html .= "<td>{$v['view']}</td>";
                    $html .= "<td>{$v['a_time']}</td>";
                    $html .= "<td>{$v['ori_time']}</td>";
                    $html .= "<td>{$v['video_size']}</td>";
                    $html .= "<td>{$v['video_type']}</td>";
                    $html .= "<td>{$v['up']}</td>";
//                $html .= "<td>{$v['']}</td>";
//                $html .= "<td>{$v['']}</td>";
//                $html .= "<td></td>";
                    $html .=  "</tr>";
                }
                $html .= "</table>";
            }



        }

        echo $html;
        exit;
    }

    function getAll(){

    }
}
