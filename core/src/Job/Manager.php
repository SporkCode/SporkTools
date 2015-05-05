<?php
namespace SporkTools\Core\Job;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Manager implements EventManagerAwareInterface, ServiceManagerAwareInterface
{
    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $eventManager;
    
    protected $jobs;
    
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    public function addFeature($feature, $options = array())
    {
        if (is_string($feature) && class_exists($feature)) {
            $feature = new $feature($options);
        }
        
        if (!$feature instanceof Feature\FeatureInterface) {
            throw new \Exception(sprintf("Invalid job feature '%s'", 
                is_object($feature) ? get_class($feature) : $feature));
        }
        
        $this->getEventManager()->attachAggregate($feature);
    }
    
    /**
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }
        
        return $this->eventManager;
    }
    
    /**
     * @param string $name
     * @return \SporkTools\Core\Job\Job
     */
    public function getJob($id)
    {
        $jobs = $this->getJobs();
        
        return array_key_exists($id, $jobs) ? $jobs[$id] : null;
    }
    
    public function getJobs()
    {
        if (null === $this->jobs) {
            //$serviceManager = $this->getServiceManager();
            $eventManager = $this->getEventManager();
            $event = new Event(Event::FETCH_JOBS, $this);
            $responses = $eventManager->trigger($event);
            $this->jobs = array();
            $this->addJobs($responses);
            /*
            foreach ($responses as $response) {
                if ($response instanceof AbstractJob) {
                    $this->jobs[$response->getId()] = $response;
                } elseif (is_array($response)) {
                    foreach ($response as $job) {
                        if ($job instanceof AbstractJob) {
                            $this->jobs[$job->getId()] = $job;
                        }
                    }
                }
            }
            */
        }
        
        return $this->jobs;
    }
    
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    public function hasFeature($name)
    {
        $eventManager = $this->getEventManager();
        $event = new Event(Event::HAS_FEATURE, $this, array('feature' => $name));
        $responses = $eventManager->triggerUntil($event, function ($response) {return $response == true;});
        return $responses->last() == true;
    }
    
    /**
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->injectDefaultListeners($eventManager);
        $this->eventManager = $eventManager;
    }
    
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    protected function addJobs($jobs)
    {
        if (!is_array($jobs) && !$jobs instanceof \Traversable) {
            throw new \Exception("Jobs must be an array or instance of Traversable");
        }
        
        foreach ($jobs as $job) {
            if ($job instanceof AbstractJob) {
                $this->jobs[$job->getId()] = $job;
            } elseif (is_array($job) || $job instanceof \Traversable) {
                $this->addJobs($job);
            }
        }
    }
    
    /**
     * @param EventManagerInterface $eventManager
     */
    protected function injectDefaultListeners(EventManagerInterface $eventManager)
    {
        $eventManager->attachAggregate(new Plugin\Message());
        $eventManager->attachAggregate(new Feature\Executor());
        $eventManager->attachAggregate(new Feature\Notifications());
    }
}