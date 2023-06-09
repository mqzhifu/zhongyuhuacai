<?php
class GirlCtrl extends BaseCtrl  {
    public $request = null;
    public $agent = null;

    function __construct($request)
    {
        $this->_st = getAppSmarty();
        parent::__construct($request);

//        $agent = $this->agentService->getOneByUid($this->uid);
//        if(!$agent){
//            out_ajax(8368);
//        }
//
//        $this->agent = $agent;
//        echo 1111;
    }

    function index(){
        $list = GirlOumeiModel::db()->getAll();
        if(!$list || !count($list)){
            var_dump("db record is empty~");exit;
        }
        $html = "<table>";
        $html.= "<tr><td>id</td><td>type</td><td>name</td><td>age</td><td>height</td><td>height</td><td>born</td><td>file_num</td></tr>";
        foreach ($list as $k=>$v){
            $html.= "<tr><td>{$v['id']}</td><td>{$v['type']}</td><td>{$v['name']}</td><td>{$v['age']}</td><td>{$v['height']}</td><td>{$v['height']}</td><td>{$v['born']}</td><td>{$v['file_num']}</td></tr>";
        }
        $html .= "</table>";
//        echo $html;

        $content = $html;
//        $this->assign("content",$html);
        $html = $this->_st->compile("girl.html");
        include $html;
    }


}