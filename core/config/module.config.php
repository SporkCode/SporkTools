<?php
use SporkTools\Core\Config\ServiceFactory as ConfigServiceFactory;
use SporkTools\Core\Access\ServiceFactory as AccessServiceFactory;
use SporkTools\Core\Job\ServiceFactory as JobServiceFactory;

return array(
    'sporktools' => array(
        'access' => array(),
        'log' => array(),
    ),
    'sporktools-log' => array(
        'table' => null,
        'dbAdapter' => 'db',
        'columns' => array(
        )
    ),
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
            ConfigServiceFactory::SERVICE => 'SporkTools\Core\Config\ServiceFactory',
            AccessServiceFactory::SERVICE => 'SporkTools\Core\Access\ServiceFactory',
            JobServiceFactory::SERVICE => 'SporkTools\Core\Job\ServiceFactory',
        )
    ),
    'view_helpers'      => array(
        'invokables'        => array(
            'SporkToolsEvents' => 'SporkTools\Core\View\Helper\Events',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../../view',
            __DIR__ . '/../view',
        ),
    ),
);
