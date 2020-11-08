<?php

namespace Jy\Facade;

use Jy\Db\Facade\DBComponent;

/**
 * @method static int insert($table, $params = array(), $name = '', $type = "write", $model = '')
 * @method static int update($sql, $params = array(), $name = '', $type = "write", $model = '')
 * @method static int updateById($table, $id, $param = array(), $name = '', $type = "write", $model = '')
 * @method static array multiInsert($table, $params = array(), $name = '', $type = "write", $model = '')
 * @method static array findOne($sql, $param = array(), $master = false, $name = '', $model = '')
 * @method static array findAll($sql, $param = array(), $master = false, $name = '', $model = '')
 * @method static int beginTransaction( $name = '', $type = "write", $model = '')
 * @method static int commit($model = '', $name = '', $type = "write")
 * @method static int rollBack( $name = '', $type = "write", $model = '')
 */
class DB extends DBComponent
{
    //..

}
