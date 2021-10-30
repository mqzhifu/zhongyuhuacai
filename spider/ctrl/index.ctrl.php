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

    function leakedlist(){
        $this->GetTableCss();
//         $LeakedList = LeakedListModel::db()->getAll(" 1 order by no_type,no asc , no ");
        $LeakedList = LeakedListModel::db()->getAll(" 1 order by payload desc , no ");
        if (!$LeakedList || count($LeakedList) <= 0 ){
            exit("db is empty...");
        }

        $cnt = _g("cnt");


        $noTypeTotal = array();
        $girlsTotal = array();

        $html = "<table id='customers'>";
        $html .= "<tr><th>id</th><th>type</th><th>no</th><th>girls</th><th>title</th><th>memo</th><th>pre_size</th><th>size</th><th>dpi</th><th>duration</th><th>payload</th><th>fps</th></tr>";
//         $html .= "<tr><th>忧忧</th><th>番号号</th></tr>";
        $no = 0;
        foreach ($LeakedList as $k=>$v){
            $repeat = 0;
            if ($k > 0) {
                $currNo = $LeakedList [$k]['no_type']."-".$LeakedList [$k]['no'];
                $lastNo = $LeakedList [$k - 1]['no_type']."-".$LeakedList [$k - 1]['no'];
                if ($currNo  == $lastNo  ){
                    $repeat = 1;
                }else{
                    $no++;
                }
            }else{
                $no++;
            }
            if  (!$repeat){
               if (   isset( $noTypeTotal[$v['no_type']]  )   ){
                   $noTypeTotal[$v['no_type']]++;
               }else{
                  $noTypeTotal[$v['no_type']]  = 1;
               }

               if ($v['girls']){
                    $girls = explode('/',$v['girls']);
                    foreach ($girls as $k3=>$v3){
                        $girlsName = trim($v3);
                        if (   isset( $girlsTotal[$girlsName]  )   ){
                            $girlsTotal[$girlsName]++;
                        }else{
                            $girlsTotal[$girlsName] = 1;
                        }
                    }
               }
            }


            $html .= "<tr>";
            $html .= "<td>$no</td><td>{$v['no_type']}</td><td>{$v['no']}</td><td>{$v['girls']}</td><td>{$v['title']}</td><td>{$v['memo']}</td><td>{$v['pre_size']}</td><td>{$v['size']}</td><td>{$v['dpi']}</td><td>{$v['duration']}</td><td>{$v['payload']}</td><td>{$v['fps']}</td>";
//             $html .= "<td>{$v['girls']}</td><td>{$v['no_type']}-{$v['no']}</td>";
            $html .= "<tr>";

        }
        if ($cnt){
            $html = "";
            $html .= "</table>";

             arsort($noTypeTotal);
            $html .= "<table>";
            foreach ($noTypeTotal as  $k=>$v){
                $html .= "<td><td>$k</td><td>$v</td></tr>";
            }
            $html .= "</table>";

             arsort($girlsTotal);
            $html .= "<table>";
            foreach ($girlsTotal as  $k=>$v){
                $html .= "<td><td>$k</td><td>$v</td></tr>";
            }
            $html .= "</table>";
        }


        echo $html;
        exit;
    }

    function GetTableCss(){
        $cssStyle = "

        <style>
        #customers {
          font-family: Arial, Helvetica, sans-serif;
          border-collapse: collapse;
          width: 100%;
        }

        #customers td, #customers th {
          border: 1px solid #ddd;
          padding: 8px;
        }

        #customers tr:nth-child(even){background-color: #f2f2f2;}

        #customers tr:hover {background-color: #ddd;}

        #customers th {
          padding-top: 12px;
          padding-bottom: 12px;
          text-align: left;
          background-color: #4CAF50;
          color: white;
        }
        </style>

        ";

        echo $cssStyle;
    }

    function leakedlistsearch(){
        if (_g("opt")){
            $k = _g("k");
            if (!$k){
                exit(" get k empty!");
            }

            $where = " no_type like '%$k%' or   no like '%$k%' or  girls like '%$k%'";
            $list = LeakedListModel::db()->getAll($where);
            if (!$list || count($list) <= 0 ) {
                exit("search is empty!");
            }else{
                var_dump($list);
            }
        }else{
            $this->display("leakedlistsearch.html");
        }
    }
}