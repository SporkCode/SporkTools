<?php
namespace SporkTools\Core\Job;

use SporkTools\Core\Job\Feature\OutputCollection;

abstract class AbstractJob extends AbstractBase
{
    protected $dirty = array();
    
    protected $report;
    
    protected $schedule;
    
    protected $tasks;
    
    public function delete()
    {
        $event = new Event(Event::DELETE_JOB, $this);
        $this->getEventManager()->trigger($event);
    }
    
    public function getReport()
    {
        if (null === $this->report) {
            $event = new Event(Event::FETCH_REPORT, $this);
            $responses = $this->getEventManager()->trigger(
                    $event, 
                    function ($response) {return null != $response;});
            $this->report = $responses->last();
        }
        return $this->report;
    }
    
    public function getSchedule()
    {
        if (null === $this->schedule) {
            $event = new Event(Event::FETCH_SCHEDULE, $this);
            $responses = $this->getEventManager()->trigger(
                    $event, 
                    function ($response) {return null != $response;});
            $this->schedule = $responses->last();
        }
        if (!$this->schedule instanceof Schedule) {
            $this->schedule = new Schedule($this->schedule);
        }
        return $this->schedule;
    }
    
    public function getTasks()
    {
        if (null === $this->tasks) {
            $event = new Event(Event::FETCH_TASKS, $this);
            $serviceManager = $this->getServiceManager();
            $eventManager = $this->getEventManager();
            $responses = $eventManager->trigger($event);
            $this->tasks = array();
            foreach ($responses as $response) {
                if ($response instanceof AbstractTask) {
                    $response->setServiceManager($serviceManager);
                    $response->setEventManager($eventManager);
                    $this->tasks[] = $response;
                } elseif (is_array($response)) {
                    foreach ($response as $task) {
                        if ($task instanceof AbstractTask) {
                            $task->setServiceManager($serviceManager);
                            $task->setEventManager($eventManager);
                            $this->tasks[] = $task;
                        }
                    }
                }
            }
        }
        
        return $this->tasks;
    }

    public function run()
    {
        $event = new Event(Event::RUN_JOB, $this);
        $outputCollection = new OutputCollection();
        $this->getEventManager()->attachAggregate($outputCollection);
        
        $response = $this->getEventManager()->trigger($event);
        
        $this->getEventManager()->detachAggregate($outputCollection);
        $this->setReport(new Report($outputCollection->getMessages()));
        return $response;
    }
    
    public function save()
    {
        $eventManager = $this->getEventManager();
        if (null === $this->id || isset($this->dirty['name'])) {
            $event = new Event(Event::STORE_JOB, $this);
            $eventManager->trigger($event);
        }
        
        if (isset($this->dirty['tasks'])) {
            $event = new Event(Event::STORE_TASKS, $this, array('tasts' => $this->tasks));
            $eventManager->trigger($event);
        }
        
        if (isset($this->dirty['report'])) {
            $event = new Event(Event::STORE_REPORT, $this, array('report' => $this->report));
            $eventManager->trigger($event);
        }
        
        if (isset($this->dirty['schedule'])) {
            $event = new Event(Event::STORE_SCHEDULE, $this, array('schedule' => $this->schedule));
            $eventManager->trigger($event);
        }
    }
    
    public function setName($name, $dirty = true)
    {
        $this->dirty['name'] = $dirty == true ? true : null;
        $this->name = $name;
    }
    
    public function setReport(Report $report, $dirty = true)
    {
        $this->dirty['report'] = $dirty == true ? true : null;
        $this->report = $report;
    }
    
    public function setSchedule(Schedule $schedule, $dirty = true)
    {
        $this->dirty['schedule'] = $dirty = true ? true : null;
        $this->schedule = $schedule;
    }
    
    public function setTasks(array $tasks, $dirty = true)
    {
        $this->dirty['tasks'] = $dirty == true ? true : null;
        $this->tasks = $tasks;
    }
}