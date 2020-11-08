dir=$1
os_dir=$2

cd $os_dir
/usr/bin/rsync -avzR $dir 10.10.7.223::XYX/