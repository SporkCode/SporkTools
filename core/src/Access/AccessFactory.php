<?php
namespace SporkTools\Core\Access;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccessFactory implements FactoryInterface
{
    const CONFIG = 'sporktools-access';
    
    const SERVICE = 'SporkToolsAccess';
    
    protected $types = array(
        'allow' => 'SporkTools\Core\Access\AllowAccess',
        'deny' => 'SporkTools\Core\Access\DenyAccess',
        'aclinheritrole' => 'SporkTools\Core\Access\AclInheritRoleAccess',
    );
    
    public function createService(ServiceLocatorInterface $services)
    {
        $appConfig = $services->get('config');
        $config = isset($appConfig[self::CONFIG])
                ? (array) $appConfig[self::CONFIG] : array();
        
        if (isset($config['type'])) {
            $key = strtolower($config['type']);
            $class = array_key_exists($key, $this->types) 
                    ? $this->types[$key] : $config['type'];
            if (!class_exists($class)) {
                throw new \Exception(sprintf("Invalid Access Service type (%s)", $class));
            }
            $access = new $class();
            if (!$access instanceof AbstractAccess) {
                throw new \Exception("Access Service must implement SporkTools\Core\Access\AbstractAccess");
            }
        } else {
            $access = new DenyAccess();
        }
        
        $access->setServices($services);
        
        foreach ($config as $name => $value) {
            $method = 'set' . $name;
            if (method_exists($access, $method)) {
                call_user_func_array(array($access, $method), (array) $value);
            }
        }
    
        return $access;
    }
}
