<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;

class LogTest extends TestCaseView
{
    public function testIndex()
    {
        $this->addVariable('isConfigured', true);
        $this->addVariable('subRows', array());
        $this->addVariable('sortProperty', 'timestamp');
        $this->addVariable('sortDescending', true);
        $this->render('spork-tools/log/index');
    }
}