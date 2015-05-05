<?php
namespace SporkTools\Core\Listener;

use Zend\Authentication\AuthenticationService;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use Itt\Lib\Permissions\Acl;

/**
 * @todo make configurable, remove ITT dependencies
 */
class ControlFooter implements ListenerAggregateInterface 
{
    protected $listener;
    
    public function attach(EventManagerInterface $events)
    {
        $this->listener = $events->attach(
            MvcEvent::EVENT_RENDER, 
            array($this, 'injectControlFooter'));
    }
    
    public function detach(EventManagerInterface $events)
    {
        if (null !== $this->listener) {
            $events->detach($this->listener);
        }
    }
    
    public function injectControlFooter(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        if ($viewModel->getTemplate() == 'layout/layout') {
            $serviceManager = $event->getApplication()->getServiceManager();
            if ($serviceManager->has('acl')) {
                $acl = $serviceManager->get('acl');
                $auth = $serviceManager->get('auth');
                $role = $this->getRole($auth, $serviceManager);
                if ($acl->inheritsRole($role, Acl::ROLE_ADMINISTRATOR)) {
                	$footerViewModel = new ViewModel();
                	$footerViewModel->setTemplate('sporktools/footer');
                	$footerViewModel->setCaptureTo('controlFooter');
                    $viewModel->addChild($footerViewModel);
                }
            }
        }
    }
    
    protected function getRole(AuthenticationService $auth, ServiceLocatorInterface $serviceManager)
    {
        //return $auth->getIdentity();
        $role = $serviceManager->get('authMember');
        return $role;
    }
}