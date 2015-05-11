<?php
namespace SporkTools\Core\Store;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;
use Zend\Http\Header\HeaderInterface;

class Store
{
    protected $filter;
    
    /**
     * @var \Zend\Http\Request
     */
    protected $request;
    
    /**
     * @var \Zend\Http\Response
     */
    protected $response;
    
    protected $order;
    
    /**
     * @var \Zend\Db\TableGateway\AbstractTableGateway
     */
    protected $table;
    
    public function __construct(AbstractTableGateway $table = null,
            Request $request = null, Response $response = null)
    {
        if (null !== $table) {
            $this->setTable($table);
        }
        
        if (null !== $request) {
            $this->setRequest($request);
        }
        
        if (null !== $response) {
            $this->setResponse($response);
        }
    }
    
    public function getData()
    {
        $table = $this->getTable();
        list($offset, $limit) = $this->getRange();
        $select = $this->getSelect();
        $select
            ->offset((int) $offset)
            ->limit((int) $limit - $offset + 1);
        if (null !== $this->order) {
            $select->order($this->order);
        }
        if (null !== $this->filter) {
            $select->where($this->filter);
        }
        $data = $table->selectWith($select);
        $this->setContentRange($select, $offset, $limit);
        return $data->toArray();
    }
    
    public function getFilter()
    {
        return $this->filter;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * @return \Zend\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * @return \Zend\Db\TableGateway\AbstractTableGateway
     */
    public function getTable()
    {
        return $this->table;
    }
    
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }
    
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
    
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
    
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    public function setTable(AbstractTableGateway $table)
    {
        $this->table = $table;
    }
    
    public function toArray()
    {
        return $this->getData();
    }
    
    protected function count(Select $select)
    {
        $select
            ->reset('offset')
            ->reset('limit')
            ->reset('order')
            ->columns(array('count' => new Expression('count(*)')));
        $db = $this->getTable()->getAdapter();
        $sql = $select->getSqlString($db->getPlatform());
        return $db->query($sql, Adapter::QUERY_MODE_EXECUTE)->current()->count;
    }
    
    protected function getRange()
    {
        $header = $this->request->getHeader('Range');
        if ($header instanceof HeaderInterface) {
            if (preg_match('`items=([0-9]+)-([0-9]+)`', $header->getFieldValue(), $matches)) {
                return array($matches[1], $matches[2]);
            }
        }
        return $this->getRangeDefault();
    }
    
    protected function getRangeDefault()
    {
        return array(0, 25);
    }
    
    protected function getSelect()
    {
        return new Select($this->getTable()->getTable());
    }
    
    protected function setContentRange(Select $select, $offset, $limit)
    {
        $count = $this->count($select);
        $this->response->getHeaders()->addHeaderLine(sprintf(
            'Content-Range: items %d-%d/%d', $offset, $limit, $count));
    }
}