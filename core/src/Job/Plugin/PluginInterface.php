<?php
namespace SporkTools\Core\Job\Plugin;

use SporkTools\Core\Job\Feature\FeatureInterface;

interface PluginInterface extends FeatureInterface
{
    /**
     * Returns a list of methods that should be overloaded 
     */
    public function getMethods();
}