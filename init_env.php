<?php
echo " in init_env\n";
$dir = dirname(__FILE__);

$content = "<?php
define('ENV','#ENV#');
";

$env = $argv[1];

$content = str_replace("#ENV#",$env,$content);

$arr = array("api",'instantplayadmin');
foreach ($arr as $k=>$v) {
    $appDir = $dir . "/$v/env.php";
    echo "append <pre> to $appDir\n";
    $fd = fopen($appDir,"w+");
    fwrite($fd,$content);
}

echo " init_env finish.\n";