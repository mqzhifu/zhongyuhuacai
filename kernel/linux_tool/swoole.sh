#!/bin/bash

ac=$1
basedir=$2
phpbin=$3

appdir=$basedir/app/instantplay/
pidname=swoole_master

if  [ ! -n "$ac" ] ;then
    echo "ac is null,demo:restart | stop"
    exit
fi

path_pid="$appdir/$pidname.pid"

if  [ "$ac"x = "stop"x ] ;then
    if [ ! -f "$path_pid" ];then
        echo "$pidname.pid not exist"
        exit
    fi

    pid=`cat $path_pid`
    echo "pid:$pid"

    `kill -15 "$pid"`
    echo "kill master...."
elif [ "$ac"x = "restart"x ];then
    if [ ! -f "$path_pid" ];then
        echo "$pidname.pid not exist"
        exit
    fi

    pid=`cat $path_pid`
    echo "pid:$pid"

    `kill -10 "$pid"`
    echo "restart all process...please wait~"
elif [ "$ac"x = "start"x ];then

    if [ -f "$path_pid" ];then
        echo "$pidname.pid exist"
        exit
    fi

    cd $appdir
    `$phpbin ws.php`
    echo "starting....."
else
    echo "ac error"
fi