<?php
//图片水印
class ImageWaterMark{
	public $gdinfo;                           //当前GD库的信息
	public $picpath;                          //需要水印的图片的路径
	public $waterpath;                        //水印的图片的路径
	public $picinfo;                          //需要水印图片的信息
	public $waterinfo;                        //需要水印图片的信息
	public $min_width=100;                    //需要加水印图片的最小宽度
	public $min_height=30;                    //最小高度
	public $mark_border=0;                   //水印边距
	public $mark_pct=60;                      //水印透明度
	public $errormsg='';                      //出错信息
	public $mark_style=3;                     //水印位置 0：随即 1：左上 2：右上 3：中间 4：左下 5：右下
	public $is_output=false;                  //是否输出图象
	public $image_output_method='imagejpeg';  //输出图象的类型
	public $info='';                          //返回信息

	function __construct(){
		$this->check_gd();                             //检查是否支持GD库
	}

	function checkpic(){
		if(!file_exists($this->picpath)){
			$this->error(1);//判断需要水印的图片的路径是否正确
		}
		if(!file_exists($this->waterpath)){
			$this->error(2);//判断水印的图片的路径是否正确
		}
		if(!$this->info['error']){
			$this->picinfo = $this->get_pic_info($this->picpath);  //获得需要水印图片的信息
			$this->waterinfo = $this->get_pic_info($this->waterpath);  //获得水印图片的信息
			//检查大小（宽度，高度）是否适合水印
			if(($this->waterinfo['width']+2*$this->mark_border>$this->picinfo['width'])||($this->waterinfo['height']+2*$this->mark_border>$this->picinfo['height'])){
				$this->error(3);
			}
			if(!$this->info['error']){
				$this->is_necessary();
			}
		}
	}

	/**
	 *使用图片来显示水印
	 *@param:$picinfo
	 *@return :
	 */
	function markpic(){

		//水印位置
		if(empty($style)){
			$style=$this->mark_style;
		}
		$picim=$this->image_create($this->picinfo);  //需要水印的图象标识符
		$waterim=$this->image_create($this->waterinfo);    //水印的图象标识符
		if(!$this->info['error']){
			$picim=$this->imagemerge($picim,$waterim,$this->waterinfo['width'],$this->waterinfo['height'],$style); //水印合并图片
			$this->output($picim);          //输出图象
		}
	}
	/**
	 *使用文字来显示水印(只显示英文)
	 *@param:$string
	 *@return :
	 */
	function markstring_en($string,$newpicpath='',$style=0)
	{
		//todo
	}
	/**
	 *设置对象的属性
	 *@param:$key $value
	 *@return
	 */
	function set($key,$value){
		if(array_key_exists($key,get_object_vars($this))){
			$this->$key=$value;
		}else{
			$this->error(5);
		}
	}


	/**
	 *获取出错信息
	 *@param void
	 *@return
	 */
	function get_error(){
		return $this->errormsg;
	}
	/*----------------------以下为私有方法-------------------------------------------------*/
	/*
	 *输出图象
	*@param:....
	*@return
	*/
	function output($picim,$newpicpath='')
	{
		$method_name=$this->image_output_method;
		if($this->is_output){
			header('Content-type: '.$this->picinfo['mime']);
			@$method_name($picim);
		}else{
			if(empty($newpicpath)){
				$newpicpath = $this->picinfo['path'];
				@unlink($this->picinfo['path']);
			}
			//写入新的文件
			@$method_name($picim,$newpicpath);
		}
	}


	/**
	 *合并水印图象
	 *@param:....
	 *@return
	 */
	function imagemerge($picim,$waterim,$water_width,$water_height,$style=0)
	{
		switch($style)
		{
			case 0:
				//Immediately
				$position[0]=rand($this->mark_border,$this->picinfo['width']-$this->mark_border-$water_width);//x
				$position[1]=rand($this->mark_border,$this->picinfo['height']-$this->mark_border-$water_height);//y
				break;
			case 1:
				//left top
				$position[0]=$this->mark_border;
				$position[1]=$this->mark_border;
				break;
			case 2:
				//right top
				$position[0]=$this->picinfo['width']-$this->mark_border-$water_width;
				$position[1]=$this->mark_border;
				break;
			case 3:
				//middle
				$position[0]=round(($this->picinfo['width']-$water_width)/2);
				$position[1]=round(($this->picinfo['height']-$water_height)/2);
				break;
			case 4:
				//left bottom
				$position[0]=$this->mark_border;
				$position[1]=$this->picinfo['height']-$this->mark_border-$water_height;
				break;
			default:
				//right bottom
				$position[0]=$this->picinfo['width']-$this->mark_border-$water_width;
			$position[1]=$this->picinfo['height']-$this->mark_border-$water_height;
			break;
		}
		//拷贝并合并图像的一部分或全部
		@imagecopymerge($picim,$waterim,$position[0],$position[1],0,0,$water_width,$water_height,$this->mark_pct);
		return $picim;
	}

	/**
	 *检查系统环境是否支持GD库
	 *return:
	 */
	function check_gd(){
		if(!extension_loaded('gd')){
			$this->error(0);
		}
		$this->gdinfo=gd_info();
	}


	/**
	 *新建一个基于调色板的图像（gd库）
	 *@param:$picinfo
	 *@return :$im 图象标识符 imagecreatefrom...
	 */
	function image_create($picinfo='')
	{
		//图像信息
		if(empty($picinfo)){
			$picinfo=$this->picinfo;
		}
		//图像MIME类型
		//imagecreatefromgif
		switch(trim($picinfo['mime'])){
			case 'image/gif':
				$this->image_output_method='imagegif';//获取输出图象的方法名称
				return imagecreatefromgif($picinfo['path']);
				break;
			case 'image/jpeg':
				$this->image_output_method='imagejpeg';
				return imagecreatefromjpeg($picinfo['path']);
				break;
			case 'image/png':
				$this->image_output_method='imagepng';
				return imagecreatefrompng($picinfo['path']);
				break;
			case 'image/wbmp':
				$this->image_output_method='imagewbmp';
				return imagecreatefromwbmp($picinfo['path']);
				break;
			default:
				$this->error(5);
			break;
		}
	}


	/**
	 *获取图片的信息，主要是高度，宽度、类型 MIME,路径
	 *@param:$path:文件路径
	 *@return :$picinfo array
	 */
	function get_pic_info($path)
	{

		$info=@getimagesize($path);//获取图片信息 长宽类型等

		$picinfo['width']=$info[0]; //宽
		$picinfo['height']=$info[1]; //高
		$picinfo['mime']=$info['mime']; //合该图像的 MIME 类型
		$picinfo['path']=$path;
		return $picinfo;
	}
	/**
	 *检查图片是否符合加水印
	 *@param $picinfo图片信息
	 *@return boolean
	 */
	function is_necessary($picinfo=''){
		if(empty($picinfo)){
			$picinfo=$this->picinfo;
		}
		if(!is_array($picinfo)){
			$this->error(1);
		}
		if(($picinfo['width']<$this->min_width)||($picinfo['height']<$this->min_height)){
			$this->error(4);
		}
		return true;
	}


	/**
	 *出错处理
	 */
	function error($id,$other=''){
		switch($id){
			case '0':
				$errormsg='水印失败,你的服务器不支持GD库！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			case '1':
				$errormsg='水印失败,图片路径有误！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			case '2':
				$errormsg='水印失败,水印图片路径有误！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			case '3':
				$errormsg='水印失败,图片太小，图片必须大于水印图片大小！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			case '4':
				$errormsg='水印失败,图片太小，不适合水印！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			case '5':
				$errormsg='水印失败,目前水印只支持gif,jpg,png,wbmp四种格式的图片！';
				$this->info['error'] = true;
				$this->info['msg'] .= $errormsg.' ';
				break;
			default:
				$errormsg='图片水印成功';
			$this->info['error'] = false;
			$this->info['msg'] .= $errormsg.' ';
			break;
		}

		//die($errormsg.$other);
	}
}
?>
