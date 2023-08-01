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

    function index($request){
        $order = " id asc ";
        if(isset( $request['order']) &&  $request['order']){
            $order = " {$request['order']} desc ";
        }
        $list = GirlOumeiModel::db()->getAll("1 order by $order ");
        if(!$list || !count($list)){
            var_dump("db record is empty~");exit;
        }
        $html = "<table>";
        $html.= "<tr><td>id</td><td>type</td><td>name</td><td>age</td><td>height</td><td>file_num</td><td>born</td><td>alias</td></tr>";
        foreach ($list as $k=>$v){
            $alias = $v['alias'];
            if(strlen($alias) > 60){
                $alias = substr($alias,0,60) . "......";
            }
            $ageClass = "";
            if(!$v["age"]){
                $ageClass = 'background-color:Green;';
            }
            $heightClass = "";
            if(!$v["height"]){
                $heightClass = 'background-color:Green;';
            }

            $html.= "<tr><td>{$v['id']}</td><td>{$v['type']}</td><td>{$v['name']}</td><td style='$ageClass'>{$v['age']}</td><td  style='$heightClass'>{$v['height']}</td><td>{$v['file_num']}</td><td>{$v['born']}</td><td>{$alias}</td></tr>";
        }
        $html .= "</table>";
//        echo $html;

        $content = $html;
//        $this->assign("content",$html);
        $html = $this->_st->compile("girl.html");
        include $html;
    }
}