<?php

return array(
    'controller_plugins' => array(
        'invokables' => array(
        ),
    ),
    'service_manager' => array(
        'aliases'       => array(
            \SporkTools\Core\Listener\Permission::SERVICE_AUTHENTICATION    => 'auth',
            \SporkTools\Core\Listener\Permission::SERVICE_PERMISSION        => 'acl',
        ),
        'invokables'    => array(
        ),
        'factories'     => array(
            \SporkTools\Core\Job\ServiceFactory::MANAGER        => '\SporkTools\Core\Job\ServiceFactory',
            \SporkTools\Module::LISTENER_PERMISSION        => '\SporkTools\Core\Listener\Permission',
        )
    ),
    'view_helpers'      => array(
        'invokables'        => array(
            //'dojomenu'          => 'SporkTools\Core\View\Helper\Naviation\DojoMenu',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array()
        ),
    ),
    'control_permission'    => array(
        'authentication_route'  => 'auth/login',
    )
);
