<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\ServiceFactory;

use Zend\ServiceManager\ServiceManager;

class ServiceFactoryTest extends TestCase
{
    
    public function testCreateService()
    {
        $services = new ServiceManager();
        $services->setService('config', array(
            'sporktools-access' => array('authenticateRedirect' => '/foo/bar'),
        ));
        $services->setFactory('access', new ServiceFactory());
        
        /* @var $access \SporkTools\Core\Access\AbstractAccess */
        $access = $services->get('access');
        
        $this->assertInstanceOf('SporkTools\Core\Access\AbstractAccess', $access);
        $this->assertEquals('/foo/bar', $access->getAuthenticateRedirect());
        $this->assertEquals($services, $access->getServices());
    }
}