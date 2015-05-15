<?php
namespace SporkToolsTest\Controller;

use SporkTools\Core\Test\TestCaseController;

class IndexControllerTest extends TestCaseController
{
    public function testIndex()
    {
        $result = $this->dispatch('index');
        $this->assertResponseOk();
    }
    
    public function testEvents()
    {
        $result = $this->dispatch('events');
        $this->assertResponseOk();
    }
    
    public function testPhpInfo()
    {
        $result = $this->dispatch('phpInfo');
        $this->assertResponseOk();
    }
    
    public function testServices()
    {
        $result = $this->dispatch('services');
        $this->assertResponseOk();
    }
    
    public function testStyle()
    {
        $result = $this->dispatch('style');
        $this->assertResponseOk();
    }
    
    protected function dispatch($action = null)
    {
        parent::dispatch('SporkTools\Core\Index', $action);
    }
}