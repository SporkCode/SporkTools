<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;

class OutputCollection extends AbstractListenerAggregate implements FeatureInterface
{
    protected $filterTypes = array('ping');
    
    protected $messages = array();
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::MESSAGE, array($this, 'addMessage'));
    }
    
    public function addMessage(Event $event)
    {
        $message = $event->getParam('message');
        if (!in_array($message->type, $this->filterTypes)) {
            $this->messages[] = $message;
        }
    }
    
    public function filterTypes(array $types)
    {
        $this->filterTypes = $types;
    }
    
    public function getMessages()
    {
        return $this->messages;
    }
}