<?php
namespace SporkTools\Core\Job\Plugin;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use SporkTools\Core\Job\Event;
use Zend\Db\Adapter\Adapter;

class SqlUpdate extends DbAdapter
{

    const PATTERN_TABLE = 'TABLE\s+(`?(?P<schema>[a-z0-9_$]+)`?\.)?`?(?P<table>[a-z0-9_$]+)`?';

    const PATTERN_CREATE_TABLE = 'TABLE\s+(IF\s+NOT\s+EXISTS\s+)?(`?(?P<schema>[a-z0-9_$]+)`?\.)?`?(?P<table>[a-z0-9_$]+)`?';

    const PATTERN_ADD_COLUMN = 'ADD\s+(COLUMN\s+)?`?(?P<column>[a-z0-9_$]+)`?';

    /**
     *
     * @var \SporkTools\Core\Job\Plugin\Message
     */
    protected $message;

    public function getMethods()
    {
        return array_merge(parent::getMethods(), 
            array(
                'addColumn',
                'addTable',
                'dropColumn',
                'dropTable',
                'hasColumn',
                'hasTable',
                'isColumnType',
                'query',
            ));
    }

    /**
     * Injects message plugin and database adapter when job is run
     *
     * @param Event $event            
     */
    public function initialize(Event $event)
    {
        $this->message = $event->getTarget()->getPlugin(
            'SporkTools\Core\Job\Plugin\Message');
        parent::initialize($event);
    }

    /**
     * Adds a column to a table.
     * Attempts to check if the column already exists
     * and if it does skips the update;
     *
     * @param string $sql
     *            SQL add column statement
     */
    public function addColumn($sql)
    {
        $pattern = '/\s+' . self::PATTERN_TABLE . '\s+' .
             self::PATTERN_ADD_COLUMN . '\s+/i';
        $result = preg_match($pattern, $sql, $matches);
        if (false == $result) {
            throw new \Exception(
                'Unable to parse SQL add column statement: ' . $sql);
        }
        if ($this->hasColumn($matches['column'], $matches['table'], 
            $matches['schema'])) {
            $this->message->info(
                "Column '{$matches['column']}' already exists in table '{$matches['table']}'");
            return;
        }
        $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $this->message->info(
            "Added column '{$matches['column']}' to table '{$matches['table']}'");
    }

    public function addTable($sql)
    {
        $pattern = '/\s+' . self::PATTERN_CREATE_TABLE . '\s+/i';
        $result = preg_match($pattern, $sql, $matches);
        if (false == $result) {
            throw new \Exception(
                'Unable to parse SQL create table statement: ' . $sql);
        }
        if ($this->hasTable($matches['table'], $matches['schema'])) {
            $this->message->info("Table '{$matches['table']}' already exists");
            return;
        }
        $this->db->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $this->message->info("Created table '{$matches['table']}'");
    }

    public function dropTable($table, $schema = null)
    {
        if (null == $schema) {
            $schema = $this->db->getCurrentSchema();
        }
        $tableIdentifier = $this->db->platform->quoteIdentifierChain(
            array(
                $table,
                $schema
            ));
        if ($this->hasTable($table, $schema)) {
            $this->db->query("drop table $tableIdentifier", 
                Adapter::QUERY_MODE_EXECUTE);
            $this->message->info("Dropped table $tableIdentifier");
        } else {
            $this->message->info(
                "Cannot drop tabele $tableIdentifier. Table does not exists");
        }
    }

    public function dropColumn($column, $table, $schema = null)
    {
        if (null != $schema) {
            $previousScheam = $this->db->getCurrentSchema();
            if ($schema == $previousScheam) {
                $schema = $previousScheam = null;
            } else {
                $this->db->query(sprintf("use %s", $this->db->platform->quoteIdentifier($schema)));
            }
        }
        
        $tableIdentifier = $this->db->platform->quoteIdentifier($table);
        $columnIdentifier = $this->db->platform->quoteIdentifier($column);
        
        if ($this->hasColumn($column, $table, $schema)) {
            $this->db->query(
                "alter table $tableIdentifier drop column $columnIdentifier", 
                Adapter::QUERY_MODE_EXECUTE);
            $this->message->info(
                "Dropped column $columnIdentifier from table $tableIdentifier");
        } else {
            $this->message->info(
                "Cannot drop column $columnIdentifier from table $tableIdentifier. " 
                    . "Table does not exist");
        }
        
        if (isset($previousScheam)) {
            $this->db->query(sprintf("use %s", $this->db->platform->quoteIdentifier($previousSchea)));
        }
    }

    /**
     * Test if a table column exists
     *
     * @param string $column            
     * @param string $table            
     * @param string $schema            
     * @return boolean
     */
    public function hasColumn($column, $table, $schema = null)
    {
        if (null == $schema) {
            $schema = $this->db->getCurrentSchema();
        }
        $result = $this->db->query(
            "select * from `information_schema`.`columns`
                where `table_schema` = :schema and `table_name` = :table and `column_name` = :column", 
            array(
                'schema' => $schema,
                'table' => $table,
                'column' => $column
            ));
        return count($result) != 0;
    }

    public function hasTable($table, $schema = null)
    {
        if (null == $schema) {
            $schema = $this->db->getCurrentSchema();
        }
        $result = $this->db->query(
            "select * from `information_schema`.`tables`
                where `table_schema` = :schema and `table_name` = :table", 
            array(
                'schema' => $schema,
                'table' => $table
            ));
        return count($result) != 0;
    }
    
    public function isColumnType($type, $column, $table, $schema = null)
    {
        if (null == $schema) {
            $schema = $this->db->getCurrentSchema();
        }
        
        if (!$this->hasColumn($column, $table, $schema)) {
            throw new \Exception("Column '$column' does not exist in '$schema:$table'");
        }
        
        $result = $this->db->query("select * from `information_schema`.`columns`
                where `table_schema` = :schema and `table_name` = :table 
                and `column_name` = :column and `data_type` = :type",
            array(
                'schema'    => $schema,
                'table'     => $table,
                'column'    => $column,
                'type'      => $type,
        ));
        return count($result) != 0;
    }
    
    public function query($sql, $parameters = null)
    {
        if (null == $parameters) {
            $parameters = Adapter::QUERY_MODE_EXECUTE;
        }
        $this->db->query($sql, $parameters);
        
        $this->message->info("Executed SQL statement: '$sql'");
    }
    
    protected function normalizeSchema(&$schema)
    {
        if (null == $schema) {
            $schema = $this->db->getCurrentSchema();
        }
    }
}