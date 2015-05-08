<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\AllowAccess;

class AllowAccessTest extends TestCase
{
    public function testIsAuthorized()
    {
        $allow = new AllowAccess();
        $this->assertTrue($allow->isAuthorized());
    }
}