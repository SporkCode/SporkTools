<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\ServiceFactory;
use SporkTools\Core\Config\ServiceFactory as ConfigServiceFactory;

use Zend\Config\Config;
use Zend\ServiceManager\ServiceManager;

class ServiceFactoryTest extends TestCase
{
    
    public function testCreateService()
    {
        $services = new ServiceManager();
        $services->setFactory('access', new ServiceFactory());
        $services->setService(ConfigServiceFactory::SERVICE, new Config(array(
            'access' => array('authenticateRedirect' => '/foo/bar'),
        )));
        
        /* @var $access \SporkTools\Core\Access\AbstractAccess */
        $access = $services->get('access');
        
        $this->assertInstanceOf('SporkTools\Core\Access\AbstractAccess', $access);
        $this->assertEquals('/foo/bar', $access->getAuthenticateRedirect());
        $this->assertEquals($services, $access->getServices());
    }
}