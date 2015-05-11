<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;

class IndexTest extends TestCaseView
{
    public function testIndex()
    {
        $this->render('spork-tools/index/index');
    }
    
    public function testEvents()
    {
        $this->markTestIncomplete('template under development');
        $this->render('spork-tools/index/events');
    }
    
    public function testPhpInfo()
    {
        $this->setVariables(array('style' => 'foo', 'info' => 'bar'));
        $this->render('spork-tools/index/php-info');
    }
    
    public function testServices()
    {
        $this->markTestIncomplete('template under development');
        $this->render('spork-tools/index/services');
    }
}