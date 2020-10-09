<?php
return array(
    200=>'',


    //base 基类相关
    3000=>"sign为空",
    3001=>"sign验证失败",


    //Db相关,也就是参数传过来的ID 到DB中查找不到
    1000=>'uid不在DB中',
    1001=>'ruleId不在DB中',
    1002=>'token解出的UID，未在DB中',
//    1003=>'appid错误，不在DB中',
    1004=>'并没有发送过短信',
    1005=>"短信已失效",
    1006=>'登陆用户不在DB中',
//    1007=>'找回密码，地址不在DB中',
//    1008=>'$configId不在DB中',
//    1009=>'$srcUid不在DB中',
//    1010=>'$targetUid不在DB中',
//    1011=>'taskId不在DB中',
//    1012=>'$rewardId不在DB中',
//    1013=>'gameId不在DB中',
//    1014=>'goodsId不在DB中',
//    1015=>'用户没有登陆日志，获取不到GPS信息',
//    1016=>'gps_geo_code为空',
//    1017=>'sessionId错误，不在DB中',
//    1018=>'roomId错误，不在DB中',
//    1019=>'$memoID，不在DB中',
//    1020=>'touid，不在DB中',
//    1021=>'id，不在DB中',
//    1022=>'code，不在DB中',
//    1023=>'app_code，不在DB中',
//    1024=>'innerId，不在DB中',
//    1025=>'adId，不在DB中',
    1026=>'pid ，不在DB中',
    1027=>"gid,不在DB中",
    1028=>"agent_id 不在DB中",
    1029=>"oid not in db {0}",

    1030=>"province code not in db",
    1031=>"city code not in db",
    1032=>"county code not in db",
    1033=>"town code not in db",
    1034=>"share_uid not in db",
    1035=>"user sel address id not in db",
    1036=>"share_uid not in db ",
    1037=>"share_uid is not agent",
    1038=>"address is is not in db",

    //给验证码-短信-邮件使用
    3100=>"type is null",
    3101=>"type err , not in arr",
    3102=>"addr is null",

    3200=>"请示3方短信供应商失败",

    4009=>"用户状态错误，可能后台禁止了",

    5001=>'此接口必须为登陆状态',
//
//    5005=>'curl错误',
    5006=>'发送短信-XX秒只能发送XX次',
    5007=>'发送短信-一天内只允许发送XX次',
//
    5008=>'发送短信-配置表里的短信内容为空',
//    5009=>'发送短信-运营商发送失败',

    5010=>"code验证失败",

//    //用户相关
//    6001=>'用户未登陆',
    6003=>'用户名(手机、邮箱)已被已注册',
//    6004=>'用户在黑名单中',
//    6105=>'用户在短时间内，请求次数过于频繁',
//    6106=>'密码不能和上次相同',
//
//
//    //金融相关
//    7000=>'parse xml error,微信返回的数据格式错误，不能解析',
//    7001=>'wx api 通信失败，请检查网络状态',
//    7002=>'wx api 错误,提现失败（提现异常，请稍后再试）',
//    7003=>'审核中 （申请成功，审核通过将在三个工作日内到账）',
//    7004=>'提现失败（微信未实名认证，请在微信【支付管理】内添加银行卡或验证身份证）',
//    7005=>'微信未知错误码',
//    7006=>'一个用户一天，只能提现2次',
//    7007=>'一个用户一天，最大提现额为20元',
//    7008=>'一个用户，单日金币上限32188',
//    7009=>'好友贡献，金币总上限1000000',
//    7010=>'好友贡献，金币单日上限5000',
//    7011=>'商品价值为0',
//    7012=>'必须是1000的倍数',
//    7013=>'不能小于1000',
//    7014=>'金币数已超过商品单价',
//    7015=>'生成微信预订单失败',
//    7016=>'微信返回的out_trade_no， 不在DB中',
//    7017=>'订单状态错误',
//    7018=>'微信返回数据校验失败',
//    7019=>'请不要重复消耗商品',
//    7020=>'审核未通过，不能提现给用户',
//    7021=>'1元以上，需要管理员审核，请等待...',
//    7022=>'管理员，请不要重复提现',
//    7023=>'10次未处理提现，请不要重复操作',
//    7024=>'不允许金币全额抵扣',
//    7025=>'并没有预扣款',
//    7026=>'1元提现已关闭',
//    7027=>'5元只允许提现一次',
//
//    // 海外APP金融相关（提现）;
//    7050=>'payPal account wrong',// payPal账号格式有误
//    7051=>'payPal account is not allowed to be empty',// payPal账号格式有误
//    7052=>'user identity invalid',// 当前用户不是fb或google用户;
//    7053=>'withdrawal element missing',// elementId字段为空;
//    7054=>'elementId is invalid',// elementId字段无效;
//    7055=>'withdrawal amount is not allowed',// 提现金额不在允许范围内;
//    7056=>'amount greater than 20',// 提现金额大于$20;
//    7057=>'the withdrawal limit has been reached today',// 今日提现已达上限;
//    7058=>'there is a withdrawal under processing',// 有一笔正在处理中的提现;
//    7059=>'gold coin shortage',// 金币不足抵扣;


    7060=>"checkDataAndFormat ctrl is null",
    7061=>"checkDataAndFormat ac is null",
    7062=>"checkDataAndFormat return is null",
    7063=>"",



//    //各种参数为空
    8000=>'手机号为空-mobile',
//    8001=>'ps(密码)-为空',
    8002=>'uid为空',
//    8003=>'key为空',
    8004=>'type为空',
    8005=>'ruleId为空',
//    8006=>'appid为空',//oauth
//    8007=>'timestamp为空',
//    8008=>'authentication为空',
//    8008=>'imgCode为空',//图片验证码
    8009=>'name为空."未填写 用户名/手机号/邮箱/三方ID"',
//    8010=>'ps为空',
//    8011=>'uniqueCode为空',
//    8012=>'pic为空',
//    8013=>'confimPs为空',
    8014=>'code为空',
    8015=>'addr为空',
//    8016=>'userinfo为空',//用户于3方登陆、修改用户个人信息
//    8017=>'上传图片 post input name 为空',
//    8018=>'上传图片 内容 为空',
//    8019=>'所有参数均为空',
//    8020=>'$configId为空',
    8021=>'num为空',
//    8022=>'srcUid 为空',
//    8023=>'targetUid 为空',
//    8024=>'taskId 为空',
//    8025=>'keyword为空',
//    8026=>'touid为空',
//    8027=>'gameid为空',
//    8028=>'list为空',
//    8029=>'goodsId为空',
    8030=>'thirdId为空',
//    8031=>'nickname为空',
//    8032=>'avatar为空',
//    8033=>'status为空',
//    8034=>'email为空',
//    8035=>'token为空',
//    8036=>'roomID为空',
//    8037=>'memo为空',
//    8038=>'sessionId为空',
//    8039=>'fromUid为空',
//    8040=>'content为空',
//    8041=>'cate为空',
//    8042=>'memo为空',
//    8043=>'id为空',
//    8044=>'id为空',
//    8045=>'code为空',
//    8046=>'platform为空',
//    8047=>'只有新注册用户(手机)可设置，密码已存在的不允许在此入口设定',
//    8048=>'elementID为空',
//    8049=>'push_xinge_touken为空',
//    8050=>'accessToken为空',
//    8051=>'app_secret为空',
//    8052=>'score为空',
//    8053=>'dataKey为空',
//    8054=>'dataValue为空',
//    8055=>'设备ID为空',
//    8056=>'app_code为空',
//    8057=>'idNo为空',
//    8058=>'realName为空',
//    8059=>'广告播放较频繁，请稍后再试',
//    8060=>'总时间错误',
//    8061=>'总时间为空',
//    8062=>'后端，游戏开始时间与结束相差小于1秒，无法计算',
//    8063=>'游戏推荐列表为空',
//    8064=>'innerId参数不合法',
//    8065=>'convertType请求参数不合法',
//    8066=>'Lucky_兑换金币数小于0',
//    8067=>'balance请求参数不合法',
//    8068=>'Lucky_兑换平台内金币失败',
//    8069=>'Lucky_今日兑换金币数已达上限',
//    8070=>'Lucky_不满足最低兑换筹码数10000',
//    8071=>'该广告不属于本人',
    8072=>'pid is null',
    8073=>'gid is null',
    8074=>'wxLittleLogin code is null',
    8975=>"title is null",
    8976=>"keyword is null",
    8977=>'categoryAttrPara is null',
    8978=>"condition is null",
    8979=>"product not include goods",
    8980=>"product not ProductLinkCategoryAttr",
    8981=>"oid is null",
    8982=>"gidsNums is null",
    8993=>"category id is null",
////    5003=>'token验证错误',
////    5004=>'key验证错误',
//    8101=>'邮箱格式错误',
//    8102=>'格式错误,md5',
    8105=>'token解出的UID，但不是整型',
//    //各种-数据格式-验证-错误
    8109=>'token解析失败',//
    8110=>"share_uid is null",
    8111=>"latitude is null",
    8112=>"longitude is null",

//    8110=>'code验证失败',//
//    8112=>'路径错误',
//    8113=>'目录不是777',
//    8114=>'图片大于2MB',
//    8115=>'文件扩展名错误',
//    8116=>'文件类型错误',
//    8117=>'文件上传失败1',
//    8118=>'文件上传失败2',
    8119=>'手机号格式错误',
//    8120=>'邮箱格式错误',
//    8121=>'日期格式错误',


//
//
//    8200=>'没有数据需要更新',
//    8201=>'密码错误',
//    8202=>'',
//    8203=>'金币不足',
//    8204=>'',
//    8205=>'用户日常任务-今日已添加',
//    8206=>'用户没有今日任务',
//    8207=>'任务已经完成',
//    8208=>'任务已经领取奖励',
//    8209=>'num >= 0 ',
    8210=>'type值错误',
//    8211=>'',
//    8212=>'',
//    8213=>'',
//    8215=>'',
//    8216=>'该任务不是您的，请不要冒领',
//    8217=>'任务还未完成',
//    8218=>'任务已刷新',
//    8219=>'已领取过了',
//    8220=>'num <=0 ',
//    8221=>'$rewardId不是自己的',
//    8222=>'targetUid不等于TOKEN-Uid',
//    8223=>'',
//    8224=>'',
//    8225=>'',
//    8226=>'',
//    8227=>'没有升级日志记录',
//    8228=>'已领取',
//    8229=>'',
    8230=>'token已失效，请重新登陆',
    8231=>'redis中没有token，用户并没有登陆过',
    8232=>'参数中的token解出来的UID，与redis中token不一致',
//    8233=>'不是整型',
//    8234=>'广告表数据为空，无法获取最大随机数',
//    8235=>'用户没有绑定facebook',
//    8236=>'目标用户关闭了PUSH，无法PUSH消息',
//    8237=>'目标用户把发送用户加入了黑名单，无法PUSH消息',
//    8238=>'30秒前刚刚领过',
//    8239=>'今日金币已达上限',
//    8240=>'商品库存不足',
//    8241=>'积分不足',
    8242=>'type值错误，必须为3方平台类型',
//    8243=>'已经绑定过的用户，再次绑定，TYPE值必须为6',
//    8244=>'未绑定过的用户，只有注册类型为<游客>才可以绑定',
//    8245=>'status值错误',
//    8246=>'请不要重复收藏',
//    8247=>'请不要重复关注',
//    8248=>'已为好友，请不要重复申请添加',
//    8249=>'一个小时内，只能对同一用户，发起一次好友申请',
//    8250=>'非好好关系，不能操作',
//    8251=>'好友，记录缺失',
//    8252=>'请不要重复拉黑',
//    8253=>'对方并没有在黑名单中',
//    8254=>'不能关注自己',
//    8255=>'不能加自己',
//    8256=>'并没有好友申请记录',
//    8257=>'状态错误',
//    8258=>'不允许给自己发消息',
//    8259=>'该SESSION不属于您，请不要查看别人的记录',
//    8260=>'登陆-无须重复登陆',
//    8261=>'并没有收藏该游戏',
//    8262=>'该游戏记录不属于该UID',
//    8263=>'该游戏记录不是当天',
//    8264=>'该游戏记录已经给过奖励了',
//    8265=>'cate值错误',
//    8266=>'关没有关注对方',
//    8267=>'list解析JSON失败',
//    8268=>'不能自己举报自己',
//    8269=>'不要重复操作',
//    8270=>'用户并不在黑名单中',
//    8271=>'您已把对方拉黑',
//    8272=>'上限200',
//    8273=>'不能对自己操作',
//    8274=>'并没有将对方加入免打扰中',
//    8275=>'不要重复添加免打扰',
    8276=>'手机号已存在',
//    8277=>'id不属于本人',
//    8278=>'已结束',
//    8279=>'今日玩游戏获取金币，已达上限',
//    8280=>'您已经绑定过邀请码了，请不要重复操作',
//    8281=>'platform值错误',
//    8282=>'未绑定手机',
//    8283=>'未绑定微信',
//    8284=>'num必须是大于0的正整数',
//    8285=>'提现金额已达上限100',
//    8286=>'金币兑换成现金，不足以支付此次提现金额',
//    8287=>"系统繁忙，请稍后再试",
//
//    8288=>"宝箱开启时间未到",
//    8289=>"无抽奖次数",
//    8290 =>'金币数异常',
//    8291=>'0.5元的金额，只能提取2次',
//    8292=>'已失效',
//    8293=>'accessToken错误',
//    8294=>'该设备ID已经绑定过邀请码',
//    8295=>'已绑定过了',
//    8296=>'游客不允许写邀请码',
//    8297=>'不足4小时',
//    8298=>'已提交过审批记录，不要重复提交',
//    8299=>'已绑定过口令，不能重复绑定',
//    8300=>'口令验证失败',
//    8301=>'提现请先分享',
//    8302=>'提现请先连续签到7天',
//    8303=>'请不要重复签到',
//    8304=>'广告-结束时间为空',
//    8305=>'广告-结束时间大于开始时间',
//    8306=>'广告-type值错误',
//    8307=>'生效时间大于当前时间',
//    8308=>'已使用',
//    8309=>'每天只能使用一次',
//    8310=>'不足5分钟',
//    8311=>'category值错误',
//    8312=>'type值错误',
//    8313=>'该邀请码使用人数达到限制100',
//    8314=>'今日福利宝箱领取次数已达上限',
//    8315=>'id值错误',
//    8316=>'今天并没有签到',
//    8317=>'签到次数不足',
//    8318=>'签到次数与领取宝箱ID对不上',
//    8319=>'今日游戏时长未达到红包领取要求',
//    8320=>'已领取过该福利红包，不可重复领取',
//    8321=>'没有对应的红包配置信息',
//    8322=>'提现请先看广告',
//    8323=>'当日翻翻卡游戏次数已达上限',
//    8324=>'缓存中的key值不存在',
//    8325=>'开心大轮盘宝箱编号不能为空',
//    8326=>'开心大轮盘宝箱id值不合法',
//    8327=>'开心大轮盘游戏次数不足，不发开启宝箱',
//    8328=>'游戏次数与宝箱ID不一致',
//    8329=>'当前宝箱已领取',
//    8330=>'aid缺失',
//    8331=>'奖励金币数为空',
//    8332=>'金币信息不在db中',
//    8333=>'奖励金币数与DB不符',
//    8334=>'当日开心大轮盘游戏次数已达上限',
//    8335=>'广告id为空',
    8336=>'库存不足 {0}',
    8337=>"小程序获取session 失败",
    8338=>"请不要重复操作，收藏/点赞",
    8339=>"请不要重复操作，加入购物车",
    8340=>"PCAP 未找到该商品",
    8341=>"该商品下，没有任何参数属性",
    8342=>"请填写PCAP",
    8343=>"pcap 必须包含 <->",
    8344=>"pcap 数组必须为2",
    8345=>"pcap 某项为空",
    8346=>"pcap 某项 转整型失败",
    8347=>"请不要重复操作，该商品并未收藏/点赞",
    8348=>"支付回调处理异常-参数/签名/xml",
    8349=>"支付回调处理异常-orderNo not in db.",
    8350=>"支付回调处理异常-status err",
    8351=>"只有待支付状态，可取消",
    8352=>"确认收货，必须为已支付状态",
    8353=>"退款只有状态为：已支付、已发货、已签收 才可以",
    8354=>"订单中的 三方交易号 transaction_id  为空",
    8355=>"退款异常",
    8356=>"状态错误，只有退款中，才OK",
    8357=>"生成预订单失败.",
    8358=>"订单已超时",

    8359=>"省为空",
    8360=>"市为空",
    8361=>"县为空",
    8362=>"乡镇为空",
    8363=>"收货人姓名为空",
    8364=>"收货人手机号为空",
    8365=>"详细地址为空",
    8366=>"scene is null",
    8367=>"getwxacodeunlimit is false",
    8368=>"该用户不是代理",
    8369=>"WithdrawMoney type not in array",
    8370=>"一级代理邀请码为空，invite_agent_code",
    8371=>"邀请码找不到agent,invite_agent_code not found agent",
    8372=>"agent id is null",
    8373=>"num is null",
    8374=>"iods is null",
    8375=>"提现金额<=0",
    8376=>"只有订单状态为 已结束 {0} ",
    8377=>"提现订单并不属于该UID 已结束 {0} ",
    8378=>"提现 agent id 不属性该订单 {0}",
    8379=>"该订单已经提现 {0}",
    8380=>"地址无法识别 没有分隔符",
    8381=>"address id is null",
    8382=>"address uid 不属于 {}",
    8383=>"str is null",
    8384=>"请不要重复评论",

    8385=>"退款记录不存在",
    8386=>"退款记录已存在 ，请不要重复操作",
    8387=>"退款type 值错误 ",
    8388=>"退款reason 值错误 ",
);