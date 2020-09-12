<?php
return array(
    200=>'',


    //base 基类相关
    3000=>"sign为空",
    3001=>"sign验证失败",


    //Db相关,也就是参数传过来的ID 到DB中查找不到
    1000=>'uid不在DB中',
//    1001=>'ruleId不在DB中',
    1002=>'token解出的UID，未在DB中',
//    1003=>'appid错误，不在DB中',
//    1004=>'并没有发送过短信',
//    1005=>'db status 状态已失效',
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
    1039=>"agentUid not in db",
    5001=>'此接口必须为登陆状态',
//
    6003=>'用户名(手机、邮箱)已被已注册',

    7060=>"checkDataAndFormat ctrl is null",
    7061=>"checkDataAndFormat ac is null",
    7062=>"checkDataAndFormat return is null",
    7063=>"",



//    //各种参数为空
    8000=>'手机号为空-mobile',
    8001=>'ps(密码)-为空',
    8002=>'图片验证码为空',
    8004=>'type为空',
    8005=>'ruleId为空',
    8009=>'name为空."未填写 用户名/手机号/邮箱/三方ID"',
//    8011=>'uniqueCode为空',
//    8012=>'pic为空',
//    8013=>'confimPs为空',
//    8014=>'code为空',
//    8015=>'addr为空',
//    8016=>'userinfo为空',//用户于3方登陆、修改用户个人信息
//    8017=>'上传图片 post input name 为空',
//    8018=>'上传图片 内容 为空',
//    8019=>'所有参数均为空',
//    8020=>'$configId为空',
//    8022=>'srcUid 为空',
//    8023=>'targetUid 为空',
//    8024=>'taskId 为空',
//    8025=>'keyword为空',
//    8026=>'touid为空',
//    8027=>'gameid为空',
//    8028=>'list为空',
//    8029=>'goodsId为空',
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
    8102=>'格式错误,md5',
    8105=>'token解出的UID，但不是整型',
//    //各种-数据格式-验证-错误
    8109=>'token解析失败',//
    8110=>"share_uid is null",
    8111=>"latitude is null",
    8112=>"longitude is null",

    8210=>'type值错误',
    8230=>'token已失效，请重新登陆',
    8231=>'redis中没有token，用户并没有登陆过',
    8232=>'参数中的token解出来的UID，与redis中token不一致',
    8242=>'type值错误，必须为3方平台类型',
    8276=>'手机号已存在',
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

    8386=>"手机号/密码错误",
);