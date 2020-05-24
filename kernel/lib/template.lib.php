<?php
class TemplateLib{
    
    private static $instance;
    public $path;

    public $placeholder = array(
        //if
        array('preg'=>'{if (.+)}',      'replace'=>' if(\\1){',                 ),
        array('preg'=>'{else}',         'replace'=>'  }else{ ',                 ),
        array('preg'=>'{elseif (.*)}',  'replace'=>'  }elseif(\\1){  ',         ),
        array('preg'=>'{/if}',          'replace'=>' } ',),
        //循环
        array('preg'=>'{for (.*)}',     'replace'=>' for (\\1) {',      ),
        array('preg'=>'{/for}',         'replace'=>' } ',                 ),
        array('preg'=>'{foreach (.*)}', 'replace'=>' foreach \\1 { ',   ),
        array('preg'=>'{/foreach}',     'replace'=>' } ',                 ),
        array('preg'=>'{loop (.*) (.*) (.*)}','replace'=>'if(is_array(\\1)){ foreach(\\1 as \\2 => \\3){',),
        array('preg'=>'{/loop}',        'replace'=>' }} ',                ),
        array('preg'=>'{continue}','replace'=>'continue;',),
        array('preg'=>'{break}','replace'=>'break;',),
        //日常
        array('preg'=>'{_(.*)}',        'replace'=>' echo \\1;',                ),
        array('preg'=>'{echo (.*)}',    'replace'=>' echo \\1;',                ),
        array('preg'=>'{\$(.*)}',        'replace'=>' echo $\\1;',               ),
        array('preg'=>'{eval (.*)}',    'replace'=>' \\1; ',                    ),
        //常量 与 函数
        array('preg'=>'{CONST\|(.*)}/',  'replace'=>' echo \\1; ',               ),
        array('preg'=>'{FUNC\| (.*)}',    'replace'=>' \\1; ',                    ),
        //文件包含
        array('preg'=>'{include (.*)}','replace'=>' include $this->_st->compile("\\1");',),
        array('preg'=>'{include_php (.*)}','replace'=>' if(is_file("\\1.php")){ include_once("\\1.php");}',),
        array('preg'=>'{include_htm(.*) (.*)}','replace'=>' if(is_file("\'.$this->path.\'\\2.htm\\1")){ $str = file_get_contents("\'.$this->path.\'\\2.htm\\1"); echo $str;} ?>\'',),

//        array('preg'=>'','replace'=>'',),
    );

    //构造函数
    function __construct(){
       
    }

    function replaceByDiyData($html,$data){
        foreach ($data as $k=>$v) {
            $h = '{$'.$k.'}';
            $html = str_replace( $h , $v, $html);
        }
        return $html;
    }

    function replace($html){
        foreach ($this->placeholder as $k=>$v) {
            $replace = "<?php" . $v['replace'] . " ?>";
            $preg = "|".$v['preg'] . "|isU";
            $html = preg_replace( $preg , $replace, $html);
        }

        return $html;
    }

    function setPath($path){
    	if(!is_dir($path))
    		exit('template dir not exist;');
    	
    	$this->path = $path;
    }
    
    function setCompilePath($path){
    	if(!is_dir($path))
    		exit('template compile dir not exist;');
    	
    	
    	$this->compilePath = $path;
    }


	function compile($file,$diyData = null) {
		if(!is_dir($this->path))
			exit('template not exist;');
		
		if(!is_dir($this->compilePath))
			exit('template compile dir not exist;');
		
        $templateFile = $this->path . $file;
		if(!file_exists($templateFile)) 
			exit("模板文件" . $templateFile . "不存在");
            
     	$str = file_get_contents($templateFile);
     	if($diyData){
            $str = $this->replaceByDiyData($str,$diyData);
        }else{
            $str = $this->replace($str);
        }

/*       	$str = preg_replace('|{if (.+)}|isU', '<?php if(\\1){ ?>', $str);*/
/*       	$str = preg_replace('|{/if}|isU', '<?php } ?>', $str);*/
/*      	$str = preg_replace('|{_(.*)}|isU', '<?php echo \\1; ?>', $str);*/
/*       	$str = preg_replace('|{\$lang_(.*)}|isU', '<?php echo $language["\\1"]; ?>', $str);*/
/*       	$str = preg_replace('|{\$(.*)}|isU', '<?php echo $\\1; ?>', $str);*/
/*        $str = preg_replace('/{CONST\|(.*)}/isU', '<?php echo \\1; ?>', $str);*/
/*       	$str = preg_replace('|{else}|isU', '<?php }else{ ?>', $str);*/
/*      	$str = preg_replace('|{elseif (.*)}|isU', '<?php }elseif(\\1){ ?>', $str);*/
/*       	$str = preg_replace('|{eval (.*)}|isU', '<?php \\1; ?>', $str);*/
/*      	$str = preg_replace('|{echo (.*)}|isU', '<?php echo \\1; ?>', $str);*/
//
/*       	$str = preg_replace('|<!--{loop (.*) (.*) (.*)}-->|isU', '<?php if(is_array(\\1)){ foreach(\\1 as \\2 => \\3){  ?>', $str);*/
/*       	$str = preg_replace('|{for (.*)}|isU', '<?php for (\\1) {  ?>', $str);*/
/*      	$str = preg_replace('|{/for}|isU', '<?php } ?>', $str);*/
/*      	$str = preg_replace('|{foreach (.*)}|isU', '<?php foreach \\1 {  ?>', $str);*/
/*      	$str = preg_replace('|{/foreach}|isU', '<?php } ?>', $str);*/
/*      	$str = preg_replace('|<!--{/loop}-->|isU', '<?php }} ?>', $str);*/
//
/*        $str = preg_replace('|{continue}|isU', '<?php continue; ?>', $str);*/
//
/*        $str = preg_replace('/{DT\|(.*)}/isU', '<?php if(\\1) echo date("Y-m-d H:i:s", \\1); ?>', $str);*/
//
/*        $str = preg_replace('/{INC1\|(.*)}/isU', '<?php \\1; ?>', $str);*/
/*        $str = preg_replace('/{=\|(.*)}/isU', '<?php \\1; ?>', $str);*/
//
//       	// 载入其他文件
/*       	$str = preg_replace('|{include (.*)}|isU', '<?php include $this->_st->compile("\\1"); ?>', $str);*/
/*       	$str = preg_replace('|{include_php (.*)}|isU', '<?php if(is_file("\\1.php")){ include_once("\\1.php");} ?>', $str);*/
/*       	$str = preg_replace('|{include_htm(.*) (.*)}|isU', '<?php if(is_file("'.$this->path.'\\2.htm\\1")){ $str = file_get_contents("'.$this->path.'\\2.htm\\1"); echo $str;} ?>', $str);*/

		//判断文件中是否包含目录名，如果包含目录，是需要创建文件夹的，不然报错
		if( $l =  strripos($file,DS)){
			$create_tmp_path = substr($file,0,$l);
			$create_tmp_path = $this->compilePath .DS .$create_tmp_path;
			if(!is_dir($create_tmp_path)){
				mkdir($create_tmp_path,0777,true);
			}
		}

		$CompliefileTmp = $this->compilePath .DS. $file.".php";
		$fd = fopen($CompliefileTmp,"w+");
		fwrite($fd,$str);
		return $CompliefileTmp;
	}
	
	function checkErrorFile(){
		$templateFile = $this->path . 'error.html';
		if(!file_exists($templateFile))
			exit("模板文件" . $templateFile . "不存在");
	}
}