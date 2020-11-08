<?php
class SpiderOfficeBulid{
    public $cnt = 0;
    public $rs_url = array();

    public $data = array();

    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        set_time_limit(0);

        $cnt = 1;
        for($i=1;$i<=105;$i++){
            if($cnt % 10 == 0){
                echo "sleep:".$cnt."\r\n";
                sleep(1);
            }
            $cnt++;

            $url = "http://bj.uban.com/searchlist/y$i/#list-result";
            $url = "http://sh.uban.com/searchlist/y$i/#list-result";
            $this->catch_url($url,$i);

        }


        require_once PLUGIN.'/phpexcel/PHPExcel.php';
        $obj = new PHPExcel();


        $cellName = array('A','B','C','D','E');
//	$fd = fopen("data.txt","a");

        $j = 1;
	foreach($this->data as $k=> $page){
		foreach($page as $row){
            echo $row['com']. " ". $row['addr']. " ". $row['area']. " ". $row['num']. " ". $row['view']."\n";
//            foreach($cellName as $x=>$col){
//                $j = $k + 1;
                $obj->setActiveSheetIndex(0)->setCellValue("A".$j, $row['com']);
                $obj->setActiveSheetIndex(0)->setCellValue("B".$j, $row['addr']);
                $obj->setActiveSheetIndex(0)->setCellValue("C".$j, $row['area']);
                $obj->setActiveSheetIndex(0)->setCellValue("D".$j, $row['num']);
                $obj->setActiveSheetIndex(0)->setCellValue("E".$j, $row['view']);
//            }

            $j++;
//			$row_data = $row['com']. "\t". $row['addr']. "\t". $row['area']. "\t". $row['num']. "\t". $row['view']."\r\n";
//			fwrite($fd,$row_data);
		}
	}





//    foreach($cellName as $k=>$v){
//
//    }

    $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel5');
//    $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');

    $objWriter->save("shanghai.xls");
    exit;


    }

    function catch_url($url,$page){
        $url_content = get_url_content($url);

        $search = "/office-list-item clearfix\">.*?<\/dl>/si";
        preg_match_all($search,$url_content,$li,PREG_SET_ORDER);


	foreach($li as $row){
		//var_dump($row[0]);exit;
		preg_match_all("/<b class=\"font20 text-black fl\">(.*?)<\/b>/si",$row[0],$company,PREG_SET_ORDER);
		preg_match_all("/item-address\"><\/i>(.*?)<\/dd/si",$row[0],$addr,PREG_SET_ORDER);
		preg_match_all("/text-black fb\">(.*?)<\/span>/si",$row[0],$area,PREG_SET_ORDER);
		preg_match_all("/<b class=\"hover\">(.*?)<\/b>/si",$row[0],$view,PREG_SET_ORDER);

		$one = array('com'=>$company[0][1],'addr'=>$addr[0][1],'area'=>'已租满','num'=>'','view'=>'');
		if($area){
			$one['area'] = $area[0][1];
			$one['num'] = $area[1][1];		
		}

		if($view){
			$one['view'] = $view[0][1];
		}
		//$one = array($company[0][1],$addr[0][1],$area[0][1],$area[1][1],$view[0][1]);

		$this->data[$page][] = $one;

		//var_dump($company);var_dump($addr);var_dump($area);var_dump($view);exit;

                /* 
		<i class="sem-icon item-address"></i>[朝阳-慈云寺/四惠] 高井文化传媒园8号</dd>
		<span class="text-black fb">74-10000</span>
		 <b class="hover">21</b>
		*/
	}


//var_dump($this->data[$page]);exit;


    }


//<div class="office-list-item clearfix">
//<a href="/detail-98.html" class="db pr" target="_blank">
//<div class="jfl pr">
//<img data-original="http://img1.static.uban.com/bc3275e2-9d84-11e5-9444-00163e00571b.JPG-wh480x320" src="http://img1.static.uban.com/bc3275e2-9d84-11e5-9444-00163e00571b.JPG-wh480x320" width="270" height="180" alt="半岛科技园" style="display: inline;">
//</div>
//<div class="price-box text-right">
//<span class="db text-gray6"><em class="font26 font-num fb text-pink-app">4</em> 元/<span class="font-num">m²</span>·天</span>
//<span class="db text-gray9 font12 mt10">均价</span>
//</div>
//<dl class="office-building-cont pr clearfix">
//<dt class="mb25 clearfix">
//<b class="font20 text-black fl">半岛科技园</b>
//</dt>
//<dd>
//<i class="sem-icon item-address"></i>[浦东-张江] 上海浦东新区达尔文路88号(近高科中路)</dd>
//
//<dd>
//<i class="sem-icon item-area"></i>可租面积  <span class="text-black fb">64-1688</span><span class="font-num"> m²</span>, 待租办公室&nbsp;<span class="font-num text-black fb">84</span>&nbsp;套</dd>
//<dd>
//<span><i class="sem-icon item-see"></i>近7天有 <b class="hover">15</b> 位用户咨询过</span>
//</dd>
//<dd class="last-fix-bottom">
//<div class="jfl building-tag">
//<span>距张江高科站982米</span>
//<span>创意园区</span>
//</div>
//</dd>
//</dl>
//</a>
//<p class="isfavorite cur-pointer" data-favorite="0" data-id="98" }="">关注</p>
//									<p class="contrast cur-pointer" data-office="98">加入对比</p>
//							</div>


    function get_a_href($html,$url){




        preg_match_all('/href="(.*?)"/is',$html,$a_href);
        $a_collection = array();
        if(!$a_href){
            return "";
        }
        foreach($a_href[1] as $k=>$v){
            if(!$v || $v == '/' || $v == '#' || $v == './' || strpos($v,'javascript' ) !== false || strpos($v,'getGrouponListUrl' ) !== false  ){

            }elseif(substr($v,-3) == 'css' || substr($v,-3) == 'ico' || substr($v,-3) == 'gif' || substr($v,-3) == 'apk' ){

            }
            elseif(substr($v,0,7) != 'http://'){
                if(substr($v,0,1) == "#")
                    return 0;


                $a_domain = explode("/",$url);
//                echo $v."\n";
                if(substr($v,0,1) != "/")
                    $tmp  = "http://".$a_domain[2] ."/".$v;
                else {
//                    echo 444 ."\n";
                    $tmp = "http://" . $a_domain[2] . $v;
                }
                $a_collection[] = $tmp;
            }
            else{
                $a_collection[] = $v;
            }
        }

        $final_coll = array();
        foreach($a_collection as $k=>$v){
            if (in_array($v, $this->rs_url))
                continue;
            $this->rs_url[] = $v;

            $final_coll[] = $v;
        }

        return $final_coll;
    }
}



function get_url_content($url){

    $ch=curl_init();

    $useragent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    //伪造header
    $header=array('Accept-Language: zh-cn','Connection: Keep-Alive','Cache-Control: no-cache');
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch,CURLOPT_USERAGENT,$useragent);
    curl_setopt($ch,CURLOPT_URL,$url);
    $timeout=10;
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//结果不输出到页面

    $file_contents=curl_exec($ch);
    $info=curl_getinfo($ch);
//    echo $info['http_code'] . "\n";
    if($info['http_code'] == 200){
        if($file_contents === false){
            echo 'Curl error: ' . curl_error($ch);
            exit;
        }
        curl_close($ch);
        return $file_contents;
    }elseif($info['http_code'] == 302 || $info['http_code'] == 301){
        echo ("http code error:".$info['http_code']) . "\n";
        return get_url_content($info['redirect_url']);//302 301 跳转的，需要再次抓取
    }else{
        echo ("http code error:".$info['http_code'])." ".$url." \n";
        return false;
    }
}
