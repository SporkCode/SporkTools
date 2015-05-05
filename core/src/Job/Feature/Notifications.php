<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;

class Notifications extends AbstractListenerAggregate implements FeatureInterface
{
    protected $jobStartTime;
    
    protected $jobStartMemory;
    
    protected $taskStartTime;
    
    protected $taskStartMemory;
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'jobHeader'), 1000);
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'jobFooter'), -1000);
        $this->listeners[] = $events->attach(Event::RUN_TASK, array($this, 'taskHeader'), 1000);
        $this->listeners[] = $events->attach(Event::RUN_TASK, array($this, 'taskFooter'), -1000);
    }
    
    public function jobHeader(Event $event)
    {
        $this->jobStartTime = microtime(true);
        $this->jobStartMemory = memory_get_usage(true);
        $job = $event->getTarget();
        $job->info("Starting job {$job->getName()}");
    }
    
    public function jobFooter(Event $event)
    {
        $job = $event->getTarget();
        $job->info("Completed job {$job->getName()} {$this->elapsed()}");
    }
    
    public function taskHeader(Event $event)
    {
        $this->taskStartTime = microtime(true);
        $this->taskStartMemory = memory_get_usage(true);
        $task = $event->getTarget();
        $task->info("Starting task {$task->getName()}");
    }
    
    public function taskFooter(Event $event)
    {
        $task = $event->getTarget();
        $task->info("Completed task {$task->getName()} {$this->elapsed()}");
    }
    
    protected function elapsed()
    {
        $elapsedTime = round(microtime(true) - $this->taskStartTime, 2);
        $memoryDelta = memory_get_usage(true) - $this->taskStartMemory;
        if ($memoryDelta > 1073741824) {
            $memoryDelta = $memoryDelta / 1073741824;
            $memoryUnit = 'G';
        } elseif ($memoryDelta > 1048576) {
            $memoryDelta = $memoryDelta / 1048576;
            $memoryUnit = 'M';
        } elseif ($memoryDelta > 1024) {
            $memoryDelta = $memoryDelta / 1024;
            $memoryUnit = 'K';
        } else {
            $memoryUnit = 'B';
        }
        return "($elapsedTime s, $memoryDelta $memoryUnit)";
    }
}