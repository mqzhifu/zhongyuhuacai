<?php

/**
 * Created by PhpStorm.
 * User: 哈哈
 * Date: 2016/9/12
 * Time: 14:13
 */
class Table_Examine_Process_Improve extends Table
{
    public $_table = "examine_process_improve";
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
     * 获取节点内容
     * @param $parentid
     * @param $examine_type
     * @param bool $getnum
     * @return array|bool|Int
     */
    public function getParentCategory($parentid, $examine_type, $getnum = false)
    {
        $return = $this->autoClearCache()->where(array('parent_id=' . $parentid, 'examine_type=' . $examine_type));

        if ($getnum) {
            return $return->selectCount();
        } else {
            return $return->field("*")->select();
        }
    }

    /**
     * 读取子分类信息,永久缓存，自动更新缓存
     * @param $parentid
     * @param $examine_type
     * @param bool $getnum
     * @return array|bool|Int
     */
    public function getChildCategory($parentid, $examine_type, $getnum = false)
    {
        $parentid = (int)$parentid;
        $where = "parent_id = {$parentid} and examine_type = {$examine_type}";
        $return = $this->where($where)->order(" `level` ASC");

        $this->autoClearCache();

        if ($getnum) {
            return $return->selectCount();
        } else {
            return $return->field("*")->select();
        }
    }

    /**
     * 根据 id 返回 id下级 或 上级
     * @param $id
     * @param $sort  true 取上级  false 去下级
     * @return int|string
     */
    public function getTreeById($id, $sort = true)
    {

        $now_info = $this->inst()->getInfo($id);

        $all_info = $this->inst()->autoClearCache()->where(array("examine_type = " . $now_info['examine_type']))->select();

        if ($sort) {
            $str = $now_info['parent_path'];
        } else {
            $str = $this->getChildPathStr($all_info, $id);
        }

        return $str;
    }

    /**
     * 返回子节点的string 串
     * @param $node_list
     * @param int $pid
     * @return int|string
     */
    public function getChildPathStr($node_list, $pid = 0)
    {
        $child_path = $pid;
        foreach ($node_list as $key => $value) {
            if ($value['parent_id'] == $pid) {
                $child_path = $child_path . "," . $this->getChildPathStr($node_list, $value['id']);
            }
        }
        return $child_path;
    }

    /**
     * 职位移动  被移动的子级不跟随
     * @param $id
     * @param $parentid
     * @return bool
     */
    public function pathHandle($id, $parentid)
    {
        //获取当前id 信息
        $now_info = $this->inst()->getInfo($id);

        //查找同当前type 一样的信息
        $all_info = $this->inst()->autoClearCache()->where(array('examine_type=' . $now_info['examine_type']))->select();

        //声明 用于记录和要移动分类有关系的信息id
        $log_array = array();
        //声明 用于记录被认爹数据的信息
        $parent_array = array();

        //记录所有数据
        $tmp_data = array();

        //循环排查
        foreach ($all_info as $_k => $_v) {
            //判断当前数据是否 和 要移动的ID有关系（他的下级） 如果有关系则把当前 id记录清楚
            $each_parent_path = explode(',', $_v['parent_path']);
            if (in_array($id, $each_parent_path)) {
                //将和他有关系的信息记录
                $log_array[$_v['id']] = $_v;

                //获取他当前在家中的地位
                $now_key = array_search($id, $each_parent_path);
                //抹去他存在的证据 哈哈哈
                unset($each_parent_path[$now_key]);

                //如果有比他level低的就提拔他一下
                if (!empty($each_parent_path)) {
                    foreach ($each_parent_path as $_k1 => $_v1) {
                        if ($now_key > $_k1) {
                            if (!isset($log_array[$_v['id']]['exist'])) {
                                $log_array[$_v['id']]['exist'] = true;  //用来记录是否处理过
                                $log_array[$_v['id']]['level'] = $log_array[$_v['id']]['level'] - 1;
                            }
                        }
                    }
                }
                //将整理后的父级路径还给他
                $log_array[$_v['id']]['parent_path'] = implode(',', $each_parent_path);
            }

            // TODO 处理 parent_id
            //如果当前parent_id 是 移动的id 的儿子  就让当前id 认 要移动id的爹 为爹 （你的爹就是我的爹）
            if ($_v['parent_id'] == $id) {
                if (!isset($log_array[$_v['id']])) {
                    $log_array[$_v['id']] = $_v;
                }
                $log_array[$_v['id']]['parent_id'] = $now_info['parent_id'];
            }


            //将要移动的信息存入数组
            if ($_v['id'] == $id) {
                $log_array[$_v['id']] = $_v;
            }

            //把被认爹的信息记录
            if ($_v['id'] == $parentid) {
                $parent_array = $_v;
            }
        }

        // TODO 对要移动id的信息整理
        $log_array[$id]['parent_path'] = $parent_array['parent_path'] . ',' . $parentid;
        $log_array[$id]['parent_id'] = $parentid;
        $log_array[$id]['level'] = $parent_array['level'] + 1;

        foreach ($all_info as $_k => $_v) {
            if (isset($log_array[$_v['id']])) {
                $tmp_data[] = $log_array[$_v['id']];
            } else {
                $tmp_data[] = $_v;
            }
        }

        $child_path = array();
        foreach ($tmp_data as $_k => $_v) {
            if (isset($_v['exist'])) {
                unset($tmp_data[$_k]['exist']);
            }
            $child_path[$_v['level']][$_v['parent_id']][] = $_v['id'];
        }

        ksort($child_path);

        //获取最高父级ID
        $parent_path = $now_info['parent_path'];
        if (strpos($parent_path, ',')) {
            $pid = substr($parent_path, 0, strpos($parent_path, ','));
        } else {
            $pid = $parent_path;
        }

        //开始事务
        Db::exec("START TRANSACTION");
        try {

            foreach ($tmp_data as $_k => $_v) {
                $this->inst()->addData($_v)->edit($_v['id']);
            }

            //修改child_path
            $this::inst()->addData(array('child_path' => json_encode($child_path)))->edit($pid);

            Db::exec("COMMIT");
            return true;
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            $msg = $e->getMessage();
            return false;
        }
    }

    /**
     * 移动节点及其子节点
     * @param $id
     * @param $parentid
     * @return bool
     */
    public function moveHandle($id, $parentid)
    {
        //获取当前id 信息
        $now_info = $this->inst()->getInfo($id);

        //查找同当前type 一样的信息
        $all_info = $this->inst()->autoClearCache()->where(array('examine_type=' . $now_info['examine_type']))->select();

        //存储以id为键名的数组
        $new_all_info = array();

        foreach ($all_info as $_k => $_v) {
            $new_all_info[$_v['id']] = $_v;
        }
        $new_all_info[$id]['parent_id'] = $parentid;


        //用于记录日志的数组
        $log_array = array();

        //处理 parent_path  和  Level
        foreach ($new_all_info as $_k => $_v) {
            //处理Level
            $new_all_info[$_v['id']]['parent_path'] = $this->getParentPath($new_all_info, $_v['id']);

            //处理Level
            $each_parent_path = explode(',', $_v['parent_path']);
            if (in_array($id, $each_parent_path)) {
                //获取他当前在家中的地位
                $now_key = array_search($id, $each_parent_path);

                //对levle 进行处理
                if (!empty($each_parent_path)) {
                    foreach ($each_parent_path as $_k1 => $_v1) {
                        if ($now_key > $_k1) {
                            if (!isset($log_array[$_v['id']]['exist'])) {
                                $log_array[$_v['id']]['exist'] = true;  //用来记录是否处理过
                                if ($new_all_info[$id]['level'] > $new_all_info[$parentid]['level']) {
                                    $limit = $new_all_info[$id]['level'] - ($new_all_info[$parentid]['level'] + 1);
                                    $new_all_info[$_v['id']]['level'] = $new_all_info[$_v['id']]['level'] - $limit;
                                } else {
                                    $limit = ($new_all_info[$parentid]['level'] + 1) - $new_all_info[$id]['level'];
                                    $new_all_info[$_v['id']]['level'] = $new_all_info[$_v['id']]['level'] + $limit;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!isset($log_array[$id]['exist'])) {
            $log_array[$id]['exist'] = true;  //用来记录是否处理过
            //处理要移动id 的 level
            if ($new_all_info[$id]['level'] > $new_all_info[$parentid]['level']) {
                $limit = $new_all_info[$id]['level'] - ($new_all_info[$parentid]['level'] + 1);
                $new_all_info[$id]['level'] = $new_all_info[$id]['level'] - $limit;
            } else {
                $limit = ($new_all_info[$parentid]['level'] + 1) - $new_all_info[$id]['level'];
                $new_all_info[$id]['level'] = $new_all_info[$id]['level'] + $limit;
            }
        }

        //获取最高父级ID
        $parent_path = $new_all_info[$id]['parent_path'];
        if (strpos($parent_path, ',')) {
            $pid = substr($parent_path, 0, strpos($parent_path, ','));
        } else {
            $pid = $parent_path;
        }

        //整理要修改的数据
        $save_data = array();
        foreach ($log_array as $_k => $_v) {
            $save_data[] = $new_all_info[$_k];
        }

        //获取child_path
        $child_path = $this->getChildPath($new_all_info);

        //开始事务
        Db::exec("START TRANSACTION");
        try {

            foreach ($save_data as $_k => $_v) {
                $this->inst()->addData($_v)->edit($_v['id']);
            }

            //修改child_path
            $this->inst()->addData(array('child_path' => json_encode($child_path)))->edit($pid);

            Db::exec("COMMIT");
            return true;
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            $msg = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除节点
     * @param $id
     * @return bool
     */
    public function delNode($id)
    {
        //开始事务
        Db::exec("START TRANSACTION");
        try {
            $cateinfo = $this::inst()->getInfo($id);

            //获取最高父级的id
            $parent_path = $cateinfo['parent_path'];
            if (strpos($parent_path, ',')) {
                $pid = substr($parent_path, 0, strpos($parent_path, ','));
            } else {
                $pid = $parent_path;
            }
            //查找出 子级路径
            $info = $this::inst()->getInfo($pid);

            if ($info) {
                $child_path = json_decode($info['child_path'], true);
            } else {
                throw new Exception("最高父级ID不存在");
            }

            //删除数据
            $key = array_search($id, $child_path[$cateinfo['level']][$cateinfo['parent_id']]);
            unset($child_path[$cateinfo['level']][$cateinfo['parent_id']][$key]);
            //判断当前pid数组是否为空
            if (empty($child_path[$cateinfo['level']][$cateinfo['parent_id']])) {
                unset($child_path[$cateinfo['level']][$cateinfo['parent_id']]);
                if (empty($child_path[$cateinfo['level']])) {
                    unset($child_path[$cateinfo['level']]);
                }
            }

            //判断当前删除的节点是否含有子节点
            $new_child_path = [];
            foreach ($child_path as $k => $v) {
                if ($k > $cateinfo['level']) {
                    if (isset($child_path[$k][$id])) {
                        $new_child_path[$k - 1][$cateinfo['parent_id']] = $child_path[$k][$id];
                        foreach ($child_path[$k][$id] as $_k => $_v) {
                            $child_data['parent_id'] = $cateinfo['parent_id'];
                            $child_data['level'] = $k - 1;
                            $this::inst()->addData($child_data)->edit($_v);
                        }
                    } else {
                        if (isset($new_child_path[$k - 1])) {
                            $new_child_path[$k] = $v;
                        } else {
                            $new_child_path[$k - 1] = $v;
                        }
                    }
                } else {
                    $new_child_path[$k] = $v;
                }
            }

            //修改子类路径
            $result1 = $this::inst()->addData(array('child_path' => json_encode($new_child_path)))->edit($pid);

            //修改parent_path
            $list = $this::inst()->autoClearCache()->where("find_in_set('$id',parent_path)")->select();
            $result2 = false;
            if ($list) {
                foreach ($list as $k => $v) {
                    $parent_path = $v['parent_path'];
                    $parent_path = explode(",", $parent_path);
                    $key = array_search($id, $parent_path);
                    unset($parent_path[$key]);

                    $parent_path = implode(",", $parent_path);

                    $data['parent_path'] = $parent_path ? $parent_path : 0;
                    $result2 = $this::inst()->addData($data)->edit($v['id']);
                }
            } else {
                $result2 = true;
            }

            //删除
            $result3 = $this::inst()->del($id);

            if ($result1 && $result2 && $result3) {
                Db::exec("COMMIT");
                return true;
            } else {
                Db::exec("ROLLBACK");
                return false;
            }
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }

    public function delNodeParent($id)
    {
        Db::exec("START TRANSACTION");
        try {
            $cateinfo = $this::inst()->getInfo($id);

            //获取最高父级的id
            $parent_path = $cateinfo['parent_path'];
            if (strpos($parent_path, ',')) {
                $pid = substr($parent_path, 0, strpos($parent_path, ','));
            } else {
                $pid = $parent_path;
            }
            //查找出 子级路径
            $info = $this::inst()->getInfo($pid);
            if ($info) {
                $child_path = json_decode($info['child_path'], true);
            } else {
                throw new Exception("最高父级ID不存在");
            }

            foreach ($child_path as $k => $v) {
                if ($k >= $cateinfo['level']) {
                    unset($child_path[$k]);
                }
            }

            //删除
            $result1 = $this::inst()->where("find_in_set('$id',parent_path) and parent_id=$id or id=$id")->del();

            //修改子类路径
            $result2 = $this::inst()->addData(array('child_path' => json_encode($child_path)))->edit($pid);

            if (false !== $result1 && false !== $result2) {
                Db::exec("COMMIT");
                return true;
            } else {
                Db::exec("ROLLBACK");
                return false;
            }
        } catch (Exception $e) {
            Db::exec("ROLLBACK");
            return false;
        }
    }

    /**
     * 获取最高级父级信息
     * @param $parent_path
     * @return string
     */
    public function getMaxParentInfo($parent_path)
    {
        //获取最高父级ID
        if (strpos($parent_path, ',')) {
            $pid = substr($parent_path, 0, strpos($parent_path, ','));
        } else {
            $pid = $parent_path;
        }


        //获取当前层级最高父级信息
        $parent_where = array(
            "id = {$pid}",
        );
        $parent_data = $this->inst()->autoClearCache()->field('child_path,parent_path')->where($parent_where)->selectOne();
        if (!$parent_data) {
            return false;
        }

        return $parent_data;
    }

    /**
     * 生成 child_path
     * @param array $all
     * @return array|string
     */
    public function getChildPath($all = array())
    {
        $return = array();

        if (!is_array($all)) {
            return '';
        }

        foreach ($all as $_k => $_v) {
            $return[$_v['level']][$_v['parent_id']][] = $_v['id'];
        }

        return $return;
    }

    /**
     * 通过id获取 parent_path
     * @param $all  查询出所有相同type的array
     * @param $id   要获取parent_path 的id
     * @return string
     */
    public function getParentPath($all, $id)
    {
        if (!isset($all[$id])) {
            return '';
        }

        $info = $all[$id];

        //记录父级路径
        $parent_path = $info['parent_id'];

        //判断上级是否存在
        if (isset($all[$info['parent_id']])) {
            //如果父级为0则返回空
            if ($all[$info['parent_id']]['parent_id'] == 0) {
                return $info['parent_id'];
            } else {
                $parent_path = $this->getParentPath($all, $info['parent_id']) . ',' . $parent_path;
            }

        }

        return $parent_path;
    }

    public function haveChild($examin_type, $group)
    {
        //判断是否是最底层的员工发起的审批
        //和产品确认过，现有业务，提交审批单的用户只有总监和员工
        $info = Table_Examine_Process_Improve::inst()->autoClearCache()->field("id")->where("examine_type=$examin_type and group_id in ($group)")->order('`level` ASC')->selectOne();
        $id = $info['id'];
        $child = Table_Examine_Process_Improve::inst()->autoClearCache()->where("examine_type=$examin_type and parent_id=$id")->select();
        if ($child) {
            return true;
        } else {
            return false;
        }
    }
}