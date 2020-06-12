<?php

namespace Jy;

use Jy\Util\InstanceTrait;
use Jy\Common\RequestContext\RequestContext;
use Jy\Common\Helpers\MachineHelper;

class Request
{
    use InstanceTrait;

    public $ipHeaders = [
        'X-Forwarded-For', // Common
    ];

    private $tokenHeader = 'Service-Token';

    private function __construct()
    {
        //.
    }

    public function getModule()
    {
        return \Jy\App::$app->router->getRequestController();
    }

    public function getAction()
    {
        return \Jy\App::$app->router->getRequestAction();
    }

    public function getVersion()
    {
        return \Jy\App::$app->router->getRequestVersion();
    }

    public function getProtocol()
    {
        return \Jy\App::$app->router->getRequestProtocol();
    }

    public function getMethod()
    {
        if ($this->getProtocol() === 'rpc') return "RPC";
        if ($this->getProtocol() === 'cli') return "CLI";

        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return 'GET';
    }

    public function getArgs()
    {
        return $this->getUserData();
    }

    public function getUserData()
    {
        return isset($this->getRequestParams()['user_data']) ? $this->getRequestParams()['user_data'] : $this->getRequestParams();
    }

    public function getRequestParams()
    {
        if (RequestContext::has('request_data')) return RequestContext::get('request_data');

        if ($this->getMethod() == 'GET') {
            return $_GET ?? [];
        } else if ($this->getMethod() == "RPC") {
            // rpc todo
            return [];
        } else if ($this->getMethod() == "CLI") {
            global $argc;
            global $argv;
            return $argc > 2 ? array_slice($argv, 2) : [];
        }

        return $_POST ?? [];
    }

    public function getRequestSysParams()
    {
        return isset($this->getRequestParams()['sys_data']) ? $this->getRequestParams()['sys_data'] : $this->getSysData();
    }

    private function getSysData()
    {
        $data = [
            'request_id' => RequestContext::get('trace_sr_data.traceid', null),
            'token' => $this->getHeader($this->tokenHeader),
            'start_time' => RequestContext::get('trace_sr_data.timestamp', microtime(true)),
            'project_name' => getJyAppName(),
            'server_addr' => MachineHelper::getLocalIp(),
            'cip' => $this->getRemoteIP(),
            'url' => $this->getUrl(),
            'method' => $this->getMethod()
        ];

        return $data;
    }

    public function getUrl()
    {
        $requestUri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        }

        return ($this->getIsSecureConnection() ? 'https' : 'http'). '://'. $this->getRemoteHost() . $requestUri;
    }

    public function getIsSecureConnection()
    {
        if (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)) {
            return true;
        }

        if ($this->getServerPort() == 443) {
            return true;
        }

        return false;
    }

    public function getRemoteHost()
    {
        return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : (
            isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (
                isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null
            )
        );
    }

    public function getServerPort()
    {
        return isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : null;
    }

    public function getRequestTraceParams()
    {
        return isset($this->getRequestParams()['trace_cs_data']) ? $this->getRequestParams()['trace_cs_data'] : [];
    }

    public function getRequestHeaderParams()
    {
        return isset($this->getRequestParams()['request_header_data']) ? $this->getRequestParams()['request_header_data'] : $this->getHeaders();
    }

    private function getHeaders()
    {
        $headers = [];

        if ($this->getMethod() == "RPC") {
            // rpc todo
        } else {
            foreach ($_SERVER ?? [] as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));

                    $headers[$name] = $value;
                }
            }
        }

        return $headers;
    }

    public function getHeader($h, $default = null)
    {
        return $this->getHeaders()[$h] ?? $default;
    }

    public function hasHeader($h)
    {
        return isset($this->getHeaders()[$h]);
    }

    public function getRemoteIP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    public function getUserIP()
    {
        $ip = $this->getUserIpFromIpHeaders();
        return $ip === null ? $this->getRemoteIP() : $ip;
    }

    private function getUserIpFromIpHeaders() {
        foreach($this->ipHeaders as $ipHeader) {
            if (RequestContext::has('request_header_data'.'.'.$ipHeader)) {
                $ip = RequestContext::get('request_header_data'.'.'.$ipHeader, null);
                if ($ip !== null) {
                    return $ip;
                }
            }
        }

        return null;
    }

    public function __get($name)
    {
        return $this->getArgs()[$name] ?? null;
    }
}
