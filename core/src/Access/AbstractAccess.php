<?php
namespace SporkTools\Core\Access;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationServiceInterface;

abstract class AbstractAccess implements FactoryInterface
{
    const KEY = 'sporktools-access';
    
    /**
     * @var \Zend\Authentication\AuthenticationServiceInterface
     */
    protected $authenticationService = 'auth';

    protected $authenticateRedirect;
    
    protected $isAuthenticateRedirectRoute;
    
    protected $authorizeRedirect;
    
    protected $isAuthorizeRedirectRoute;
    
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $services;

    abstract public function isAuthorized();
    
    public function createService(ServiceLocatorInterface $services)
    {
        $this->services = $services;
        
        $appConfig = $this->services->get('config');
        $config = isset($appConfig[self::KEY])
                ? (array) $appConfig[self::KEY] : array();
        foreach ($config as $name => $value) {
            $method = 'set' . $name;
            if (method_exists($this, $method)) {
                call_user_func_array(array($this, $method), (array) $value);
            }
        }
        
        return $this;
    }
    
    public function isAuthenticated()
    {
        return $this->getAuthenticationService()->hasIdentity();
    }
    
    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }
    
    public function getAuthenticationService()
    {
        if (!$this->authenticationService instanceof AuthenticationServiceInterface) {
            if (!$this->services->has($this->authenticationService)) {
                throw new \Exception('Authentication Service not found');
            }
            $this->authenticationService = $this->services->get($this->authenticationService);
            
            if (!$this->authenticationService instanceof AuthenticationServiceInterface) {
                throw new \Exception('Authentication Service must implement Zend\Authentication\AuthenticationServiceInterface');
            }
        }
        
        return $this->authenticationService;
    }
    
    public function setAuthenticateRedirect($redirect, $isRoute = false)
    {
        $this->authenticateRedirect = $redirect;
        $this->isAuthenticateRedirectRoute = (boolean) $isRoute;
    }
    
    public function getAuthenticateRedirect()
    {
        return $this->authenticateRedirect;
    }
    
    public function isAuthenticateRedirectRoute()
    {
        return $this->isAuthenticateRedirectRoute;
    }
    
    public function setAuthorizeRedirect($redirect, $isRoute = false)
    {
        $this->authorizeRedirect = $redirect;
        $this->isAuthorizeRedirectRoute = (boolean) $isRoute;
    }
    
    public function getAuthorizeRedirect()
    {
        return $this->authorizeRedirect;
    }
    
    public function isAuthorizeRedirectRoute()
    {
        return $this->isAuthorizeRedirectRoute;
    }
    
    public function setServices(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }
    
    public function getServices()
    {
        return $this->services;
    }
}