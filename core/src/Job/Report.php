<?php
namespace SporkTools\Core\Job;

/**
 * A report for a jobs execution.
 */
class Report implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $messages = array();
    
    public function __construct(array $messages = null)
    {
        if (null !== $messages) {
            $this->setMessages($messages);
        }
    }
    
    /**
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }
    
    public function count()
    {
        return count($this->messages);
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->messages);
    }
    
    /**
     * @return \DateTime | NULL
     */
    public function getLastRun()
    {
        if (empty($this->messages)) {
        	return null;
        }
        
        return $this->messages[0]->datetime;
    }
    
    /**
     * @return array:
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = array();
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }
    
    public function toArray()
    {
        $messages = array();
        foreach ($this->messages as $message) {
            $message = (array) $message;
            $message['datetime'] = $message['datetime']->format(\DateTime::ISO8601);
            $messages[] = $message;
        }
        return $messages;
    }
}