<?php
namespace SporkTools\Core\Access;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;

class AccessListener extends AbstractListenerAggregate 
{
    const SERVICE_AUTHENTICATION = 'controlauthentication';
    
    const SERVICE_PERMISSION = 'controlpermission';
    
    const PERMISSION_RESOURCE = 'controlresource';
    
    protected $authenticationRoute;
    
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER, 
            array($this, 'injectLayoutMenu'));
        
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach(
            'SporkTools', 
            MvcEvent::EVENT_DISPATCH, 
            array($this, 'authorize'), 
            10000);
    }
    
    public function authorize(MvcEvent $event)
    {
        $response = $event->getResponse();
        $services = $event->getApplication()->getServiceManager();
        if ($services->has(AbstractAccess::KEY)) {
            $authorize = $services->get(AbstractAccess::KEY);
            if (!$authorize instanceof AbstractAccess) {
                throw new \Exception('Authorize Service must implement of SporkTools\Core\Access\AbstractAccess');
            }
            
            if (!$authorize->isAuthenticated()) {
                $redirect = $authorize->getAuthenticateRedirect();
                if (null === $redirect) {
                    $response->setStatusCode(403);
                    return $response;
                }
                $redirectPlugin = $services->get('controllerPluginManager')->get('redirect'); 
                if ($authorize->isAuthenticateRedirectRoute()) {
                    return $redirectPlugin->toRoute($redirect);
                }
                return $redirectPlugin->toUrl($redirect);
            }
            
            if ($authorize->isAuthorized()) {
                return;
            }
            
            $redirect = $authorize->getAuthorizeRedirect();
            if (null === $redirect) {
                $response->setStatusCode(403);
                return $response;
            }
            $redirectPlugin = $services->get('controllerPluginManager')->get('redirect');
            if ($authorize->isAuthorizeRedirectRoute()) {
                return $redirectPlugin->toRoute($redirect);
            }
            return $redirectPlugin->toUrl($redirect);
        }

        $response->setStatusCode(403);
        return $response;
    }
    
    public function injectLayoutMenu(MvcEvent $event)
    {
        $access = $event->getApplication()->getServiceManager()->get(AbstractAccess::KEY);
        if ($access->isAuthorized()) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('spork-tools/menu');
            $event->getViewModel()->addChild($viewModel, 'sporkToolsMenu');
        }
    }
}