<?php
class parserTBDetail{
    public $taobao_username = "mqzhifu";
    public $taobao_password = "mqzhifu";
    public $host = "https://detail.1688.com/";
    public function __construct($c){
        $this->commands = $c;
    }

//<script src="/jquery.js"></script>
//<script>
//
//$("document").ready(function(){
//    setTimeout("init()", 5000 )
//    });
//
//    function init(){
//        var h = $(document).height()-$(window).height();
//        $(document).scrollTop(h);
//
//        setTimeout("getImg()", 5000 );
//
//    }
//
//    function getImg(){
//        var img=$("body img");
//        for(var i=0;i<img.length;i++){
//            var n=img.get(i).src;
//            console.log(n);
//        }
//    }
//</script>


    function test(){
        //        echo iconv("UTF-8",'GBK',"王");exit;
        //        $encode = mb_detect_encoding($productTxt, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//        var_dump($encode);

        //        $imgs  = preg_match_all('/<img src="(.*)"/U',$productTxt,$match);
//        $url  = preg_match_all('/<link rel="canonical" href="(.*)"(.*)>/U',$productTxt,$match);
//        var_dump($match);
    }

    function getUrl($offerId){
        return $this->host . "offer/$offerId.html";
    }

    function processOneFile($fileName){
        $mysqlData = array('a_time'=>time());

        out("file_name:$fileName");
        $productTxt = file_get_contents(STORAGE_DIR."/tb_product/$fileName");
//        $encode = mb_detect_encoding($productTxt, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//        var_dump($encode);exit;
//        $productTxt = mb_convert_encoding($productTxt,"CP936","UTF-8");
        preg_match_all('/<title>(.*)<\/title>/s',$productTxt,$match);
        $title = str_replace("阿里巴巴","", $match[1]['0']);
        out("title:$title");
        $mysqlData['title'] = $title;


        $ge = '/http(.*?)60x60\.jpg/U';
        preg_match_all( $ge,$productTxt,$match);
        $boxImgArr = $match[0];
        $boxImg = "";
        foreach ($boxImgArr as $k=>$v) {
            $boxImg .= $v . ",";
        }
        $boxImg = substr($boxImg,0,strlen($boxImg)-1);
        $boxImg = str_replace(".60x60","",$boxImg);
        $mysqlData['box_img'] = $boxImg;



//        $this->js($productTxt);

        //获取 整个 json块 数据
        preg_match_all( '/var iDetailConfig(.*?)script>/s',$productTxt,$match);
        if(!$match ||  !arrKeyIssetAndExist($match,0)){
            return false;
        }

        $jsonBlock = substr($match[0][0],0,strlen($match[0][0]) - 12);
        //获取iDetailConfig 结构体
        preg_match_all( '/var iDetailConfig(.*?)}/s',$jsonBlock,$match);
        $DataConfigMatch = $match[0][0];
        $DataConfig = substr($DataConfigMatch ,20);
        $DataConfig = explode(",",$DataConfig);
        $DataConfigJson = [];
        foreach ($DataConfig as $k=>$v) {
            $row = explode(":",$v);

            if(!$k){
                $row[0] = str_replace("{\r\n","",$row[0]);
            }
            $value =trim($row[1]);
            if($value == "''" || $value == '""'){
                $value = "";
            }

            if($k == count($DataConfig) - 1){
                $value =  str_replace("\r\n}","",$value);
            }

            if($value){
                $value = substr($value,1);
                $value = substr($value,0,strlen($value)-1);
            }

            $DataConfigJson[trim($row[0])] = $value;
        }
        $linkUrl = $this->getUrl($DataConfigJson['offerid']);
        $mysqlData['offerid'] = $DataConfigJson['offerid'];
        $mysqlData['tb_link'] = $linkUrl;
        out($linkUrl);
        out("data config".json_encode($DataConfigJson));
        $mysqlData['data_config'] = json_encode($DataConfigJson);


        //获取 iDetailData ，核心的一个结构
        preg_match_all( '/var iDetailData(.*?)};/s',$jsonBlock,$match);
        $dataPriceMath = $match[0][0];
        //获取  sku  数据
        preg_match_all( '/sku(.*?){(.*?)};/s',$dataPriceMath,$match);

//        if($DataConfigJson['offerid'] == '611047702279'){
//            var_dump($match);exit;
//        }

        if(!$match || !isset($match[0]) || !isset($match[0][0])){
            $ge = '/data-range=(.*?)}/';
            $priceInfo = [];
            preg_match_all( $ge,$productTxt,$match);
            foreach ($match[1] as $k=>$v) {
                $row = substr($v,1);
                $row = htmlspecialchars_decode($row) . "}";
                $priceInfo[] = json_decode($row,true);
            }

            $mysqlData['data_detail'] = $dataPriceMath;
            $mysqlData['price'] = htmlspecialchars_decode(json_encode($priceInfo));
            $mysqlData['category_attr_para'] = "";
            $mysqlData['category_attr'] = "";
            out("detailDataArr".json_encode($dataPriceMath));
        }else{
            $detailDataOri = substr($match[0][0],0,strlen($match[0][0])-5);
            $detailData = explode("\n",$detailDataOri);

            $detailDataArr = null;
            foreach ($detailData as $k=>$v) {
                if(!$v || !trim($v)){
                    continue;
                }
                if(!$k){//第一行不用处理
                    continue;
                }

                $v = trim($v);
                if($v == "}"){
                    continue;
                }

                $tmp = explode(":",$v);
                $key = trim($tmp[0]);
                if($key == 'priceRange'){
                    $detailDataArr[$key] = $this->getJsonObjBlock("priceRange",$detailDataOri);
                }elseif($key == 'priceRangeOriginal'){
                    $detailDataArr[$key] = $this->getJsonObjBlock("priceRangeOriginal",$detailDataOri);
                }elseif($key == 'skuProps'){
                    $detailDataArr[$key] = $this->getJsonArrBlock("skuProps",$detailDataOri);
                }elseif($key == 'skuMap'){
                    $detailDataArr[$key] = $this->getJsonArrBlockSkuMap("skuMap",$detailDataOri);
                }
                else{
                    $value = trim($tmp[1]);
                    if($value == "''" || $value == '""'){
                        $value = "";
                    }

                    if($value){
                        $value = substr($value,1);
                        $value = substr($value,0,strlen($value)-1);
                    }

                    $detailDataArr[$key] = substr($value,0,strlen($value)-1);
                }
            }

            $categoryAttrName = "";
            $paraData = [];
            foreach ($detailDataArr['skuProps'] as $k=>$v) {
                $categoryAttrName .= trim($v['prop']) . ",";
                foreach ($v['value'] as $k2=>$v2) {
                    $para = array('name'=>$v2['name']);
                    if(arrKeyIssetAndExist($v2,'imageUrl')){
                        $para['img_url'] = $v2['imageUrl'];
                    }
                    $paraData[trim($v['prop'])][] = $para;
                }
            }

            if(arrKeyIssetAndExist($detailDataArr,'price')){
                if(!arrKeyIssetAndExist($detailDataArr,'skuMap')){
                    exit("detailDataArr price skuMap is null");
                }

                $priceInfo = [];
                foreach ($detailDataArr['skuMap'] as $k=>$v) {
                    if(isset($v['price'])){
                        $priceInfo[$k] = $v['price'];
                    }elseif(isset($v['discountPrice'])){
                        $priceInfo[$k] = $v['discountPrice'];
                    }else{
                        exit("err:skuMap price is null");
                    }

                }
            }elseif(arrKeyIssetAndExist($detailDataArr,'priceRange')){
                $priceInfo = $detailDataArr['priceRange'];
            }else{
                exit("detailDataArr price unknow error");
            }

            $mysqlData['data_detail'] = json_encode($detailDataArr);
            $mysqlData['price'] = htmlspecialchars_decode(json_encode($priceInfo));
            $mysqlData['category_attr_para'] = json_encode($paraData);
            $mysqlData['category_attr'] = trim(substr( $categoryAttrName,0,strlen($categoryAttrName)));
            out("detailDataArr".json_encode($detailDataArr));
        }
        //产品属性
        $ge = '/产品属性(.*?)<tbody>(.*?)<\/table>/s';
        preg_match_all( $ge,$productTxt,$match);

        $ge = '/<td(.*?)>(.*?)<\/td>/s';
        $table = $match[2][0];
        preg_match_all( $ge,$table,$match);
        $attr = $match[2];
        $attrArr = null;
        for ($i=0 ; $i < count($attr) -1 ; $i++) {
            $attrArr[$attr[$i]] = $attr[++$i];
        }

        $mysqlData['attr'] = json_encode($attrArr);

//        <meta name="description" content="阿里巴巴1号猫果蔬清洗剂1.08KG 家用果蔬清洗剂清洁液一件代发oem贴牌，洗洁精，这里云集了众多的供应商，采购商，制造商。这是1号猫果蔬清洗剂1.08KG 家用果蔬清洗剂清洁液一件代发oem贴牌的详细页面。品牌:1号猫，是否进口:否，品牌类型:国货品牌，产地:河南郑州，适用范围:果蔬专用，净含量:1080ml，货号:05，是否促销装:否，是否跨境货源:否，货源类别:现货，商品条形码:6970477648866。我们还为您精选了洗洁精公司黄页、行业资讯、价格行情、展会信息等，欲了解更多详细信息,请点击访问!"/>

        $ge = '/description(.*?)content\=(.*?)>/';
        preg_match_all( $ge,$productTxt,$match);
        $mysqlData['desc'] =str_replace("阿里巴巴","", $match[2]['0']);
        $mysqlData['desc'] = substr($mysqlData['desc'],1);

        ProductTbModel::db()->add($mysqlData);
    }

    public function run(){
        $fileList = get_dir(STORAGE_DIR .DS ."tb_product");
        foreach ($fileList as $k=>$files) {
            out("k:",$k);
            foreach ($files as $k=>$file) {
                if($file == "demo.txt"){
                    continue;
                }
                $this->processOneFile($file);
            }
        }


        exit;

    }

    function getJsonObjBlock($pre,$str){
//        var_dump($str);
        $ge = "/$pre:\[\[(.*?)\]\],/s";
//        var_dump($ge);
        preg_match_all( $ge,$str,$match);

        $rs = trim("[[".$match[1][0] . "]]");
//        var_dump($rs);
        $rs = json_decode($rs,true);
        return $rs;
    }
    function getJsonArrBlock($pre,$str){
        $ge = "/$pre:\[\{(.*?)\}\],/s";
        var_dump($pre,$ge);
        preg_match_all( $ge,$str,$match);
//        var_dump($match);

        $rs = trim("[{".$match[1][0] . "}]");
//        var_dump($rs);
        $rs = json_decode($rs,true);
        return $rs;
    }

    function getJsonArrBlockSkuMap($pre,$str){
        $ge = "/$pre:\{(.*?)\}},/s";
        var_dump($pre,$ge);
        preg_match_all( $ge,$str,$match);
//        var_dump($match);

        $rs = trim("{".$match[1][0] . "}}");
//        var_dump($rs);
        $rs = json_decode($rs,true);
        return $rs;
    }


    function js($productTxt){
        preg_match_all('/<script src="(.*?)"(.*)>(.*?)<\/script>/U',$productTxt,$match);
//        foreach ($match as $k=>$v) {
//            var_dump($v);
//        }
//        exit;
        $jsUrl = "";
        foreach ($match[1] as $k=>$v) {
            if(strpos($v[0],'??') === false){

            }else{
                $v = str_replace('app="1688-default',"",$v);
                out($v);
                $ee = explode("??",$v);
                $host = "http://". substr($ee[0],2);
                if(strpos($ee[0],'i.alicdn.com') !== false){
                    $jsUrl .= "<script src='{$host}'></script> ";
                }else{
                    var_dump($host);
                    $jsGroup = explode(",",$ee[1]);
                    foreach ($jsGroup as $k2=>$v2) {
                        $jsUrl .= "<script src='{$host}{$v2}'></script> ";
                    }
                }
            }


        }

        var_dump($jsUrl);exit;
        foreach ($jsUrl as $k=>$v) {

        }
    }

}