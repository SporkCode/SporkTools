<?php
namespace SporkTools\Core\Access;

use SporkTools\Core\Config\ServiceFactory as ConfigServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Config\Config;

class ServiceFactory implements FactoryInterface
{
    const SERVICE = 'SporkToolsAccess';
    
    protected $types = array(
        'allow' => 'SporkTools\Core\Access\AllowAccess',
        'deny' => 'SporkTools\Core\Access\DenyAccess',
        'aclinheritrole' => 'SporkTools\Core\Access\AclInheritRoleAccess',
    );
    
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get(ConfigServiceFactory::SERVICE)->get('access');
        
        if ($config->offsetExists('type')) {
            $key = strtolower($config->get('type'));
            $class = array_key_exists($key, $this->types) 
                    ? $this->types[$key] : $config->get('type');
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
                if ($value instanceof Config) {
                    $value = $value->toArray();
                }
                call_user_func_array(array($access, $method), (array) $value);
            }
        }
    
        return $access;
    }
}
