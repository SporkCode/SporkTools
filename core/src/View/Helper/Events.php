<?php
namespace SporkTools\Core\View\Helper;

use SporkTools\Core\Controller\IndexController;

use Zend\View\Helper\AbstractHelper;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;

class Events extends AbstractHelper
{
    protected $triggerMap = array(
        'Zend\Mvc\DispatchListener::onDispatch' => 'renderControllerEvents',
    );
    
    public function __invoke(EventManagerInterface $events, $name = 'Application Events')
    {
        $sharedEvents = $events->getSharedManager();
        $identifiers = $events->getIdentifiers();
        
        $eventsHtml = '';
        foreach ($this->sortEvents($events->getEvents()) as $event) {
            // get listeners
            $listeners = new PriorityQueue();
            foreach ($events->getListeners($event) as $listener) {
                $priority = $this->getPriority($listener);
                $class = $this->getClass($listener);
                $method = $this->getMethod($listener);
                $listeners->insert(array(
                    'priority' => $priority,
                    'class' => $class,
                    'method' => $method,
                    'identifier' => '*',
                ), $priority);
            }
            foreach ($identifiers as $identifier) {
                $sharedListeners = $sharedEvents->getListeners($identifier, $event); 
                if ($sharedListeners) {
                    foreach ($sharedListeners as $sharedListener) {
                        $priority = $this->getPriority($sharedListener);
                        $class = $this->getClass($listener);
                        $method = $this->getMethod($listener);
                        $listeners->insert(array(
                            'priority' => $priority,
                            'class' => $class,
                            'method' => $method,
                            'identifier' => $identifier,
                        ), $priority);
                    }
                }
            }
            
            // render listeners
            $listenersHtml = '';
            foreach ($listeners as $listener) {
                extract($listener);
                $key = $class . '::' . $method;
                if (array_key_exists($key, $this->triggerMap)) {
                    $eventsHtml = call_user_func(array($this, $this->triggerMap[$key]));
                } else {
                    $eventsHtml = '';
                }
                $listenersHtml .= $this->renderListener(
                    $class, 
                    $method, 
                    $priority,
                    $identifier,
                    $eventsHtml);
            }
            
            // render event
            $eventsHtml .= $this->renderEvent($event, $listenersHtml);
        }
        
        $html = $this->renderEvents($name, $identifiers, $eventsHtml);
        return $html;
    }

    protected function getClass(CallbackHandler $listener)
    {
        $callback = $listener->getCallback();
        if ($callback instanceof \Closure) {
            $closureRef = new \ReflectionFunction($callback);
            if (method_exists($closureRef, 'getClosureThis')) {
                return get_class($closureRef->getClosureThis());
            }
            return $closureRef->getName();
        }
        return get_class($callback[0]);
    }

    protected function getMethod(CallbackHandler $listener)
    {
        $callback = $listener->getCallback();
        if ($callback instanceof \Closure) {
            return 'CLOSURE';
        }
        return $callback[1];
    }
    
    protected function getPriority(CallbackHandler $listener) 
    {
        $priority = $listener->getMetadatum('priority');
        
        if (null === $priority) {
            return 1;
        }
        
        if (is_array($priority)) {
            return array_shift($priority);
        }
        
        return $priority;
    }
    
    protected function renderEvents($name, array $identifiers, $events)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $html = <<<HDOC
<h1 class="eventManager">{$escape($name)}
    <span class="identifiers">({$escape(implode(', ', $identifiers))})</span>
    <ol class="events">$events</ol>
</h1>
HDOC;
        return $html;
    }
    
    protected function renderEvent($name, $listeners)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $html = <<<HDOC
<li><span class="name">{$escape($name)}</span>
    <ol class="listeners">$listeners</ol>
</li>        
HDOC;
        return $html;
    }
    
    protected function renderListener($class, $method, $priority, $identifier, $events)
    {
        $escape = $this->getView()->plugin('escapeHtml');
        $html = <<<HDOC
<li>
    <span class="class">{$escape($class)}</span> ::
    <span class="method">{$escape($method)}</span>
    <dl>
        <dt class="priority">Priority</dt>
        <dd class="priority">{$escape($priority)}</dd>
        <dt class="identifier">Identifier</dt>
        <dd class="identifier">{$escape($identifier)}</dd>
    </dl>
    $events
</li>
HDOC;
        return $html;
    }
    
    protected function renderControllerEvents()
    {
        $controller = new IndexController();
        $events = $controller->getEventManager();
        return $this($events, 'Controller Events');
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