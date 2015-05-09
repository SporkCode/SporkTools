<?php
namespace SporkTools\Core\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class InjectLayout extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'SporkTools', 
            MvcEvent::EVENT_DISPATCH, 
            array($this, 'injectLayout'), 
            -99);
    }
    
    /**
     * Wraps result view model in sporktools model layout model. 
     * 
     * @param MvcEvent $event
     */
    public function injectLayout(MvcEvent $event)
    {
        $result = $event->getResult();
        if (!$result instanceof ViewModel) {
            return;
        }
        
        if ($result->terminate()) {
            return;
        }
        
        $layout = new ViewModel();
        $layout->setTemplate('spork-tools/layout');
        $layout->addChild($result);
        $event->setResult($layout);
    }
}