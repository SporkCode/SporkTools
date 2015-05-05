<?php
namespace SporkTools\Core\Job\Plugin;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;

abstract class AbstractPlugin extends AbstractListenerAggregate implements PluginInterface
{
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'injectPlugin'), 1000);
        $this->listeners[] = $events->attach(Event::RUN_TASK, array($this, 'injectPlugin'), 1000);
    }
    
    public function injectPlugin(Event $event)
    {
        $event->getTarget()->addPlugin($this);
    }
}