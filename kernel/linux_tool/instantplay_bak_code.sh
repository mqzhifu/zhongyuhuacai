#!/bin/sh
process_name=("app/ app" "functions/ functions" "lib/ lib" "plugins/ plugins" "service/ service" "z.class.php z.class.php" "config/instantplay/rediskey.php config/instantplay" "config/instantplay/api.php config/instantplay" "config/instantplay/main.php config/instantplay" "config/instantplay/code.php config/instantplay" )

ac=$1
if  [ ! -n "$ac" ] ;then
    echo "ac is null,please enter:api , admin ,publish_foreign, all"
    exit
fi


if [ "$ac" == "admin" ]; then
    sc="./publish_admin.py"
elif [ "$ac" == "api" ]; then
    sc="./publish.py"
elif [ "$ac" == "foreign" ]; then
    sc="./publish_foreign.py"
else
    echo "ac is err"
    exit
fi



function checkAndStart(){


    echo $1 $2 $3


}

for(( i=0;i<${#process_name[@]};i++))
do
    checkAndStart $sc  ${process_name[i]}
done;


#for name in ${process_name[*]}
#do
#    checkAndStart $name $ac
#done