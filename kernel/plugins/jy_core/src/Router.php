<?php

namespace Jy;

use Jy\Common\RequestContext\RequestContext;

class Router
{

    private $version;
    private $controller;
    private $action;

    public $handle;

    private $protocol = null;
    // 常见的几种
    private static $protocolMap = [
        'cli' => 'cli',
        'cli-server' => 'cli',
        'cgi-fcgi' => 'http',
        'cgi' => 'http',
        'fpm-fcgi' => 'http',
        'fpm' => 'http',
        'apache2handler' => 'http',
    ];

    public function getRouterInfo():array
    {
        $this->resolveProtocol();

        $method = "get".ucfirst($this->protocol)."RouterInfo";
        if (method_exists($this, $method)) {
            $this->{$method}();
        }

        return [$this->controller, $this->action, $this->version, $this->protocol];
    }

    private function resolveProtocol()
    {
        if (RequestContext::isInSwooleCoroutine()) {
            $this->protocol = "rpc";
        } else {
            $this->protocol = static::$protocolMap[php_sapi_name()] ?? "cli";
        }
    }

    private function getRpcRouterInfo()
    {
        $pathRoot = '';// todo

        $this->resolveRouter($pathRoot);
    }

    private function getCliRouterInfo()
    {
        global $argc;
        global $argv;
        $pathRoot =  $argc  > 1 ? $argv[1] : '';

        $this->resolveRouter($pathRoot);
    }

    private function getHttpRouterInfo()
    {
        $pathRoot = strpos($_SERVER['REQUEST_URI'], '?') ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
        if (substr($pathRoot, -9) === 'index.php'){
            $pathRoot = substr($pathRoot, 0, -9);
        }

        $this->resolveRouter($pathRoot);
    }

    private function resolveRouter(string $pathRoot)
    {
        if (empty($pathRoot)) return false;

        $pathArr = explode('/', trim($pathRoot, '/'));
        $pathArr = array_filter($pathArr);

        $this->version = trim(array_shift($pathArr));
        $this->action = trim(array_pop($pathArr));
        $this->controller = trim(implode("/", $pathArr));
    }

    public function getRequestVersion()
    {
        if (empty($this->version))  $this->getRouterInfo();
        return !empty($this->version) ? $this->version : 'V1';
    }

    public function getRequestController()
    {
        if (empty($this->controller))  $this->getRouterInfo();
        return !empty($this->controller) ? $this->controller : 'Index';
    }

    public function getRequestAction()
    {
        if (empty($this->action))  $this->getRouterInfo();
        return !empty($this->action) ? $this->action : 'index';
    }

    public function getRequestProtocol()
    {
        if (empty($this->protocol))  $this->getRouterInfo();
        return !empty($this->protocol) ? $this->protocol : 'none';
    }

}
