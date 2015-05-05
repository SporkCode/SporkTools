<?php
namespace SporkToolsTest;

use Zend\Loader\StandardAutoloader;

class Bootstrap
{
    public static function initialize()
    {
        $root = dirname(dirname(__DIR__));
        require_once dirname($root) . '/library/zendframework/zendframework/library/Zend/Loader/StandardAutoloader.php';
        $autoloader = new StandardAutoloader(array(
            'autoregister_zf' => true,
            'namespaces' => array(
                'SporkTools\Core' => $root . '/core/src',
                'SporkTools' => $root . '/src',
            ),
        ));
        $autoloader->register();
    }
}

Bootstrap::initialize();