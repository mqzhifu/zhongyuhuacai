<?php

/**
 * @Author: Kir
 * @Date:   2019-03-27 15:29:11
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-28 12:04:01
 */

require PLUGIN ."/wechat-php-sdk/autoload.php";	// 引入自动加载SDK类的方法

use Gaoming13\WechatPhpSdk\Wechat;
use Gaoming13\WechatPhpSdk\Api;

/**
 * 
 */
class WxopenCtrl extends BaseCtrl
{
	// private $appId = 'wx1b491abc5fa24c60'; //test
    // private $appSecret = '82b46a5709325862c582e24deeba00c9';//test
    private $token = 'f313c94d34cae6632d37b2d2eb617fe5';
	private $encodingAESKey = 'KSl9zyAi5UlucYHhDQ1hKblveoNcgb1cqVHqGslbN4K';

	private $wechat;
	// api模块 - 包含各种系统主动发起的功能
	private $api;

	private $keyWords = ['提现','我要提现','怎么提现','口令','提现口令','验证码','提取','提现码','口令红包','堤坝','堤现','提钱','提现。','体现','提現','题现','提线','现提','邀请码','提醒','提款','口令是什么','提示','验证']; 

	private $subscribeReplay = "hello，这里是#开心小游戏# 
/:,@-D要开心，上开心小游戏/:,@-D
丰富有趣的小游戏，总有一款能够让您开开心心让您爱不释手让您停不下来~
😍要交友，上开心小游戏😍
开心小游戏秉承开心网的真人社交特色，为您推荐兴趣相投的好友，一起游戏一起开心！
/:rose要赚钱，更要上开心小游戏/:rose 
对玩游戏要花钱 Say No！来开心小游戏，玩游戏，挣现金！零花钱私房钱统统都在这里等你哟~
【PS：想提现的宝贝们~发送“提现”两个字给我，就可以获取提现口令哦~/:heart】";
	
	function __construct()
	{
	    if (ENV == 'dev') {
            $this->appId = 'wx89b356e52c0a0f2f';
            $this->appSecret = '2586d72b976141368ff0cde89cf9895e';
        } elseif (ENV == 'release') {
            $this->appId = 'wx706e89a6738a4065';
            $this->appSecret = 'c2e47891b658b1805f4b05149303d845';
        }
		$this->wechat = new Wechat(array(	
		    // 开发者中心-配置项-AppID(应用ID)		
		    'appId' 		=>	$this->appId,
		    // 开发者中心-配置项-服务器配置-Token(令牌)
		    'token' 		=> 	$this->token,
		    // 开发者中心-配置项-服务器配置-EncodingAESKey(消息加解密密钥)
		    // 可选: 消息加解密方式勾选 兼容模式 或 安全模式 需填写
		    'encodingAESKey' =>	$this->encodingAESKey,
		));

		// api模块 - 包含各种系统主动发起的功能
		$this->api = new Api(
			array(
		        'appId' => $this->appId,
		        'appSecret'	=> $this->appSecret,
		    )
		);
	}
	
	function push()
	{
		// 获取微信消息
		$msg = $this->wechat->serve();

		// 回复微信消息
		if ($msg->MsgType == 'text' && in_array($msg->Content, $this->keyWords)) {
			$token = $this->getMoneyToken();
		    $this->wechat->reply($token);
		} elseif ($msg->MsgType == 'event' && $msg->Event == 'CLICK' && $msg->EventKey == 'qqgroup') {
			$this->wechat->reply("再等等就可以和小伙伴们一起玩耍啦~");
		} elseif ($msg->MsgType == 'event' && $msg->Event == 'subscribe') {
			$this->wechat->reply($this->subscribeReplay);
		} else {
		    $this->wechat->reply(array(
				'type' => 'transfer_customer_service',
			));
		}
	}


	function createMenu()
	{
		$rand = md5(mt_rand(10000,99999));
		// $gameCenterUrl = "https://mgres.kaixin001.com.cn/xyxnew/dev/static/frontend/share_link/gameCenter.html?r=$rand"; //test
		$gameCenterUrl = "https://mgres.kaixin001.com.cn/xyxnew/pro/static/frontend/share_link/gameCenter.html?r=$rand";
		$menu = 
		'{
		    "button":[
		        {   
		        	"name":"萌新上路",
		          	"sub_button":[
		          		{    
			               "type":"view",
			               "name":"游戏下载",
			               "url":"http://a.app.qq.com/o/simple.jsp?pkgname=com.kaixin.instantgame"
			            },
			        	{    
			               "type":"view",
			               "name":"新手引导",
			               "url":"https://mp.weixin.qq.com/s/wlO9u1oKkgXZysDEVezOZg"
			            },
			            {    
			               "type":"view",
			               "name":"攻略宝典",
			               "url":"https://mp.weixin.qq.com/mp/homepage?__biz=Mzg2MDEwNjMyOQ==&hid=1&sn=52a749025825a436f2e545727158da75"
			            },
			            {    
			               "type":"view",
			               "name":"最新爆料",
			               "url":"https://mp.weixin.qq.com/mp/homepage?__biz=Mzg2MDEwNjMyOQ==&hid=3&sn=b348c1aa46a665fd2f9a1f3e5339234e"
			            }
			        ]
		        },
		        {    
	               "type":"view",
	               "name":"游戏中心",
	               "url":"'.$gameCenterUrl.'"
	            },
		        {   
		        	"name":"老鸟集结",
		          	"sub_button":[
			        	{    
			               "type":"view",
			               "name":"福利中心",
			               "url":"https://mp.weixin.qq.com/mp/homepage?__biz=Mzg2MDEwNjMyOQ==&hid=2&sn=d1f09a26fe6c41bbda705926a228de95"
			            },
			            {    
			               "type":"view",
			               "name":"官方微博",
			               "url":"https://weibo.com/u/7024409539"
			            },
			            {    
			               "type":"view",
			               "name":"官方贴吧",
			               "url":"https://tieba.baidu.com/f?kw=%BF%AA%D0%C4%D0%A1%D3%CE%CF%B7"
			            },
			            {    
			               "type":"view",
			               "name":"官方网站",
			               "url":"http://xyx.kaixin001.com/"
			            }
			        ]
		        },
		        
		    ]
		}';
		$this->api->create_menu($menu);
	}

	


	function getMoneyToken()
	{
		$date = strtotime('today');
		$key = RedisPHPLib::getAppKeyById($GLOBALS['rediskey']['money_token_day']['key'],$date);
		$token = RedisPHPLib::get($key);
		if (!$token) {
			$token = $this->createMoneyToken();
			RedisPHPLib::set($key, $token);
		}
		$token = json_decode($token);
		var_dump($token[mt_rand(0, 4)]);
		return $token[mt_rand(0, 4)];
	}

	function createMoneyToken()
	{
		$keys = [];
		
		$pattern='1234567890abcdefghijkmnpqrstuvwxyz'; // 无 l o
		for ($i=0; $i<5; $i++) {
			$key = '';
			for( $j=0; $j<6; $j++ ) {
				$key .= $pattern[mt_rand(0, 33)];
			}
			$keys[$i] = $key;
		}
		return json_encode($keys);
	}


	function getUnionId($openid)
	{
		$user = $this->api->get_user_info($openid);
		var_dump($user);
		return $this->out(200,$user['unionid']);
	}

	function getAuthorizeUrl()
	{
		// 弹出授权页面，可通过openid拿到昵称、性别、所在地。即使在未关注的情况下，只要用户授权，也能获取其信息
		// 走服务号
		$this->api = new Api(
			array(
		        'appId' => 'wx89b356e52c0a0f2f',
		        'appSecret'	=> '2586d72b976141368ff0cde89cf9895e',
		    )
		);
		$scope = 'snsapi_userinfo';
		$redirect_uri = urlencode('https://isstatic-test.feidou.com/frondend/FunGameInvites/tutorial.html');
		$authorizeUrl = $this->api->get_authorize_url($scope, $redirect_uri);
		header("Location:$authorizeUrl");
	}

	function getUserinfoInOpen()
	{
		// 走服务号
		$this->api = new Api(
			array(
		        'appId' => 'wx89b356e52c0a0f2f',
		        'appSecret'	=> '2586d72b976141368ff0cde89cf9895e',
		    )
		);
		$scope = 'snsapi_userinfo';
		$rs = $this->api->get_userinfo_by_authorize($scope);

		LogLib::appWriteFileHash("============getUserinfoInOpen: ".json_encode($rs)."============");

		return $this->out(200,$rs);
	}

	function getUserinfoInWeb()
	{
		$this->api = new Api(
			array(
		        'appId' => 'wxe5d0a7df31d4797f',
		        'appSecret'	=> '952ef4fb1fbd7d5bb41a5ff8a90fb71d',
		    )
		);
		$scope = 'snsapi_userinfo';
		$rs = $this->api->get_userinfo_by_authorize($scope);

		LogLib::appWriteFileHash("============getUserinfoInWeb: ".json_encode($rs)."============");

		return $this->out(200,$rs);
	}

	function getUserinfoInApp()
	{
		$this->api = new Api(
			array(
		        'appId' => 'wx406c54b223a06df0',
		        'appSecret'	=> '7a7cfc70d0be9662e3901fb4bebe4939',
		    )
		);
		$scope = 'snsapi_userinfo';
		$rs = $this->api->get_userinfo_by_authorize($scope);

		LogLib::appWriteFileHash("============getUserinfoInApp: ".json_encode($rs)."============");
		
		return $this->out(200,$rs);
	}

	function getJsapiConfig($url)
    {
        return $this->out(200, $this->api->get_jsapi_config($url, 'json'));
    }
}