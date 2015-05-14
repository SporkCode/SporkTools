<?php
namespace SporkTools\Core\Controller;

use SporkTools\Core\Store\Store;
use SporkTools\Core\Config\ServiceFactory as ConfigServiceFactory;

use Zend\Config\Config;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * SporkTools\Core\Controller$LogController
 * @todo implement as Spork RestfulTableController
 */
class LogController extends AbstractActionController
{
    public function indexAction()
    {
        $table = $this->getLogTable();
        $config = $this->getConfig();
        
        $subRows = array(array(), array());
        $columns = $config->get('columns', array(
            'timestamp' => 'Date Time',
            'priorityName' => 'Priority',
        ));
        foreach ($columns as $field => $label) {
            $subRows[1][] = array('field' => $field, 'label' => $label);
        }
        $subRows[0][] = array(
            'field' => $config->get('messageColumn', 'message'), 
            'label' => 'Message',
            'colSpan' => count($columns),
        );
        
//        $messageRow = array()
//         $columns = array();
//         foreach ($config->get('columns'))
//         $columns = $config->get('columns', new Config(array(
//             'timestamp' => 'Date Time',
//             'priorityName' => 'Priority',
//         )))->toArray();
        
        $sort = $config->get('sort', '-timestamp');
        preg_match('`^(\+|-)?(.*)$`', $sort, $parts);
        $property = $parts[2];
        $descending = $parts[1] == '-';
        //$sort = array('property' => $property, 'descending' => $descending);
        
        return array(
            'isConfigured' => $table instanceof TableGatewayInterface,
            'subRows' => $subRows,
            'sortProperty' => $property,
            'sortDescending' => $descending,
        );
    }
    
    public function storeAction()
    {
        $logTable = $this->getLogTable();
        $store = new Store($logTable, $this->request, $this->response);
        $sort = $this->request->getQuery('sort');
        if (null !== $sort) {
            preg_match('`^( |-)?(.+)$`', $sort, $parts);
            $store->setOrder($parts[2] . ' ' . ($parts[1] == '-' ? 'desc' : 'asc'));
        }
        return new JsonModel($store->getData());
    }
    
    /**
     * @return \Itt\Model\Log\Table
     */
    protected function getLogTable()
    {
        $config = $this->getConfig();
        
        if ($config->offsetExists('table')) {
            $services = $this->getServiceLocator();
            $adapterName = $config->get('dbAdapter', 'db');
            if (!$services->has($adapterName)) {
                throw new \Exception('Database adapter service not found');
            }
            $adapter = $services->get($adapterName);
            $table = new TableGateway($config['table'], $adapter);
            return $table;
        }

        return false;
    }
    
    /**
     * @return \Zend\Config\Config
     */
    protected function getConfig()
    {
        return $this
                ->getServiceLocator()
                ->get(ConfigServiceFactory::SERVICE)
                ->get('log');
    }
}