<?php
/**
* 通用的树型类，可以生成任何树型结构
*/
class TreeLib
{
	/**
	* 生成树型结构所需要的2维数组
	* @public array
	*/
	public $arr = array();

	/**
	* 生成树型结构所需修饰符号，可以换成图片
	* @public array
	*/
	public static $icon = array('│','├','└');

	/**
	* @access 
	*/
	public $ret = '';

	function tree($arr=array())
	{
       $this->arr = $arr;
	   $this->ret = '';
	   return is_array($arr);
	}

    /**
	* 得到父级数组
	* @param int
	* @return array
	*/
	function get_parent($myid)
	{
		$newarr = array();
		if(!isset($this->arr[$myid])) return false;
		$pid = $this->arr[$myid]['parentid'];
		$pid = $this->arr[$pid]['parentid'];
		if(is_array($this->arr))
		{
			foreach($this->arr as $id => $a)
			{
				if($a['parentid'] == $pid) $newarr[$id] = $a;
			}
		}
		return $newarr;
	}

    /**
	* 得到子级数组
	* @param int
	* @return array
	*/
	function get_child($myid)
	{
		$a = $newarr = array();
		if(is_array($this->arr))
		{
			foreach($this->arr as $id => $a)
			{
				if(isset($a['parentid'])&&$a['parentid'] == $myid) $newarr[$id] = $a;
			}
		}
		return $newarr ? $newarr : false;
	}
	
	
 /**
	* 得到所有子级数组
	* @param int
	* @return array
	*/
	function get_allchildid($myid)
	{
		$a = $newarr = array();
		if(is_array($this->arr))
		{
			foreach($this->arr as $id => $a)
			{
				if(isset($a['parentid']) && $a['parentid'] == $myid){
					$newarr[] = $a['id'];
					$arr=$this->get_allchildid($a['id']);
					if(!empty($arr)){
						$newarr = array_merge($newarr,$arr) ;
					}		
					
				}
			}
		}
		return $newarr ? $newarr : false;
	}

    /**
	* 得到当前位置数组
	* @param int
	* @return array
	*/
	function get_pos($myid,&$newarr)
	{
		$a = array();
		if(!isset($this->arr[$myid])) return false;
        $newarr[] = $this->arr[$myid];
		$pid = $this->arr[$myid]['parentid'];
		if(isset($this->arr[$pid]))
		{
		    $this->get_pos($pid,$newarr);
		}
		if(is_array($newarr))
		{
			krsort($newarr);
			foreach($newarr as $v)
			{
				$a[$v['id']] = $v;
			}
		}
		return $a;
	}


	/**
	 * -------------------------------------
	 *  得到树型结构
	 * -------------------------------------
	 * @author  Midnight(杨云洲),  yangyunzhou@foxmail.com
	 * @param $myid 表示获得这个ID下的所有子级
	 * @param $str 生成树形结构基本代码, 例如: "<option value=\$id \$select>\$spacer\$name</option>"
	 * @param $sid 被选中的ID, 比如在做树形下拉框的时候需要用到
	 * @param $adds
	 * @param $str_group
	 * @return unknown_type
	 */
	function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = '')
	{
		$number=1;
		$child = self::get_child($myid);
		if(is_array($child))
		{
		    $total = count($child);
			foreach($child as $id=>$a)
			{
				$j=$k='';
				if($number==$total)
				{
					$j .= self::$icon[2];
				}
				else
				{
					$j .= self::$icon[1];
					$k = $adds ? self::$icon[0] : '';
				}
				$spacer = $adds ? $adds.$j : '';

				$selected = $a['id'] == $sid ? 'selected' : '';
				
				@extract($a);
				$parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
				$this->ret .= $nstr;
				self::get_tree($id, $str, $sid, $adds.$k.'&nbsp;',$str_group);
				$number++;
			}
		}
		return $this->ret;
	}
  

	function have($list,$item){
		return(strpos(',,'.$list.',',','.$item.','));
	}
}
?>