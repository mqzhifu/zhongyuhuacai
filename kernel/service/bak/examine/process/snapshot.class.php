<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-6-6
 * Time: 上午10:41
 */
class Table_Examine_Process_Snapshot extends Table
{
    public $_table = "examine_process_snapshot";
    public $_primarykey = "id";

    public static $_static = false;

    public static function inst()
    {
        if (false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }

    /**
     * 创建快照
     * @param $oid
     * @param $type
     * @param int $degree
     * @param $product
     * @return bool
     */
    public function create_snapshot($oid, $type, $degree = 1, $product)
    {
        $examin_type = Table_Examine_Type::inst()->autoClearCache()->getInfo($type);

        //如果$product_type为空或0 默认为142
        $product = empty($product) ? 142 : $product;

        Db::exec("START TRANSACTION");
        try {
            $result1 = 0;
            $result2 = 0;

            $process_list = Table_Examine_Process::inst()->autoClearCache()->where("examine_type={$type} and product_type={$product}")->select();
            if ($process_list) {
                foreach ($process_list as $k => $v) {
                    $id = $v['id'];
                    //添加审批流快照表
                    $data['oid'] = $oid;
                    $data['examine_type'] = $type;
                    $data['type_key'] = $examin_type['type_key'];
                    $data['step'] = $v['step'];
                    $data['product_type'] = $product;
                    $data['approver_type'] = $v['approver_type'];
                    $data['before_hook'] = $v['before_hook'];
                    $data['later_hook'] = $v['later_hook'];
                    $data['degree'] = $degree;
                    if (!Table_Examine_Process_Snapshot::inst()->addData($data)->add()) {
                        throw new Exception("添加审批流快照出现错误");
                    }
                    $result1 += 1;

                    $process_snapshot_id = Db::insertId();

                    //添加审批人员快照表
                    $people = Table_Examine_People::inst()->autoClearCache()->where("process_id=$id")->select();
                    if ($people) {
                        foreach ($people as $pk => $pv) {
                            $people_data['process_snapshot_id'] = $process_snapshot_id;
                            $people_data['ext_id'] = $pv['ext_id'];
                            if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                                throw new Exception("添加审批流快照出现错误");
                            }
                            $result2 += 1;
                        }
                    } else {
                        throw new Exception("未查询到相关的审批人员");
                    }
                }
            } else {
                throw new Exception("未查询到相关的审批流");
            }

            if ($result1 && $result2) {
                Db::exec("COMMIT");
                return true;
            } else {
                throw new Exception("出现错误");
            }
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }

    /**
     * 创建审批流快照(新)
     * @param $oid
     * @param $type
     * @param int $degree
     * @param $product_type
     * @param $user
     * @return bool
     */
    public function create_snapshot_new($oid, $type, $degree = 1, $product_type, $user)
    {
        $examin_type = Table_Examine_Type::inst()->autoClearCache()->getInfo($type);

        //查询申请人所在的组
        $user_info = Table_Admin::inst()->autoClearCache()->getInfo($user);
        if (empty($user_info)) {
            return false;
        }
        $group = $user_info['groupid'];

        //查询公司的结构图
        $group_level = Table_Admin_Department::inst()->autoClearCache()->field("group_id,level")->where("group_id in ($group) and (level=5 or level=6)")->select();

        if ($group_level) {
            $level = array_column($group_level, 'level');
            $group_id = array_column($group_level, 'group_id');
            $max = max($level);

            if ($max == 6) {
                $key = array_search($max, $level);
                $group = $group_id[$key];
            } else if ($max == 5) {
                //总监提交单子，去寻找他的下属
                $key = array_search($max, $level);
                $group = $group_id[$key];
                $dep_info = Table_Admin_Department::inst()->autoClearCache()->field("id")->where("group_id=$group")->selectOne();
                if (empty($dep_info)) {
                    return false;
                }
                $dep_id = $dep_info['id'];
                $group_info = Table_Admin_Department::inst()->autoClearCache()->field("group_id")->where("parent_id=$dep_id")->selectOne();
                $group = $group_info['group_id'];
            } else {
                return false;
            }
        } else {
            return false;
        }

        $where = "examine_type=" . $examin_type['id'] . " and group_id=$group";
        $process = Table_Examine_Process_Improve::inst()->autoClearCache()->field("id,parent_path")->where($where)->order('level desc')->selectOne();
        if (empty($process)) {
            return false;
        }

        Db::exec("START TRANSACTION");
        try {
            $parent_path = $process['parent_path'];
            $parent_path = explode(",", $parent_path);
            $parent_path = array_reverse($parent_path);

            $step = 0;
            foreach ($parent_path as $k => $v) {
                $info = Table_Examine_Process_Improve::inst()->getInfo($v);
                //添加审批流快照表
                $step += 1;
                $data['oid'] = $oid;
                $data['examine_type'] = $type;
                $data['type_key'] = $examin_type['type_key'];
                $data['step'] = $step;
                $data['product_type'] = $product_type;
                $data['approver_type'] = 1;
                $data['before_hook'] = $info['before_hook'];
                $data['later_hook'] = $info['later_hook'];
                $data['degree'] = $degree;
                if (!Table_Examine_Process_Snapshot::inst()->addData($data)->add()) {
                    throw new Exception("添加审批流快照出现错误");
                }

                $process_snapshot_id = Db::insertId();

                //添加审批人员快照表
                $people_data['process_snapshot_id'] = $process_snapshot_id;
                $people_data['ext_id'] = $info['group_id'];
                if (!Table_Examine_People_Snapshot::inst()->addData($people_data)->add()) {
                    throw new Exception("添加审批流快照出现错误");
                }
            }

            Db::exec("COMMIT");
            return true;
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }

    /**
     * 根据快照检查当前后台用户的审批权限
     * @param $oid
     * @param $type
     * @param $product
     * @param int $step
     * @param int $degree
     * @return array
     */
    public function checkExaminePower($oid, $type, $product, $step = 1, $degree = 1)
    {
        if (is_numeric($type)) {
            $where = "examine_type=$type";
        } else {
            $where = "type_key='$type'";
        }

        //如果$product_type为空或0 默认为142
        $product = empty($product) ? 142 : $product;

        //根据类型查询审批流
        $process = self::inst()->autoClearCache()->where("oid=$oid and $where and step=$step and degree=$degree and product_type=$product")->selectOne();

        if ($process) {
            $people = Table_Examine_People_Snapshot::inst()->autoClearCache()->where("process_snapshot_id=" . $process['id'])->select();
            if (empty($people)) {
                return array("code" => 400, "msg" => "出现错误，未查询到相应的审批人员信息。");
            }
            $a_ext_id = array_column($people, "ext_id");
            $ext_id = implode(",", $a_ext_id);
            //验证当前用户是否有权限审批
            $approver_type = $process['approver_type'];

            if ($approver_type == 1) {
                $group_list = Table_Admin_Group::inst()->noCache()->field("item")->where("id in ($ext_id)")->select();
                if (empty($group_list)) {
                    return array("code" => 400, "msg" => "出现错误，未查询到组。");
                }
                $item = array_column($group_list, "item");
                $item = implode(",", $item);

                //只显示职位
                if (strstr($item, "总监")) {
                    $item = "总监";
                } else if (strstr($item, "副总")) {
                    $item = "副总";
                }
                //查看用户组是否有权限
                $is_power = false;
                $group = Common::$_admin_groupid;
                if (is_array($group)) {
                    foreach ($group as $v) {
                        if (in_array($v, $a_ext_id)) {
                            $is_power = true;
                            break;
                        }
                    }
                } else {
                    if (in_array($group, $a_ext_id)) {
                        $is_power = true;
                    }
                }

                if ($is_power) {
                    return array("code" => 200, "msg" => "待" . $item . "审核", "action" => $item . "审核", "groupid" => $ext_id);
                } else {
                    return array("code" => 500, "msg" => "待" . $item . "审核", "groupid" => $ext_id);
                }
            } else {
                $admin_list = Table_Admin::inst()->noCache()->field("realname")->where("id in ($ext_id)")->select();
                if (empty($group_list)) {
                    return array("code" => 400, "msg" => "出现错误，未查询到管理员。");
                }
                $realname = array_column($admin_list, "realname");
                $realname = implode(",", $realname);
                //查看个人用户是否有权利
                if (in_array(Common::$_adminid, $a_ext_id)) {
                    return array("code" => 200, "msg" => "待" . $realname . "审核", "action" => $realname . "审核", "adminid" => $ext_id);
                } else {
                    return array("code" => 500, "msg" => "待" . $realname . "审核", "adminid" => $ext_id);
                }
            }
        } else {
            return array("code" => 400, "msg" => "出现错误，未查询到相应的审批流信息。");
        }
    }

    /**
     * 根据快照检查当前后台用户的审批权限（新）
     * @param $oid
     * @param $type
     * @param $product
     * @param int $step
     * @param int $degree
     * @return array
     */
    public function checkExaminePowerNew($oid, $type, $product, $step = 1, $degree = 1)
    {
        if (is_numeric($type)) {
            $where = "examine_type=$type";
        } else {
            $where = "type_key='$type'";
        }

        //如果$product_type为空或0 默认为142
        $product = empty($product) ? 142 : $product;

        //根据类型查询审批流
        $process = self::inst()->autoClearCache()->where("oid=$oid and $where and step=$step and degree=$degree and product_type=$product")->selectOne();

        if ($process) {
            $people = Table_Examine_People_Snapshot::inst()->autoClearCache()->field("ext_id")->where("process_snapshot_id=" . $process['id'])->selectOne();
            if (empty($people)) {
                return array("code" => 400, "msg" => "出现错误，未查询到相应的审批人员信息。");
            }
            $ext_id = $people['ext_id'];
            //验证当前用户是否有权限审批
            $approver_type = $process['approver_type'];

            if ($approver_type == 1) {
                $group = Table_Admin_Group::inst()->getInfo($ext_id);
                if (empty($group)) {
                    return array("code" => 400, "msg" => "出现错误，未查询到组。");
                }
                $role = empty($group['role']) ? $group['item'] : $group['role'];

                //查看用户组是否有权限
                $is_power = false;
                $group = Common::$_admin_groupid;
                if (is_array($group)) {
                    $group = implode(",", $group);
                    if (strstr("," . $group . ",", "," . $ext_id . ",")) {
                        $is_power = true;
                    }
                } else {
                    if ($group == $ext_id) {
                        $is_power = true;
                    }
                }

                if ($is_power) {
                    return array("code" => 200, "msg" => "待" . $role . "审核", "action" => $role . "审核", "groupid" => $ext_id);
                } else {
                    return array("code" => 500, "msg" => "待" . $role . "审核", "groupid" => $ext_id);
                }
            } else {
                //暂时没有个人的情况。。。
                return array("code" => 400, "msg" => "出现错误，暂时没有个人的情况。。。");
            }
        } else {
            return array("code" => 400, "msg" => "出现错误，未查询到相应的审批流信息。");
        }
    }
}