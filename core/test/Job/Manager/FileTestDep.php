<?php
namespace SporkToolsTest\Job\Manager;

use SporkTools\Core\Job\Manager\File;
use Zend\ServiceManager\ServiceManager;

class FileTest extends \PHPUnit_Framework_TestCase
{
    protected $fileManager;
    
    public function testGetJob()
    {
        $fileManager = $this->fileManager;
        $fileManager->setPath(__DIR__ . DIRECTORY_SEPARATOR . 'jobs');
        $job = $fileManager->getJob('Foo');
        $this->assertInstanceOf('Foo', $job);
        $this->assertInstanceOf('SporkTools\Core\Job\JobFile', $job);
    }
    
    public function testGetJobs()
    {
        $fileManager = $this->fileManager;
        $fileManager->setPath(__DIR__ . DIRECTORY_SEPARATOR . 'jobs');
        $jobs = $fileManager->getJobs();
        $this->assertInternalType('array', $jobs);
        $this->assertCount(1, $jobs);
    }
    
    public function testGetJobsNoPath()
    {
        $fileManager = $this->fileManager;
        $this->setExpectedException('Exception');
        $fileManager->getJobs();
    }
    
    public function testGetJobsBadPath()
    {
        $fileManager = $this->fileManager;
        $fileManager->setPath('foo');
        $this->setExpectedException('Exception');
        $fileManager->getJobs();
    }
    
    protected function setUp()
    {
        $this->fileManager = new File();
        $this->fileManager->setServiceManager(new ServiceManager());
    }
}