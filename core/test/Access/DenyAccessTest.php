<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\DenyAccess;

class DenyAccessTest extends TestCase
{
    public function testIsAuthorized()
    {
        $deny = new DenyAccess();
        $this->assertFalse($deny->isAuthorized());
    }
}