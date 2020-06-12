<?php

namespace Jy;

use Jy\Facade\Log;
use Jy\Common\RequestContext\RequestContext;
use Jy\Facade\Trace;

class Dispatcher
{

    public static $protocolDirMap = [
        'cli' => 'Console',
        'http' => 'Controller',
        'rpc' => 'Rpc'
    ];

    public function __construct()
    {
    }

    public function dispatcher()
    {
        return $this->execute();

    }

    public function execute()
    {
        $requests = \Jy\App::$app->request;

        return $this->call($requests);
    }

    private function call(Request $request)
    {
        $module = $request->getModule();
        $action = $request->getAction();
        $protocol = $request->getProtocol();
        $version = $request->getVersion();

        $dir = static::$protocolDirMap[$protocol] ?? 'Console';

        $namespace = "Rouchi\\{$dir}" .
            "\\". ($version) . "\\" .
            str_ireplace("/", "\\\\", $module);

        if (!class_exists($namespace)) {
            throw new \Exception('controller : '. $namespace .' not exists');
        }

        $controller = \Jy\App::$app->di->getClassInstance($namespace);

        if (!method_exists($controller, $action)) {
            throw new \Exception('action : '. $namespace .':'. $action .' not exists');
        }

        \Jy\Event::trigger('JY.ACTION.BEFORE', [
            'namespace' => $namespace,
            'action' => $action
        ]);

        $para = \Jy\App::$app->di->initMethod($namespace,$action,$controller);
        $result = call_user_func_array([$controller, $action], $para ?? []);

        echo $result;

        \Jy\Event::trigger('JY.REQUEST.END', $result);

        exit();
    }
}
