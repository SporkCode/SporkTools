<?php
namespace SporkTools\Core\Job;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceFactory implements FactoryInterface
{
    const SERVICE = 'sporkToolsJobs';
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $jobManager = new Manager();
        $config = $serviceLocator->get('config');
        if (array_key_exists('control_jobs', $config) && is_array($config['control_jobs'])) {
            foreach ($config['control_jobs'] as $key => $value) {
                switch ($key) {
                	case 'features':
                        foreach ((array) $value as $name => $options) {
                            if (is_int($name)) {
                                $jobManager->addFeature($options);
                            } else {
                                $jobManager->addFeature($name, $options);
                            }
                        }
                	    break;
                }
            }
        }
        
        return $jobManager;
    }
}