<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;

class LogTest extends TestCaseView
{
    public function testIndex()
    {
        $this->render('spork-tools/log/index');
    }
}