<?php
use SporkTools\Core\Access\ServiceFactory as AccessServiceFactory;
use SporkTools\Core\Job\ServiceFactory as JobServiceFactory;

return array(
    'controller_plugins' => array(
        'invokables' => array(
        ),
    ),
    'service_manager' => array(
        'aliases'       => array(
        ),
        'invokables'    => array(
        ),
        'factories'     => array(
            AccessServiceFactory::SERVICE => 'SporkTools\Core\Access\ServiceFactory',
            JobServiceFactory::MANAGER => '\SporkTools\Core\Job\ServiceFactory',
        )
    ),
    'view_helpers'      => array(
        'invokables'        => array(
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../../view',
            __DIR__ . '/../view',
        ),
    ),
);
