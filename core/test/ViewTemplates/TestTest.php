<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;

class TestTest extends TestCaseView
{
    public function testExtensions()
    {
        $this->render('spork-tools/test/extensions');
    }
}