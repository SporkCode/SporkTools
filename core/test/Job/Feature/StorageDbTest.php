<?php
namespace SporkToolsTest\Job\Feature;

use SporkTools\Core\Job\Event;
use SporkTools\Core\Job\Feature\FeatureInterface;
use SporkTools\Core\Job\Feature\StorageDb;

class StorageDbTest extends \PHPUnit_Framework_TestCase
{
    public function testHasFeature()
    {
        $storage = new StorageDb();
        $event = new Event();

        $event->setParam('feature', FeatureInterface::ENABLED);
        $this->assertFalse($storage->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::MANAGE_JOBS);
        $this->assertFalse($storage->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::REPORTING);
        $this->assertTrue($storage->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::SCHEDULE);
        $this->assertTrue($storage->hasFeature($event));
    }
    
    public function testColumnMap()
    {
        $storage = new StorageDb();
        
        $expected = $storage->getColumnMap();
        
        $storage->setColumnMap(array('report' => 'test'));
        $expected['report'] = 'test';
        
        $this->assertEquals($expected, $storage->getColumnMap());
    }
}