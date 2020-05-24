<?php
class JsonCtrl extends BaseCtrl
{

    function manager(){
        //分类
        $type = _g('type');
        if(!$type){
            out_ajax(6000);
        }
        //判断是否在 分类KEY中
        if(!in_array($type,array_keys($GLOBALS['jsonindex']) )){
            out_ajax(6001);
        }
        //取这个分类对应的文件名
        $path = APP_CONFIG."json".DS.$type.".json";
        $content = null;
        //如果文件存在，获取内容
        if(file_exists($path)){
            $jsonContent = file_get_contents($path);
            if($jsonContent){
                $content = json_decode($jsonContent,true);
            }
        }


        $this->addCss('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css');
        $this->addCss('/assets/global/plugins/  bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
        $this->addCss('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');


        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');

        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/table-editable-soft.js');


        $this->addJs('/js/jquery.form.js');

        $this->assign('jsondesc',$GLOBALS['jsonindex'][$type]);
        $this->assign('type',$type);
        $this->assign('content',$content);


        $this->display("jsonmanager.html");

    }

    function makeindex(){
        $type = "index";
        $path = APP_CONFIG.DS.$type.".json";
        $content = null;
        //如果文件存在，获取内容
        if(file_exists($path)){
            $jsonContent = file_get_contents($path);
            if($jsonContent){
                $content = json_decode($jsonContent,true);
            }
        }

        $this->addCss('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css');
        $this->addCss('/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css');
        $this->addCss('/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');


        $this->addJs('/assets/global/plugins/select2/select2.min.js');
        $this->addJs('/assets/global/plugins/datatables/media/js/jquery.dataTables.min.js');
        $this->addJs('/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js');

        $this->addJs('/assets/global/scripts/metronic.js');
        $this->addJs('/assets/admin/layout/scripts/layout.js');
        $this->addJs('/assets/admin/layout/scripts/demo.js');
        $this->addJs('/assets/admin/pages/scripts/table-editable-soft.js');


        $this->addJs('/js/jquery.form.js');

//        $this->assign('jsondesc',$GLOBALS['jsonindex'][$type]);
//        $this->assign('type',$type);
        $this->assign('content',$content);


        $this->display("jsonindex.html");
    }


    function importExcelIndex(){
        include_once PLUGIN . "phpexcel/PHPExcel.php";
        $file = $_FILES;
        $filename = $file['import_excel']['name'];
        $file_temp_name =$file['import_excel']['tmp_name'];

        $objReader = PHPExcel_IOFactory::createReaderForFile($file_temp_name);

        $objPHPExcel = $objReader->load($file_temp_name);


        $Sheets = $objPHPExcel->getSheetNames();
        $rs = array();
        foreach($Sheets as $k=>$v){
            $name = explode("-",$v);
            if($name[0] == 'export'){
                $row = array('name'=>$name[1] ,'no'=>$k);
                $Sheet = $objPHPExcel->getSheet($k);

                $highestColumn=$Sheet->getHighestColumn();
                $title = $Sheet->getCell("A1")->getValue();
//                var_dump($title);
//                var_dump($highestColumn);
                $row['title'] = $title;
                $keys = array();
                for($i=65;$i<=ord($highestColumn);$i++){
                    $key = $Sheet->getCell(chr($i)."4")->getValue();
                    $val = $Sheet->getCell(chr($i)."2")->getValue();
                    $keys[$key] = $val;
                }

//                var_dump($keys);

                $row['field'] = $keys;
                $rs[$name[1]] = $row;
            }


        }

//        exit;

        $type = "index";
        $path = APP_CONFIG.DS.$type.".json";
        if(file_exists($path)){
            $newPath = APP_CONFIG.DS.$type.time().".json";
            rename($path,$newPath);
        }

        $content = json_encode($rs);
        $fd = fopen($path,"a");
        fwrite($fd,$content);

        exit;
    }

    function importExcel(){
        $type = _g('type');
        if(!$type){
            out_ajax(6000);
        }

        if(!in_array($type,array_keys($GLOBALS['jsonindex']) )){
            out_ajax(6001);
        }



        include_once PLUGIN . "phpexcel/PHPExcel.php";

        $file = $_FILES;
        $filename = $file['import_excel']['name'];
        $file_temp_name =$file['import_excel']['tmp_name'];

//        var_dump($file_temp_name);
//        var_dump($filename);exit;

        $objReader = PHPExcel_IOFactory::createReaderForFile($file_temp_name);

        $objPHPExcel = $objReader->load($file_temp_name);
//        $objPHPExcel->setActiveSheetIndex(0);
//        $sheet = $objPHPExcel->getSheet(0);


        $objPHPExcel->setActiveSheetIndex($GLOBALS['jsonindex'][$type]['no']);
        $sheet = $objPHPExcel->getSheet($GLOBALS['jsonindex'][$type]['no']);


        $highestRow=$sheet->getHighestRow();//取得总行数
//        $highestColumn=$sheet->getHighestColumn(); //取得总列数

        $fieldCnt = count($GLOBALS['jsonindex'][$type]['field']);
        $highestColumn= 65 + $fieldCnt - 1;
//        $highestColumn= ord($highestColumn);
//        var_dump($highestRow);
//        var_dump($highestColumn);


        $fieldKey = array_keys($GLOBALS['jsonindex'][$type]['field']);

        $rs = null;
        for($i=5;$i<=$highestRow;$i++){
//            if($i>15){
//                exit;
//            }
            $row = [];
            for($j=65;$j<=$highestColumn;$j++){
                $word = chr($j);
                $key = $j - 65;
                $row[$fieldKey[$key]] = $objPHPExcel->getActiveSheet()->getCell($word.$i)->getValue();
            }

//            if($type == 'task_reward'){
//                $d = $row;
//                $d['reward_goldcoin'] = round($d['reward_goldcoin']);
//                $d['reward_exp'] = round($d['reward_exp']);
//                $d['reward_sunflower'] = round($d['reward_sunflower']);
//
//                TaskRewardModel::db()->add($d);
////                $sql = "truncate table task_reward";
////                $rs = TaskRewardModel::db()->execute($sql);
////                var_dump($rs);exit;
//            }

            $rs[] = $row;

        }

        //离线奖励
//        foreach($rs as $k=>$v){
//            OfflineRewardModel::db()->add($v);
//        }
//
//        exit;

        //任务奖励导入

//        for($i=2;$i<=$highestRow;$i++){
//            $row = [];
//            for($j=65;$j<=84;$j++){
//                $word = chr($j);
//                $row[] = $objPHPExcel->getActiveSheet()->getCell($word.$i)->getValue();
//            }
//            $rs[] = $row;
//        }
//
//        foreach($rs as $k=>$v){
//            $level = $v[0];
//
//
//            $reward_goldcoin = round( $v[2]);
//            $reward_sunflower = round($v[3]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[1],'task_id'=>6);
//            TaskRewardModel::db()->add($arr1);
//
//            $reward_goldcoin = round( $v[5]);
//            $reward_sunflower = round($v[6]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[4],'task_id'=>5);
//            TaskRewardModel::db()->add($arr1);
//
//            $reward_goldcoin = round( $v[8]);
//            $reward_sunflower = round($v[9]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[7],'task_id'=>4);
//            TaskRewardModel::db()->add($arr1);
//
//            $reward_goldcoin = round( $v[11]);
//            $reward_sunflower = round($v[12]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[10],'task_id'=>3);
//            TaskRewardModel::db()->add($arr1);
//
//            $reward_goldcoin = round( $v[14]);
//            $reward_sunflower = round($v[15]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[13],'task_id'=>2);
//            TaskRewardModel::db()->add($arr1);
//
//            $reward_goldcoin = round( $v[17]);
//            $reward_sunflower = round($v[18]);
//
//            $arr1 = array('base_level'=>$level,'reward_goldcoin'=>$reward_goldcoin,'reward_sunflower'=>$reward_sunflower,'task_title'=>$v[16],'task_id'=>1);
//
//            TaskRewardModel::db()->add($arr1);
//        }
//
//        exit;

        $path = APP_CONFIG."json".DS.$type.".json";
        if(file_exists($path)){
            $newPath = APP_CONFIG."json".DS.$type.time().".json";
            rename($path,$newPath);
        }

        $content = json_encode($rs);
        $fd = fopen($path,"a");
        fwrite($fd,$content);

        exit;

    }
    //
    function mergeJson(){
        $rs = array();
        $baseDir = $path = APP_CONFIG."json/";
        foreach($GLOBALS['jsonindex'] as $k=>$v){
            $path = $baseDir.$k.".json";
            $content = file_get_contents($path);
            $rs[$k] = json_decode($content,true);
        }

        $path = APP_CONFIG."json".DS."total.json";
        if(file_exists($path)){
            $newPath = APP_CONFIG."json".DS."total".time().".json";
            rename($path,$newPath);
        }

        $content = json_encode($rs);
        $fd = fopen($path,"a");
        fwrite($fd,$content);

        $key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['jsonTotal']['key'],null,'sanguoadmin');

        $content = file_get_contents($path);
        RedisPHPLib::set($key,$content);

        $this->rsyncTotalJsonToStatic();
    }
    //全并JSON并同步到静态目录中
    function rsyncTotalJsonToStatic(){
        $path = APP_CONFIG."json/total.json";
        $base = BASE_DIR."/www/json/{$GLOBALS['apiVersion']}_total.json";
        if(file_exists($base)){
            $newPath = BASE_DIR."/www/json/{$GLOBALS['apiVersion']}_total".time().".json";
            rename($base,$newPath);
        }

        copy($path,$base);
    }

}
