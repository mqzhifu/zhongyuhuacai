<?php
class IndexCtrl extends BaseCtrl  {
    function index(){
        echo "https://www.c000.me/forum.php?mod=forumdisplay&fid=167<br/>";
        $html = "<table>";
        foreach ($this->_config as $k=>$v){
            $html .= "<tr><td>$k</td></tr>";
            $html .= "<tr>";



            $html .= "<table>";
            $html .= "<tr><td>域名</td>";
            foreach ($v['host'] as $k2=>$v2){
                $html .= "<td>";
                $html .= "&nbsp;" . $v2 . "&nbsp;";
                $html .= "</td>";
            }
            $html .= "</tr>";
            $html .= "</table>";


            $html .= "<table>";
            $html .= "<tr><td>分类</td>";
            foreach ($v['forum'] as $k2=>$v2){
                $html .= "<td>";
                $html .= "&nbsp;<a href='/forum/index/?website={$k}&category=$k2'>" . $k2 . "</a>&nbsp;";
                $html .= "</td>";
            }
            $html .= "</tr>";
            $html .= "</table>";



            $html .= "</tr>";
        }
        $html .= "</table>";
        echo $html;
        exit;
    }
}