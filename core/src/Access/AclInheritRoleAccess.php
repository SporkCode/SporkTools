<?php
namespace SporkTools\Core\Access;

use Zend\Permissions\Acl\Acl;

class AclInheritRoleAccess extends AbstractAccess
{
    /**
     * @var \Zend\Permissions\Acl\Acl
     */
    protected $acl = 'acl';
    
    /**
     * @var \Zend\Permissions\Acl\Role\RoleInterface|string
     */
    protected $role = 'administrator';
    
    /**
     * @var \Zend\Permissions\Acl\Role\RoleInterface|string
     */
    protected $user;
    
    protected $isUserService;
    
    public function isAuthorized()
    {
        $user = $this->getUser();
        $role = $this->getRole();
        return $this->getAcl()->inheritsRole($user, $role);
    }
    
    public function setAcl($acl)
    {
        $this->acl = $acl;
    }
    
    public function getAcl()
    {
        if (!$this->acl instanceof Acl) {
            if (!$this->services->has($this->acl)) {
                throw new \Exception('ACL not found');
            }
            $this->acl = $this->services->get($this->acl);
            
            if (!$this->acl instanceof Acl) {
                throw new \Exception('ACL must implement Zend\Permission\Acl\Acl');
            }
        }
        
        return $this->acl;
    }
    
    public function setRole($role)
    {
        $this->role = $role;
    }
    
    public function getRole()
    {
        return $this->role;
    }
    
    public function setUser($user, $isService = false)
    {
        $this->user = $user;
        $this->isUserService = $isService;
    }
    
    public function getUser()
    {
        if (null === $this->user) {
            throw new \Exception('User not set');
        }
        
        if ($this->isUserService) {
            if (!$this->services->has($this->user)) {
                throw new \Exception('User not found');
            }
            $this->user = $this->services->get($this->user);
            $this->isUserService = false;
        }
        
        return $this->user;
    }
}