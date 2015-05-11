<?php
namespace SporkTools\Core\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use SporkTools\Core\Store\Store;
use Zend\Db\TableGateway\TableGatewayInterface;

/**
 * SporkTools\Core\Controller$LogController
 * @todo implement as Spork RestfulTableController
 */
class LogController extends AbstractActionController
{
    const LOG_TABLE_SERVICE = 'SporkToolsLogTable';
    
    public function indexAction()
    {
        return array();
    }
    
    public function storeAction()
    {
        $logTable = $this->getLogTable();
        $store = new Store($logTable, $this->request, $this->response);
        //$store->setOrder('id desc');
        return new JsonModel($store->getData());
    }
    
    /**
     * @return \Itt\Model\Log\Table
     */
    protected function getLogTable()
    {
        $services = $this->getServiceLocator();
        
        if (!$services->has(self::LOG_TABLE_SERVICE)) {
            throw new \Exception('Log Table Service not found');
        }
        
        $table = $services->get(self::LOG_TABLE_SERVICE);
        
        if (!$table instanceof TableGatewayInterface) {
            throw new \Exception("Log Table must implement Zend\Db\TableGateway\TableGatewayInterface");
        }
        
        return $table;
    }
}