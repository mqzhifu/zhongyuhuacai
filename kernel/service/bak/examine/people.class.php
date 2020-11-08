<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-5-24
 * Time: 下午4:10
 */
class Table_Examine_People extends Table
{
    public $_table = "examine_people";
    public $_primarykey = "id";

    public static $_static = false;

    public static function inst()
    {
        if (false == self::$_static) {
            self::$_static = new self();
        }
        return self::$_static;
    }

} 