<?php
class spiderTB{
    public $taobao_username = "mqzhifu";
    public $taobao_password = "mqzhifu";

    public function __construct($c){
        $this->commands = $c;
    }

    public function run(){
        $this->tb();
    }

    function tb($code = "",$id = 0){
        #获取登录框中的淘宝验证数据
        $text=trim(file_get_contents('https://login.taobao.com/member/login.jhtml'));
//        $text=iconv('gbk','utf-8',trim($text));

        #正则提取要发送的参数
        $rs=preg_match_all('/]*name="([^"]*)"[^>]*value="([^"]*)"[^>]*/>/s',$text,$match);
        $sdata=array();
        foreach($match[1] as $t=>$skey)
        {
            $sdata[$skey]=$match[2][$t];
        }

        #提取提交的URL、如果不是转向跳转过来的，登录页面是固定的，此处是原来从转发页面跳转过来登录的时写的，就不改了。
        $rs=preg_match('/<form action="/member([^"]*)"/s',$text,$match);
        $url='https://login.taobao.com/member'.$match[1];

        #加入淘宝登录账号
        $sdata['tid']='';
        $sdata['TPL_username']=$this->taobao_username;;    #修改淘宝账号密码
        $sdata['TPL_password']=$this->taobao_password;
        $sdata['naviVer']='';
        $sdata['poy']='';
        $sdata['TPL_password_2']='';
        $sdata['oslanguage']='';
        $sdata['sr']='';
        $sdata['osVer']='';
        if($code) $sdata['TPL_checkcode']=$code;

        #组装要发送的数据
        $char='';
        foreach($sdata as $k=>$v) $char.="{$k}={$v}&";
        $char=rtrim($char,'&');

        #执行页面登录、返回需要输入验证码
        $res=$this->vlogin($url,$char);

        #提取验证码图片、呈现出提交验证码的表单
        $rs=preg_match('/data-src="([^"]*)"/s',$res,$match);
        var_dump($rs);exit;
        if($rs){
            echo " <img src=\"{$match[1]}\" data-ke-src=\"{$match[1]}\" /> ";
            exit;
        }

//        $this->这里写提取产品页面的数据方法，传入产品ID值;
    }

    public function vlogin($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_REFERER, 'https://login.taobao.com/member/login.jhtml');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_file);        #设置一下你的cookies文件存储路径
        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_file);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl))
        {
            echo 'Errno' . curl_error($curl);exit;
        }
        return $tmpInfo;
    }
}