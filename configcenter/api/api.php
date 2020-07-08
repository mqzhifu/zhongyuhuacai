<?php
$arr = array(
    'index'=>array(
        'title'=>'默认/首页',

        'index'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'测试',
            'request'=>array(),



            //最简单的类型
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),

 //           //一维 数组 - 数字自增
//            'return'=>array(
//                'array_number_auto_incr'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'一维-数字自增-下标-数组'),
//            ),
//            //二维 数组 - 数字自增
//            'return'=>array(
//                'array_number_auto_incr'=>array('must'=>1,'default'=>1,'title'=>'一维-数字自增-下标-数组',
//                    'subset'=>array("subset_key"=>'array_number_auto_incr','type'=>'int','must'=>1 )
//                ),
//            ),
//
//            //一维 数字下标自增 数组 ，嵌入 一维对象
//            'return'=>array(
//                'array_number_auto_incr'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出',
//                    'subset'=>array("cate"=>'obj','must'=>1 ,'list'=>array(
//                        'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
//                        'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'产品ID'),
//                        'gid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
//                        'time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'添加时间'),
//                    )
//                    )
//                ),
//            ),
//
//            //一维 对象
//            'return'=>array(
//                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
//                        'uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
//                        'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'产品ID'),
//                        'gid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'商品ID'),
//                        'time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'添加时间'),
//                    )
//                ),
//            ),
//            //一维 对象 中，某个键值，是一个对象
//            'return'=>array(
//                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
//                    'uid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'用户ID'),
//                    'pid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'产品ID'),
//                    'gid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'商品ID'),
//                    'payList'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'支付列表',
//                            'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
//                                'status'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'状态'),
//                                'time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'时间'),
//                                'price'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'价格'),
//                                )
//                            ),
//                        )
//                    ),
//                ),
//            ),
//            //一维 对象 中，某个键值，是一个  数字自增数组  - ，同时 再包含一个对象
//            'return'=>array(
//                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'前置索引','list'=>array(
//                    'uid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'用户ID'),
//                    'pid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'产品ID'),
//                    'gid'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'商品ID'),
//                    'payList'=>array('must'=>1,'default'=>1,'title'=>'支付列表',
//                        'subset'=>array("subset_key"=>'array_number_auto_incr','must'=>1 ,'list'=>array(
//                            'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
//                                        'status'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'状态'),
//                                        'time'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'时间'),
//                                        'price'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'价格'),
//                                        )
//                                    ),
//                                ),
//                            )
//                        ),
//                    ),
//                ),
//            ),


        ),

        'parserAddressByStr'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'将字符串，解析成，一个个<地址>字段',
            'request'=>array(
                'address_str'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'地址解析字符串-省,市,县,乡镇,详细地址,收货人姓名,收货人手机号'),
            ),
            'return'=>array(
                'obj'=>array("must"=>1 ,'title'=>"数组",'list'=>array(
                        'name'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'收货人姓名'),
                        'province'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'省'),
                        'city'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'市'),
                        'county'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'县'),
                        'town'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'乡镇'),
                        'village'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'村'),
                        'mobile'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'手机号'),
                    )
                )
            ),
        ),

        'shareProduct'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'记录分享',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'产品ID'),
                'source'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'来源类型 1微信指向朋友'),
            ),
            'return'=>array(

            ),
        ),

        'checkToken'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'检测token是否合法',
            'request'=>array(
                'token'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'token'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),

        'wxPushLocation'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'微信端获取用户gps位置信息',
            'request'=>array(
                'latitude'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'纬度'),
                'longitude'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'经度'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),
        'getBannerList'=>array(
            'title'=>'首页轮播图',
            'ws'=>array('request_code'=>5013,'response_code'=>5014),
            'request'=>array(
            ),
            'return'=>array(
                'array_number_auto_incr'=>array("must"=>1,'list'=>array(
                    'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                                    'type' => array('type'=>'int','title'=>'类型（1、直接跳转产品详情页）','must'=>1),
                                    'id' => array('type'=>'int','title'=>'id','must'=>1),
                                    'title'=>  array('type'=>'int','title'=>'标题-描述','must'=>1),
                                    'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                    'pid'=>  array('type'=>'string','title'=>'产品ID','must'=>1),
//                                    'a_time'=>  array('type'=>'int','title'=>'添加时间','must'=>1),
//                                    'sort'=>  array('type'=>'int','title'=>'排序','must'=>1),
//                                    'status'=>  array('type'=>'int','title'=>'1上架2下架','must'=>1),
            //                    'is_relative'=>  array('type'=>'int','title'=>'链接地址1站内2非站内（目前只有站内,默认返回1）','must'=>1),
            //                    'relative_path'=>  array('type'=>'string','title'=>'APP内跳转地址（1：游戏，2：邀请，3：任务，4：金币欢乐送）','must'=>1),
                                )
                            ),
                        ),
                    )

            ),
        ),

    ),

    'product'=>array(
        'title'=>'产品',

        'getAllCategory'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'产品的所有分类',
            'request'=>array(),
            'return'=>array(
                'array_number_auto_incr'=>array("must"=>1,'list'=>array(
                    'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                        'name' => array('type'=>'string','title'=>'名称','must'=>1),
                        'id' => array('type'=>'int','title'=>'id','must'=>1),
                        'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                    )
                    ),
                ),
                )

            ),
        ),

        'getDetailRecommend'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'详情页-推荐的产品列表',
            'request'=>array(
                'page'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前请求的页数'),
                'limit'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页多少条'),
            ),
            'return'=>array(
                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
                    'page'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前请求的页数'),
                    'limit'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页多少条'),
                    'record_cnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'总记录数'),
                    'page_cnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'总页数'),
                    'list'=>array('must'=>1,'default'=>1,'title'=>'数据列表',
                        'subset'=>array("must"=>1,"subset_key"=>'array_number_auto_incr','list'=>array(
                            'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                                'goods_total' => array('type'=>'string','title'=>'商品总数','must'=>0),
                                'id' => array('type'=>'int','title'=>'id','must'=>1),
                                'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                'title'=>  array('type'=>'string','title'=>'标题','must'=>1),
                                'user_buy_total'=>  array('type'=>'string','title'=>'用户购买统计','must'=>0),
                                'lowest_price'=>  array('type'=>'string','title'=>'最低价格','must'=>0),
                            )
                            ),
                        ),
                        )
                    )
                ),
                ),
            ),
        ),

        'getRecommendList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'首页-推荐的产品列表',
            'request'=>array(
                'page'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前请求的页数'),
                'limit'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页多少条'),
            ),
            'return'=>array(
                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
                    'page'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前请求的页数'),
                    'limit'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页多少条'),
                    'record_cnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'总记录数'),
                    'page_cnt'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'总页数'),
                    'list'=>array('must'=>1,'default'=>1,'title'=>'数据列表',
                            'subset'=>array("must"=>1,"subset_key"=>'array_number_auto_incr','list'=>array(
                                'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                                            'goods_total' => array('type'=>'string','title'=>'商品总数','must'=>0),
                                            'id' => array('type'=>'int','title'=>'id','must'=>1),
                                            'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                            'title'=>  array('type'=>'string','title'=>'标题','must'=>1),
                                            'user_buy_total'=>  array('type'=>'string','title'=>'用户购买统计','must'=>0),
                                            'lowest_price'=>  array('type'=>'string','title'=>'最低价格','must'=>0),
                                        )
                                    ),
                                ),
                            )
                        )
                    ),
                ),
            ),
        ),

        'getUserHistoryPVList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'一个产品的，最近访客',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'产品ID'),
            ),
            'return'=>array(
                    'array_number_auto_incr'=>array("must"=>1,'list'=>array(
                        'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                            'pid' => array('type'=>'string','title'=>'名称','must'=>1),
                            'uid' => array('type'=>'int','title'=>'用户ID','must'=>1),
                            'a_time'=>  array('type'=>'string','title'=>'添加时间','must'=>1),
                            'nickname'=>  array('type'=>'string','title'=>'昵称','must'=>1),
                            'avatar'=>  array('type'=>'string','title'=>'头像' , 'must'=>1),

                        )
                        ),
                    ),
                ),
            )
        ),

        'getOneDetail'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'产品详情页',
            'request'=>array(
                'id'=>array(           'type'=>'int','must'=>1,'default'=>100001,'title'=>'产品ID'),
                'include_goods'=>array('type'=>'int','must'=>1,'default'=>2,     'title'=>'是否包含商品1是2否'),
            ),
            'return'=>array(
                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
                    'id' => array('type'=>'int','title'=>'id','must'=>1),
                    'title' => array('type'=>'string','title'=>'标题','must'=>1),
                    'subtitle' => array('type'=>'string','title'=>'副标题','must'=>0),
                    'desc' => array('type'=>'string','title'=>'详细描述','must'=>0),
                    'brand' => array('type'=>'string','title'=>'品牌','must'=>0),
                    'attribute' => array('type'=>'string','title'=>'属性参数','must'=>0),
                    'notice' => array('type'=>'string','title'=>'购买须知','must'=>0),
                    'category_id' => array('type'=>'int','title'=>'分类ID','must'=>1),
                    'status' => array('type'=>'int','title'=>'状态1上架2下架','must'=>1),
                    'a_time'=>  array('type'=>'string','title'=>'添加时间','must'=>1),
                    'admin_id'=>  array('type'=>'string','title'=>'管理员ID','must'=>0),
                    'pic' => array('type'=>'string','title'=>'详情描述图片','must'=>1),

                    'lowest_price' => array('type'=>'int','title'=>'最低价格(分)','must'=>1),
                    'desc_attr' => array('type'=>'string','title'=>'产品描述的详细参数，逗号分隔','must'=>0),
                    'desc_attr_format' => array('type'=>'int','title'=>'产品描述的详细参数,数组(格式化desc_attr)','must'=>0),

                    'category_attr_null' => array('type'=>'int','title'=>'产品没有任何属性参数1是2否','must'=>1),
                    'goods_total' => array('type'=>'int','title'=>'包含多少个商品数','must'=>1),
                    'user_buy_total' => array('type'=>'int','title'=>'用户总购买数','must'=>0),
                    'user_up_total' => array('type'=>'int','title'=>'用户点赞总数','must'=>0),
                    'user_collect_total' => array('type'=>'int','title'=>'用户收藏总数','must'=>0),
                    'user_comment_total' => array('type'=>'int','title'=>'用户总评论数','must'=>0),
                    'recommend' => array('type'=>'int','title'=>'是否推荐首页1是2否','must'=>1),
                    'recommend_detail' => array('type'=>'int','title'=>'1是2否,推荐详情页','must'=>0),
    //                'goods_list' => array('type'=>'int','title'=>'商品列表','must'=>1),
    //                'pcap' => array('type'=>'int','title'=>'产品参数属性列表','must'=>1),
    //                'goodsLowPriceRow'=>array('type'=>'int','title'=>'最低价的商品','must'=>0),
                    'stock' => array('type'=>'int','title'=>'总库存数','must'=>0),



                    'has_collect' => array('type'=>'int','title'=>'用户是否已收藏','must'=>0),
                    'has_up' => array('type'=>'int','title'=>'用户是否已点赞','must'=>0),

                    'pv' => array('type'=>'int','title'=>'总访问数','must'=>0),
                    'uv' => array('type'=>'int','title'=>'总用户访问数','must'=>0),
                ),
//                'original_price' => array('type'=>'int','title'=>'原始价格','must'=>1),
//                'factory_uid' => array('type'=>'int','title'=>'工厂ID','must'=>1),
//                'sort' => array('type'=>'int','title'=>'排序','must'=>1),
//                'spider_source_type' => array('type'=>'int','title'=>'抓取来源1平台自己2:1688','must'=>1),
//                'spider_source_pid' => array('type'=>'int','title'=>'抓取来源-产品ID','must'=>1),
            ),
        ),
            ),


        'search'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'搜索产品',
            'request'=>array(
                'page'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'当前请求的页数'),
                'limit'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'每页多少条'),

                'keyword'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'关键字'),
                'category'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'产品类型ID'),
                'orderType'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'排序字段'),
                'orderUpDown'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'升/降'),
            ),
            'return'=>array(
                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
                    'page'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'当前请求的页数'),
                    'limit'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'每页多少条'),
                    'record_cnt'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'总记录数'),
                    'page_cnt'=>array('type'=>'int','must'=>0,'default'=>1,'title'=>'总页数'),
                    'list'=>array('must'=>1,'default'=>0,'title'=>'数据列表',
                        'subset'=>array("must"=>0,"subset_key"=>'array_number_auto_incr','list'=>array(
                            'subset'=>array("subset_key"=>'obj','must'=>0 ,'list'=>array(
                                'goods_total' => array('type'=>'string','title'=>'商品总数','must'=>0),
                                'id' => array('type'=>'int','title'=>'id','must'=>1),
                                'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                'title'=>  array('type'=>'string','title'=>'标题','must'=>1),
                                'user_buy_total'=>  array('type'=>'string','title'=>'用户购买统计','must'=>0),
                                'lowest_price'=>  array('type'=>'string','title'=>'最低价格','must'=>0),
                            )
                            ),
                        ),
                        )
                    )
                ),
                ),
            ),
        ),

        'up'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'点赞一个产品',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'id'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),

        'cancelUp'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'取消-点赞一个产品',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'id'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),

        'collect'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'收藏一个产品',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'id'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),

        'cancelCollect'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'取消-收藏一个产品',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'id'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),


        'comment'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'评论一个产品',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'id'),
                'title' => array('type'=>'string','title'=>'标题','default'=>"",'must'=>1),
                'content' => array('type'=>'string','title'=>'内容','default'=>"",'must'=>1),
                'pic' => array('type'=>'string','title'=>'图片','default'=>"",'must'=>1),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),

        'getCommentList'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'获取评论列表',
            'request'=>array(
                'pid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'产品ID'),
            ),
            'return'=>array(
                'id' => array('type'=>'int','title'=>'id','must'=>1),
                'pic' => array('type'=>'string','title'=>'图片','must'=>1),
                'title' => array('type'=>'string','title'=>'标题','must'=>1),
                'content' => array('type'=>'string','title'=>'内容','must'=>1),
                'uid' => array('type'=>'string','title'=>'用户ID','must'=>1),
                'a_time' => array('type'=>'string','title'=>'添加时间','must'=>1),
            ),
        ),

        'getSearchAttr'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'产品列表-搜索项(分类&排序)',
            'request'=>array(
            ),
            'return'=>array(
                'obj'=>array( 'must'=>1,'default'=>1,'title'=>'任意输出','list'=>array(
                        'category' => array('type'=>'int','title'=>'aaa','must'=>1,
                            'subset'=>array("must"=>1,"subset_key"=>'array_number_auto_incr','list'=>array(
                                'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                                            'name' => array('type'=>'string','title'=>'商品总数','must'=>0),
                                            'id' => array('type'=>'int','title'=>'id','must'=>1),
                                            'pic'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                        )
                                    ),
                                ),
                            )
                        ),
                        'order_type' => array('type'=>'int','title'=>'aaa','must'=>1,
                            'subset'=>array("must"=>1,"subset_key"=>'array_number_auto_incr','list'=>array(
                                        'subset'=>array("subset_key"=>'obj','must'=>1 ,'list'=>array(
                                            'name' => array('type'=>'string','title'=>'商品总数','must'=>0),
                                            'id' => array('type'=>'int','title'=>'id','must'=>1),
                                            'field'=>  array('type'=>'string','title'=>'图片地址','must'=>1),
                                        )
                                    ),
                                ),
                            )
                        ),
                    )
                ),
            ),
        ),


    ),

    'pay'=>array(
        'title'=>'支付',

        'wxLittle'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'小程序',
            'request'=>array(
                'oid'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'订单ID'),
            ),
            'return'=>array(
            ),
        ),
    ),

    'wxLittleCallback'=>array(
        'title'=>'小程序回调',

        'receive'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'用户消息',
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'pay'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'支付回调',
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),
    ),

    'order'=>array(
        'title'=>'订单',

        'getListByUser'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'用户订单列表',
            'request'=>array(),
            'return'=>array(
            ),
        ),

        'getOneDetail'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'订单详情',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),

        'refund'=>array(
            'ws'=>array('request_code'=>1001,'response_code'=>1002),
            'title'=>'申请退款',
            'request'=>array(
                'id'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'id'),
            ),
            'return'=>array(
            ),
        ),



        'doing'=>array(
            'ws'=>array('request_code'=>1009,'response_code'=>1010),
            'title'=>'下单',
            'request'=>array(
                'share_uid'=>array('type'=>'int','must'=>0,'default'=>100001,'title'=>'商品ID'),
                'userSelAddressId'=>array('type'=>'int','must'=>0,'default'=>100001,'title'=>'购买数量'),
                'gidsNums'=>array('type'=>'int','must'=>1,'default'=>100001,'title'=>'代理ID'),
                'memo'=>array('type'=>'int','must'=>0,'default'=>100001,'title'=>'代理ID'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'任意输出'),
            ),
        ),
    ),

    'login'=>array(
        'title'=>'登陆',


        'wxLittleLoginByCode'=>array(
            'ws'=>array('request_code'=>2005,'response_code'=>2006),
            'title'=>'小程序登陆并注册',
            'request'=>array(
                'code'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'微信给的CODE'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(
                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
                    'session_key'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'微信返回的session'),
                ))
            ),
        ),

//        'logout'=>array(
//            'ws'=>array('request_code'=>2007,'response_code'=>2008),
//            'title'=>'登出',
//            'request'=>array(
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//
//        'third'=>array(
//            'ws'=>array('request_code'=>2007,'response_code'=>2008),
//            'title'=>'3方平台-登陆/3方平台注册',
//            'request'=>array(
//                'type'=>    array('type'=>'int','title'=>'类型，类型，4:微信.6:facebook.9:qq','must'=>1,'default'=>6),
//                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
//                'nickname'=>    array('type'=>'string','title'=>'昵称','default'=>"imZ",'must'=>1),
//                'avatar'=>    array('type'=>'string','title'=>'头像','must'=>1,'default'=>"https://b-ssl.duitang.com/uploads/people/201805/21/20180521200051_imuch.thumb.36_36_c.jpeg"),
//                'sex'=>    array('type'=>'int','title'=>'性别，1男2女','must'=>0,'default'=>2),
//                'unionId'=>    array('type'=>'string','title'=>'3方联合ID，跨应用的','must'=>0,'default'=>2),
//            ),
//            'return'=>array(
//                'array_key_number_one'=>array("must"=>0,'list'=>array(
//                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
//                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
//                ))
//            ),
//        ),
//
//        'cellphoneSMS'=>array(
//            'ws'=>array('request_code'=>2005,'response_code'=>2006),
//            'title'=>'手机-验证码-登陆',
//            'request'=>array(
//                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
//            ),
//            'return'=>array(
//                'array_key_number_one'=>array("must"=>0,'list'=>array(
//                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
//                    'isReg'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为注册，1是0不是'),
//                ))
//            ),
//        ),
//
//        'cellphonePS'=>array(
//            'ws'=>array('request_code'=>2005,'response_code'=>2006),
//            'title'=>'手机-密码-登陆',
//            'request'=>array(
//                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//                'ps'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'密码'),
//            ),
//            'return'=>array(
//                'array_key_number_one'=>array("must"=>0,'list'=>array(
//                    'token'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token'),
//                ))
//            ),
//        ),
//
//        'index'=>array(
//            'ws'=>array('request_code'=>2009,'response_code'=>2010),
//            'title'=>'用户名密码登陆',
//            'request'=>array(
//                'username'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//                'ps'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为分享，加倍奖励~'),
//            ),
//            'return'=>array(
//                'taskId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'任务ID'),
//            ),
//        ),

    ),

    'user'=>array(
        'title'=>'用户',

        'getOneDetail'=>array(
            'title'=>'获取/查看其它，用户基础信息',
            'ws'=>array('request_code'=>4001,'response_code'=>4002),
            'request'=>array(
                'toUid'=>array('type'=>'int','must'=>0,'default'=>100001,'title'=>'要查看的用户UID,为空:代表是查看自己'),
            ),
            'return'=>array(
                'array_key_number_one'=>array("must"=>0,'list'=>array(

                    'uid'=>          array('type'=>'int','must'=>1,'default'=>1,'title'=>'用户ID'),
                    'name'=>        array('type'=>'string','must'=>1,'default'=>1,'title'=>'用户名'),
                    'nickname'=>    array('type'=>'string','must'=>1,'default'=>1,'title'=>'昵称'),
                    'avatar'=>      array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
                    'sex'=>         array('type'=>'int','must'=>1,'default'=>1,'title'=>'1男2女'),
                    'a_time'=>      array('type'=>'int','must'=>1,'default'=>1,'title'=>'注册时间'),
                    'push_status'=> array('type'=>'int','must'=>1,'default'=>1,'title'=>'关闭PUSH,1是2否'),
                    'robot'=>       array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否机器人，1是2否'),
                    'hidden_gps'=>  array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否隐藏GPD，1是2否'),
                    'sign'=>        array('type'=>'string','must'=>1,'default'=>1,'title'=>'个性签名'),
                    'summary'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'简介'),
                    'im_tencent_sign'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'IM用户签名验证'),
                    'invite_code'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'邀请码'),
                    'qq_uid'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'绑定QQ的ID'),
                    'wechat_uid'=>     array('type'=>'string','must'=>1,'default'=>1,'title'=>'绑定微信的ID'),

                    'cellphone'=>   array('type'=>'string','must'=>0,'default'=>1,'title'=>'手机号,如果是查看别人的信息，此值为没有'),
                    'type'=>        array('type'=>'int','must'=>0,'default'=>1,'title'=>'类型,,如果是查看别人的信息，此值为没有'),
                    'isFollow'=>    array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否已关注,1是2否,查看别人信息才有此字段'),
                    'isBlack'=>     array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否被对方拉黑,1是2否,查看别人信息才有此字段'),
                    'selfBlack'=>     array('type'=>'int','must'=>0,'default'=>1,'title'=>'我把对方拉黑,1是2否,查看别人信息才有此字段'),
                    'isBother'=>    array('type'=>'int','must'=>0,'default'=>1,'title'=>'是否被对方免打扰,1是2否,查看别人信息才有此字段'),

                    'invite_uid'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'邀请人的UID'),

                    'developer'=>array('type'=>'int','must'=>1,'default'=>2,'title'=>'1是2否'),


//                    'point'=>       array('type'=>'int','must'=>0,'default'=>1,'title'=>'积分数/token,如果是查看别人的信息，此值为没有'),
//                    'goldcoin'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'金币数'),
//                    'diamond'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'钻石数'),
//                    'email'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'邮箱'),
//                    'vip_endtime'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'VIP到期时间'),
//                    'avatars'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像集'),
//                    'language'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'语言'),
//                    'isFriend'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'是否为好友'),
                ),),

            ),
        ),

        'upInfo'=>array(
            'title'=>'更改基础信息',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'nickname'=>array('type'=>'string','must'=>1,'default'=>"张3必疯",'title'=>'昵称'),
                'ps'=>array('type'=>'string','must'=>1,'default'=>"e10adc3949ba59abbe56e057f20f883e",'title'=>'密码，MD5格式'),
                'sex'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'性别1男2女'),
//                'sign'=>array('type'=>'string','must'=>1,'default'=>"每天必疯3次，疯呀疯",'title'=>'个性签名'),
//                'summary'=>array('type'=>'string','must'=>1,'default'=>'啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊','title'=>'简介'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

        'upAvatar'=>array(
            'title'=>'修改头像',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'avatar'=>array('type'=>'string','must'=>1,'default'=>'二进制','title'=>'头像二进制流'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

        'feedback'=>array(
            'title'=>'发起反馈',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
                'title'=>array('type'=>'string','must'=>1,'default'=>"",'title'=>'标题'),
                'content'=>array('type'=>'string','must'=>1,'default'=>'','title'=>'内容'),
                'mobile'=>array('type'=>'string','must'=>1,'default'=>'','title'=>'手机号'),
                'pic'=>array('type'=>'string','must'=>1,'default'=>'二进制','title'=>'图片'),

            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

        'getCollectList'=>array(
            'title'=>'反馈',
            'ws'=>array('request_code'=>4011,'response_code'=>4012),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'头像'),
            ),
        ),

    ),
    'system'=>array(
        'title'=>'系统',
        'sendSMS'=>array(
            'title'=>'发送短信',
            'ws'=>array('request_code'=>5001,'response_code'=>5002),
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'ruleId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类ID,1:登陆/注册'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'token值'),
            ),
        ),

        'share'=>array(
            'title'=>'分享',
            'ws'=>array('request_code'=>4007,'response_code'=>4008),
            'request'=>array(
                //'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'类型，5分享好友，6分享收益'),
                'type'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类：5：分享给好友奖励，6：晒收入奖励，61：SDK内分享，62：提现分享，72：wechat幸运宝箱，73:qq幸运宝箱，78添加好友，79：sdk调用app分享，80：app直接调用分享，83：分享给站内联系人，95：游戏分享【海外】;96：分享给好友【海外】；97：提现分享【海外】；98：开宝箱【海外】'),
                'gameId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'游戏ID'),
                'platform'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'4微信，9QQ, 6:facebook【海外】;15:messenger【海外】;16:系统内应用分享【海外】'),
                'toUid'=>array('type'=>'int','must'=>0,'default'=>10000000,'title'=>'分享给指定的好友,如果没有可以为空'),
                'platformMethod'=>array('type'=>'string','must'=>0,'default'=>1,'title'=>'1指定人2平台'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),



        'feedback'=>array(
            'title'=>'用户反馈',
            'ws'=>array('request_code'=>5011,'response_code'=>5012),
            'request'=>array(
                'type'=>array('type'=>'int','must'=>1,'default'=>'1','title'=>'分类 ID'),
                'contact'=>array('type'=>'string','must'=>1,'default'=>18812366547,'title'=>'联系方式'),
                'content'=>array('type'=>'string','must'=>1,'default'=>'什么破玩艺，不好用。。。。','title'=>'内容'),
                'pics'=>array('type'=>'string','must'=>1,'default'=>'什么破玩艺，不好用。。。。','title'=>'图片'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'sendEmail'=>array(
//            'title'=>'发送邮件',
//            'ws'=>array('request_code'=>5003,'response_code'=>5004),
//            'request'=>array(
//                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
//                'ruleId'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'分类ID'),
//            ),
//            'return'=>array(
//                'code'=>    array('type'=>'boolean','title'=>'false失败true成功','must'=>1),
//            ),
//        ),

    ),


    'userSafe'=>array(
        'title'=>'用户安全',
        'upPs'=>array(
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'title'=>'手机-验证码-修改密码',
            'request'=>array(
                'ps'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'密码'),
                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'addReadIdAuth'=>array(
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'title'=>'添加实名验证',
//            'request'=>array(
//                'idNo'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'身份证号'),
//                'realName'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'真实姓名'),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),
//
//        'isReadIdAuth'=>array(
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'title'=>'是否添加过实名验证',
//            'request'=>array(
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'1是2否'),
//            ),
//        ),

        'bindCellphone'=>array(
            'title'=>'绑定手机',
            'ws'=>array('request_code'=>1503,'response_code'=>1504),
            'request'=>array(
                'cellphone'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'手机号'),
                'smsCode'=>array('type'=>'string','must'=>1,'default'=>1,'title'=>'短信验证码'),
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

//        'bindThird'=>array(
//            'title'=>'绑定3方平台',
//            'ws'=>array('request_code'=>1503,'response_code'=>1504),
//            'request'=>array(
//                'type'=>    array('type'=>'int','title'=>'类型，6:facebook(详细看getUserTypeDesc接口)','must'=>1,'default'=>6),
//                'uniqueId'=>    array('type'=>'int','title'=>'3方平台用户唯一标识','default'=>"fb123fbi",'must'=>1),
//            ),
//            'return'=>array(
//                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
//            ),
//        ),

    ),

    'msg'=>array(
        'title'=>'站内信',

        'getListByUser'=>array(
            'title'=>'获取用户列表',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),

        'detail'=>array(
            'title'=>'详情',
            'ws'=>array('request_code'=>1401,'response_code'=>1402),
            'request'=>array(
            ),
            'return'=>array(
            ),
        ),


        'unreadNum'=>array(
            'title'=>'未读数',
            'ws'=>array('request_code'=>1403,'response_code'=>1404),
            'request'=>array(
            ),
            'return'=>array(
                'scalar'=>array('type'=>'int','must'=>1,'default'=>1,'title'=>'成功/失败'),
            ),
        ),

    ),


);
//$GLOBALS[APP_NAME]['api'] = $arr;
return $arr;