<?php
namespace SporkTools\Core\Job;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Methods from Message plugin which is loaded by default
 * @method null info($message)
 * @method null warning($message)
 * @method null error($message)
 * @method null ping($character = '.')
 */
abstract class AbstractTask extends AbstractBase
{
    abstract public function run(Event $event);
}