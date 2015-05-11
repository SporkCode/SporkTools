<?php
namespace SporkToolsTest\Controller;

use SporkTools\Core\Test\TestCaseController;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use SporkTools\Core\Controller\LogController;

class LogControllerTest extends TestCaseController
{
    public function testIndex()
    {
        $result = $this->dispatch('index');
        $this->assertResponseOk();
    }
    
    public function testStore()
    {
        $db = self::$dbAdapter;
        $sql = <<<SQL
CREATE TEMPORARY TABLE `log`(
    `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `priority` smallint(5) unsigned NOT NULL,
    `priorityName` varchar(15) CHARACTER SET ascii NOT NULL,
    `message` varchar(255) CHARACTER SET ascii NOT NULL,
    `extra` text NOT NULL)
SQL;
        $db->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $table = new TableGateway('log', $db);
        $this->services->setService(LogController::LOG_TABLE_SERVICE, $table);
        
        $result = $this->dispatch('store');
        
        $this->assertResponseOk();
    }
    
    protected function dispatch($action = null)
    {
        parent::dispatch('SporkTools\Core\Controller\Log', $action);
    }
}