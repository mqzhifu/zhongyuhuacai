<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-5-12
 * Time: 下午2:39
 */
class Table_Examine_Log extends Table
{
    public $_table = "examine_log";
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
     * 添加日志
     * @param array $data
     * @return bool
     */
    public function addLog($data = array())
    {
        $result = self::inst()->addData($data)->add();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 日志列表
     * 返回数据格式：数组
     * key：信息 value[remark]：批注或驳回理由 value[time]：时间
     * @param $oid
     * @param $type
     * @param $degree
     * @param $product_type
     * @return array
     */
    public function loglist($oid, $type, $degree, $product_type)
    {
        if (is_numeric($type)) {
            $where = "examine_type=$type";
        } else {
            $where = "type_key='$type'";
        }
        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $list = self::inst()->autoClearCache()->where("oid=$oid and $where and degree=$degree and product_type=$product_type")->order("step")->select();

        $array = array();
        if ($list) {
            foreach ($list as $k => $v) {
                $step = $v['step'];
                $process = Table_Examine_Process_Snapshot::inst()->autoClearCache()->where("oid=$oid and $where and step=$step and degree=$degree and product_type=$product_type")->selectOne();
                $people = Table_Examine_People_Snapshot::inst()->autoClearCache()->field("ext_id")->where("process_snapshot_id=" . $process['id'])->selectOne();
                $ext_id = $people['ext_id'];

                $group = Table_Admin_Group::inst()->getInfo($ext_id);
                $name = empty($group['role']) ? $group['item'] : $group['role'];

                //组装最终返回数组
                $examine_status = $v['examine_status'];

                if ($v['remark'] && $v['is_jump'] != 1) {
                    if ($examine_status == 1) {
                        //通过
                        $txt = $name . "批注";
                    } else {
                        //未通过
                        $txt = $name . "驳回";
                    }
                    //可能存在相同的key，会被覆盖
                    if (isset($array[$txt])) {
                        $txt = $txt . "-调入";
                    }

                    $array[$txt]['remark'] = $v['remark'];
                    $array[$txt]['time'] = date('Y-m-d H:i:s', $v['addtime']);
                }
            }
        }
        return $array;
    }

    /**
     * 获取完整审批信息
     * @param $oid
     * @param $type
     * @param $product_type
     * @return array
     */
    public function getProcessLog($oid, $type, $product_type)
    {
        if (is_numeric($type)) {
            $where = "examine_type=$type";
        } else {
            $where = "type_key='$type'";
        }

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $array = array();
        $result = Table_Examine_Result::inst()->noCache()->where("oid=$oid and $where and product_type=$product_type")->selectOne();

        $degree = $result['degree'];

        $process = Table_Examine_Process_Snapshot::inst()->autoClearCache()->where("oid=$oid and $where and degree=$degree and product_type=$product_type")->order("step")->select();

        if ($process) {
            foreach ($process as $k => $v) {
                $people = Table_Examine_People_Snapshot::inst()->autoClearCache()->field("ext_id")->where("process_snapshot_id=" . $v['id'])->selectOne();

                $ext_id = $people['ext_id'];

                if ($v['approver_type'] == 1) {
                    $group = Table_Admin_Group::inst()->getInfo($ext_id);
                    $name = empty($group["role"]) ? $group["item"] : $group["role"];
                } else {
                    $name = "出现错误，暂时没有个人的情况。。";
                }

                $step = $v['step'];
                //读取日志
                $log = self::inst()->autoClearCache()->where("oid=$oid and $where and degree=$degree and step=$step and product_type=$product_type")->selectOne();
                if (empty($log)) {
                    $key = $name;
                    $val = "";
                } else {
                    if ($log['is_jump'] == 1) {
                        $key = $name . '【系统确认】';
                        $val = $log['remark'];
                    } else {
                        $approver = $log['approver'];
                        $examine_status = $log['examine_status'];
                        if ($examine_status == 1) {
                            $key = $name . "【确认】";
                        } else {
                            $key = $name . "【驳回】";
                        }

                        $admin = Table_Admin::inst()->noCache()->getInfo($approver);
                        $val = $admin['realname'] . "&nbsp;" . date("Y-m-d H:i:s", $log['addtime']);
                    }
                }
                //可能存在相同的key，会被覆盖
                    if (isset($array[$key])) {
                        if($type == 8 || $type == 'item_advance'){
                            $key = $key . ' ';
                        }else{
                            $key = $key . "-调入";
                        }

                    }
                $array[$key] = $val;
            }
        }

        return $array;
    }



    /**
     * 获取完整审批信息
     * @param $oid
     * @param $type
     * @param $product_type
     * @return array
     */
    public function getProcessLog_bak($oid, $type, $product_type)
    {
        if (is_numeric($type)) {
            $where = "examine_type=$type";
        } else {
            $where = "type_key='$type'";
        }

        //如果$product_type为空或0 默认为142
        $product_type = empty($product_type) ? 142 : $product_type;

        $array = array();
        $result = Table_Examine_Result::inst()->noCache()->where("oid=$oid and $where and product_type=$product_type")->selectOne();

        $degree = $result['degree'];

        $process = Table_Examine_Process_Snapshot::inst()->noCache()->where("oid=$oid and $where and degree=$degree and product_type=$product_type")->order("step")->select();

        if ($process) {
            foreach ($process as $k => $v) {
                $people = Table_Examine_People_Snapshot::inst()->noCache()->field("ext_id")->where("process_snapshot_id=" . $v['id'])->selectOne();

                $ext_id = $people['ext_id'];

                if ($v['approver_type'] == 1) {
                    $group = Table_Admin_Group::inst()->getInfo($ext_id);
                    $name = empty($group["role"]) ? $group["item"] : $group["role"];
                } else {
                    $name = "出现错误，暂时没有个人的情况。。";
                }

                $step = $v['step'];
                //读取日志
                $log = self::inst()->noCache()->where("oid=$oid and $where and degree=$degree and step=$step and product_type=$product_type")->selectOne();
                if (empty($log)) {
                    $key = $name;
                    $val = "";
                } else {
                    if ($log['is_jump'] == 1) {
                        $key = $name . '【系统确认】';
                        $val = $log['remark'];
                    } else {
                        $approver = $log['approver'];
                        $examine_status = $log['examine_status'];
                        if ($examine_status == 1) {
                            $key = $name . "【确认】";
                        } else {
                            $key = $name . "【驳回】";
                        }

                        $admin = Table_Admin::inst()->noCache()->getInfo($approver);
                        $val = $admin['realname'] . "&nbsp;" . date("Y-m-d H:i:s", $log['addtime']);
                    }
                }
                //可能存在相同的key，会被覆盖
                if (isset($array[$key])) {
                    $key = $key . "-调入";
                }
                $array[$key] = $val;
            }
        }

        return $array;
    }



} 