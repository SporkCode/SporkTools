<?php
namespace SporkTools\Core\Job;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SporkTools\Core\Job\Plugin\PluginInterface;

abstract class AbstractBase implements EventManagerAwareInterface
{
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $eventManager;
    
    protected $id;
    
    protected $name;
    
    protected $pluginMap = array();
    
    protected $plugins = array();

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    public function __call($method, $arguments)
    {
        $method = strtolower($method);
        if (isset($this->pluginMap[$method])) {
            $plugin = $this->plugins[$this->pluginMap[$method]];
            return call_user_func_array(array($plugin, $method), $arguments);
        } else {
            throw new \Exception(sprintf("Call to undefined method %s::%s()", get_class($this), $method));
        }
    }

    public function addPlugin(PluginInterface $plugin)
    {
        $class = get_class($plugin);
        $this->plugins[$class] = $plugin;
        foreach ($plugin->getMethods() as $method) {
            $method = strtolower($method);
            $this->pluginMap[$method] = $class;
        }
    }
    
    /**
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getPlugin($name)
    {
        return isset($this->plugins[$name]) ? $this->plugins[$name] : null;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    /**
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function setServiceManager(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
}