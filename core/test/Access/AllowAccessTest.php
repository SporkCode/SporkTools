<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\AllowAccess;
use SporkTools\Core\Access\SporkTools\Core\Access;

class AllowAccessTest extends TestCase
{
    public function testIsAuthenticated()
    {
        $allow = new AllowAccess();
        $this->assertTrue($allow->isAuthenticated());
    }
    
    public function testIsAuthorized()
    {
        $allow = new AllowAccess();
        $this->assertTrue($allow->isAuthorized());
    }
}