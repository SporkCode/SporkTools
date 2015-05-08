<?php
namespace SporkTools\Core\Access;

class DenyAccess extends AbstractAccess
{
    public function isAuthorized()
    {
        return false;
    }
}