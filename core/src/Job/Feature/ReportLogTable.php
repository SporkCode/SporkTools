<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceLocatorInterface;
use SporkTools\Core\Job\Event;
use SporkTools\Core\Job\Report;
use SporkTools\Core\Job\Message;
use SporkTools\Core\Job\AbstractJob;

class ReportLogTable extends AbstractListenerAggregate implements FeatureInterface
{
    protected $columnMap = array(
        'timestamp'     => 'timestamp',
        'priority'      => 'priority',
        'message'       => 'message',
    );

    /**
     * @var \Zend\Db\Adapter\Adapter | string
     */
    protected $db;
    
    /**
     * @var string
     */
    protected $table;
    
    protected $typeMap = array(
        0           => 'emergency',
        'EMERG'     => 'emergency',
        1           => 'alert',
        'ALERT'     => 'alert',
        2           => 'critical',
        'CRIT'      => 'critical',
        3           => 'error',
        'ERR'       => 'error',
        4           => 'warning',
        'WARN'      => 'warning',
        5           => 'notice',
        'NOTICE'    => 'notice',
        6           => 'information',
        'INFO'      => 'information',
        7           => 'debug', 
        'DEBUG'     => 'debug', 
    );
    
    public function __construct($options = null)
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                	case 'db':
                	    $this->setDb($value);
                	    break;
                	case 'table':
                	    $this->setTable($value);
                	    break;
                }
            }
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::HAS_FEATURE, array($this, 'hasFeature'));
        $this->listeners[] = $events->attach(Event::FETCH_REPORT, array($this, 'report'));
    }
    
    /**
     * Handles job manager's has feature event. Indicates get report feature
     * is available.
     * 
     * @param Event $event
     * @return boolean
     */
    public function hasFeature(Event $event)
    {
        $feature = $event->getParam('feature');
        
        if ($feature == FeatureInterface::REPORTING) {
            return true;
        }
    }
    
    /**
     * Handles job get report event by fetching messages from the log table
     * 
     * @param Event $event
     * @return \SporkTools\Core\Job\Report
     */
    public function report(Event $event)
    {
        $job = $event->getTarget();
        $serviceManager = $job->getServiceManager();
        
        $this->initialize($serviceManager);
        
        $report = new Report();
        $runId = $this->fetchLastBatchId($job);
        
        $select = new Select($this->table);
        $select->columns(array(
                'message'   => $this->columnMap['message'],
                'type'      => $this->columnMap['priority'],
                'datetime'  => $this->columnMap['timestamp']))
            ->where->like($this->columnMap['message'], "% [$runId]");
        $results = $this->db->query($select->getSqlString($this->db->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
        foreach ($results as $row) {
            $type = isset($this->typeMap[$row->type]) ? $this->typeMap[$row->type] : $row->type;
            $report->addMessage(new Message($row->message, $type, $row->datetime));
        }
        return $report;
    }

    /**
     * Sets the database adapter or the name of the adapter available from
     * the service manager.
     * 
     * @param \Zend\Db\Adapter\Adapter | string $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }
    
    public function setColumnMap(array $map)
    {
        $this->columnMap += $map;
    }
    
    /**
     * Sets the log's table name
     * 
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }
    
    /**
     * Setup properties required to query database
     * 
     * @param ServiceLocatorInterface $serviceManager
     * @throws \Exception on resources not available
     */
    protected function initialize(ServiceLocatorInterface $serviceManager)
    {
        if (null === $this->table) {
            throw new \Exception('Table not set');
        }
        if (! $this->db instanceof Adapter) {
            if (null === $this->db) {
                throw new \Exception('Db adapter not set');
            }
            if (!$serviceManager->has($this->db)) {
                throw new \Exception('Db adapter not available from service manager');
            }
            $this->db = $serviceManager->get($this->db);
            if (! $this->db instanceof Adapter) {
                throw new \Exception('Db adapter must be instance of \Zend\Db\Adapter\Adapter');
            }
        }
    }
    
    /**
     * Queries the log table to find batch id for the job's last execution.
     * 
     * Note: This function expects the log entries to be formated by the
     * OutputLog feature class. 
     * 
     * @param SporkTools\Core\Job\AbstractJob $job
     * @return boolean|string
     */
    protected function fetchLastBatchId(AbstractJob $job)
    {
        $sql = new Sql($this->db);
        $message = 'Starting job ' . str_replace('\\', '\\\\', $job->getName()) . ' [%]';
        $select = new Select($this->table);
        $select->columns(array('message' => $this->columnMap['message']))
            ->order($this->columnMap['timestamp'] . ' desc')
            ->limit(1)
            ->where->like($this->columnMap['message'], $message);
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        //$results = $this->db->query($select->getSqlString($this->db->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
        if (count($results) == 0) {
            return false;
        }
        $row = $results->current();
        $message = $row['message'];
        return substr($message, strrpos($message, '[') + 1 , 13);
    }
}