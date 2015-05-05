<?php
namespace SporkTools\Core\Listener;

use Zend\EventManager\ListenerAggregateInterface;
//use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Acl\AclInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\FactoryInterface;

class Permission implements FactoryInterface, ListenerAggregateInterface 
{
    const SERVICE_AUTHENTICATION = 'controlauthentication';
    
    const SERVICE_PERMISSION = 'controlpermission';
    
    const PERMISSION_RESOURCE = 'controlresource';
    
    protected $authenticationRoute;
    
    protected $listener;
    
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listener = $sharedEvents->attach('Control', MvcEvent::EVENT_DISPATCH, array($this, 'permission'), 10000);
    }
    
    public function detach(EventManagerInterface $events)
    {
        if (null !== $this->listener) {
            $events->detach($this->listener);
        }
    }
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //$eventManager = $serviceLocator->get('eventManager');
        //$eventManager->attachAggregate($this);
        $config = $serviceLocator->get('config');
        if (isset($config['control_permission'])) {
            if (isset($config['control_permission']['authentication_route'])) {
                $this->authenticationRoute = (string) $config['control_permission']['authentication_route'];
            }
        }
        return $this;
    }
    
    public function permission(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        if ($serviceManager->has(self::SERVICE_AUTHENTICATION)) {
            $auth = $serviceManager->get(self::SERVICE_AUTHENTICATION);
            if (!$auth instanceof AuthenticationService) {
                throw new \Exception('Control authentication service must be an instance of \Zend\Authentication\AuthenticationService');
            }
            $role = $this->getRole($auth, $serviceManager);
            if (null === $role && null !== $this->authenticationRoute) {
                return $serviceManager->get('controllerPluginManager')->get('redirect')->toRoute($this->authenticationRoute);
            }
            
            if ($serviceManager->has(self::SERVICE_PERMISSION)) {
                $permission = $serviceManager->get(self::SERVICE_PERMISSION);
                
                if ($permission instanceof AclInterface || $permission instanceof Rbac) {
                    if ($permission->hasResource(self::PERMISSION_RESOURCE)) {
                        if ($permission->isAllowed($role, self::PERMISSION_RESOURCE)) {
                            return;
                        }
                    }
                } else {
                    throw new \Exception('Control permission service must be an instace of \Zend\Permissions\Acl\AclInterface or \Zend\Permissions\Rbac\Rbac');
                }
            }
        }
        $response = $event->getResponse();
        $response->setStatusCode(403);
        return $response;
    }
    
    protected function getRole(AuthenticationService $auth, ServiceLocatorInterface $serviceManager)
    {
        //return $auth->getIdentity();
        $role = $serviceManager->get('authMember');
        return $role instanceof \Itt\Model\Member\Guest ? null : $role;
    }
}