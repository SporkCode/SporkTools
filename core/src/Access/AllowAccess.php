<?php
namespace SporkTools\Core\Access;

class AllowAccess extends AbstractAccess
{
    public function isAuthenticated()
    {
        return true;
    }
    
    public function isAuthorized()
    {
        return true;
    }
}