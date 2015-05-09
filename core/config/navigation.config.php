<?php
use Itt\Lib\Permissions\Acl;
return array(
    'navigation' => array(
        'sporktools' => array(
            array(
                'label'     => 'Jobs',
                'route'     => 'spork-tools/job',
                'visible'   => false,
            ),
            array(
                'label'     => 'Log',
                'route'     => 'spork-tools/log',
            ),
            array(
                'label'     => 'Info',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'PHP',
                        'route'     => 'spork-tools/info/php',
                    ),
                    array(
                        'label'     => 'Events',
                        'route'     => 'spork-tools/events'
                    ),
                    array(
                        'label'     => 'Services',
                        'route'     => 'spork-tools/services',
                    ),
                )
            ),
            array(
                'label'     => 'Tests',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'Error',
                        'route'     => 'spork-tools/test/error',
                    ),
                    array(
                        'label'     => 'Exception',
                        'route'     => 'spork-tools/test/exception',
                    ),
                    array(
                        'label'     => 'PHP Extensions',
                        'route'     => 'spork-tools/test/extensions',
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