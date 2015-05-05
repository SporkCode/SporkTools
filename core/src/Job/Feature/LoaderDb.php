<?php
namespace SporkTools\Core\Job\Feature;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;
use SporkTools\Core\Job\Job;
use SporkTools\Core\Job\Task;
use Zend\ServiceManager\ServiceLocatorInterface;
use SporkTools\Core\Job\Report;
use SporkTools\Core\Job\Schedule;

/**
 * Loads Jobs, Tasks, Schedules and Report data from a single database.
 * 
 * Handles hasFeature, fetchJobs, storeJob, deleteJob, storeReport and storeSchedule 
 * events. Does not need to handle fetchTasks, storeTasks, fetchReport,
 * fetchSchedule events because the functionality for those events is taken
 * care of during the fetchJobs and storeJob calls.
 * 
 * CREATE TABLE IF NOT EXISTS `job` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `name` varchar(255) CHARACTER SET ascii NOT NULL,
 *   `tasks` blob NOT NULL,
 *   `schedule` blob NOT NULL,
 *   `report` blob NOT NULL,
 *   PRIMARY KEY (`id`),
 *   KEY `name` (`name`)
 * ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
 * 
 */
class LoaderDb extends AbstractListenerAggregate implements FeatureInterface
{
    protected $columnMap = array(
        'id'            => 'id',
        'name'          => 'name',
        'tasks'         => 'tasks',  
        'report'        => 'report',
        'schedule'      => 'schedule',        
    );
    
    protected $db;
    
    protected $jobTable;
    
    public function __construct(array $options = array())
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
            	case 'db':
            	    $this->setDb($value);
            	    break;
            	case 'jobtable':
            	    $this->setJobTable($value);
            	    break;
            }
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::HAS_FEATURE, array($this, 'hasFeature'));
        $this->listeners[] = $events->attach(Event::FETCH_JOBS, array($this, 'fetchJobs'));
        $this->listeners[] = $events->attach(Event::STORE_JOB, array($this, 'storeJob'));
        $this->listeners[] = $events->attach(Event::DELETE_JOB, array($this, 'deleteJob'));
        $this->listeners[] = $events->attach(Event::STORE_SCHEDULE, array($this, 'storeSchedule'));
        $this->listeners[] = $events->attach(Event::STORE_REPORT, array($this, 'storeReport'));
    }
    
    public function deleteJob(Event $event)
    {
        $job = $event->getTarget();
        $table = $this->getJobTable();
        $table->delete(array($this->columnMap['id'] => $job->getId()));
    }
    
    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDb()
    {
        if (null === $this->db) {
            throw new \Exception('DB Adapter not set');
        }
        return $this->db;
    }
    
    public function fetchJobs(Event $event)
    {
        $jobManager = $event->getTarget();
        $serviceManager = $jobManager->getServiceManager();
        $eventManager = $jobManager->getEventManager();
        $this->initialize($event);
        $table = $this->getJobTable();
        $jobs = array();
        foreach ($table->select() as $row) {
            $job = new Job();
            $job->setId($row[$this->columnMap['id']]);
            $job->setName($row[$this->columnMap['name']], false);
            $job->setEventManager($eventManager);
            $job->setServiceManager($serviceManager);
            $tasks = $row[$this->columnMap['tasks']];
            $tasks = empty($tasks) ? array() : unserialize($tasks);
            foreach ($tasks as $task) {
                $task->setEventManager($eventManager);
                $task->setServiceManager($serviceManager);
            }
            $job->setTasks($tasks, false);
            $report = $row[$this->columnMap['report']];
            $report = empty($report) ? new Report() : unserialize($report);
            $job->setReport($report, false);
            $schedule = $row[$this->columnMap['schedule']];
            $schedule = empty($schedule) ? new Schedule() : unserialize($schedule);
            $job->setSchedule($schedule, false);
            $jobs[] = $job;
        }
        return $jobs;
    }
    
    /**
     * @return \Zend\Db\TableGateway\TableGateway
     */
    public function getJobTable()
    {
        if (null === $this->jobTable) {
            throw new \Exception('Job table not set');
        }
        if (!$this->jobTable instanceof TableGatewayInterface) {
            $this->jobTable = new TableGateway($this->jobTable, $this->getDb());
        }
        return $this->jobTable;
    }
    
    public function hasFeature(Event $event)
    {
        $feature = $event->getParam('feature');
        
        return FeatureInterface::ENABLED == $feature ||
                FeatureInterface::MANAGE_JOBS == $feature ||
                FeatureInterface::REPORTING == $feature ||
                FeatureInterface::SCHEDULE == $feature;
    }
    
    /**
     * @param string | \Zend\Db\Adapter\Adapter $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
    
    /**
     * @param string | TableGatewayInterface $table
     */
    public function setJobTable($table)
    {
        $this->jobTable = $table;
    }
    
    public function storeJob(Event $event)
    {
        $this->initialize($event);
        $job = $event->getTarget();
        $id = $job->getId();
        $tasks = $job->getTasks();
        $data = array(
            'name'      => $job->getName(),
            'tasks'     => serialize($tasks),
        );
        $table = $this->getJobTable();
        if (null === $id) {
            $table->insert($data);
        } else {
            $table->update($data, array($this->columnMap['id'] => $id));
        }
    }
    
    public function storeReport(Event $event)
    {
        $this->initialize($event);
        $job = $event->getTarget();
        $report = $event->getParam('report');
        $id = $job->getId();
        $table = $this->getJobTable();
        $table->update(
            array($this->columnMap['report'] => serialize($report)), 
            array($this->columnMap['id'] => $id));
    }
    
    public function storeSchedule(Event $event)
    {
        $this->initialize($event);
        $job = $event->getTarget();
        $schedule = $event->getParam('schedule');
        $id = $job->getId();
        $table = $this->getJobTable();
        $table->update(
            array($this->columnMap['schedule'] => serialize($schedule)), 
            array($this->columnMap['id'] => $id));
    }
    
    protected function initialize(Event $event)
    {
        if (!$this->db instanceof Adapter && null !== $this->db) {
            $serviceManager = $event->getTarget()->getServiceManager();
            $this->db = $serviceManager->get($this->db);
        }
    }
}