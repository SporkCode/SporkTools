<?php
namespace SporkTools\Core\Job;

use SporkTools\Core\Config\ServiceFactory as ConfigServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ServiceFactory implements FactoryInterface
{
    const SERVICE = 'sporkToolsJobs';
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $jobManager = new Manager();
        $config = $serviceLocator->get(ConfigServiceFactory::SERVICE)->job;
        foreach ($config as $key => $value) {
            switch ($key) {
                case 'features':
                    foreach ($value as $name => $options) {
                        if ($options instanceof Config) {
                            $options = $options->toArray();
                        }
                        if (is_int($name)) {
                            $jobManager->addFeature($options);
                        } else {
                            $jobManager->addFeature($name, $options);
                        }
                    }
                    break;
            }
        }
        
        return $jobManager;
    }
}