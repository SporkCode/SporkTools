<?php
namespace SporkTools\Core\Access;

class AllowAccess extends AbstractAccess
{
    public function isAuthorized()
    {
        return true;
    }
}