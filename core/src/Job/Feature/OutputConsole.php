<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use SporkTools\Core\Job\Event;

class OutputConsole extends AbstractListenerAggregate implements FeatureInterface
{
    protected static $ping = false;
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::MESSAGE, array($this, 'onMessage'));
    }
    
    public function onMessage(EventInterface $event)
    {
        $message = $event->getParam('message');
        $text = $message->text;
        $type = $message->type;
        switch ($type) {
        	case 'warning':
        	    $text = "*** $text ***";
        	    break;
        	case 'error':
        	    $text = "!!! $text !!!";
        	    break;
        }
        
        if ($type != 'ping' && self::$ping == true) {
            echo PHP_EOL;
            self::$ping = false;
        }
        
        echo $text;
        
        if ($type == 'ping') {
            self::$ping = true;
        } else {
            echo PHP_EOL;
        }
    }
}