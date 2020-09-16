<?php
class Help{
	function __construct($c){
		$this->commands = $c;
	}

	public function run($attr){
		if(isset($attr['script_name'])){
			$info = $attr['script_name'];
			echo "<$info>command not found:\n";
		}else{
			echo "command null\n";
		}
		
		echo "The following commands are available:\n";
                foreach($this->commands as $k=>$v){
                echo "-" .$k ."-\n";
		}
	}
}