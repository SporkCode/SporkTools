<?php
namespace SporkTools\Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use SporkTools\Core\Store\Store;

/**
 * SporkTools\Core\Controller$LogController
 * @todo implement as Spork RestfulTableController
 */
class LogController extends AbstractActionController
{
    public function indexAction()
    {
        return array();
    }
    
    public function storeAction()
    {
        $logTable = $this->getLogTable();
        $store = new Store($logTable, $this->request, $this->response);
        $store->setOrder('id desc');
        return new JsonModel($store->getData());
    }
    
    /**
     * @return \Itt\Model\Log\Table
     */
    protected function getLogTable()
    {
        return $this->getServiceLocator()->get('modelLogTable');
    }
}