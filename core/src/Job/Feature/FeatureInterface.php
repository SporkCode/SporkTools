<?php
namespace SporkTools\Core\Job\Feature;

use Zend\EventManager\ListenerAggregateInterface;

interface FeatureInterface extends ListenerAggregateInterface
{
    const ENABLED       = 'enabled';

    const MANAGE_JOBS   = 'manageJobs';
    
    const REPORTING     = 'reporting';
    
    const SCHEDULE      = 'schedule';
}