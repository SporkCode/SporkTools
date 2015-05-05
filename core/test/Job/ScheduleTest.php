<?php
namespace SporkToolsTest\Job;

use SporkTools\Core\Job\Schedule;
class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testDescription()
    {
        $testMap = array(
            '600:0'             => 'Every 10 minuets',
            '600:345'           => 'Every 10 minuets starting at 0:05:45',
            '3600:0'            => 'Every hour',
            '3600:630'          => 'Every hour starting at 0:10:30',
            '7200:0'            => 'Every 2 hours',
            '7200:1215'         => 'Every 2 hours starting at 0:20:15',
            '86400:0'           => 'Every day',
            '86400:45045'       => 'Every day at 4:30:45 PST',
            '108000:45045'      => 'Every 30 hours starting at 4:30:45 PST',
            '172800:0'          => 'Every 2 days',
            '172800:45045'      => 'Every 2 days at 4:30:45 PST',
            '604800:0'          => 'Every week on Wednesday at 16:00:00 PST',
            '604800:28800'      => 'Every week on Thursday at 0:00:00 PST',
            '604800:425145'     => 'Every week on Monday at 14:05:45 PST',
            '2592000:0'         => 'Every 30 days',
            '2628000:0'         => 'Every month on the 31st at 16:00:00 PST',
            '2628000:28800'     => 'Every month on the 1st at 0:00:00 PST',
            '2628000:597945'    => 'Every month on the 7th at 14:05:45 PST',  
        );
        
        foreach ($testMap as $times => $description) {
            $schedule = new Schedule($times);
            $this->assertEquals($description, $schedule->getDescription(),
                "Failed asserting that the description for the schedule '$times' is '$description'");
        }
    }
}