<?php

namespace Jy\Listener;

use Jy\Contract\Event\EventListenerInterface;
use Jy\Contract\Middleware\MiddlewareInterface;
use Jy\Common\RequestContext\RequestContext;
use Jy\Component\Event\EventParam;
use Jy\Facade\Config;

class Middleware implements EventListenerInterface
{

    public function handle(EventParam $p)
    {
        $data = $p->getData();
        $annotation = \Jy\App::$app->reflect->resolveClass($data['namespace'], $data['action'], "method");

        if($annotation && isset($annotation['middleware']) && $annotation['middleware']){
            $mids = $annotation['middleware'];

            // todo co
            //
            // sys config
            $framMidConf = Config::get('@fram.framConfig.middleware');

            //.. 用户的MID
            $UsermidConf = Config::get('@app.middleware');

            foreach ([
                $framMidConf ?? [],
                $UsermidConf ?? []
            ] as $items) {
                foreach ($items as $item) {
                    if (!isset($item['className']) || !class_exists($item['className'])) continue;

                    $obj = \Jy\App::$app->di->getClassInstance($item['className']);

                    if ($obj instanceof MiddlewareInterface) {
                        $obj->handle();
                    }
                }
            }
        }
    }
}
