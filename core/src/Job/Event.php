<?php
namespace SporkTools\Core\Job;

use Zend\EventManager\EventManagerInterface;

class Event extends \Zend\EventManager\Event
{
    const DELETE_JOB        = 'controlJobDelete';
    
    const FETCH_JOBS        = 'controlJobFetch';
    
    const FETCH_REPORT      = 'controlJobFetchReport';

    const FETCH_SCHEDULE    = 'controlJobFetchSchedule';
    
    const FETCH_TASKS       = 'controlJobFetchTasks';
    
    const HAS_FEATURE       = 'controlJobHasFeature';
    
    const MESSAGE           = 'controlJobMessage';
    
    const RUN_JOB           = 'controlJobRun';
    
    const RUN_TASK          = 'controlJobRunTask';
    
    const STORE_JOB         = 'controlJobStore';
    
    const STORE_REPORT      = 'controlJobStoreReport';
    
    const STORE_SCHEDULE    = 'controlJobStoreSchedule';
    
    const STORE_TASKS       = 'controlJobStoreTasks';
    
    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @throws \Exception on Service Manager not available
     */
    public function getServiceManager()
    {
        if ($this->target instanceof AbstractBase) {
            return $this->target->getServiceManager();
        }
        
        throw new \Exception('Service Manager not available');
    }
}