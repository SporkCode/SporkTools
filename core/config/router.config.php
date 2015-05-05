<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'SporkTools\Core\Controller\Index'  => 'SporkTools\Core\Controller\IndexController',
            'SporkTools\Core\Controller\Log'    => 'SporkTools\Core\Controller\LogController',
            'SporkTools\Core\Controller\Job'    => 'SporkTools\Core\Controller\JobController',
            'SporkTools\Core\Controller\Test'   => 'SporkTools\Core\Controller\TestController',
        ),
    ),
    'router'        => array(
        'routes'        => array(
            'control'       => array(
                'type'          => 'literal',
                'options'       => array(
                    'route'         => '/control',
                    'defaults'      => array(
                        'controller'    => 'SporkTools\Core\Controller\Index',
                        'action'        => 'index',
                    ),
                    'constraints'   => array(
                        'action'        => '[a-zA-Z0-9-]*',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'action'        => array(
                        'type'          => 'segment',
                        'options'       => array(
                            'route'         => '/:action',
                        ),
                    ),
                    'info'          => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/info',
                        ),
                        'may_erminate'  => false,
                        'child_routes'  => array(
                            'php'           => array(
                                'type'          => 'literal',
                                'options'       => array(
                                    'route'         => '/php',
                                    'defaults'      => array(
                                        'action'        => 'php-info',
                                    ),
                                ),                   	
                            ),
                        ),
                    ),       
                    'events'        => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/events',
                            'defaults'      => array(
                                'action'       => 'events',
                            ),
                        ),
                    ),
                    'job'           => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/job',
                            'defaults'      => array(
                                'controller'    => 'SporkTools\Core\Controller\Job',
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'run'           => array(
                                'type'          => 'segment',
                                'options'       => array(
                                    'route'         => '/:job/run',
                                    'constraints'   => array(
                                        'job'           => '[a-zA-Z0-9._-]+',
                                    ),
                                    'defaults'      => array(
                                        'action'        => 'run',
                                    ),
                                ),
                            ),
                            'edit'           => array(
                                'type'          => 'segment',
                                'options'       => array(
                                    'route'         => '[/:job]/edit',
                                    'constraints'   => array(
                                        'job'           => '[a-zA-Z0-9._-]+',
                                    ),
                                    'defaults'      => array(
                                        'action'        => 'edit',    
                                    ),
                                ),
                            ),
                            'schedule'      => array(
                                'type'          => 'segment',
                                'options'       => array(
                                    'route'         => '[/:job]/schedule',
                                    'constraints'   => array(
                                        'job'           => '[a-zA-Z0-9._-]+',
                                    ),
                                    'defaults'      => array(
                                        'action'        => 'schedule',    
                                    ),
                                ),
                            ),
                            'delete'        => array(
                                'type'          => 'segment',
                                'options'       => array(
                                    'route'         => '/:job/delete',
                                    'constraints'   => array(
                                        'job'           => '[a-zA-Z0-9._-]+',
                                    ),
                                    'defaults'      => array(
                                        'action'        => 'delete',    
                                    ),
                                ),        
                            ),
                        ),
                    ),
                    'log'           => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/log',
                            'defaults'      => array(
                                'controller'    => 'SporkTools\Core\Controller\Log',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'index'         => array(
                                'type'          => 'literal',
                                'options'       => array(
                                    'route'         => '/index',
                                    'defaults'      => array(
                                        'action'        => 'index',
                                    ),
                                ),
                            ),
                            'store'         => array(
                                'type'          => 'literal',
                                'options'       => array(
                                    'route'         => '/store',
                                    'defaults'      => array(
                                        'action'        => 'store',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'services'      => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/services',
                            'defaults'      => array(
                                'action'       => 'services',
                            ),
                        ),
                    ),
                    'test'          => array(
                        'type'          => 'literal',
                        'options'       => array(
                            'route'         => '/test',
                            'defaults'      => array(
                                'controller'    => 'SporkTools\Core\Controller\Test',
                            ),
                        ),
                        'may_terminate' => false,
                        'child_routes'  => array(
                            'error'         => array(
                                'type'      => 'literal',
                                'options'   => array(
                                    'route'     => '/error',
                                    'defaults'  => array(
                                        'action'    => 'error',
                                    ),
                                ),
                            ),
                            'exception'     => array(
                                'type'      => 'literal',
                                'options'   => array(
                                    'route'     => '/exception',
                                    'defaults'  => array(
                                        'action'    => 'exception',
                                    ),
                                ),
                            ),
                            'extensions'     => array(
                                'type'      => 'literal',
                                'options'   => array(
                                    'route'     => '/extensions',
                                    'defaults'  => array(
                                        'action'    => 'extensions',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);