<?php
namespace php_base\MsgQueue\Test;

include_once "../../loader.php";

use php_base\MsgQueue\ProductBean\HomeworkBean;

$HomeworkBean = new HomeworkBean();
$HomeworkBean->primary_key = "1";
$HomeworkBean->send();