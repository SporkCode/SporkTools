<?php
namespace SporkToolsTest\View\Helper;

use SporkTools\Core\Test\TestCaseService;
use SporkTools\Core\View\Helper\Events;

use Zend\EventManager\EventManager;

class EventsTest extends TestCaseService
{
    public function testEvents()
    {
        $application = $this->services->get('application');
        $application->bootstrap();
        $events = new Events();
        $events->setView($this->services->get('viewRenderer'));
        
        $eventManager = $application->getEventManager();
        $events($eventManager);
    }
}