<?php
namespace Jy;


class Controller
{
    //..
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->request = \Jy\App::$app->request;
        $this->response = \Jy\App::$app->response;
        $this->data = \Jy\App::$app->request->getArgs();

    }

    public function json($data, $code = 200, $msg = "success")
    {
        return $this->response->json($data, $code, $msg);
    }
}
