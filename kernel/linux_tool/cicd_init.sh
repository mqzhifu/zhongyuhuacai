ENV=$1
AREA=$2
BASEDIR=$3
APPNAME1=instantplay
APPNAME2=instantplayadmin
APPNAME3=open
APPNAME7=instantplayadminnew
APPNAME8=instantplay_new
APPNAME9=geteway
APPNAME10=adsystemadmin

pwd
echo $ENV $AREA
echo $APPNAME1 $APPNAME2 $APPNAME3 $APPNAME7 $APPNAME8 $APPNAME9


#APPNAME4=game
#APPNAME5=quiz
#APPNAME6=gameadmin

pwd
echo $ENV $AREA
echo $APPNAME1 $APPNAME2 $APPNAME3 $APPNAME7 $APPNAME8 $APPNAME9 $APPNAME10
echo "cp env server"
cp $BASEDIR/config/$APPNAME1/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME1/env.php
cp $BASEDIR/config/$APPNAME2/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME2/env.php
cp $BASEDIR/config/$APPNAME3/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME3/env.php
cp $BASEDIR/config/$APPNAME7/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME7/env.php
cp $BASEDIR/config/$APPNAME8/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME8/env.php
cp $BASEDIR/config/$APPNAME9/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME9/env.php
cp $BASEDIR/config/$APPNAME10/conn/$ENV/env_$AREA.php $BASEDIR/config/$APPNAME10/env.php

cp $BASEDIR/config/$APPNAME1/conn/$ENV/server_$AREA.php $BASEDIR/config/$APPNAME1/server.php
cp $BASEDIR/config/$APPNAME8/conn/$ENV/server_$AREA.php $BASEDIR/config/$APPNAME8/server.php

echo "power: www log xyxnew view_c"
chown -R www:www $BASEDIR
chmod -R 744 $BASEDIR
chmod -R 777 $BASEDIR/www/xyxnew

chmod -R 777 $BASEDIR/app/$APPNAME2/view_c
chmod -R 777 $BASEDIR/app/$APPNAME3/view_c
chmod -R 777 $BASEDIR/app/$APPNAME7/view_c
chmod -R 777 $BASEDIR/app/$APPNAME9/view_c
chmod -R 777 $BASEDIR/app/$APPNAME10/view_c

echo write CDN rsync IP
if [ "$AREA" == "cn" ]; then
    echo '/usr/bin/rsync -avzR $dir 10.10.7.223::XYXNEW/' >>  $BASEDIR/www/xyxnew/rsync_to_cdn.sh
elif [ "$AREA" == "en" ]; then
    echo '/usr/bin/rsync -avzR $dir 49.51.252.214::XYXNEW/' >>  $BASEDIR/www/xyxnew/rsync_to_cdn.sh
else
    echo "AREA is err"
    exit
fi


cd $BASEDIR/app/$APPNAME9
pwd
touch ./makeProto.sh
chmod 777 ./makeProto.sh
php shell.php makeProto > ./makeProto.sh
./makeProto.sh
ls -l $BASEDIR/config/$APPNAME9/protobuf_class

echo makeProto ok


cd  $BASEDIR/
ls -l



echo done
