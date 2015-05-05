<?php
use Itt\Lib\Permissions\Acl;
return array(
    'navigation' => array(
        'sporktools' => array(
            array(
                'label'     => 'Jobs',
                'route'     => 'sporktools/job',
                'visible'   => false,
            ),
            array(
                'label'     => 'Log',
                'route'     => 'sporktools/log',
            ),
            array(
                'label'     => 'Info',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'PHP',
                        'route'     => 'sporktools/info/php',
                    ),
                    array(
                        'label'     => 'Events',
                        'route'     => 'sporktools/events'
                    ),
                    array(
                        'label'     => 'Services',
                        'route'     => 'sporktools/services',
                    ),
                )
            ),
            array(
                'label'     => 'Tests',
                'uri'       => '#',
                'pages'     => array(
                    array(
                        'label'     => 'Error',
                        'route'     => 'sporktools/test/error',
                    ),
                    array(
                        'label'     => 'Exception',
                        'route'     => 'sporktools/test/exception',
                    ),
                    array(
                        'label'     => 'PHP Extensions',
                        'route'     => 'sporktools/test/extensions',
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