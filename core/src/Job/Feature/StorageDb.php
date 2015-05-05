<?php
namespace SporkTools\Core\Job\Feature;

use SporkTools\Core\Job\AbstractJob;
use SporkTools\Core\Job\Event;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * 
 * CREATE TABLE IF NOT EXISTS `job` (
 * `job` varchar(255) CHARACTER SET ascii NOT NULL,
 * `type` enum('report','schedule') CHARACTER SET ascii NOT NULL,
 * `data` blob NOT NULL,
 * PRIMARY KEY (`job`,`type`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 */
class StorageDb extends AbstractListenerAggregate implements FeatureInterface
{
    protected $columnMap = array(
    	'job'       => 'job',
        'type'      => 'type',
        'data'      => 'data',
    );
    
    protected $db = 'db';
    
    protected $table;
    
    public function __construct(array $options = array())
    {
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
            	case 'columnmap':
            	    $this->setColumnMap($value);
            	    break;
            	case 'db':
            	    $this->setDb($value);
            	    break;
            	case 'table':
            	    $this->setTable($value);
            	    break;
            }
        }
    }
    
    public function getColumnMap()
    {
        return $this->columnMap;
    }
    
    public function setColumnMap(array $map)
    {
        $this->columnMap = $this->columnMap + $map;
    }
    
    public function getDb()
    {
        if (!$this->db instanceof Adapter) {
            throw new \Exception('Database adapter not initialized');
        }
        return $this->db;
    }
    
    public function setDb($db)
    {
        if (is_string($db) || $db instanceof Adapter) {
            $this->db = $db;
        } else {
            throw new \Exception('Storage::db must be a database adapter instance or the name of service');
        }
    }
    
    public function getTable()
    {
        if (null === $this->table) {
            throw new \Exception('Table not set');
        }
        if (!$this->table instanceof TableGatewayInterface) {
            $this->table = new TableGateway($this->table, $this->getDb());
        }
        return $this->table;
    }
    
    public function setTable($table)
    {
        if (is_string($table) || $table instanceof TableGatewayInterface) {
            $this->table = $table;
        } else {
            throw new \Exception('StorageDb::table must be a table gateway instance or the name of a service');
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::HAS_FEATURE, array($this, 'hasFeature'));
        $this->listeners[] = $events->attach(Event::FETCH_REPORT, array($this, 'fetchReport'));
        $this->listeners[] = $events->attach(Event::STORE_REPORT, array($this, 'storeReport'));
        $this->listeners[] = $events->attach(Event::FETCH_SCHEDULE, array($this, 'fetchSchedule'));
        $this->listeners[] = $events->attach(Event::STORE_SCHEDULE, array($this, 'storeSchedule'));
    }
    
    public function hasFeature(Event $event)
    {
        $feature = $event->getParam('feature');
        
        return $feature == FeatureInterface::SCHEDULE 
                || $feature == FeatureInterface::REPORTING;
    }
    
    public function fetchReport(Event $event)
    {
        $job = $event->getTarget();
        $this->initialize($job->getServiceManager());
        $report = $this->fetchData($job, 'report');
        if (false !== $report) {
            return unserialize($report);
        }
    }
    
    public function storeReport(Event $event)
    {
        $job = $event->getTarget();
        $this->initialize($job->getServiceManager());
        $report = $event->getParam('report');
        $this->storeData($job, 'report', $report);
    }
    
    public function fetchSchedule(Event $event)
    {
        $job = $event->getTarget();
        $this->initialize($job->getServiceManager());
        $schedule = $this->fetchData($job, 'schedule');
        if (false !== $schedule) {
            return unserialize($schedule);
        }
    }
    
    public function storeSchedule(Event $event)
    {
        $job = $event->getTarget();
        $this->initialize($job->getServiceManager());
        $schedule = $event->getParam('schedule');
        $this->storeData($job, 'schedule', $schedule);
    }
    
    protected function initialize(ServiceLocatorInterface $serviceManager)
    {
        if (!$this->table instanceof TableGatewayInterface 
                && !$this->db instanceof Adapter) {
            $this->setDb($serviceManager->get($this->db));
        }
    }
    
    /**
     * @param AbstractJob $job
     * @param string $type 'report' | 'schedule'
     * @return ArrayObject, false
     */
    protected function fetchData(AbstractJob $job, $type)
    {
        $table = $this->getTable();
        $result = $table->select(array(
            $this->columnMap['job']      => $job->getId(),
            $this->columnMap['type']    => $type))->current();
        return $result[$this->columnMap['data']];
    }
    
    protected function storeData(AbstractJob $job, $type, $data)
    {
        $table = $this->getTable();
        $set = array(
        	$this->columnMap['job']      => $job->getId(),
            $this->columnMap['type']    => $type);
        $table->delete($set);
        $set[$this->columnMap['data']] = serialize($data);
        $table->insert($set);
    }
}