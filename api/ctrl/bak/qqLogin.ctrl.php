<?php

/**
 * @Author: Kir
 * @Date:   2019-04-25 20:40:12
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-04-29 10:16:25
 */

/**
 * 
 */
class qqloginCtrl extends BaseCtrl
{
	private $appId = '101574904';
    private $appKey = 'e89626687f2ea705e4e5a7c7c6b8e2a6';
    private $callback = 'http://is-test.feidou.com/qqLogin/getUserinfo/';

    function login()
    {
        $callback = _g("redirect_uri");
    	$qq_login = new \qqLib\QQ_LoginAction($this->appId, $this->appKey, $callback); 
		$qq_login->qq_login(); 
    }

    function getUserinfo()
    {
        $callback = _g("redirect_uri");
    	$qc = new \qqLib\QQ_LoginAction($this->appId, $this->appKey, $callback); 
    	$acs = $qc->qq_callback();    //access_token
        $openid=$qc->get_openid();    //unionid
		$unionid=$qc->get_unionid();    //unionid
		$user_data = $qc->get_user_info();

		return $this->out(200,[ "openid"=>$openid,"unionid"=>$unionid, "user"=>$user_data]);
    }

}