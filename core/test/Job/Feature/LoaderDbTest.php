<?php
namespace SporkToolsTest\Job\Feature;

use SporkTools\Core\Job\Feature\LoaderDb;
use SporkTools\Core\Job\Event;
use SporkTools\Core\Job\Feature\FeatureInterface;
class LoaderDbTest extends \PHPUnit_Framework_TestCase
{
    public function testHasFeature()
    {
        $loader = new LoaderDb();
        $event = new Event();

        $event->setParam('feature', FeatureInterface::ENABLED);
        $this->assertTrue($loader->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::MANAGE_JOBS);
        $this->assertTrue($loader->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::REPORTING);
        $this->assertTrue($loader->hasFeature($event));
        
        $event->setParam('feature', FeatureInterface::SCHEDULE);
        $this->assertTrue($loader->hasFeature($event));
        
        $event->setParam('feature', 'gndn');
        $this->assertFalse($loader->hasFeature($event));
    }
}