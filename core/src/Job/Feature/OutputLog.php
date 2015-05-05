<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use SporkTools\Core\Job\Event;
use Zend\Log\LoggerInterface;

class OutputLog extends AbstractListenerAggregate implements FeatureInterface
{
    protected $filterTypes = array('ping');
    
    /**
     * @var \Zend\Log\LoggerInterface | string
     */
    protected $log;
    
    protected $postfix;
    
    public function __construct($options)
    {
        if (is_string($options) || $options instanceof LoggerInterface) {
            $this->setLog($options);
        } elseif (is_array($options)) {
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                	case 'filtertypes':
                	    $this->filterTypes($value);
                	    break;
                	case 'log':
                	    $this->setLog($log);
                }
            }
        }
    }
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::RUN_JOB, array($this, 'init'), 10000);
        $this->listeners[] = $events->attach(Event::MESSAGE, array($this, 'log'));
    }
    
    public function filterTypes(array $types)
    {
        $this->filterTypes = $types;
    }
    
    public function init(Event $event)
    {
        if (null === $this->log) {
            throw new \Exception('Log not set');
        }
        
        if (! $this->log instanceof LoggerInterface) {
            $serviceManager = $event->getTarget()->getServiceManager();
            if (null === $serviceManager) {
                throw new \Exception('Log could not be initialized. Service Manager not available.');
            }
            if ($serviceManager->has($this->log)) {
                $this->log = $serviceManager->get($this->log);
                if (! $this->log instanceof LoggerInterface) {
                    throw new \Exception('Log must implement \Zend\Logger\LoggerInterface');
                }
            } else {
                throw new \Exception("Log '{$this->log}' not found in service manager");
            }
        }
        $this->postfix = ' [' . uniqid() . ']';
    }
    
    public function log(Event $event)
    {
        $message = $event->getParam('message');
        
        if (in_array($message->type, $this->filterTypes)) {
            return;
        }
        
        switch ($message->type) {
            case 'error':
                $this->log->err($message->text . $this->postfix);
                break;
            case 'warning':
                $this->log->warn($message->text . $this->postfix);
                break;
            default:
                $this->log->info($message->text . $this->postfix);
        }
    }
    
    public function setLog($log)
    {
        $this->log = $log;
    }
}