<?php

namespace Jy\Contract\Event;

use Jy\Component\Event\EventItem;
use Jy\Component\Event\EventParam;
use Jy\Facade\Config;
use Jy\Contract\Event\EventListenerInterface;

abstract class EventAbstract implements EventInterface
{
    /**
     * 事件数据
     */
    private $events = [];

    /**
     * 事件队列
     */
    private $eventQueue = [];

    /**
     * 事件更改记录
     *
     * @var array
     */
    private $eventChangeRecords = [];

    /**
     * 事件监听
     * @param string|string[] $name 事件名称
     * @param mixed $callback 回调
     * @param int $priority 优先级
     * @return void
     */
    public function on($name, $callback, $priority = 0)
    {
        foreach(is_array($name) ? $name : [$name] as $eventName) {
            $this->events[$eventName][] = new EventItem($callback, $priority);
            $this->eventChangeRecords[$eventName] = true;
        }
    }

    /**
     * 取消事件监听
     * @param string|string[] $name 事件名称
     * @param mixed $callback 回调
     * @return void
     */
    public function off($name, $callback)
    {
        foreach(is_array($name) ? $name : [$name] as $eventName) {
            if(isset($this->events[$eventName])) {
                $map = &$this->events[$eventName];

                foreach($this->events[$eventName] as $k => $item) {
                    if($callback === $item->callback) {
                        unset($map[$k]);
                    }
                }

                $this->eventChangeRecords[$eventName] = true;
            }
        }
    }

    /**
     * 触发事件
     * @param string $name 事件名称
     * @param array $data 数据
     * @param mixed $target 目标对象
     * @param string $paramClass 参数类
     * @return void
     */
    public function trigger($name, $data = [], $target = null, $paramClass = EventParam::class)
    {
        if(!isset($this->eventQueue[$name])) {
            $classEventdata = Config::get('@fram.framConfig.event');

            if(!isset($this->events[$name])) {
                $this->events[$name] = [];
            }

            $eventsMap = &$this->events[$name];
            $this->rebuildEventQueue($name);
            foreach((array) $classEventdata[$name] ?? [] as $callback) {
                $eventsMap[] = $item = new EventItem($callback['className'], $callback['priority']);
                $this->eventQueue[$name]->insert($item, $callback['priority']);
            }
        } else if (empty($this->events[$name])) {
            return;
        } else if (isset($this->eventChangeRecords[$name])) {
            $this->rebuildEventQueue($name);
        }

        $callbacks = clone $this->eventQueue[$name];

        $param = new $paramClass($name, $data, $target);
        $oneTimeCallbacks = [];

        // todo co
        foreach($callbacks as $option) {
            $callback = $option->callback;

            if (!class_exists($callback)) continue;

            $obj = \Jy\App::$app->di->getClassInstance($callback);

            if ($obj instanceof EventListenerInterface) {
                $obj->handle($param);
            }
        }
    }

    /**
     * 重建事件队列
     * @return void
     */
    private function rebuildEventQueue($name)
    {
        $this->eventQueue[$name] = new \SplPriorityQueue;
        foreach($this->events[$name] ?? [] as $item) {
            $this->eventQueue[$name]->insert($item, $item->priority);
        }
        $this->eventChangeRecords[$name] = null;
    }
}
