<?php
namespace SporkTools\Core\Job;

class Task extends AbstractTask
{
    protected $code;
    
    public function __construct($code = null)
    {
        if (null !== $code) {
            $this->setCode($code);
        }
    }
    
    public function __sleep()
    {
        return array('id', 'name', 'code');
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function run(Event $event)
    {
        $onError = function ($errno, $errstr, $errfile, $errline) use ($event) {
            $message = sprintf("Error (%d) %s executing task %s", $errno, $errstr, $this->getName());
            if ($errno == E_WARNING || $errno == E_NOTICE || $errno == E_CORE_WARNING
                    || $errno == E_COMPILE_WARNING || $errno == E_USER_WARNING
                    || $errno == E_USER_NOTICE) {
                $this->warning($message);
            } else {
                $this->error($message);
                $event->stopPropagation(true);
            }
        };

        set_error_handler($onError);
        
        $return = eval($this->code);
        
        if (false === $return && null !== ($error = error_get_last())) {
            $onError($error['type'], $error['message'], $error['file'], $error['line']);
        }
        
        restore_error_handler();
    }
    
    public function setCode($code)
    {
        $this->code = $code;
    }
}