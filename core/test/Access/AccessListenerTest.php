<?php
namespace SporkToolsTest\Access;

use Spork\Test\TestCase\TestCase;
use SporkTools\Core\Access\ServiceFactory;
use SporkTools\Core\Access\AccessListener;
use SporkTools\Core\Access\AllowAccess;
use SporkTools\Core\Access\DenyAccess;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

class AccessListenerTest extends TestCase
{

    public function testAttach()
    {
        $sharedEvents = new SharedEventManager();
        $events = new EventManager();
        $events->setSharedManager($sharedEvents);
        $listener = new AccessListener();
        $listener->attach($events);
        
        $this->assertCount(1, $sharedEvents->getListeners('SporkTools', MvcEvent::EVENT_DISPATCH));
    }

    public function testAuthorizeNoService()
    {
        $listener = new AccessListener();
        $event = new MvcEvent();
        $event->setApplication($this->getMockApplication());
        $event->setResponse(new Response());
        
        $response = $listener->authorize($event);
        
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAuthorizeAuthenticationNegative()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $event = new MvcEvent();
        $application = $this->getMockApplication(false, $access);
        $event->setApplication($application);
        $event->setResponse(new Response());
        
        $response = $listener->authorize($event);
        
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAuthorizeAuthenticationNagativeRedirect()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $access->setAuthenticateRedirect('foo');
        $event = new MvcEvent();
        $application = $this->getMockApplication(false, $access);
        $redirect = $application->getServiceManager()->get('controllerPluginManager')->get('redirect');
        $redirect->expects($this->once())->method('toUrl');
        $event->setApplication($application);
        $event->setResponse(new Response());
        
        $response = $listener->authorize($event);
        
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testAuthorizeAuthenticationNagativeRedirectRoute()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $access->setAuthenticateRedirect('foo', true);
        $event = new MvcEvent();
        $application = $this->getMockApplication(false, $access);
        $redirect = $application->getServiceManager()->get('controllerPluginManager')->get('redirect');
        $redirect->expects($this->once())->method('toRoute');
        $event->setApplication($application);
        $event->setResponse(new Response());
    
        $response = $listener->authorize($event);
    
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    public function testAuthorizePositive()
    {
        $listener = new AccessListener();
        $access = new AllowAccess();
        $event = new MvcEvent();
        $event->setApplication($this->getMockApplication(true, $access));
        $event->setResponse(new Response());
        
        $response = $listener->authorize($event);
        
        $this->assertNull($response);
    }
    
    public function testAuthorizeNegative()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $event = new MvcEvent();
        $event->setApplication($this->getMockApplication(true, $access));
        $event->setResponse(new Response());
        
        $response = $listener->authorize($event);
        
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAuthorizeNegativeRedirect()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $access->setAuthorizeRedirect('foo');
        $application = $this->getMockApplication(true, $access);
        $redirect = $application->getServiceManager()->get('controllerPluginManager')->get('redirect');
        $redirect->expects($this->once())->method('toUrl');
        $event = new MvcEvent();
        $event->setApplication($application);
        $event->setResponse(new Response());
    
        $response = $listener->authorize($event);
    
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testAuthorizeNegativeRedirectRoute()
    {
        $listener = new AccessListener();
        $access = new DenyAccess();
        $access->setAuthorizeRedirect('foo', true);
        $application = $this->getMockApplication(true, $access);
        $redirect = $application->getServiceManager()->get('controllerPluginManager')->get('redirect');
        $redirect->expects($this->once())->method('toRoute');
        $event = new MvcEvent();
        $event->setApplication($application);
        $event->setResponse(new Response());
    
        $response = $listener->authorize($event);
    
        $this->assertInstanceOf('Zend\Http\Response', $response);
        $this->assertEquals(302, $response->getStatusCode());
    }
    
    public function testInjectLayoutPositive()
    {
        $listener = new AccessListener();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('layout/layout');
        $access = new AllowAccess();
        $event = new MvcEvent();
        $event->setApplication($this->getMockApplication(true, $access));
        $event->setViewModel($viewModel);
        
        $listener->injectLayoutMenu($event);
        
        $this->assertCount(1, $viewModel->getChildrenByCaptureTo('sporkToolsMenu'));
    }

    public function testInjectLayoutNegative()
    {
        $listener = new AccessListener();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('layout/layout');
        $access = new DenyAccess();
        $event = new MvcEvent();
        $event->setApplication($this->getMockApplication(true, $access));
        $event->setViewModel($viewModel);
    
        $listener->injectLayoutMenu($event);
    
        $this->assertCount(0, $viewModel->getChildrenByCaptureTo('sporkToolsMenu'));
    }
    
    /**
     * Create mock Application with ServiceManager, controller plugin
     * manager, mock redirect plugin, authentication service, and access 
     * service (optional)
     * 
     * @return \Zend\Mvc\Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockApplication($authenticated = false, $access = null)
    {
        $services = new ServiceManager();
        
        $response = new Response();
        $response->setStatusCode(302);
        $redirect = $this->getMock('Zend\Mvc\Controller\Plugin\Redirect');
        $redirect->method('toUrl')->willReturn($response);
        $redirect->method('toRoute')->willReturn($response);
        $pluginManager = new PluginManager();
        $pluginManager->setService('redirect', $redirect);
        $services->setService('controllerPluginManager', $pluginManager);
        
        $auth = new AuthenticationService();
        if (true === $authenticated) {
            $auth->getStorage()->write(true);
        }
        $services->setService('auth', $auth);
        
        if (null !== $access) {
            $access->setServices($services);
            $access->setAuthenticationService($auth);
            $services->setService(ServiceFactory::SERVICE, $access);
        }
        
        $application = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $application->method('getServiceManager')->willReturn($services);
        
        return $application;
    }
}