#!/bin/bash

ac=$1
pid_name=$2
app_name=$3
php_dir=$4

if  [ ! -n "$ac" ] ;then
    echo "ac is null,demo:restart | stop"
    exit
fi

php=$php_dir
app_dir=/data0/www/kxgame/master/app/$app_name

path_pid="/var/run/swoole_$pid_name.pid"
echo $path_pid
echo $app_dir
echo $php

if  [ "$ac"x = "stop"x ] ;then
    if [ ! -f "$path_pid" ];then
        echo "$path_pid not exist"
        exit
    fi

    pid=`cat $path_pid`
    echo "pid:$pid"

    `kill -15 "$pid"`
    echo "kill master...."
elif [ "$ac"x = "restart"x ];then
    if [ ! -f "$path_pid" ];then
        echo "$path_pid not exist"
        exit
    fi

    pid=`cat $path_pid`
    echo "pid:$pid"

    `kill -10 "$pid"`
    echo "restart all process...please wait~"
elif [ "$ac"x = "start"x ];then

    if [ -f "$path_pid" ];then
        echo "$path_pid.pid exist"
        exit
    fi

    cd $app_dir
    `$php ws.php`
    echo "starting....."
else
    echo "ac error"
fi