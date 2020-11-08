<?php

namespace Jy\Listener;

use Jy\Contract\Event\EventListenerInterface;
use Jy\Component\Event\EventParam;
use Jy\Common\RequestContext\RequestContext;

class ActionBefore implements EventListenerInterface
{

    public function handle(EventParam $p)
    {
        $data = $p->getData();
        $annotation = \Jy\App::$app->reflect->resolveClass($data['namespace'], $data['action'], "method");

        if($annotation && isset($annotation['valid']) && $annotation['valid']){
            \Jy\Common\Valid\Facades\Valid::match(\Jy\App::$app->request->getArgs(),$annotation['valid']);
        }

    }
}
