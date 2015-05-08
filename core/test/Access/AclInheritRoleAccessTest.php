<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\AclInheritRoleAccess;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\ServiceManager;

class AclInheritRoleAccessTest extends TestCase
{
    public function testSetAclInstance()
    {
        $acl = new Acl();
        $access = new AclInheritRoleAccess();
        $access->setAcl($acl);
        
        $this->assertEquals($acl, $access->getAcl());
    }
    
    public function testSetAclReference()
    {
        $acl = new Acl();
        $services = new ServiceManager();
        $services->setService('myAcl', $acl);
        $access = new AclInheritRoleAccess();
        $access->setServices($services);
        $access->setAcl('myAcl');
        
        $this->assertEquals($acl, $access->getAcl());
    }
    
    public function testSetRole()
    {
        $access = new AclInheritRoleAccess();
        $access->setRole('foo');
        
        $this->assertEquals('foo', $access->getRole());
    }
    
    public function testSetUser()
    {
        $access = new AclInheritRoleAccess();
        $access->setUser('foo');
        
        $this->assertEquals('foo', $access->getUser());
    }
    
    public function testSetUserReference()
    {
        $user = array('name' => 'foo');
        $services = new ServiceManager();
        $services->setService('myUser', $user);
        $access = new AclInheritRoleAccess();
        $access->setServices($services);
        $access->setUser('myUser', true);
        
        $this->assertEquals($user, $access->getUser());
    }
    
    public function testIsAuthorizedPositive()
    {
        $acl = new Acl();
        $acl->addRole('administrator');
        $acl->addRole('foo', 'administrator');
        $access = new AclInheritRoleAccess();
        $access->setAcl($acl);
        $access->setUser('foo');
        
        $this->assertTrue($access->isAuthorized());
    }

    public function testIsAuthorizedNegative()
    {
        $acl = new Acl();
        $acl->addRole('administrator');
        $acl->addRole('foo', 'administrator');
        $acl->addRole('bar');
        $access = new AclInheritRoleAccess();
        $access->setAcl($acl);
        $access->setUser('bar');
        
        $this->assertFalse($access->isAuthorized());
    }
}