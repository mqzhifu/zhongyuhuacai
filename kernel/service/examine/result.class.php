<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-5-13
 * Time: 上午10:49
 */
class Table_Examine_Result extends Table
{
    public $_table = "examine_result";
    public $_primarykey = "id";

    public static $_RESULT_SUCCESS = 1; //审批结果  通过
    public static $_RESULT_FAIL = 2; //审批结果  驳回
    public static $_STATUS_FINISH = 300; //审批流完结
    public static $_CODE_OK = 200; //状态码为正常
    public static $_CODE_ERROR = 400; //状态码为错误
    public static $_CODE_POWER = 500; //状态码为权限不足

    public $_error = array(
        "出现错误" => array(
            "remark" => "出现错误",
            "time" => "出现错误"
        )
    );

    public $_group_id = array(
        139 => 138,
        141 => 140,
        143 => 142,
        145 => 144,
        147 => 146,
        149 => 148,
        151 => 150,
        153 => 152,
        158 => 157,
        162 => 161,
    );

    public static $_static = false;

    public static function inst()
    {
        if (false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }

    /**
     * 获取审批类型的查询条件信息
     * @param $examine_type
     * @return string
     */
    public function getTypeWhere($examine_type)
    {
        //判断$type的类型
        if (is_numeric($examine_type)) {
            $where = "examine_type=$examine_type";
        } else {
            $where = "type_key='$examine_type'";
        }
        return $where;
    }

    /**
     * 获取当前用户的审批权限
     * @param $uniton_id 审批的单ID
     * @param $type 审批类型ID或审批类型key
     * @param $product_type 产品类型ID
     * @return array
     * 返回值：array{
     *          code 200:当前用户可以审批 300:审批流已完成 400:出现错误 500:权限不足
     *          msg 信息概要
     *          log 日志信息 {key：描述信息 {remark：驳回或批注信息 time：审批时间}}
     *      }
     */
    public function getExaminePower($uniton_id, $type, $product_type)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type)) {
            return array("code" => 400, "msg" => "请检查getExaminePower方法传入的参数", "log" => $this->_error);
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;
        //查询中间表是否有数据
        $info = Table_Examine_Result::inst()->noCache()->where("oid={$uniton_id} and $where and product_type={$product_type}")->selectOne();

        if ($info) {
            $degree = $info['degree'];
            //查询快照
            $snapshot = Table_Examine_Process_Snapshot::inst()->autoClearCache()->where("oid=$uniton_id and $where and degree=$degree and product_type={$product_type}")->select();

            if (empty($snapshot)) {
                //创建快照
                $examin_type = Table_Examine_Type::inst()->getInfoByType($type);
                if (!Table_Examine_Process_Snapshot::inst()->create_snapshot($uniton_id, $examin_type['id'], $degree, $product_type)) {
                    return array("code" => 400, "msg" => "创建快照时出现错误", "log" => $this->_error);
                }
            }

            //读取日志信息
            $loglist = Table_Examine_Log::inst()->loglist($info['oid'], $type, $info['degree'], $info['product_type']);

            if ($info['status'] == 0) {
                //审批流未完成
                $step = $info['step'];

                //通过快照信息读取当前用户的审批权限
                $process = Table_Examine_Process_Snapshot::inst()->checkExaminePower($uniton_id, $type, $product_type, $step, $info['degree']);
                $process['log'] = $loglist;
                return $process;
            } else {
                //审批流已完成
                if ($info['final_status'] == 1) {
                    return array("code" => 300, "msg" => "通过", "log" => $loglist);
                } else if ($info['final_status'] == 2) {
                    return array("code" => 300, "msg" => "驳回", "log" => $loglist);
                } else {
                    return array("code" => 400, "msg" => "出现错误", "log" => $loglist);
                }
            }
        } else {
            //添加到中间Result表
            $examin_type = Table_Examine_Type::inst()->getInfoByType($type);

            $data['oid'] = $uniton_id;
            $data['examine_type'] = $examin_type['id'];
            $data['type_key'] = $examin_type['type_key'];
            $data['degree'] = 1;
            $data['product_type'] = $product_type;

            $result = Table_Examine_Result::inst()->addData($data)->add();
            if ($result) {
                //创建快照
                if (!Table_Examine_Process_Snapshot::inst()->create_snapshot($uniton_id, $examin_type['id'], 1, $product_type)) {
                    return array("code" => 400, "msg" => "创建快照失败", "log" => $this->_error);
                }

                //发送短信
                $this->sendExamineMessage($uniton_id, $examin_type['id'], 1, 1, $product_type);

                return Table_Examine_Process_Snapshot::inst()->checkExaminePower($uniton_id, $type, $product_type);
            } else {
                return array("code" => 400, "msg" => "出现错误", "log" => $this->_error);
            }
        }
    }

    /**
     * 获取当前用户的审批权限（新）
     * @param $uniton_id
     * @param $type
     * @param $product_type
     * @param $user
     * @return array
     */
    public function getExaminePowerNew($uniton_id, $type, $product_type, $user)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type)) {
            return array("code" => 400, "msg" => "请检查getExaminePower方法传入的参数", "log" => $this->_error);
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;
        //查询中间表是否有数据
        $info = Table_Examine_Result::inst()->noCache()->where("oid={$uniton_id} and $where and product_type={$product_type}")->selectOne();
        if ($info) {
            $degree = $info['degree'];
            //查询快照
            $snapshot = Table_Examine_Process_Snapshot::inst()->autoClearCache()->where("oid=$uniton_id and $where and degree=$degree and product_type={$product_type}")->select();

            if (empty($snapshot)) {
                //创建快照
                $examin_type = Table_Examine_Type::inst()->getInfoByType($type);
                if (!Table_Examine_Process_Snapshot::inst()->create_snapshot_new($uniton_id, $examin_type['id'], $degree, $product_type, $user)) {
                    return array("code" => 400, "msg" => "创建快照时出现错误", "log" => $this->_error);
                }
            }

            //读取日志信息
            $loglist = Table_Examine_Log::inst()->loglist($info['oid'], $type, $info['degree'], $info['product_type']);

            if ($info['status'] == 0) {
                //审批流未完成
                $step = $info['step'];

                //通过快照信息读取当前用户的审批权限
                $process = Table_Examine_Process_Snapshot::inst()->checkExaminePowerNew($uniton_id, $type, $product_type, $step, $info['degree']);
                $process['log'] = $loglist;
                return $process;
            } else {
                //审批流已完成
                if ($info['final_status'] == 1) {
                    return array("code" => 300, "msg" => "通过", "log" => $loglist);
                } else if ($info['final_status'] == 2) {
                    return array("code" => 300, "msg" => "驳回", "log" => $loglist);
                } else {
                    return array("code" => 400, "msg" => "出现错误", "log" => $loglist);
                }
            }
        } else {
            //添加到中间Result表
            $examin_type = Table_Examine_Type::inst()->getInfoByType($type);

            $user_info = Table_Admin::inst()->autoClearCache()->getInfo($user);
            if (empty($user_info)) {
                return array("code" => 400, "msg" => "出现错误，未查询到相关申请人信息", "log" => $this->_error);
            }

            $data['oid'] = $uniton_id;
            $data['examine_type'] = $examin_type['id'];
            $data['type_key'] = $examin_type['type_key'];
            $data['degree'] = 1;
            $data['product_type'] = $product_type;
            $data['applicant'] = $user;

            $result = Table_Examine_Result::inst()->addData($data)->add();
            if ($result) {
                //创建快照
                if (!Table_Examine_Process_Snapshot::inst()->create_snapshot_new($uniton_id, $examin_type['id'], 1, $product_type, $user)) {
                    return array("code" => 400, "msg" => "创建快照失败", "log" => $this->_error);
                }

                $p = Table_Examine_People_Snapshot::inst()->getExaminePeople($examin_type['id'], 1, $product_type, 1, $uniton_id);
                //总监自己提交的审批，就不需要自己再审核了，直接到下一步领导去审
                $group = Common::$_admin_groupid;
                if (!is_array($group)) {
                    $group = explode(",", $group);
                }
                if (!in_array($p['ext_id'], $group)) {
                    //发送短信
                    $this->sendExamineMessage($uniton_id, $examin_type['id'], 1, 1, $product_type);
                }

                return Table_Examine_Process_Snapshot::inst()->checkExaminePowerNew($uniton_id, $type, $product_type);
            } else {
                return array("code" => 400, "msg" => "出现错误", "log" => $this->_error);
            }
        }
    }

    /**
     * 审批操作
     * @param $uniton_id 审批的单ID
     * @param $type 审批类型ID或审批类型key
     * @param int $product_type 产品类型ID
     * @param $status 审批状态 1:通过 2:不通过
     * @param string $remark 驳回或批注内容
     * @param string $param 自定义参数
     * @return array
     */
    public function examine($uniton_id, $type, $product_type = 142, $status, $remark = "", $param = "")
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($status)) {
            return array("code" => 400, "msg" => "请检查examine方法传入的参数", "log" => $this->_error);
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        //验证当前用户审批的权限
        $power = Table_Examine_Result::inst()->getExaminePowerNew($uniton_id, $type, $product_type, Common::$_adminid);
        if ($power['code'] == 200) {
            //权限匹配成功，开始审批

            //开始事务
            Db::exec("START TRANSACTION");
            try {
                //通过单ID和审批类型 查找中间表信息
                $info = Db::getOne("select id,oid,examine_type,product_type,type_key,step,degree,status,final_status,version from examine_result where oid = {$uniton_id} and $where and product_type=$product_type for update");
                $degree = $info['degree']; //获取标识次数

                //读取版本信息，防止并发
                $version = $info['version'];

                //读取审批流快照
                $snapshot = Db::getOne("select id,oid,examine_type,type_key,step,product_type,approver_type,before_hook,later_hook,degree from examine_process_snapshot where oid={$uniton_id} and product_type={$product_type} and degree={$degree} and $where for update");

                if ($snapshot) {
                    $hook = new Hook();
                    //调用前置钩子
                    $before_hook = $snapshot['before_hook'];
                    if ($before_hook) {
                        if (method_exists($hook, "$before_hook")) {
                            //执行、返回结果
                            if (!$hook->$before_hook()) {
                                throw new Exception("前置钩子 $before_hook 执行失败！");
                            }
                        } else {
                            throw new Exception("未找到前置钩子 $before_hook ！");
                        }
                    }

                    if ($status == 1) {
                        //审批通过，不执行任何操作
                        //流程的转运逻辑 全部在后置钩子当中
                        $result = 1;
                    } else {
                        //审批不通过
                        $new_version = $version + 1;
                        $result_data["version"] = $new_version;
                        $result_data['status'] = 1;
                        $result_data['final_status'] = $status;
                        $result = Db::exec("UPDATE examine_result set status=1,final_status={$status},version={$new_version} where version=$version and id=" . $info['id']);

                    }

                    if (!$result || $result < 0) {
                        throw new Exception("修改审批中间表失败！");
                    }

                    //添加审批日志
                    $log_data['oid'] = $uniton_id;
                    $log_data['examine_type'] = $info['examine_type'];
                    $log_data['type_key'] = $info['type_key'];
                    $log_data['product_type'] = $product_type;
                    $log_data['step'] = $info['step'];
                    $log_data['approver'] = Common::$_adminid;
                    $log_data['group_id'] = is_array(Common::$_admin_groupid) ? implode(",", Common::$_admin_groupid) : Common::$_admin_groupid;
                    $log_data['examine_status'] = $status;
                    $log_data['addtime'] = time();
                    $log_data['remark'] = $remark;
                    $log_data['degree'] = $info['degree'];
                    $log_data['param'] = $param;
                    $log = Table_Examine_Log::inst()->addData($log_data)->add();

                    if (!$log) {
                        throw new Exception("添加日志失败！");
                    }

                    if ($log && $result) {
                        //调用后置钩子
                        $later_hook = $snapshot['later_hook'];
                        if ($later_hook) {
                            if (method_exists($hook, "$later_hook")) {
                                $flag = $hook->$later_hook($param);
                                if (!$flag) {
                                    throw new Exception("后置钩子 $later_hook 执行失败！");
                                } else {
                                    Db::exec("COMMIT");
                                    return array("code" => 200, "msg" => "审批成功！");
                                }
                            } else {
                                throw new Exception("未找到后置钩子 $later_hook ！");
                            }
                        } else {
                            Db::exec("COMMIT");
                            return array("code" => 200, "msg" => "审批成功！");
                        }
                    } else {
                        throw new Exception("未知错误！");
                    }
                } else {
                    throw new Exception("读取审批流错误！");
                }
            } catch (Exception $e) {
                Db::exec("ROLLBACK");
                $msg = $e->getMessage();
                return array("code" => 400, "msg" => "出现错误：$msg");
            }
        } else {
            return $power;
        }
    }

    /**
     * 发起人修改审批单
     * 注：在前台发起人修改已驳回审批单成功后，请调用此方法
     * @param $uniton_id 审批的单ID
     * @param $type 审批类型ID或审批类型key
     * @param int $product_type 产品类型ID
     * @return bool true：成功 false：失败
     */
    public function update_result($uniton_id, $type, $product_type = 142)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type)) {
            return false;
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $type_info = Table_Examine_Type::inst()->getInfoByType($type);

        $where = "oid=$uniton_id and $where and product_type=$product_type";

        $info = Table_Examine_Result::inst()->noCache()->field("id,oid,examine_type,type_key,product_type,step,degree,status,final_status,version")->where($where)->selectOne();
        $degree = $info['degree'];

        //删除result表的相关数据
        Table_Examine_Result::inst()->where($where)->del();

        //添加相关数据
        $data['oid'] = $uniton_id;
        $data['examine_type'] = $type_info['id'];
        $data['degree'] = $degree + 1;
        $data['product_type'] = $product_type;
        $data['type_key'] = $type_info['type_key'];

        $result = Table_Examine_Result::inst()->addData($data)->add();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改Result第几步
     * 注：此方法在层级审批中调用
     * @param $uniton_id 审批单ID
     * @param $type 审批类型ID或审批类型key
     * @param $step 第几步
     * @param int $product_type 产品类型ID
     * @return array
     */
    public function update_step($uniton_id, $type, $step, $product_type = 142)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($step)) {
            return array("code" => 400, "msg" => "请检查update_step方法传入的参数");
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        try {
            $info = Db::getOne("select id,oid,examine_type,type_key,product_type,step,degree,status,final_status,version from examine_result where oid={$uniton_id} and $where and product_type={$product_type}");
            if ($info) {
                $num = $step - $info['step'];
                if ($num != 1) {
                    //跳过
                    for ($i = 1; $i < $num; $i++) {
                        $info_step = $info['step'] + $i;
                        $oid = $info['oid'];
                        $examine_type = $info['examine_type'];
                        $type_key = $info['type_key'];
                        $info_product_type = $info['product_type'];
                        $degree = $info['degree'];
                        $time = time();

                        $insert = "INSERT INTO examine_log(oid,examine_type,type_key,product_type,step,approver,group_id,examine_status,degree,remark,param,addtime,is_jump)
VALUES($oid,$examine_type,'$type_key',$info_product_type,$info_step,0,0,1,$degree,'系统智能忽略','',$time,1)";
                        Db::exec($insert);
                    }
                }

                $sql = "UPDATE examine_result SET step={$step} WHERE oid={$uniton_id} and $where and product_type={$product_type}";
                $result = Db::exec($sql);
                if ($result && $result > 0) {
                    //发送短信
                    $this->sendExamineMessage($uniton_id, $info['examine_type'], $step, $info['degree'], $product_type);
                    return array("code" => 200, "msg" => "更新成功！");
                } else {
                    throw new Exception("更新失败！");
                }
            } else {
                throw new Exception("未找到相关数据！");
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return array("code" => 400, "msg" => $msg);
        }
    }





    /**
     * 修改Result第几步
     * 注：此方法在层级审批中调用
     * @param $uniton_id 审批单ID
     * @param $type 审批类型ID或审批类型key
     * @param $step 第几步
     * @param int $product_type 产品类型ID
     * @return array
     */
    public function update_last_step($uniton_id, $type, $step, $product_type = 142)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($step)) {
            return array("code" => 400, "msg" => "请检查update_step方法传入的参数");
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        try {
            $info = Db::getOne("select id,oid,examine_type,type_key,product_type,step,degree,status,final_status,version from examine_result where oid={$uniton_id} and $where and product_type={$product_type}");
            if ($info) {
                $num = $step - $info['step'];
               // if ($num != 1) {
                    //跳过
                  //  for ($i = 1; $i < $num; $i++) {
                        $info_step = $step;
                        $oid = $info['oid'];
                        $examine_type = $info['examine_type'];
                        $type_key = $info['type_key'];
                        $info_product_type = $info['product_type'];
                        $degree = $info['degree'];
                        $time = time();

                        $insert = "INSERT INTO examine_log(oid,examine_type,type_key,product_type,step,approver,group_id,examine_status,degree,remark,param,addtime,is_jump)
VALUES($oid,$examine_type,'$type_key',$info_product_type,$info_step,0,0,1,$degree,'系统智能忽略','',$time,1)";
                        Db::exec($insert);
                //    }
              //  }

                $sql = "UPDATE examine_result SET step={$step} WHERE oid={$uniton_id} and $where and product_type={$product_type}";
                $result = Db::exec($sql);
                if ($result && $result > 0) {
                    //发送短信
                    $this->sendExamineMessage($uniton_id, $info['examine_type'], $step, $info['degree'], $product_type);
                    return array("code" => 200, "msg" => "更新成功！");
                } else {
                    throw new Exception("更新失败！");
                }
            } else {
                throw new Exception("未找到相关数据！");
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return array("code" => 400, "msg" => $msg);
        }
    }









    /**
     * 审批流通知短信
     * @param $uniton_id
     * @param $type
     * @param $step
     * @param int $degree
     * @param int $product_type
     * @return bool
     */
    public function sendExamineMessage($uniton_id, $type, $step, $degree = 1, $product_type = 142)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($step)) {
            return false;
        }

        //供应商付款，用户退款，调拨，项目结算 才需要发送短信
        $type_array = [
            Table_Examine_Type::$type_item_payment,
            Table_Examine_Type::$type_finance_user_refund,
            Table_Examine_Type::$type_item_allocate,
            Table_Examine_Type::$type_item,
        ];

        //供应商付款，用户退款 才需要发送操作通知
        $msg_type_array = [
            Table_Examine_Type::$type_item_payment,
            Table_Examine_Type::$type_finance_user_refund,
        ];

        //类型中文名称数组（其实可以再去查表，但是觉得麻烦）
        $type_str_array = [
            1 => "供应商付款",
            3 => "用户退款",
            6 => "调拨",
            7 => "项目结算",
        ];

        $is_msg = true;

        //类型对应URL数组
        $type_url_array = [
            1 => ADMIN_URL . "/financial/paymentlist.php?action=paymentshow&id=$uniton_id",
            3 => ADMIN_URL . "/financial/userRefund.php?userRefundApplicationForm=true&ur_id=$uniton_id",
            6 => ADMIN_URL . "/financial/allocatelist.php?action=allocateshow&id=$uniton_id",
            7 => ADMIN_URL . "/financial/clearinglist.php?action=preview&id=$uniton_id",
        ];

        $result = Table_Examine_Result::inst()
            ->autoClearCache()
            ->where("oid={$uniton_id} and examine_type={$type} and product_type={$product_type} and degree={$degree}")
            ->selectOne();

        $applicant = $result['applicant'];
        $admin_info = Table_Admin::inst()->getInfo($applicant);
        if ($admin_info) {
            //屏蔽测试账号，测试账号申请的单子不需要发送短信
            $applicant_name = $admin_info['realname'];
            if (strstr($applicant_name, "测试")) {
                $is_msg = false;
            }
        }

        //判断当前是否为财务，财务不需要短信提醒
        $people = Table_Examine_People_Snapshot::inst()->getExaminePeople($type, $step, $product_type, $degree, $uniton_id);

        $ext_id = $people['ext_id'];
        if ($ext_id == 123) {
            //财务需要向申请者发送操作通知
            if (in_array($type, $msg_type_array)) {
                if ($type == Table_Examine_Type::$type_item_payment) {
                    $operate_type = 1;
                    $item_payment = Table_Item_Payment::inst()->getInfo($uniton_id);
                    $item_id = $item_payment['item_id'];
                    $item_info = Table_Item::inst()->getInfo($item_id);
                    $item_code = $item_info['code'];
                    $name = $item_payment['name'];
                    $rmb_total = $item_payment['rmb_total'];

                    $msg_title = "有待打印付款申请单，项目号：{$item_code}";
                    $msg_content = "您有一条有待打印付款申请单，付款ID: {$uniton_id}，付款金额:{$rmb_total}，付款对象：{$name}。<br/>所属项目号：<a target=\"_blank\" href=\"" . ADMIN_URL . "/item/item.php?action=show&id={$item_id}\">{$item_code}</a>。<br/>请及时打印支出单并提交给财务。";
                } else {
                    $operate_type = 2;
                    $user_refund = Table_Finance_User_Refund::inst()->getInfo($uniton_id);
                    $oid = $user_refund['oid'];
                    $payment_target = $user_refund['payment_target'];
                    $money = $user_refund['money'];

                    $item_order = Table_Item_Order::inst()->autoClearCache()->field("item_id")->where("order_id=$oid")->selectOne();
                    $item_id = $item_order['item_id'];

                    $item_info = Table_Item::inst()->getInfo($item_id);
                    $item_code = $item_info['code'];

                    $msg_title = "有待打印用户退款申请单，项目号：{$item_code}";
                    $msg_content = "您有一条有待打印用户退款申请单，请及时打印支出单并提交给财务。<br/>付款ID:{$uniton_id}，付款金额:{$money}，付款对象:{$payment_target}。<br/>所属项目号：<a target=\"_blank\" href=\"" . ADMIN_URL . "/item/item.php?action=show&id={$item_id}\">{$item_code}</a>。";
                }

                Table_Admin_Msggang::inst()->addOperateToAdmin($applicant, $item_code, $operate_type, $msg_title, $msg_content);
            }
        }

        if ($is_msg && in_array($type, $type_array)) {
            $p = Table_Examine_People_Snapshot::inst()->getExaminePeople($type, 1, $product_type, $degree, $uniton_id);
            if ($ext_id == 135) {
                //特殊情况：CEO组目前有两个账号，“综合营销组”和“综合营销组总监组”提交的单子，由任总审核。其余组提交的单子由蒋总审核。
                if ($p['ext_id'] == 150) {
                    //任斌审批
                    $admin_id = [542];
                } else {
                    //蒋松涛审批
                    $admin_id = [498];
                }
            } else {
                $admin = Table_Admin::inst()->autoClearCache()->field("id")->where("FIND_IN_SET($ext_id,groupid)")->select();
                if ($admin) {
                    $admin_id = array_column($admin, "id");
                } else {
                    return false;
                }
            }

            $admin_id = implode(",", $admin_id);
            //查询手机号码
            $mobile = Table_Admin::inst()->autoClearCache()->field("mobile")->where("id in ($admin_id)")->select();
            if ($mobile) {
                $config = Table_Sms_Config::inst()->autoClearCache()->where("type='uthing_oa_msg'")->selectOne();
                if ($config) {
                    $content = $config['send_msg'];
                } else {
                    $content = "有#type#申请需要您审批，申请人：#people#，审批地址：#url#";
                }

                if ($admin_info) {
                    $applicant = $admin_info['realname'];
                } else {
                    $applicant = "无";
                }

                $mobile_list = array_column($mobile, "mobile");
                $mobile_list = array_filter($mobile_list);
                if ($mobile_list) {
                    $msg = str_replace("#type#", $type_str_array[$type], $content);
                    $msg = str_replace("#people#", $applicant, $msg);
                    $msg = str_replace("#url#", $type_url_array[$type], $msg);

                    Sms::inst()->newSendSms($mobile_list, $msg, "uthing_oa_msg");
                } else {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 审核最终步，更新Result状态
     * @param $uniton_id 审批单ID
     * @param $type 审批类型ID或审批类型key
     * @param int $product_type 产品类型ID
     * @param int $status 状态 1：通过 2：不通过
     * @return array
     */
    public function update_result_status($uniton_id, $type, $product_type = 142, $status = 1)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type)) {
            return array("code" => 400, "msg" => "请检查update_result_status方法传入的参数");
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        try {
            $info = Db::getOne("select id,oid,examine_type,product_type,step,degree,status,final_status,version from examine_result where oid={$uniton_id} and $where and product_type={$product_type}");
            if ($info) {
                $sql = "UPDATE examine_result SET status=1,final_status={$status} WHERE oid={$uniton_id} and $where and product_type={$product_type}";
                $result = Db::exec($sql);
                if ($result && $result > 0) {
                    return array("code" => 200, "msg" => "更新成功！");
                } else {
                    throw new Exception("更新失败！");
                }
            } else {
                throw new Exception("未找到相关数据！");
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return array("code" => 400, "msg" => "$msg");
        }
    }

    /**
     * 获取当前第几步
     * @param $uniton_id 审批单ID
     * @param $type 审批类型ID或审批类型key
     * @param $product_type 产品类型ID
     * @return array
     */
    public function getStep($uniton_id, $type, $product_type)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type)) {
            return array("code" => 400, "msg" => "请检查update_result_status方法传入的参数");
        }

        $where = $this->getTypeWhere($type);

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $info = Table_Examine_Result::inst()->noCache()->field("id,oid,examine_type,type_key,product_type,step,degree,status,final_status,version")->where("oid=$uniton_id and $where and product_type=$product_type")->selectOne();
        $step = $info['step'];
        $degree = $info['degree'];
        if ($info) {
            //判断是否为最后一步
            $process = Table_Examine_Process_Snapshot::inst()->noCache()->where("oid=$uniton_id and $where and degree=$degree and product_type=$product_type")->select();
            $array = array_column($process, "step");
            $max = max($array);
            if ($step < $max) {
                return array("code" => 200, "step" => $step);
            } else {
                return array("code" => 300, "step" => $step);
            }
        } else {
            return array("code" => 400, "msg" => "出现错误");
        }
    }

    /**
     * 通过 用户组ID，统计类型 查询 待审核的信息
     * @param bool $group_id 用户组
     * @param bool $type_id 类型  关联examine_type 中数据
     * @return bool
     */
    public function verifyInfoByGroup($group_id = false, $type_id = false)
    {
        $examine_type = Table_Examine_Type::inst()->getInfoByType($type_id);

        if (!$group_id) {
            return false;
        }
        //查询该组的所有审批流ID
        $where = array(
            "ext_id in($group_id)"
        );
        $Examine_People_Snapshot = Table_Examine_People_Snapshot::inst()->autoClearCache()->field('process_snapshot_id')->where($where)->select();
        if (!$Examine_People_Snapshot) {
            return false;
        }

        $process_snapshot_id = '';
        foreach ($Examine_People_Snapshot as $_k => $_v) {
            $process_snapshot_id .= ',' . $_v['process_snapshot_id'];
        }
        $process_snapshot_id = substr($process_snapshot_id, 1);

        //获取所有审核流信息
        $where = array(
            "a.id in($process_snapshot_id) and er.status=0",
        );

        //根据类型进行统计
        if ($type_id) {
            $where[] = "er.examine_type = " . $examine_type['id'];
        }
        $Examine_Process_Snapshot = Table_Examine_Process_Snapshot::inst()->noCache()->join('inner', 'examine_result as er', 'a.examine_type=er.examine_type and a.step=er.step and a.degree=er.degree and a.oid = er.oid and a.product_type = er.product_type')->field('er.oid as id')->where($where)->select();
        //print_r(Table_Examine_Process_Snapshot::inst()->getLastQuerySql());die;
        return $Examine_Process_Snapshot;
    }

    /**
     * 在生成调拨单时，通过此方法实时生成审批信息(旧调拨)
     * @param $uniton_id 单ID
     * @param $type 审批类型
     * @param int $product_type 产品类型
     * @param $out_group 调出部门组ID
     * @param $fold_group 调入部门组ID
     * @return bool
     */
    public function examine_allocate_old($uniton_id, $type, $product_type = 142, $out_group, $fold_group)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($out_group) || empty($fold_group)) {
            return false;
        }

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $admin_group = Common::$_admin_groupid;
        if (!is_array($admin_group)) {
            $admin_group = explode(",", $admin_group);
        }
        $is_out_self = false; //用于标记当前申请人是不是调出部门的总监

        //寻找调出部门的上级
        $out_group_info = Table_Admin_Department::inst()->autoClearCache()->field("parent_id")->where("group_id=$out_group")->order("level DESC")->selectOne();
        if ($out_group_info) {
            $out_group_parent = $out_group_info['parent_id'];
            $out_group_zj = Table_Admin_Department::inst()->getInfo($out_group_parent)['group_id'];
        } else {
            return false;
        }

        //判断获取到的总监组 是否是当前申请人所在的组
        if (in_array($out_group_zj, $admin_group)) {
            $is_out_self = true;
        }

        //去寻找调入部门的上级
        $fold_group_info = Table_Admin_Department::inst()->autoClearCache()->field("parent_id")->where("group_id=$fold_group")->order("level DESC")->selectOne();
        if ($fold_group_info) {
            $fold_group_parent = $fold_group_info['parent_id'];
            $fold_group_zj = Table_Admin_Department::inst()->getInfo($fold_group_parent)['group_id'];
        } else {
            return false;
        }

        //开始事务
        Db::exec("START TRANSACTION");
        try {
            $result1 = 0;
            $result2 = 0;

            //添加到中间Result表
            $examin_type = Table_Examine_Type::inst()->getInfoByType($type);
            if (empty($examin_type)) {
                throw new Exception("未查询到相关的审批类型");
            }

            //查询配置的审批流信息
            $process_where = "examine_type=" . $examin_type['id'] . " and product_type=$product_type";
            $process_list = Table_Examine_Process::inst()->autoClearCache()->where($process_where)->select();

            $min_step = 0;
            if ($process_list) {
                foreach ($process_list as $k => $v) {
                    $id = $v['id'];
                    $step = $v['step'];

                    if (($step == 1 && $is_out_self) || ($step == 1 && $out_group_zj == $fold_group_zj)) {
                        //如果是第一步，并且调出部门总监是当前申请人，则不需要审批第一步
                        //或者 调入、调出是同一个审批
                        continue;
                    }

                    /*if ($step == 2 && $is_fold_self) {
                        //如果是第二步，并且调入部门总监是当前申请人，则不需要审批第二步
                        continue;
                    }*/

                    //添加审批流快照
                    $process_data['oid'] = $uniton_id;
                    $process_data['examine_type'] = $examin_type['id'];
                    $process_data['type_key'] = $examin_type['type_key'];
                    $process_data['step'] = $step;
                    $process_data['product_type'] = $product_type;
                    $process_data['approver_type'] = 1;
                    $process_data['before_hook'] = $v['before_hook'];
                    $process_data['later_hook'] = $v['later_hook'];

                    if (!Table_Examine_Process_Snapshot::inst()->addData($process_data)->add()) {
                        throw new Exception("添加审批流快照出现错误");
                    }

                    $result1 += 1;

                    $process_snapshot_id = Db::insertId();

                    if ($step == 1) {
                        $min_step = 1;
                        //第一步，添加调出部门总监审核
                        $people_data['process_snapshot_id'] = $process_snapshot_id;
                        $people_data['ext_id'] = $out_group_zj;
                        if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                            throw new Exception("添加审批流快照出现错误");
                        }
                        $result2 += 1;
                    } elseif ($step == 2) {
                        if (!$min_step) {
                            $min_step = 2;
                        }
                        //第二步，添加调入部门总监审核
                        $people_data['process_snapshot_id'] = $process_snapshot_id;
                        $people_data['ext_id'] = $fold_group_zj;
                        if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                            throw new Exception("添加审批流快照出现错误");
                        }
                        $result2 += 1;
                    } else {
                        if (!$min_step) {
                            $min_step = $step;
                        }
                        //其他，添加审批人员快照表
                        $people = Table_Examine_People::inst()->autoClearCache()->where("process_id=$id")->select();
                        foreach ($people as $pk => $pv) {
                            $people_data['process_snapshot_id'] = $process_snapshot_id;
                            $people_data['ext_id'] = $pv['ext_id'];
                            if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                                throw new Exception("添加审批流快照出现错误");
                            }
                            $result2 += 1;
                        }
                    }
                }

                $result_data['oid'] = $uniton_id;
                $result_data['examine_type'] = $examin_type['id'];
                $result_data['type_key'] = $examin_type['type_key'];
                $result_data['step'] = $min_step;
                $result_data['product_type'] = $product_type;

                $result = Table_Examine_Result::inst()->addData($result_data)->add();
            } else {
                throw new Exception("未找到相关审批信息");
            }

            if ($result && $result1 && $result2) {
                Db::exec("COMMIT");
                return true;
            } else {
                throw new Exception("更新出现错误");
            }
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }





    /**
     * 在生成调拨单时，通过此方法实时生成审批信息(新调拨)
     * @param $uniton_id 单ID
     * @param $type 审批类型
     * @param int $product_type 产品类型
     * @param $out_group 调出部门组ID
     * @param $fold_group 调入部门组ID
     * @return bool
     */
    public function examine_allocate($uniton_id, $type, $product_type = 142, $out_group, $fold_group,$admin_id)
    {
        //必要数据验证
        if (empty($uniton_id) || empty($type) || empty($out_group) || empty($fold_group)) {
            return false;
        }

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $admin_group = Common::$_admin_groupid;
        if (!is_array($admin_group)) {
            $admin_group = explode(",", $admin_group);
        }
        $is_out_self = false; //用于标记当前申请人是不是调出部门的总监

        //寻找调出部门的上级
        $out_group_info = Table_Admin_Department::inst()->autoClearCache()->field("parent_id")->where("group_id=$out_group")->order("level DESC")->selectOne();
        if ($out_group_info) {
            $out_group_parent = $out_group_info['parent_id'];
            $out_group_zj = Table_Admin_Department::inst()->getInfo($out_group_parent)['group_id'];
        } else {
            return false;
        }

        //判断获取到的总监组 是否是当前申请人所在的组
        if (in_array($out_group_zj, $admin_group)) {
            $is_out_self = true;
        }

        //去寻找调入部门的上级
        $fold_group_info = Table_Admin_Department::inst()->autoClearCache()->field("parent_id")->where("group_id=$fold_group")->order("level DESC")->selectOne();
        if ($fold_group_info) {
            $fold_group_parent = $fold_group_info['parent_id'];
            $fold_group_zj = Table_Admin_Department::inst()->getInfo($fold_group_parent)['group_id'];
        } else {
            return false;
        }

        //开始事务
        Db::exec("START TRANSACTION");
        try {
            $result1 = 0;
            $result2 = 0;

            //添加到中间Result表
            $examin_type = Table_Examine_Type::inst()->getInfoByType($type);
            if (empty($examin_type)) {
                throw new Exception("未查询到相关的审批类型");
            }

            //查询配置的审批流信息
            //$process_where = "examine_type=" . $examin_type['id'] . " and product_type=$product_type";
            //$process_list = Table_Examine_Process::inst()->autoClearCache()->where($process_where)->select();
            $process_where = "examine_type=" . $examin_type['id'];
            $process_list = Table_Examine_Process_Improve::inst()->autoClearCache()->where($process_where)->order('id DESC')->select();
//var_dump($process_list);die;
            $min_step = 0;
            $step = 0;
            if ($process_list) {
                foreach ($process_list as $k => $v) {
                    //$id = $v['id'];
                    //$step = $v['step'];
                    $step += 1;
                    if (($step == 1 && $is_out_self) || ($step == 1 && $out_group_zj == $fold_group_zj)) {
                        //如果是第一步，并且调出部门总监是当前申请人，则不需要审批第一步
                        //或者 调入、调出是同一个审批
                        continue;
                    }

                    /*if ($step == 2 && $is_fold_self) {
                        //如果是第二步，并且调入部门总监是当前申请人，则不需要审批第二步
                        continue;
                    }*/

                    //添加审批流快照
                    $process_data['oid'] = $uniton_id;
                    $process_data['examine_type'] = $examin_type['id'];
                    $process_data['type_key'] = $examin_type['type_key'];
                    $process_data['step'] = $step;
                    $process_data['product_type'] = $product_type;
                    $process_data['approver_type'] = 1;
                    $process_data['before_hook'] = $v['before_hook'];
                    $process_data['later_hook'] = $v['later_hook'];
                    if (!Table_Examine_Process_Snapshot::inst()->addData($process_data)->add()) {
                        throw new Exception("添加审批流快照出现错误");
                    }

                    $result1 += 1;

                    $process_snapshot_id = Db::insertId();
                    if ($step == 1) {
                        $min_step = 1;
                        //第一步，添加调出部门总监审核
                        $people_data['process_snapshot_id'] = $process_snapshot_id;
                        $people_data['ext_id'] = $out_group_zj;
                        if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                            throw new Exception("添加审批流快照出现错误");
                        }
                        $result2 += 1;
                    } elseif ($step == 2) {
                        if (!$min_step) {
                            $min_step = 2;
                        }
                        //第二步，添加调入部门总监审核
                        $people_data['process_snapshot_id'] = $process_snapshot_id;
                        $people_data['ext_id'] = $fold_group_zj;
                        if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                            throw new Exception("添加审批流快照出现错误");
                        }
                        $result2 += 1;
                    } else {
                        if (!$min_step) {
                            $min_step = $step;
                        }
                        //其他，添加审批人员快照表
                        //$people = Table_Examine_People::inst()->autoClearCache()->where("process_id=$id")->select();
                        //foreach ($people as $pk => $pv) {
                            $people_data['process_snapshot_id'] = $process_snapshot_id;
                            $people_data['ext_id'] = $v['group_id'];
                            if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                                throw new Exception("添加审批流快照出现错误");
                            }
                            $result2 += 1;
                        //}
                    }
                }

                $result_data['oid'] = $uniton_id;
                $result_data['examine_type'] = $examin_type['id'];
                $result_data['type_key'] = $examin_type['type_key'];
                $result_data['step'] = $min_step;
                $result_data['product_type'] = $product_type;
                $result_data['applicant'] = $admin_id;

                $result = Table_Examine_Result::inst()->addData($result_data)->add();
            } else {
                throw new Exception("未找到相关审批信息");
            }

            if ($result && $result1 && $result2) {
                Db::exec("COMMIT");
                return true;
            } else {
                throw new Exception("更新出现错误");
            }
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }







}