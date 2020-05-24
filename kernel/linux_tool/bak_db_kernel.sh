#!/bin/sh
if [ ! -n "$1" ] ;then
`echo "$1 para not 1.." >> /tmp/bak_mysql.log`
exit
fi
#设置用户名和密码
v_user="root"
v_password="mqzhifu"
v_host='127.0.0.1'
 
#mysql安装全路径
MysqlDir=/usr/local/mysql/bin
 
#备份数据库
database="kernel"
 
#设置备份路径，创建备份文件夹
Full_Backup=/home/bak_mysql

#开始备份,记录备份开始时间
echo '=========='$(date +"%Y-%m-%d %H:%M:%S")'=========='"备份开始">>/tmp/full_buckup.log
 
$MysqlDir/mysqldump -h$v_host -u$v_user -p$v_password -P$1 --single-transaction --databases $database > $Full_Backup/$(date +%Y%m%d)-$(date +%H).sql
 
#压缩备份文件
#gzip $Full_Backup/$(date +%Y%m%d)/full_backup.sql
 
echo '=========='$(date +"%Y-%m-%d %H:%M:%S")'=========='"备份完成">>/tmp/full_buckup.log

