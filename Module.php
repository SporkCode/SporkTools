<?php
namespace SporkTools;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Itt\Lib\Permissions\Acl;
use Zend\Stdlib\Glob;
use SporkTools\Core\Listener;

class Module
{

    const LISTENER_PERMISSION = 'controllistenerpermission';

    protected $config;

    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager = $application->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        
        $appConfig = $serviceManager->get('config');
        $this->config = isset($appConfig['control_module']) 
                && is_array($appConfig['control_module']) 
                        ? $appConfig['control_module'] : array();
        
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attachAggregate($serviceManager->get(self::LISTENER_PERMISSION));
        $eventManager->attachAggregate(new Listener\ControlFooter());
        $eventManager->attachAggregate(new Listener\InjectLayout());
        
        // $headLink = $serviceManager->get('viewHelperManager')->get('headLink');
        // $headLink->appendStylesheet('/sporktools/style');
    }

    public function getConfig()
    {
        $config = array_merge_recursive(
            require __DIR__ . '/core/config/module.config.php', 
            require __DIR__ . '/core/config/navigation.config.php', 
            require __DIR__ . '/core/config/router.config.php');
        
        foreach (Glob::glob(__DIR__ . '/config/*.config.php') as $file) {
            $config = array_merge_recursive($config, require $file);
        }
        
        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ . '\Core' => __DIR__ . '/core/src/',
                    __NAMESPACE__ => __DIR__ . '/src/'
                )
            )
        );
    }
}
