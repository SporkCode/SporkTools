<?php
namespace SporkTools\Core\EventManager;

use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\PriorityQueue;

class Mapper
{
    protected $events;
    
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $eventManager;
    
    protected $name;
    
    public function __construct($name, EventManagerInterface $eventManager)
    {
        $this->name = $name;
        $this->eventManager = $eventManager;
    }
    
    public function getEvents()
    {
        if (null === $this->events) {
            $this->mapEvents();
        }
        
        return $this->events;
    }
    
    public function getIdentifiers()
    {
        return $this->eventManager->getIdentifiers();
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    protected function mapEvents()
    {
        $eventManager = $this->eventManager;
        $sharedEventManager = $eventManager->getSharedManager();
        $identifiers = $this->eventManager->getIdentifiers();
        $events = array_flip($this->sortEvents($eventManager->getEvents()));
        
        // Get Listeners
        foreach (array_keys($events) as $event) {
            $queue = new PriorityQueue();
            foreach ($eventManager->getListeners($event) as $listener) {
                $listener = $this->normalizeListener($listener);
                $queue->insert($listener, $listener['priority']);
            }
            foreach ($identifiers as $identifier) {
                $listeners = $sharedEventManager->getListeners($identifier, $event);
                if (false !== $listeners) {
                    foreach ($listeners as $listener) {
                        $listener = $this->normalizeListener($listener, $identifier);
                        $queue->insert($listener, $listener['priority']);
                    }
                }
            }
            $events[$event] = $queue;
        }
        $this->events = $events;
    }
    
    protected function normalizeListener($listener, $identifier = '*')
    {
        $callback = $listener->getCallback();
        $metadata = $listener->getMetadata();
        $priority = $metadata['priority'];
        if (null === $priority) {
            $priority = 1;
        } elseif (is_array($priority)) {
            $priority = array_shift($priority);
        }
        
        if ($callback instanceof \Closure) {
            $closureRef = new \ReflectionFunction($callback);
            if (method_exists($closureRef, 'getClosureThis')) {
                $info = array(
                    'class'     => get_class($closureRef->getClosureThis()),
                    'method'    => 'Closure',
                    'priority'  => $priority);
            } else {
                $info = array(
                    'class'     => $closureRef->getName(),
                    'method'    => 'Closure',
                    'priority'  => $priority);
            }
        } else {
            $info = array(
                'class'     => get_class($callback[0]),
                'method'    => $callback[1],
                'priority'  => $priority);
        }
        $info['identifier'] = $identifier;
        return $info;
    }
    
    protected function sortEvents($events)
    {
        $order = array(
            'bootstrap',
            'route',
            'dispatch',
            'dispatch.error',
            'render',
            'render.error',
            'finish',
        );
        usort($events, function($a, $b) use ($order) {
            return array_search($a, $order) - array_search($b, $order);
        });
        return $events;
    }
}