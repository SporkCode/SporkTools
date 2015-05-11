<?php
namespace SporkToolsTest\Core\ViewTemplates;

use SporkTools\Core\Test\TestCaseView;
use SporkTools\Core\Job\Manager;
use SporkTools\Core\Job\Job;
use SporkTools\Core\Job\Schedule;

use Zend\EventManager\EventManager;

class JobTest extends TestCaseView
{
    public function testIndex()
    {
        $this->setVariables(array(
            'manager' => new Manager(),
        ));
        $this->render('spork-tools/job/index');
    }
    
    public function testEdit()
    {
        $job = new Job();
        $job->setEventManager(new EventManager());
        $this->setVariables(array(
            'job' => $job,
        ));
        $this->render('spork-tools/job/edit');
    }
    
    public function testRun()
    {
        $job = new Job();
        $job->setEventManager(new EventManager());
        $this->setVariables(array(
            'job' => $job,
            'messages' => array(),
        ));
        $this->render('spork-tools/job/run');
    }
    
    public function testSchedule()
    {
        $this->setVariables(array(
            'schedule' => new Schedule(),
        ));
        $this->render('spork-tools/job/schedule');
    }
    
    public function testTime()
    {
        $this->render('spork-tools/job/time');
    }
}