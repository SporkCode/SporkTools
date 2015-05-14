<?php
namespace SporkTools\Core\Job;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;

/**
 * An MVC listener aggregate that runs scheduled jobs after the application
 * request has completed. This is intended as a convient way to test jobs in a 
 * development environment and is not recommended to be used on production 
 * servers.  
 */
class ScheduleListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'run'), -10001);
    }
    
    public function run(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        if ($serviceManager->has(ServiceFactory::SERVICE)) {
            $jobManager = $serviceManager->get(ServiceFactory::SERVICE);
            foreach ($jobManager->getJobs() as $job) {
                $schedule = $job->getSchedule();
                $lastScheduledRun = $schedule->getLast();
                if (null !== $lastScheduledRun) {
                    $report = $job->getReport();
                    $lastRun = null === $report ? null : $report->getLastRun();
                    if (null === $lastRun || $lastRun < $lastScheduledRun) {
                        $job->run();
                        $job->save();
                    }
                }
            }
        }
    }
}