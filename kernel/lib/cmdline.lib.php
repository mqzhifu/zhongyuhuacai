<?php
//处理指令行参数
class CmdlineLib {
	
	protected $attribute = array (); //属性数组,声明为保护型，利于其他类进行相应的继承操作
	public $commands = array();

	public function __construct() {
		$this->parseArgs();
		if(isset($_SERVER ['argv'][1]))
			$this->attribute['script_name'] = $_SERVER ['argv'][1];
	}
	
	 //解析命令行参数
	private function parseArgs() {
		for($i = 0; $i < count ( $_SERVER ['argv'] ); $i ++) {
			$currentArgs =  $_SERVER ['argv'] [$i] ;
			$currentValue = "";
			$position = strpos ( $currentArgs, "=" );
			if ($position > 0) {
				$argsName = strtolower (substr ( $currentArgs, 0, $position ));
				$argsValue = substr ( $currentArgs, $position + 1 );
				$this->attribute [$argsName] = $argsValue;
			}
		}
	}
	
	public function __get($attributeName) {
		return $this->attribute [$attributeName];
	}
	
	public function __set($attributeName, $attributeValue) {
		$this->attribute [$attributeName] = $attributeValue;
		return $this->attribute [$attributeName];
	}
	
	public function runCommand()
	{
		if(isset($this->attribute['script_name'])){
			$name=$this->attribute['script_name'];
			
			if(isset($this->commands[$name]))
				$command= $this->commands[$name];
			else{
				$command = APP_SHELL_DIR.DS.'Help.shell.php';
				$name = 'Help';
			}
		}else{
				$command = APP_SHELL_DIR.DS.'Help.shell.php';
				$name = 'Help';
		}


//		$rs = include_once APP_SHELL_DIR . DS .  $command;
        $rs = include_once  $command;
// 		echo $command;var_dump($name);exit;
		$commandClass = new $name($this->commands);
		return $commandClass->run($this->attribute);
		
	}
	
	public function addCommands($path)
	{
		if(($commands=$this->findCommands($path))!==array())
		{
			foreach($commands as $name=>$file)
			{
				if(!isset($this->commands[$name]))
					$this->commands[$name]=$file;
			}
		}
	}
	
	public function findCommands($path)
	{
		if(($dir=@opendir($path))===false)
			return array();
		$commands=array();
		while(($name=readdir($dir))!==false)
		{
			$file=$path.$name;
			if(!strcasecmp(substr($name,-9),'shell.php') && is_file($file))
				$commands[substr($name,0,-10)]=$file;
		}
		closedir($dir);
		if(!$commands)exit('no command files can exec...');
		return $commands;
	}
	
	public function confirm($message,$default=false)
	{
		echo $message.' (yes|no) [' . ($default ? 'yes' : 'no') . ']:';
	
		$input = trim(fgets(STDIN));
		return empty($input) ? $default : !strncasecmp($input,'y',1);
	}

}
