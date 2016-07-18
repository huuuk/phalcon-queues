<?php
namespace Huuuk\Queues;
use Huuuk\Queues\QueuedJobs\DatabaseJob;

/**
* All jobs will be stored in database table,
* and will be processed by worker in future
*/
class DatabaseQueue extends Queue
{
    use DatabaseTable;

    /**
     * Name of the default queue
     */
    const DEFAULT_QUEUE = 'default';

    /**
     * Get queue type
     * @return string
     */
    public function getType()
    {
        return QueueManager::DATABASE_QUEUE;
    }

    /**
     * Push job to database
     * @param  \Closure|Huuuk\Queues\Job  $job
     * @param  string  $queue
     * @param  integer $delay
     * @return void
     */
    public function push($job, $queue = null, $delay = 0)
    {
        $now = time();
        $this->pushToDatabase([
            'queue'        => $queue ? $queue : static::DEFAULT_QUEUE,
            'payload'      => $this->createPayload($job),
            'available_at' => $now + $delay,
            'created_at'   => $now,
            'attemps'      => 0,
            'done'         => 0,
            'failed'       => 0,
        ]);
    }

    /**
     * Get all available jobs based on conditions
     * @param  Array $conditions 
     * @return Array
     */
    public function pull($conditions)
    {
        $now = time();
        $attributes = array_merge($conditions, [ 'now' => $now ]);
        $bindAttributes = $this->prepareAttributes($attributes);
        $rawJobs = $this->getDb()->query( $this->getSelectQuery(), $bindAttributes );
        $rawJobs->setFetchMode(\Phalcon\Db::FETCH_OBJ);
        $jobs = [];
        foreach ($rawJobs->fetchAll() as $rawJob) {
            $job = new DatabaseJob($rawJob);
            $job->setTable($this->getTable());
            $job->setDi($this->getDi());
            $jobs[] = $job;
        }
        return $jobs;
    }

    /**
     * Insert job into jobs table
     * @param  Array $values
     */
    protected function pushToDatabase($values)
    {
        $this->getDb()->insertAsDict($this->getTable(), $values);
    }

    /**
     * Resolve database container
     * @return Phalcon\Db\Adapter\Pdo
     */
    protected function getDb()
    {
        return $this->getDI()->getShared('db');
    }

    /**
     * Tempalte for select query
     * @return string
     */
    protected function getSelectQuery()
    {
        return "SELECT * FROM {$this->getTable()} 
                WHERE failed <> 1 AND 
                      done = 0 AND
                      (available_at <= :now  AND attemps < :max_tries AND queue IN (:queues))
                ORDER BY created_at";
    }

    /**
     * Prepare attributes for binding
     * @param  Array $attrs
     * @return Array
     */
    protected function prepareAttributes($attrs)
    {
        $attributes = [];
        foreach ($attrs as $key => $value) {
            $attributes[':'.$key] = $value;
        }
        return $attributes;
    }
}