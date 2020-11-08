<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-5-12
 * Time: 下午2:37
 */
class Table_Examine_Process extends Table
{
    public $_table = "examine_process";
    public $_primarykey = "id";

    public $def = array(
        1 => "==",
        2 => ">",
        3 => "<",
        4 => ">=",
        5 => "<="
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
     * 检查当前后台用户的审批权限
     * @param $type
     * @param $setp
     * @return array
     */
    public function checkExaminePower($type, $setp)
    {
        //根据类型查询审批流
        $process = Table_Examine_Process::inst()->autoClearCache()->where("examine_type=$type and step=$setp")->selectOne();

        $people = Table_Examine_People::inst()->autoClearCache()->where("process_id=" . $process['id'])->select();
        $a_ext_id = array_column($people, "ext_id");
        $ext_id = implode(",", $a_ext_id);

        if ($process) {
            //验证当前用户是否有权限审批
            $approver_type = $process['approver_type'];

            if ($approver_type == 1) {
                $group_list = Table_Admin_Group::inst()->autoClearCache()->field("item")->where("id in ($ext_id)")->select();
                $item = array_column($group_list, "item");
                $item = implode(",", $item);
                //查看用户组是否有权限
                if (in_array(Common::$_user_groupid, $a_ext_id)) {
                    return array("code" => 200, "msg" => "待" . $item . "审核");
                } else {
                    //查询当前用户所在组是否具有一键审批权
                    $process_info = Table_Examine_Process::inst()->autoClearCache()->where("examine_type=$type")->selectOne();
                    if ($process_info['is_root'] == 1) {
                        $arr_ext_id = explode(",", $process_info['ext_id']);
                        if (in_array(Common::$_user_groupid, $arr_ext_id)) {
                            return array("code" => 200, "msg" => "待" . $item . "审核");
                        }
                    }

                    return array("code" => 400, "msg" => "待" . $item . "审核");
                }
            } else {
                $admin_list = Table_Admin::inst()->autoClearCache()->field("realname")->where("id in ($ext_id)")->select();
                $realname = array_column($admin_list, "realname");
                $realname = implode(",", $realname);
                //查看个人用户是否有权利
                if (in_array(Common::$_adminid, $a_ext_id)) {
                    return array("code" => 200, "msg" => "待" . $realname . "审核");
                } else {
                    //查询当前用户是否具有一键审批权
                    $process_info = Table_Examine_Process::inst()->autoClearCache()->where("examine_type=$type")->selectOne();
                    if ($process_info['is_root'] == 1) {
                        $arr_ext_id = explode(",", $process_info['ext_id']);
                        if (in_array(Common::$_adminid, $arr_ext_id)) {
                            return array("code" => 200, "msg" => "待" . $realname . "审核");
                        }
                    }

                    return array("code" => 400, "msg" => "待" . $realname . "审核");
                }
            }
        } else {
            return array("code" => 400, "msg" => "出现错误，请联系管理员。");
        }
    }

    /**
     * 当前审批完成，执行下一步
     * @param $oid
     * @param $type
     * @param $step
     * @return array|bool
     */
    public function selectNextStep($oid, $type, $step)
    {
        //查询下一步
        $next_step = $step + 1;
        $next = Table_Examine_Process::inst()->autoClearCache()->where("examine_type=$type and step=$next_step")->selectOne();

        if ($next) {
            $result_info = Table_Examine_Result::inst()->autoClearCache()->getInfo($oid);

            //限制亏损项目
            if ($next['loss'] != $result_info['loss']) {
                //不满足满足条件，跳过此步 继续检测下一步
                return $this->selectNextStep($oid, $type, $next_step);
            }

            if ($next['is_limit_money']) {
                //金额限定
                $money = $result_info['money']; //金额

                $start = empty($next['limit_money_start']) ? 0 : $next['limit_money_start']; //起始 限定金额
                $end = empty($next['limit_money_end']) ? 999999999 : $next['limit_money_end']; //截止 限定金额
                //是否包含
                $start_include = empty($next['start_include']) ? ">=" : ">";

                $end_include = empty($next['end_include']) ? "<=" : "<";

                //运算
                eval("\$limit_money_flag=$money$start_include$start&&$money$end_include$end;");
            }

            if ($next['is_limit_type']) {
                //限定项目类型及毛利率

                $dz_rate = empty($next['dz_rate']) ? 0 : $next['dz_rate']; //定制类型限定毛利率
                $other_rate = empty($next['other_rate']) ? 0 : $next['dz_rate']; //非定制类型限定毛利率
                //界定符
                $dz_def = $this->def[$next['dz_def']];
                $other_def = $this->def[$next['other_def']];

                //毛利率
                $rate = $result_info['rate'];
                //类型
                $o_type = $result_info['type'];

                //根据类型，运算结果
                if ($o_type) {
                    //定制类型
                    eval("\$limit_type_flag=($rate$dz_def$dz_rate);");
                } else {
                    //非定制类型
                    eval("\$limit_type_flag=($rate$other_def$other_rate);");
                }
            }

            //只有存在金额限定 和 项目类型及毛利率 才会读取money_type_relation
            $flag = false;
            if ($next['is_limit_money'] && $next['is_limit_type']) {
                $money_type_relation = empty($next['money_type_relation']) ? "&&" : "||";
                eval("\$flag=$limit_money_flag$money_type_relation$limit_type_flag;");
            } else if ($next['is_limit_money']) {
                if ($limit_money_flag) {
                    $flag = true;
                }
            } else if ($next['is_limit_type']) {
                if ($limit_type_flag) {
                    $flag = true;
                }
            } else {
                $flag = true;
            }

            if ($flag) {
                //满足条件，返回当前步骤
                return $next;
            } else {
                //不满足满足条件，跳过此步 继续检测下一步
                return $this->selectNextStep($oid, $type, $next_step);
            }
        } else {
            //没有查询到下一步，审批已完成
            return false;
        }
    }
} 