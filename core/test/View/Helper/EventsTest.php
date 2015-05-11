<?php
namespace SporkToolsTest\View\Helper;

use SporkTools\Core\Test\TestCaseService;
use SporkTools\Core\View\Helper\Events;

use Zend\View\Renderer\PhpRenderer;

class EventsTest extends TestCaseService
{
    public function testEvents()
    {
        $application = $this->services->get('application');
        $application->bootstrap();
        $events = new Events();
        $events->setView(new PhpRenderer());
        
        $eventManager = $application->getEventManager();
        $events($eventManager);
    }
}