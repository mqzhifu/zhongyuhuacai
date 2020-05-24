#!/bin/sh

#删除当前日期前7天的备份

if [ ! -n "$1" ] ;then
echo "pleace day num...";
exit
fi

save_day=$1
day_num=`date -d "-$save_day day" +%Y%m%d`

dir="/home/bak_mysql/all_dump"
for filename in `ls $dir` ;
do
    if [ $filename -le $day_num ];then
        echo "del $filename"
       `rm -rf $dir/$filename`

    fi

done
