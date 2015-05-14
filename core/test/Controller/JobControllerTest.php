<?php
namespace SporkToolsTest\Controller;

use SporkTools\Core\Test\TestCaseController;
use SporkTools\Core\Job\ServiceFactory;
use SporkTools\Core\Job\Job;
use SporkTools\Core\Job\Schedule;
use SporkTools\Core\Job\Feature\FeatureInterface;
use SporkTools\Core\Job\Event;

use Zend\EventManager\EventManager;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;

class JobLoader extends AbstractListenerAggregate implements FeatureInterface
{
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(Event::FETCH_JOBS, function(Event $event) {
            $job = new Job();
            $job->setId('foo');
            $job->setName('Foo Job');
            $jobManager = $event->getTarget();
            $job->setEventManager($jobManager->getEventManager());
            $job->setServiceManager($jobManager->getServiceManager());
            return array('foo' => $job);
        });
    }
}

class JobControllerTest extends TestCaseController
{
    public function testIndex()
    {
        $result = $this->dispatch('index');
        $this->assertResponseOk();
    }
    
    public function testCreate()
    {
        $result = $this->dispatch('edit');
        $this->assertResponseOk();
    }
    
    public function testEdit()
    {
        $this->setParams(array('job' => 'foo'));
        $result = $this->dispatch('edit');
        $this->assertResponseOk();
    }
    
    public function testEditPost()
    {
        $this->setParams(array('job' => 'foo'));
        $this->setMethod(Request::METHOD_POST);
        $this->setPost(array('name' => 'Bar Job'));
        $this->dispatch('edit');
        
        $this->assertResponseRedirect('/sporktools/job');
        $job = $this->services->get(ServiceFactory::SERVICE)->getJob('foo');
        $this->assertEquals('Bar Job', $job->getName());
    }
    
    public function testSchedule()
    {
        $this->setParams(array('job' => 'foo'));
        $result = $this->dispatch('schedule');
        $this->assertResponseOk();
    }
    
    public function testRun()
    {
        $this->setParams(array('job' => 'foo'));
        $result = $this->dispatch('run');
        $this->assertResponseOk();
    }
    
    public function testDelete()
    {
        $this->setParams(array('job' => 'foo'));
        $result = $this->dispatch('delete');
        $this->assertResponseRedirect('/sporktools/job');
    }
    
    protected function dispatch($action = null)
    {
        parent::dispatch('SporkTools\Core\Controller\Job', $action);
    }
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->services->get(ServiceFactory::SERVICE)->addFeature(new JobLoader());
    }
}