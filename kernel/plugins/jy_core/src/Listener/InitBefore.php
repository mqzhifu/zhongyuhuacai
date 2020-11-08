<?php

namespace Jy\Listener;

use Jy\Contract\Event\EventListenerInterface;
use Jy\Component\Event\EventParam;
use Jy\Common\RequestContext\RequestContext;
use Jy\Facade\Config;

class InitBefore implements EventListenerInterface
{

    public function handle(EventParam $p)
    {

        $check = new \Jy\Util\CheckFramework();
        $check->check();

        date_default_timezone_set(Config::get('@app.timezone','PRC'));

        RequestContext::create();

    }
}
