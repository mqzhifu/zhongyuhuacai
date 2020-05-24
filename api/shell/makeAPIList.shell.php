<?php
class makeAPIList{
    function __construct($c){
        $this->commands = $c;
    }

    public function run($attr){
        if(PHP_OS == 'WINNT'){
            exec('chcp 936');
        }


//        $sql = "truncate table api_config;truncate table api_para;";
//        ApiparaModel::db()->execute($sql);

        include APP_CONFIG.DS."main.php";

//        var_dump($GLOBALS['main']['loginAPIExcept']);exit;

        $content = file_get_contents( APP_DIR.DS."interface.php");
        $rs = array();
        preg_match_all("/\/\/(.*?)\}/si",$content,$rs,PREG_SET_ORDER);

        foreach($rs as $k=>$v){
            $block = explode("\n",$v[0]);
            $module_desc = substr($block[0],2);
            o("模块名描述：".$module_desc);

            $module = array();
            preg_match_all("/interface(.*?)\{/si",$block[1],$module,PREG_SET_ORDER);
            $module = $module[0][1];
            o("模块：".$module);


            $ctrl = trim( strtolower(      substr($module,0,strlen( $module)-3     )   ) );
            for($i=2;$i<count($block)-1;$i++){

                $function = array();
                preg_match_all("/function(.*?)\(/si",$block[$i],$function,PREG_SET_ORDER);
                $function = trim($function[0][1]);
                o("函数名：".$function);

                $func_ex = explode('//',$block[$i]);
                $function_desc = explode("|",$func_ex[1]);
                if(count($function_desc) > 1){
                    $para_desc = $function_desc[1];
                    $para_desc = explode("##",$para_desc);
                }


                o("函数描述：".$function_desc[0]);

                $loginAPIExcept = 1;
                foreach($GLOBALS['main']['loginAPIExcept'] as $k=>$v){
                    if($ctrl == $v[0] && $function == $v[1]){
                        $loginAPIExcept = 2;
                        break;
                    }
                }

                if($loginAPIExcept == 2){
                    o("不需要登陆");
                }else{
                    o("要登陆");
                }

                $data = array(
                    'title'=>$function_desc[0],'ac'=>$function,'ctrl'=>$ctrl,'a_time'=>time(),'module'=>$module_desc,'is_login'=>$loginAPIExcept
                );

                $api_config_id = ApiconfigModel::db()->add($data);
                $para = array();
                preg_match_all("/\((.*?)\)/si",$block[$i],$para,PREG_SET_ORDER);

                if($para[0][1]){
                    $para = trim($para[0][1]);
                    $paras = explode(",",$para);
                    foreach($paras as $k=>$v){
                        $tmp = explode("=",$v);
                        $name =  substr(trim($tmp[0]),1);
                        o(trim($tmp[0]));
                        if(count($tmp) > 1){
                            o("选填");
                            $must = 2;
                        }else{
                            o("必填");
                            $must = 1;
                        }

                        $data = array('a_time'=>time(),'name'=>$name,'is_must'=>$must,'api_config_id'=>$api_config_id,'title'=>$para_desc[$k]);
                        ApiparaModel::db()->add($data);
                    }
//                    o($paras);
//                    exit;
                }

//                o($para);


            }

        }
	}

}

function o($str){
//    $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
//    var_dump($encode);
//    var_dump(iconv("UTF-8","gbk//TRANSLIT",$str));
    if(PHP_OS == 'WINNT'){
        $rs = iconv("UTF-8","GBK",$str)."\r\n";
    }

    echo $str."\n";
}