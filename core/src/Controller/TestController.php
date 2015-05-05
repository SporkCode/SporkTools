<?php
namespace SporkTools\Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class TestController extends AbstractActionController
{
    public function errorAction()
    {
        trigger_error('Test Error', E_USER_ERROR);
    }
    
    public function exceptionAction()
    {
        throw new \Exception('Test Exception', 42, new \Exception('Previous Exception', 69));
    }
    
    public function extensionsAction()
    {
    	
    }
}