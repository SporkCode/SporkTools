<?php
namespace SporkTools\Core\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use SporkTools\Core\Job\Feature\FeatureInterface;

class ServiceFactory extends \Zend\Navigation\Service\DefaultNavigationFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::createService($serviceLocator);
        
        if ($serviceLocator->has(\SporkTools\Core\Job\ServiceFactory::MANAGER)) {
            $jobManager = $serviceLocator->get(\SporkTools\Core\Job\ServiceFactory::MANAGER);
            if ($jobManager->hasFeature(FeatureInterface::ENABLED)) {
                $page = $pages->findOneBy('route', 'control/job');
                if (null !== $page) {
                    $page->setVisible(true);
                }
            }
        }
        
        return $pages;
    }
    
    protected function getName()
    {
        return 'control';
    }
}