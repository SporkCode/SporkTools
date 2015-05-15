<?php
namespace SporkTools\Core\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;
use Zend\ServiceManager\ServiceLocatorInterface;

class Controller extends AbstractController
{
    public function onDispatch(MvcEvent $event) {
    }
}

class Events extends AbstractHelper
{
    protected $renderCss = true;
    
    /**
     * @var \Zend\View\Helper\EscapeHtml
     */
    protected $escape;
    
    protected $profiles = array(
        'application' => array(
            'name' => 'Application Events',
            'identifiers' => array(
                'Zend\Mvc\Application',
            ),
            'events' => array(
                'bootstrap',
                'route',
                'dispatch',
                'dispatch.error',
                'render',
                'render.error',
                'finish',
            ),
            'map' => array(
                'Zend\Mvc\DispatchListener::onDispatch' 
                        => array('class' => 'SporkTools\\Core\\View\\Helper\\Controller'),
                'Zend\Mvc\View\Http\DefaultRenderingStrategy::render' 
                        => array('service' => 'View'),
                'Zend\Mvc\SendResponseListener::sendResponse'
                        => array('service' => 'SendResponseListener'),
            ),
        ),
        'controller' => array(
            'name' => 'Controller Events',
            'identifiers' => array(
                'Zend\Mvc\Controller\AbstractController',
            ),
        ),
        'view' => array(
            'name' => 'View Events',
            'identifiers' => array(
                'Zend\View\View',
            ),
        ),
        'response' => array(
            'name' => 'Response Events',
            'identifiers' => array(
                'Zend\Mvc\SendResponseListener',
            ),
        ),
        'module' => array(
            'name' => 'Module Events',
            'identifiers' => array(
                'Zend\ModuleManager\ModuleManager',
            ),
            'events' => array(
                'loadModules',
                'loadModules.post',
            ),
            'map' => array(
                'Zend\ModuleManager\ModuleManager::onLoadModules' 
                        => array('profile' => 'loadModule'),
                'Zend\ModuleManager\Listener\ConfigListener::onLoadModules' 
                        => array('profile' => 'configureModules')
            )
        ),
        'loadModule' => array(
            'name' => 'Load Module',
            'events' => array(
                'loadModule.resolve',
                'loadModule',
            )
        ),
        'configureModules' => array(
            'name' => 'Configure Modules',
            'events' => array('mergeConfig'),
        )
    );
    
    public function __invoke(EventManagerInterface $eventManager, $profile = null, $renderCss = null)
    {
        $profile = $this->getProfile($eventManager, $profile);

        $html = '';
        foreach ($profile['events'] as $event) {
            $html .= $this->renderEvent($event, $eventManager, $profile);
        }
        
        $identifiers = $eventManager->getIdentifiers();
        
        $html = <<<HDOC
<h1 class="eventManager">{$this->escape($profile['name'])}
    <span class="identifiers">({$this->escape(implode(', ', $identifiers))})</span>
</h1>
<ol class="events">$html</ol>
HDOC;
        
        if ($renderCss === true || ($renderCss !== false && $this->renderCss == true)) {
            $html .= $this->renderCss();
            $this->renderCss = false;
        }
        
        return $html;
    }
    
    protected function escape($value)
    {
        if (null === $this->escape) {
            $this->escape = $this->getView()->plugin('escapeHtml');
        }
        
        $escape = $this->escape;
        return $escape($value);
    }
    
    protected function getListenerInfo(CallbackHandler $listener, $identifier = '*')
    {
        $info = array(
            'class' => 'Unknown class',
            'method' => 'Unknown method',
            'identifier' => $identifier);
        
        $priority = $listener->getMetadatum('priority');
        if (null === $priority) {
            $priority = 1;
        } elseif (is_array($priority)) {
            $priority = array_shift($priority);
        }
        $info['priority'] = $priority;
        
        $callback = $listener->getCallback();
        if (is_array($callback)) {
            $info['class'] = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            $info['method'] = $callback[1];
        } elseif ($callback instanceof \Closure) {
            $closureRef = new \ReflectionFunction($callback);
            if (method_exists($closureRef, 'getClosureThis')) {
                $info['class'] = get_class($closureRef->getClosureThis());
            } else {
                $info['class'] = $closureRef->getName();
            }
            $info['method'] = 'CLOSURE';
        } elseif (is_object($callback)) {
            $info['class'] = get_class($callback);
            $info['method'] = 'INVOKABLE';
        }
        
        return $info;
    }
    
    protected function getProfile(EventManagerInterface $eventManager, $profile = null)
    {
        $profiles = $this->profiles;
        if (null === $profile) {
            $profile = $this->searchProfiles($eventManager->getIdentifiers());
        } elseif (is_scalar($profile)) {
            if (!array_key_exists($profile, $profiles)) {
                throw new \Exception("Profile '$profile' not found");
            }
            $profile = $profiles[$profile];
        }
        
        $profile = array_merge(array(
            'name' => 'Event Manager',
            'identifiers' => array(),
            'events' => array(),
            'map' => array(),
        ), $profile);
        
        if (empty($profile['events'])) {
            $profile['events'] = $eventManager->getEvents();
        }
        
        return $profile;
    }
    
    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface 
     */
    protected function getServices()
    {
        return $this->getView()->getHelperPluginManager()->getServiceLocator();
    }
    
    protected function renderEvent($name, EventManagerInterface $eventManager, $profile)
    {
        $listeners = new PriorityQueue();
        foreach ($eventManager->getListeners($name) as $listener) {
            $info = $this->getListenerInfo($listener);
            $listeners->insert($info, $info['priority']);
        }
        $sharedEvents = $eventManager->getSharedManager();
        foreach ($eventManager->getIdentifiers() as $identifier) {
            $sharedListeners = $sharedEvents->getListeners($identifier, $name); 
            if ($sharedListeners) {
                foreach ($sharedListeners as $sharedListener) {
                    $info = $this->getListenerInfo($sharedListener, $identifier);
                    $listeners->insert($info, $info['priority']);
                }
            }
        }
        
        $html = '';
        
        foreach ($listeners as $listener) {
            $html .= $this->renderListener($listener, $eventManager, $profile);
        }
        
        $html = <<<HDOC
<li><span class="name">{$this->escape($name)}</span>
    <ol class="listeners">$html</ol></li>
HDOC;
                
        return $html;
    }
    
    protected function renderListener(array $listener, EventManagerInterface $eventManager, $profile)
    {
        extract($listener);
        $html = $this->renderChildEvents($listener, $profile['map'], $eventManager);
        $html = <<<HDOC
<li>
    <span class="class">{$this->escape($class)}</span> ::
    <span class="method">{$this->escape($method)}</span>
    <dl>
        <dt class="priority">Priority</dt>
        <dd class="priority">{$this->escape($priority)}</dd>
        <dt class="identifier">Identifier</dt>
        <dd class="identifier">{$this->escape($identifier)}</dd>
    </dl>
    $html
</li>
HDOC;
        return $html;
    }
    
    protected function renderChildEvents(array $listener, array $map, EventManagerInterface $eventManager)
    {
        $key = $listener['class'] . '::' . $listener['method'];
        if (!array_key_exists($key, $map)) {
            return '';
        }
        
        $path = $map[$key];
        
        $profile = isset($path['profile']) ? $path['profile'] : null;
        
        if (array_key_exists('class', $path)) {
            $instance = new $path['class']();
        }
        
        if (array_key_exists('service', $path)) {
            $instance = $this->getServices()->get($path['service']);
        }

        if (isset($instance)) {
            if ($instance instanceof EventManagerAwareInterface) {
                $instance = $instance->getEventManager();
            }
            if (!$instance instanceof EventManagerInterface) {
                throw new \Exception('Could not resolve event manager');
            }
            $eventManager = $instance;
        } elseif (null === $profile) {
            throw new \Exception('Could not resolve event map');
        }
        return $this($eventManager, $profile);
    }
    
    protected function renderCss()
    {
        return <<<CSS
<style>
h1.eventManager {
    border-radius: 14px 14px 0 0;
    padding: .6em 1ex .2em 1ex;
    margin: 1em 0 0 0;
    
    color: #444444;
    background-color: #CCCCCC;
    
    font-size: 200%;
    line-height: 50%;
}

h1.eventManager .identifiers {
    font-size: 50%;
}

ol.events {
    padding: 0;
    margin: 0;
    
    list-style-position: inside;

}

ol.events > li {
    margin: 0;
    padding: .25em 1ex;
    
    color: #444444;
    background-color: #CCCCCC;
    
    font-size: 166%;
    font-weight: 700;
}
            
ol.events > li:last-child {
    border-radius: 0 0 14px 14px;
    margin: 0 0 .5em 0;
}

ol.events > li > span.name {
    display: inline-block;
    margin: 2px 0;    
    border-radius: 14px;
    padding: 3px 2ex;
    
    color: #CCCCCC;
    background-color: #444444;
            
    font-size: 75%;
    vertical-align: text-bottom;
}

ol.listeners {
    padding-left: 8ex;
    list-style-position: outside;

    font-size: 60.241%;
    font-weight: normal;
}

ol.listeners > li {
    margin: .25em 0;
    border-radius: 14px;
    border: 2px solid #CCCCCC;
    padding: .25em 2ex;
    
    background-color: #EEEEEE;
}

ol.listeners > li span.class {
    font-weight: bold;
}

ol.listeners > li dl dt,
ol.listeners > li dl dd {
    display: inline-block;
    padding: 0 1ex 0 0;
    margin: 0;
    
    color: #666666;
}

ol.listeners li dl dt:AFTER {
    content: ': ';
}

ol.listeners li dl dt {
    font-weight: 700;
}
            
ol.listeners > li h1.eventManager {
    margin-top: .5em;
}
</style>
CSS;
    }
    
    protected function searchProfiles(array $identifiers)
    {
        foreach ($this->profiles as $profile) {
            if (array_key_exists('identifiers', $profile)) {            
                foreach ($profile['identifiers'] as $identifier) {
                    if (!in_array($identifier, $identifiers)) {
                        continue 2;
                    }
                }
                return $profile;
            }
        }
        return array();
    }
}