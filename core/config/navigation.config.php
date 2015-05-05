<?php
use Itt\Lib\Permissions\Acl;
return array(
    'navigation' => array(
        'control' => array(
            array(
                'label'     => 'Jobs',
                'route'     => 'control/job',
                'visible'   => false,
            ),
            array(
                'label'     => 'Log',
                'route'     => 'control/log',
            ),
            array(
                'label'     => 'Info',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'PHP',
                        'route'     => 'control/info/php',
                    ),
                    array(
                        'label'     => 'Events',
                        'route'     => 'control/events'
                    ),
                    array(
                        'label'     => 'Services',
                        'route'     => 'control/services',
                    ),
                )
            ),
            array(
                'label'     => 'Tests',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'Error',
                        'route'     => 'control/test/error',
                    ),
                    array(
                        'label'     => 'Exception',
                        'route'     => 'control/test/exception',
                    ),
                    array(
                        'label'     => 'PHP Extensions',
                        'route'     => 'control/test/extensions',
                    ),
                )
            ),
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'controlnavigation' => 'SporkTools\Core\Navigation\ServiceFactory'
        )
    )
);