<?php

/**
 * @Author: Kir
 * @Date:   2019-06-13 14:35:40
 * @Last Modified by:   Kir
 * @Last Modified time: 2019-06-13 17:35:47
 */


/**
 * 
 */
class UiCtrl extends BaseCtrl
{
	
	function index()
	{
		$this->display("promotion/app_ui_config.html");
	}


	function getList()
	{
		$records = array();
        $records["data"] = array();
        $sEcho = intval($_REQUEST['draw']);

        $configs = AppUiConfigModel::db()->getAll("1 order by id");

        $iTotalRecords = count($configs);//DB中总记录数
        if ($iTotalRecords){

            foreach ($configs as $conf) {
            	$desc = $this->getDesc($configs, $conf);
    			$data = array(
                    $conf['id'],
                    $desc,
                    $conf['keys'],
                    $conf['is_show'] == 1 ? '是':'否',
                    '<button class="btn btn-sm default blue edit_btn" onclick="edit(this)" attr-id="'.$conf['id'].'" attr-title="'.$conf['title'].'" attr-show="'.$conf['is_show'].'">编辑</button>',
                );
                $records["data"][] = $data;
        	}

        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        echo json_encode($records);
        exit;
	}

	function getDesc($configs, $conf, $desc='')
	{
		if ($desc) {
			$desc = $conf['title'] . " / " . $desc;
		} else {
			$desc = $conf['title'];
		}
		if ($conf['pid'] == 0) {
			return $desc;
		} else {
			foreach ($configs as $value) {
				if ($conf['pid'] == $value['id']) {
					return $this->getDesc($configs, $value, $desc);
				}
			}
			
		}
	}


	function update()
	{
		$id = _g("id");
		$title = _g("title");
		$is_show = _g("is_show");
		if (!$id || !is_numeric($id) || !$title || !$is_show || !is_numeric($is_show)) {
			$this->outputJson(0, "参数有误");
		}

		$data = ['title'=>$title, 'is_show'=>$is_show];
		AppUiConfigModel::db()->upById($id, $data);
		$this->outputJson(200, "succ");
	}

	function init()
	{
		$config = array(
		    'common'=>array(
		    	'title'=>'公共',
		        'isShow'=>'是否展示，1是2否',
		        'bottom'=>array(
		            'title'=>'底部',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'header'=>array(
		            'title'=>'头部',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'login'=>array(
		            'title'=>'登陆',
		            'isShow'=>'是否展示，1是2否',

		            'cellphoneSMS'=>array(
		                'title'=>'手机短信',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'cellphonePS'=>array(
		                'title'=>'手机密码',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'wechat'=>array(
		                'title'=>'微信',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'qq'=>array(
		                'title'=>'QQ',
		                'isShow'=>'是否展示，1是2否',
		            ),
		            'facebook'=>array(
		                'title'=>'脸书',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'google'=>array(
		                'title'=>'谷歌',
		                'isShow'=>'是否展示，1是2否',
		            ),

		        ),
		        'sign'=>array(
		            'title'=>'签到',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'playGame'=>array(
		            'title'=>'玩游戏',
		            'isShow'=>'是否展示，1是2否',
		        ),

		        'newPlayerRewardPckPop'=>array(
		            'title'=>'首页红包奖励弹窗',
		            'isShow'=>'是否展示，1是2否',
		        ),

		        'openLuckBoxPop'=>array(
		            'title'=>'开宝箱弹窗',
		            'isShow'=>'是否展示，1是2否',
		        ),

		        'luckLotteryPop'=>array(
		            'title'=>'幸运抽奖',
		            'isShow'=>'是否展示，1是2否',
		        ),

		        'happyLottery'=>array(
		            'title'=>'开心抽一抽',
		            'isShow'=>'是否展示，1是2否',
		        ),

		    ),

		    'index'=>array(
		        'title'=>'首页',
		        'isShow'=>'',
		        'gameList'=>array(
		            'title'=>'游戏列表',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'banner'=>array(
		            'title'=>'轮播广告位',
		            'isShow'=>'是否展示，1是2否',
		        ),

		        'getAdminGameCheck'=>array(
		            'title'=>'管理员查看待审核游戏',
		            'isShow'=>'是否展示，1是2否',
		        ),


		    ),

		    'chat'=>array(
		        'title'=>'聊天',
		        'isShow'=>'是否展示，1是2否',
		        'addFried'=>array(
		            'title'=>'添加好友',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'recommendUserList'=>array(
		            'title'=>'推荐好友列表',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'sessionList'=>array(
		            'title'=>'会话列表',
		            'isShow'=>'是否展示，1是2否',
		        ),
		    ),

		    'userDetail'=>array(
		        'title'=>'用户详情页',
		        'isShow'=>'是否展示，1是2否',
		        'edit'=>array(
		            'title'=>'编辑',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'userinfo'=>array(
		            'title'=>'用户信息',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'playGameHistory'=>array(
		            'title'=>'玩过的游戏列表',
		            'isShow'=>'是否展示，1是2否',
		        ),
		    ),



		    'task'=>array(
		        'title'=>'任务',
		        'isShow'=>'是否展示，1是2否',
		        'daily'=>array(
		            'title'=>'日常',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'growup'=>array(
		            'title'=>'成长任务',
		            'isShow'=>'是否展示，1是2否',
		        ),
                'lucky'=>array(
                    'title'=>'刮一刮',
                    'isShow'=>'是否展示，1是2否',
                ),
                'slideTask'=>array(
                    'title'=>'滑动任务栏',
                    'isShow'=>'是否展示，1是2否',

                    'dayLottery'=>array(
                        'title'=>'游戏福利红包',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'draw'=>array(
                        'title'=>'幸运大抽奖',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'luckyCoin'=>array(
                        'title'=>'刮一刮',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'friendsAdd'=>array(
                        'title'=>'好友加成',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'signAward'=>array(
                        'title'=>'签到有奖',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'award'=>array(
                        'title'=>'奖金排行榜',
                        'isShow'=>'是否展示，1是2否',
                    ),
                    'happySwheel'=>array(
                        'title'=>'开心大轮盘',
                        'isShow'=>'是否展示，1是2否',
                    ),
                ),
		    ),

		    'me'=>array(
		        'title'=>'我的',
		        'isShow'=>'是否展示，1是2否',
		        'goldTotal'=>array(
		            'title'=>'金币汇总：今日、总共获取、游戏时长',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'banner'=>array(
		            'title'=>'轮播广告位',
		            'isShow'=>'是否展示，1是2否',
		        ),
		        'menu'=>array(
		            'title'=>'个人菜单',
		            'isShow'=>'是否展示，1是2否',

		            'invite_code'=>array(
		                'title'=>'邀请码',
		                'isShow'=>'是否展示，1是2否',
		            ),
		            'shop'=>array(
		                'title'=>'商城',
		                'isShow'=>'是否展示，1是2否',
		            ),
		            'invite'=>array(
		                'title'=>'邀请',
		                'isShow'=>'是否展示，1是2否',
		            ),
		            'contact'=>array(
		                'title'=>'联系人',
		                'isShow'=>'是否展示，1是2否',
		            ),
		            'wallet'=>array(
		                'title'=>'钱包',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'task'=>array(
		                'title'=>'任务',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'settings'=>array(
		                'title'=>'设置',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'getMoney'=>array(
		                'title'=>'提现',
		                'isShow'=>'是否展示，1是2否',
		            ),

		            'feedback'=>array(
		                'title'=>'反馈',
		                'isShow'=>'是否展示，1是2否',
		            ),
		        ),
		    ),

		);

		$cnt = AppUiConfigModel::db()->getCount();
		if ($cnt) {
			AppUiConfigModel::db()->delete("1 limit $cnt");
		}
		
		$data = [];

		$cnt = 0;

		foreach ($config as $k => $v) {
			$cnt ++;
			$data = [
				'id' => $cnt,
				'is_show' => 1,
				'pid' => 0,
				'title' => $v['title'],
				'keys' => $k
			];
			AppUiConfigModel::db()->add($data);

			$pid = $cnt;

			foreach ($v as $kk => $vv) {
				if (is_array($vv)) {
					$cnt ++;
					$data = [
						'id' => $cnt,
						'is_show' => 1,
						'pid' => $pid,
						'title' => $vv['title'],
						'keys' => $kk
					];
					AppUiConfigModel::db()->add($data);

					$ppid = $cnt;

					foreach ($vv as $kkk => $vvv) {
						if (is_array($vvv)) {
							$cnt ++;
							$data = [
								'id' => $cnt,
								'is_show' => 1,
								'pid' => $ppid,
								'title' => $vvv['title'],
								'keys' => $kkk
							];
							AppUiConfigModel::db()->add($data);
						}
					}
				}
			}
		}
		
	}

}