<?php
//从api.php 配置文件中，读取所有接口描述信息，给前端显示
class ApiLib{
    function getFormatInfo($apiList,$domain,$module,$loginExceptList){
        $moduleArr = $apiList[$module];
        if(!$moduleArr){
            exit("参数 值 错误");
        }
        unset($moduleArr['title']);

        $map = array();
        $requestUrl = array();
        $i = 0;

        foreach($moduleArr as $k=>$v){
            if( ! $this->loginAPIExcept($module,$k,$type)){
                $moduleArr[$k]['request']['token'] = array('type'=>'string','must'=>1,'default'=>'sre6Yn94stiGuZzbfbWt2LN2ua5_yXRx','title'=>'token');
            }
            $map[$i] = $k;
            $requestUrl[$i] = $domain .$module."/".$k."/";

            if($moduleArr[$k]['request']){
                foreach($moduleArr[$k]['request'] as $k2=>$v2){
                    $requestUrl[$i] .= $k2 ."={$v2['default']}&";
                }
            }

            $i++;
        }

        $rs = array(
            'map'=>$map,
            'requestUrl'=>$requestUrl,
            'moduleArr'=>$moduleArr,
        );
        return $rs;
    }

    function loginAPIExcept($ctrl = "",$ac = "",$func = ""){
        if(!$ctrl && !$ac ){
            $ctrl = $this->ctrl;
            $ac = $this->ac;
        }

        $arr = $GLOBALS['main']['loginAPIExcept'];

        foreach($arr as $k=>$v){
            if($v[0] == $ctrl && $v[1] == $ac){
                return 1;
            }
        }

        return 0;
    }
}
