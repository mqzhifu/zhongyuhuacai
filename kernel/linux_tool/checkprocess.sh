#!/bin/sh
php_path="/usr/local/php7.1/bin/php"
project_path="/www/wwwroot/test.gitwan.vsgogo.com"
process_name=(sendGiftTaskDemon)

ac=$1
if  [ ! -n "$ac" ] ;then
    echo "ac is null,checkAndStart or restart"
    exit
fi

function checkAndStart(){

    name=`ps -fe|grep $1 |grep -v grep`
    if  [ ! -n "$name" ] ;then
        cd $project_path
        `nohup $php_path  cmd.php $1   > output 2>&1 & `
        echo "start $1 ok!"
    else
        pid=`echo $name|awk '{print $2}'`
        echo "$1 pid is $pid"
        if [ "$ac" = "restart" ];then
            `kill $pid`
            cd $project_path
            `nohup $php_path  cmd.php $1   > output 2>&1 & `
            echo "restart $1 ok!"
        fi
    fi

}

for name in ${process_name[*]}

do

    checkAndStart $name $ac

done