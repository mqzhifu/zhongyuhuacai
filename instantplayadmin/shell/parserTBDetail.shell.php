<?php
class parserTBDetail{
    public $taobao_username = "mqzhifu";
    public $taobao_password = "mqzhifu";
    public $host = "https://detail.1688.com/";
    public function __construct($c){
        $this->commands = $c;
    }
    /*
     * 1、运营到1688，找取想要解析的产品
     * 2、运营将该产品右击-》查看源码-》ctrl+a 选中全部，保存成txt格式-文件
     * 3、运营将文件保存、整理、打包后，打包成压缩包发与，管理员
     * 4、脚本：
     *  (1)先将压缩包，解压，把所有文件统一放置到tb_product目录下
     *  (2)遍历出该文件夹下，所有文件名
     *  (3)循环，处理每一个文件
     *  (4)将每个文件里的内容，正则匹配，最后，插入到tb_product表中
     *  (5)然后，另外一个脚本，从刚刚的tb_product 表中再生成 记录，最后 插入到系统表中
     */


    public function run(){
        $s_time = time();
        //存放txt的html源码文件目录
        $open_dir = STORAGE_DIR .DS ."tb_product";
        if(!is_dir($open_dir)){
            exit("not dir $open_dir");
        }
        //读取文件夹下的，所有文件
        $fileList = get_dir(STORAGE_DIR .DS ."tb_product");
        if(!$fileList){
            exit("open dir is empty!");
        }

        $skuPropsTooManyErr = 0;//有多少个，参数规格是大于10的，这种处理起来，特殊影响MYSQL
        $singleBuyNoAttr = 0;//单个购买，没有任何参数规格的
        $skuPropsIsNull = 0;//未匹配到sku
        $carriagePriceNotMatch = 0;//未匹配到运费
        $success = 0;
        foreach ($fileList as $k=>$files) {
            out("k:",$k);
            $n = 0;//计数，先处理，一个文件夹下的10个文件用于测试
            foreach ($files as $k=>$file) {
                if($file == "demo.txt"){//忽略特殊文件
                    continue;
                }
//                if($n > 10){
//                    break;
//                }
//                $n++;
                $backRs = $this->processOneFile($file);
                if($backRs == -1){
                    $skuPropsTooManyErr++;
                }elseif($backRs == -2){
                    $singleBuyNoAttr++;
                }elseif($backRs == -3){
                    $skuPropsIsNull++;
                }elseif($backRs == -4){
                    $carriagePriceNotMatch++;
                }else{
                    $success++;
                }
            }
        }

        out("skuPropsTooManyErrNum:".$skuPropsTooManyErr);
        out("singleBuyNoAttr:".$singleBuyNoAttr);
        out("skuPropsIsNull:".$skuPropsIsNull);
        out("carriagePriceNotMatch:".$carriagePriceNotMatch);

        out("totalFileNum:".count($files) . "success num:".$success);

        $eTime = time() - $s_time;
        out("final process time:".$eTime);
    }


    function getUrl($offerId){
        return $this->host . "offer/$offerId.html";
    }

    function processOneFile($fileName){
        //先初始化一条mysql 插入的记录结构体
        $mysqlData = array('a_time'=>time());
        out("file_name:$fileName");
        //读取文件内容
        $productTxt = file_get_contents(STORAGE_DIR."/tb_product/$fileName");
//        $encode = mb_detect_encoding($productTxt, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//        $productTxt = mb_convert_encoding($productTxt,"CP936","UTF-8");

        //匹配 - 标题
        preg_match_all('/<title>(.*)<\/title>/s',$productTxt,$match);
        $title = str_replace("阿里巴巴","", $match[1]['0']);
        out("title:$title");
        $mysqlData['title'] = $title;

        //匹配 - 产品描述信息
        $ge = '/description(.*?)content\=(.*?)>/';
        preg_match_all( $ge,$productTxt,$match);
        $mysqlData['desc'] =str_replace("阿里巴巴","", $match[2]['0']);
        $mysqlData['desc'] = substr($mysqlData['desc'],1);

        //匹配 - 运费
        preg_match_all('/cost-entries-type\"(.*?)<em class=\"value\">(.*?)<\/em>/s',$productTxt,$match);
        $carriagePrice = 0;
        if(isset($match[2][0]) && $match[2][0]){
            if (strpos($match[2][0], 'costItem') === false) {
                $carriagePrice = $match[2][0];
            }
        }else{
            return -4;
        }

        out("carriagePrice:$carriagePrice");
        $mysqlData['haulage'] = $carriagePrice;

        //匹配 - 图片
        $ge = '/http(.*?)60x60\.jpg/U';
        preg_match_all( $ge,$productTxt,$match);
        $boxImgArr = $match[0];
        $boxImg = "";
        foreach ($boxImgArr as $k=>$v) {
            $img = str_replace('https://cbu01.alicdn.com/cms/upload/other/lazyload.png" data-lazy-src="',"",$v);
            $img = trim($img);
            $boxImg .= $img . ",";
        }
        $boxImg = substr($boxImg,0,strlen($boxImg)-1);
        $boxImg = str_replace(".60x60","",$boxImg);
        $mysqlData['box_img'] = $boxImg;

        //获取 整个 js <script>****</script> json块 数据
        preg_match_all( '/var iDetailConfig(.*?)script>/s',$productTxt,$match);
        if(!$match ||  !arrKeyIssetAndExist($match,0)){
            return false;
        }
        //$jsonBlock 核心json数据，包含两块内容，1 商品基础属性(iDetailConfig)  2 商品价格SKU属性 (iDetailData)
        $jsonBlock = substr($match[0][0],0,strlen($match[0][0]) - 12);
        //获取 iDetailConfig （json）结构体 - 这里包含了价格信息
        preg_match_all( '/var iDetailConfig(.*?)}/s',$jsonBlock,$match);
        //先处理第一部分，商品基础属性 iDetailConfig
        $DataConfigMatch = $match[0][0];
        //前20个字符是类似这种东西："var iDetailConfig = {"
        $DataConfig = substr($DataConfigMatch ,20);
        //余下的，才是 真正想要的json 元素，以逗号分隔获取
        $DataConfig = explode(",",$DataConfig);

        $DataConfigJson = [];
        //将string 类型的js json格式，转换为php格式的数组
        foreach ($DataConfig as $k=>$v) {
            //获取一个元素的k v
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
        //该产品的ID，URL中用到此值
        $linkUrl = $this->getUrl($DataConfigJson['offerid']);
        $mysqlData['offerid'] = $DataConfigJson['offerid'];
        $mysqlData['tb_link'] = $linkUrl;
        out($linkUrl);
        out("data config".json_encode($DataConfigJson));
        //将第一步处理后的json 格式存于DB中，用于查错
        $mysqlData['data_config'] = json_encode($DataConfigJson);
        //===========json 中的第一部分，基础属性已处理完成 ===========

        //接下来，处理json 第二块结构，iDetailData 属性
        preg_match_all( '/var iDetailData(.*?)};/s',$jsonBlock,$match);
        $dataPriceMath = $match[0][0];
        //获取  sku  数据
        preg_match_all( '/sku(.*?){(.*?)};/s',$dataPriceMath,$match);
        if(!$match || !isset($match[0]) || !isset($match[0][0])){
            return -2;
            //这里是特殊情况，有些产品没有任何 参数规格，就是单纯 按'个数'卖
            $ge = '/data-range=(.*?)}/';
            $priceInfo = [];
            preg_match_all( $ge,$productTxt,$match);
            foreach ($match[1] as $k=>$v) {
                $row = substr($v,1);
                $row = htmlspecialchars_decode($row) . "}";
                $priceInfo[] = json_decode($row,true);
            }
            echo "55555555555555555\n";
            $mysqlData['data_detail'] = $dataPriceMath;
            $mysqlData['price'] = htmlspecialchars_decode(json_encode($priceInfo));
            $mysqlData['category_attr_para'] = "";
            $mysqlData['category_attr'] = "";
            out("detailDataArr".json_encode($dataPriceMath));
        }else{
            $detailDataOri = substr($match[0][0],0,strlen($match[0][0])-5);
            //以换行符 切割，最后变成  k:v 形势
            $detailData = explode("\n",$detailDataOri);

            $detailDataArr = null;
            //先把所有的string 类型的js json ，转换成php 的arr
            foreach ($detailData as $k=>$v) {
                if(!$v || !trim($v)){
                    continue;
                }
                if(!$k){//第一行不用处理
                    continue;
                }

                $v = trim($v);
                if($v == "}"|| $v == "},"){
                    continue;
                }

                $tmp = explode(":",$v);
                $key = trim($tmp[0]);
                if($key == 'priceRange'){
                    $detailDataArr[$key] = $this->getJsonObjBlock("priceRange",$detailDataOri);
                }elseif($key == 'priceRangeOriginal'){
                    //目前不知道这个是干什么的，反正是没用到....
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

            if(!arrKeyIssetAndExist($detailDataArr,'skuProps')){
                return -3;
            }

            $categoryAttrName = "";
            $paraData = [];
            //skuProps : 颜色 {黑-icon，白-icon，花-icon，绿-icon } , 尺码 {S-icon，L-icon，M-icon，XL-icon }
            //格式化参数规格成 php 数组
            foreach ($detailDataArr['skuProps'] as $k=>$v) {
                $skuPropsNum = count($v['value']);
                out("skuPropsNum:$skuPropsNum");
                if($skuPropsNum > 10){
                    return -1;
                }
                $categoryAttrName .= trim($v['prop']) . ",";
                foreach ($v['value'] as $k2=>$v2) {
                    $para = array('name'=>$v2['name']);
                    if(arrKeyIssetAndExist($v2,'imageUrl')){
                        $para['img_url'] = $v2['imageUrl'];
                    }
                    $paraData[trim($v['prop'])][] = $para;
                }
            }
            //价格分为2种
            //1 固定价格
            //2 购买数量越多，价格越便宜
            if(arrKeyIssetAndExist($detailDataArr,'price')){
                //这种比较复杂，虽然价格是固定的，但是得跟参数规格属性挂钩，不同规格价格不一样
                //所以就得从参数规格里取价格了
                if(!arrKeyIssetAndExist($detailDataArr,'skuMap')){
                    exit("detailDataArr price skuMap is null");
                }
                //skuMap:参数规格对应的价格
                $priceInfo = [];
                foreach ($detailDataArr['skuMap'] as $k=>$v) {
                    if(isset($v['price'])){//正常价格
                        $priceInfo[$k] = $v['price'];
                    }elseif(isset($v['discountPrice'])){//折扣价
                        $priceInfo[$k] = $v['discountPrice'];
                    }else{
                        exit("err:skuMap price is null");
                    }
                }
            }elseif(arrKeyIssetAndExist($detailDataArr,'priceRange')){
                //这种情况就比较简单， 购买多少数量 => 多少钱 ,跟参数规格没任何关系
                $priceInfo = $detailDataArr['priceRange'];
            }else{
                exit("detailDataArr price unknow error");
            }
            //整个大的json 块 html,用于查错
            $mysqlData['data_detail'] = json_encode($detailDataArr);
            //价格信息
            $mysqlData['price'] = htmlspecialchars_decode(json_encode($priceInfo));
            //参数名数组，如：参数名=>参数名+icon
            $mysqlData['category_attr_para'] = json_encode($paraData);
            //参数名 字符串   颜色,尺码  ，好像没什么用
            $mysqlData['category_attr'] = trim(substr( $categoryAttrName,0,strlen($categoryAttrName)));
            out("detailDataArr".json_encode($detailDataArr));
        }
        //产品属性 - 目前没用到，是详情页中，上方的属性值，仅用于参数
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

        $newId = ProductTbModel::db()->add($mysqlData);
        out("insert productTB ok , id:$newId");
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