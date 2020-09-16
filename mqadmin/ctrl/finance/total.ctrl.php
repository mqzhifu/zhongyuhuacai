<?php
set_time_limit(600);
header("Content-type:text/html;charset=utf-8");
class TotalCtrl extends BaseCtrl{
    function index(){

        $br = "<br/>";
        echo "uv:0 $br";
        echo "pv:0 $br";
        echo "adu:0 $br";
        echo "mdu:0 $br";
        echo "收入:0 $br";
        echo "支出:0 $br";
        echo "订单数:0 $br";
        echo "用户数:0 $br";
    }


}