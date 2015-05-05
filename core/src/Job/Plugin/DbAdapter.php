<?php
namespace SporkTools\Core\Job\Plugin;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use SporkTools\Core\Job\Event;
use Zend\Db\Adapter\Adapter;

class DbAdapter extends AbstractPlugin
{
    /**
     * @var \Zend\Db\Adapter\Adapter | string
     */
    protected $db = 'db';
    
    public function __construct(array $options = array())
    {
        foreach ($options as $key => $value) {
            switch ($key) {
            	case 'db':
            	    $this->setDb($value);
            	    break;
            }
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        parent::attach($events);
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'initialize'), 1000);
    }
    
    public function getDb()
    {
        return $this->db;
    }
    
    public function getMethods()
    {
        return array(
            'getDb',
        );
    }

    /**
     * Injects message plugin and database adapter when job is run
     * 
     * @param Event $event
     */
    public function initialize(Event $event)
    {
        if (!$this->db instanceof Adapter) {
            $this->db = $event->getTarget()->getServiceManager()->get($this->db);
        }
    }
    
    /**
     * @param \Zend\Db\Adapter\Adapter | string $db A database adapter instance
     * or the name of and adapter available from the service manager
     */
    public function setDb($db)
    {
        if (!is_string($db) && !$db instanceof Adapter) {
            throw new \Exception('Invalid parameter $db: must be string name of service or instance of database adapter');
        }
        $this->db = $db;
    }
}