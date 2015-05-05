<?php
namespace SporkTools\Core\Job\Feature;

use SporkTools\Core\Job\Manager;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use SporkTools\Core\Job\AbstractTask;
use SporkTools\Core\Job\AbstractJob;
use SporkTools\Core\Job\Event;

class LoaderFile extends AbstractListenerAggregate implements FeatureInterface
{
    /**
     * List of paths to folders containing jobs
     * 
     * @var array
     */
    protected $paths = array();
    
    public function __construct($options)
    {
        if (is_string($options)) {
            $this->addPath($options);
        } elseif (is_array($options)) {
            if (isset($options['paths'])) {
                $this->addPaths((array) $options['paths']);
            }
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::FETCH_JOBS, array($this, 'fetchJobs'));
        $this->listeners[] = $events->attach(Event::FETCH_TASKS, array($this, 'fetchTasks'));
        $this->listeners[] = $events->attach(Event::HAS_FEATURE, array($this, 'onHasFeature'));
    }
    
    public function addPath($path, $namespace = null)
    {
        $this->paths[] = array('path' => $path, 'namespace' => $namespace);
    }

    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            if (is_array($path)) {
                $this->addPath($path['path'], 
                        isset($path['namespace']) ? $path['namespace'] : null);
            } else {
                $this->addPath($path);
            }
        }
    }

    public function getPaths()
    {
        return $this->paths;
    }
    
    public function fetchJobs(EventInterface $event)
    {
        $jobs = array();

        foreach ($this->paths as $path) {
            $namespace = $path['namespace'];
            $path = $path['path'];
            if (!is_dir($path)) {
                throw new \Exception("Jobs path '$path' is not valid");
            }
            
            $jobManager = $event->getTarget();
            $eventManager = $jobManager->getEventManager();
            $serviceManager = $jobManager->getServiceManager();
            
            $files = scandir($path);
            foreach ($files as $file) {
                if (substr($file, -4) == '.php') {
                    $name = substr($file, 0, -4);
                    if (in_array($name, $files) && is_dir($path . DIRECTORY_SEPARATOR . $name)) {
                        require_once $path . DIRECTORY_SEPARATOR . $file;
                        $class = ($namespace ? $namespace . '\\' : '') . $name;
                        if (!class_exists($class)) {
                            throw new \Exception("Class '$class' not found for job '$file'");
                        }
                        $job = new $class();
                        if (!$job instanceof AbstractJob) {
                            throw new \Exception("Job '$name' does not extend AbstractJob");
                        }
                        $job->setId(substr($class, strrpos($class, '\\') + 1));
                        $job->setName($class, false);
                        $job->setEventManager($eventManager);
                        $job->setServiceManager($serviceManager);
                        $jobs[$name] = $job;
                    }
                }
            }
        }
        return $jobs;
    }
    
    public function fetchTasks(EventInterface $event)
    {
        $job = $event->getTarget();
        $path = false;
        foreach ($this->paths as $path) {
            if (is_dir($path['path'] . DIRECTORY_SEPARATOR . $job->getId())) {
                $namespace = $path['namespace'];
                $path = $path['path'] . DIRECTORY_SEPARATOR . $job->getId();
                break;
            }
        }
        if (false === $path) {
            throw new \Exception("Tasks folder '$path' does not exist");
        }
        $eventManager = $job->getEventManager();
        $tasks = array();
        foreach (scandir($path) as $file) {
            if (preg_match('`^[0-9]+-([a-zA-Z0-9]+).php$`', $file, $matches)) {
                require_once $path . DIRECTORY_SEPARATOR . $file;
                $class = $matches[1];
                if (null !== $namespace) {
                    $class = $namespace . '\\' . $job->getId() . '\\' . $class;
                }
                if (!class_exists($class)) {
                    throw new \Exception("Job class '$class' not found");
                }
        
                $task = new $class();
                if (!$task instanceof AbstractTask) {
                    throw new \Exception("Job task '$class' not instance of Task");
                }
                
                $className = substr($class, strrpos($class, '\\') + 1);
                $task->setId($className);
                $task->setName($className);
                $tasks[] = $task;
            }
        }
        
        return $tasks;
    }
    
    public function onHasFeature(EventInterface $event)
    {
        $feature = $event->getParam('feature');
        
        if ($feature == FeatureInterface::ENABLED) {
            return !empty($this->paths);
        }
        
        return false;
    }
}