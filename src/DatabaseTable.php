<?php
namespace Huuuk\Queues;

trait DatabaseTable {

    /**
     * Jobs table in database
     * @var string
     */
    protected $table;

    /**
     * Set jobs table
     * @param string $table table name
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Get jobs table
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
}