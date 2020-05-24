<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-6-6
 * Time: 上午10:56
 */
class Table_Examine_People_Snapshot extends Table
{
    public $_table = "examine_people_snapshot";
    public $_primarykey = "id";

    public static $_static = false;

    public static function inst()
    {
        if (false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }

    public function getExaminePeople($type, $step, $product_type, $degree, $uniton_id)
    {
        $process = Table_Examine_Process_Snapshot::inst()
            ->autoClearCache()
            ->field("id")
            ->where("examine_type={$type} and step={$step} and product_type={$product_type} and degree={$degree} and oid={$uniton_id}")
            ->selectOne();
        $people = $this::inst()
            ->autoClearCache()
            ->field("ext_id")
            ->where("process_snapshot_id=" . $process['id'])
            ->selectOne();

        return $people;
    }
}