<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;

use Zend\ServiceManager\ServiceManager;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Zend\Authentication;

class AbstractAccessTest extends TestCase
{
    /**
     * @var \SporkTools\Core\Access\AbstractAccess
     */
    protected $access;
    
    public function testSetAuthenticationRedirec()
    {
        $this->access->setAuthenticateRedirect('foo');
        $this->assertEquals('foo', $this->access->getAuthenticateRedirect());
        $this->assertFalse($this->access->isAuthenticateRedirectRoute());
        
        $this->access->setAuthenticateRedirect('bar', true);
        $this->assertEquals('bar', $this->access->getAuthenticateRedirect());
        $this->assertTrue($this->access->isAuthenticateRedirectRoute());
    }

    public function testSetAuthenticationServiceInstance()
    {
        $auth = new AuthenticationService();
        $this->access->setAuthenticationService($auth);
        $this->assertEquals($auth, $this->access->getAuthenticationService());
    }
    
    public function testSetAuthenticationServiceReference()
    {
        $auth = new AuthenticationService();
        $services = new ServiceManager();
        $this->access->setServices($services);
        $services->setService('auth', $auth);
        $this->access->setAuthenticationService('auth');
        $this->assertEquals($auth, $this->access->getAuthenticationService());
    }
    
    public function testSetAuthorizeRedirect()
    {
        $this->access->setAuthorizeRedirect('foo');
        $this->assertEquals('foo', $this->access->getAuthorizeRedirect());
        $this->assertFalse($this->access->isAuthorizeRedirectRoute());
        
        $this->access->setAuthorizeRedirect('bar', true);
        $this->assertEquals('bar', $this->access->getAuthorizeRedirect());
        $this->assertTrue($this->access->isAuthorizeRedirectRoute());
    }
    
    public function testIsAuthenticated()
    {
        $auth = new AuthenticationService();
        $this->access->setAuthenticationService($auth);
        $this->assertFalse($this->access->isAuthenticated());
        $auth->getStorage()->write(true);
        $this->assertTrue($this->access->isAuthenticated());
    }
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->access = $this->getMockForAbstractClass('SporkTools\Core\Access\AbstractAccess');
    }
}