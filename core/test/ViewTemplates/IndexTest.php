<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;
use SporkTools\Core\ServiceManager\Mapper as ServiceMapper;

use Zend\EventManager\EventManager;

class IndexTest extends TestCaseView
{
    public function testIndex()
    {
        $this->render('spork-tools/index/index');
    }
    
    public function testEvents()
    {
        $this->addVariable('eventManagers', array(new EventManager()));
        $this->render('spork-tools/index/events');
    }
    
    public function testPhpInfo()
    {
        $this->setVariables(array('style' => 'foo', 'info' => 'bar'));
        $this->render('spork-tools/index/php-info');
    }
    
    public function testServices()
    {
        $this->markTestSkipped('Broken 2015/05/15');
        $serviceMapper = new ServiceMapper();
        $serviceMapper->setServiceManager($this->services);
        $this->addVariable('serviceMapper', $serviceMapper);
        $this->render('spork-tools/index/services');
    }
}