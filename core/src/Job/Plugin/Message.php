<?php
namespace SporkTools\Core\Job\Plugin;

use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;

class Message extends AbstractPlugin
{
    protected $eventManager;
    
    public function attach(EventManagerInterface $events)
    {
        parent::attach($events);
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'initialize'), 1000);
    }
    
    public function getMethods()
    {
        return array(
        	'error',
            'info',
            'ping',
            'warning',
        );
    }
    
    public function initialize(Event $event)
    {
        $this->eventManager = $event->getTarget()->getEventManager();
    }

    /**
     * Generate an error message
     *
     * @param string $message
     */
    public function error($message)
    {
        return $this->message($message, 'error');
    }
    
    /**
     * Generate a message
     *
     * @param string $message
     */
    public function info($message)
    {
        return $this->message($message);
    }
    
    /**
     * Output a charater to indicate activity
     *
     * @param string $character
     */
    public function ping($character = '.')
    {
        return $this->message($character, 'ping');
    }
    
    /**
     * Generate warning message
     *
     * @param string $message
     */
    public function warning($message)
    {
        return $this->message($message, 'warning');
    }
    
    protected function message($message, $type = 'info', $class = null)
    {
        if (!$message instanceof Message) {
            $message = new \SporkTools\Core\Job\Message($message, $type, $class);
        }
        $event = new Event(Event::MESSAGE, $this, array('message' => $message));
        $this->eventManager->trigger($event);
    }
}