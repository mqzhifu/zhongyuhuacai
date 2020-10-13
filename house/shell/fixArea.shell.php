<?php
class fixArea{
    private $userInfoTextPath = "";
    function __construct($c){


    }

    public function run($attr){
        set_time_limit(0);

        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }

//        $this->process("province");
//        $this->process("city");
        $this->process("county");

    }


    function process($type){
        $where = "";
        $model = null;
        if($type == 'province'){
            $where = " pid = 0 ";
            $model = AreaProvinceModel::db();
        }elseif($type == 'city'){
            $where = " level = 2 or level = 3 ";
            $model = AreaCityModel::db();
        }elseif($type == 'county'){
            $where = " level = 2 OR level = 3 ";
            $model = AreaCountyModel::db();
        }else{
            exit("type err");
        }


//        $where .= " and ( pinyin = '' OR pinyin IS NULL ) OR ( first_letter = '' OR first_letter IS NULL ) ";
        $data = FixAreaModel::db()->getAll($where);

        foreach ($data as $k=>$v){
            o("$k keyword:{$v['shortname']}");
//            if($v['shortname'] == '仙桃'){
//                var_dump("xiantao");exit;
//            }
            $row = $model->getRow(" short_name  like '%{$v['shortname']}%' or  name  like '%{$v['shortname']}%' ");
            if(!$row){
                o("no match");
                var_dump($row);
                continue;
            }
            $upData = array("first_letter"=>$v['first'],'pinyin'=>$v['pinyin']);
            if(arrKeyIssetAndExist($v,'zip_code')){
                $upData['zip_code'] = $v['zip_code'];
            }
            $rs = $model->upById($row['id'],$upData);
            o("up rs:".$rs);
        }
    }

}

function o($str){
//    $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//    var_dump($encode);
//    var_dump($str);
//    var_dump(iconv("UTF-8","gbk//TRANSLIT",$str));
    if(PHP_OS == 'WINNT'){
        $str = iconv("UTF-8","GBK//IGNORE",$str)."\r\n";
    }

    echo $str."\n";
}