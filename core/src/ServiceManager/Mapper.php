<?php
namespace SporkTools\Core\ServiceManager;

use Zend\ServiceManager\ServiceLocatorInterface;
class Mapper
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    protected $serviceManagerName;
    
    public function __construct(ServiceLocatorInterface $serviceManager = null) {
        if (null !== $serviceManager) {
            $this->setServiceManager($serviceManager);
        }
    }
    
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    public function map()
    {
        if (null === $this->serviceManager) {
            throw new \Exception('Service Manager not set');
        }
        
        set_error_handler(function($level, $message, $file, $line) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        });
        
        $map = array();
        $queue = array(array($this->serviceManagerName, $this->serviceManager));
        do {
            list($managerName, $serviceManager) = array_shift($queue);
            if (array_key_exists($managerName, $map)) {
                continue;
            }
            $map[$managerName] = array();
            $serviceManagerRef = new \ReflectionClass($serviceManager);
            
            $instancesRef = $serviceManagerRef->getProperty('instances');
            $instancesRef->setAccessible(true); 
            $instances = $instancesRef->getValue($serviceManager);
            
            $invokablesRef = $serviceManagerRef->getProperty('invokableClasses');
            $invokablesRef->setAccessible(true); 
            $invokables = $invokablesRef->getValue($serviceManager);
            
            $factoriesRef = $serviceManagerRef->getProperty('factories');
            $factoriesRef->setAccessible(true); 
            $factories = $factoriesRef->getValue($serviceManager);
        
            foreach ($invokables as $name => $class) {
                $map[$managerName][$name] = array(
                    'name'      => $name,
                    'class'     => $class,
                    'source'    => 'Class',
                );
                if (array_key_exists($name, $instances)) {
                    $service = $instances[$name];
                    if ($service instanceof ServiceLocatorInterface) {
                        $queue[] = array($name, $service);
                    }
                } elseif (class_exists($class)) {
                    $serviceRef = new \ReflectionClass($class);
                    if ($serviceRef->implementsInterface('\Zend\ServiceManager\ServiceLocatorInterface')) {
                        $service = $serviceManager->get($name);
                        $queue[] = array($name, $service);
                    }
                }
            }
        
            foreach ($factories as $name => $factory) {
                if (array_key_exists($name, $instances)) {
                    $service = $instances[$name];
                    $class = is_object($service) ? get_class($service) : gettype($service);
                } else {
                    try {
                        $service    = $serviceManager->get($name);
                        $class      = get_class($service);
                    } catch (\Exception $exception) {
                        $service    = null;
                        $class      = 'Error creating service';
                    }
                }
                if (is_object($factory)) {
                    if ($factory instanceof \Closure) {
                        $factoryRef = new \ReflectionFunction($factory);
                        $factoryClass = method_exists($factoryRef, 'getClosureThis') ?
                                get_class($factoryRef->getClosureThis()) . '::Closure()' 
                                :  $factoryRef->getName();
                    } else {
                        $factoryClass = get_class($factory);
                    }
                } else {
                    $factoryClass = (string) $factory;
                }
                $map[$managerName][$name] = array(
                    'name'      => $name,
                    'class'     => $class,
                    'source'    => "Factory: $factoryClass",
                );
                if (null !== $service && $service instanceof ServiceLocatorInterface) {
                    $queue[] = array($name, $service);
                }
            }
            
            foreach ($instances as $name => $instance) {
                if (array_key_exists($name, $map[$managerName])) {
                    continue;
                }
                $map[$managerName][$name] = array(
                    'name'      => $name,
                    'class'     => is_object($instance) ? get_class($instance) : gettype($instance),
                    'source'    => 'Instance',
                );
                if ($instance instanceof ServiceLocatorInterface) {
                    $queue[] = array($name, $instance);
                }
            }
        } while (!empty($queue));
        
        restore_error_handler();
        
        return $map;
    }
    
    public function setServiceManager(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $ref = new \ReflectionClass($serviceManager);
        $instancesRef = $ref->getProperty('instances');
        $instancesRef->setAccessible(true); 
        $instances = $instancesRef->getValue($serviceManager);
        $name = array_search($serviceManager, $instances);
        $this->serviceManagerName = $name ?: 'servicemanager';
    }
}