<?php
namespace SporkTools\Core\Job;

use Zend\Log\Logger;

class Message
{
    public $class;
    
    /**
     * @var \DateTime
     */
    public $datetime;
    
    public $text;
    
    public $type;
    
    public function __construct($text, $type, $datetime = null)
    {
        $this->text = $text;
        $this->type = $type;
        
        if (! $datetime instanceof \DateTime) {
            $datetime = new \DateTime($datetime);
        }
        $this->datetime = $datetime;
    }
}