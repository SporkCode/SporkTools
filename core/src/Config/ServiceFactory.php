<?php
namespace SporkTools\Core\Config;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ServiceFactory implements FactoryInterface
{
    const SERVICE = 'sporkToolsConfig';
    
    public function createService(ServiceLocatorInterface $services)
    {
        $appConfig = $services->get('config');
        $config = isset($appConfig['sporktools']) ? $appConfig['sporktools'] : array();
        return new Config($config);
    }
}