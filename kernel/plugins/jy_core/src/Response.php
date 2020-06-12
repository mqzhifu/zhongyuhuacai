<?php
namespace Jy;

use Jy\Common\RequestContext\RequestContext;

class Response
{

    private static $_instance = null;
    private static $handle = null;

    /**
     * @return null|Request
     */
    public static function getInstance()
    {
        if (NULL === self::$_instance){
            return new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        // init handle
    }

    public function json(array $userData, $code = 200, $msg = "success")
    {
        // fpm  json
        // rpc stream   pack

        // handle
        // return $this->handle->response($userData, $code, $msg);

        return new \Jy\JSONResponse([
            'code' => $code,
            'message' => $msg,
            'data' => $userData
        ]);
    }
}
