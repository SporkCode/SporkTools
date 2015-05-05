<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;
use SporkTools\Core\Job\Message;

class Executor extends AbstractListenerAggregate implements FeatureInterface
{
    protected $messages = array();
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'executeJob'));
        $this->listeners[] = $events->attach(Event::RUN_TASK, array($this, 'executeTask'));
    }
    
    public function executeJob(Event $event)
    {
        $job = $event->getTarget();
        $eventManager = $job->getEventManager();
        $tasks = $job->getTasks();
        $taskEvent = new Event(Event::RUN_TASK);
        $taskEvent->setParams($event->getParams());
        foreach ($tasks as $task) {
            $taskEvent->setTarget($task);
            $responses = $eventManager->trigger($taskEvent);
            if ($responses->stopped()) {
                $event->stopPropagation(true);
                break;
            }
        }
    }
    
    public function executeTask(Event $event)
    {
        $task = $event->getTarget();
        return $task->run($event);
    }
}