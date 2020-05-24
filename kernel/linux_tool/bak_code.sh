#!/bin/sh
process_name=("app" " functions" "lib" "plugins" "service" "z.class.php" "config" "www" "index.php" "shell.php")

src="/data0/kpool/apps/instantplay"
tar="/data0/kpool/apps/bak_code/"

if [ ! -d $src ];then
      echo "src $src,not exist"
      exit;
fi

if [ ! -d $tar ];then
      echo "tar $tar,not exist"
      exit;
fi


today=`date +"%Y-%m-%d"`

echo $today

new_tar=$tar$today

echo $new_tar

if [ -d $new_tar ];then
      echo "new_tar $new_tar,has exist"
      exit;
fi

`mkdir $new_tar`


for name in ${process_name[*]}
do
    echo "cp -r $src/$name $new_tar/"
    `cp -r $src/$name $new_tar/`
done