<?php
namespace Huuuk\Queues;

/**
* 
*/
class QueueManager
{
    /**
     * Name of 'database' driver
     */
    const DATABASE_QUEUE = 'database';

    /**
     * Name of 'synchronus' driver
     */
    const SYNC_QUEUE = 'sync';

    /**
     * Name of 'fake' driver
     */
    const FAKE_QUEUE = null;

    /**
     * Queue config
     * @var Phalcon\Config
     */
    protected $config;

    /**
     * Create a new queue manager instance.
     * @param  \Phalcon\Config $config
     * @return void
     */
    public function __construct($config)
    {
        if ( isset($config->queue) && isset($config->queue->driver) ) {
            $this->config = $config->queue;

            if ( $this->config->driver === static::DATABASE_QUEUE && 
                !isset( $this->config->database->jobs_table ) ) {
                throw new \Phalcon\Config\Exception("Jobs database table does not set.", 1);
            }
        }
        else {
            $this->config = new \Phalcon\Config( [ 'driver' => static::FAKE_QUEUE ] );
        }


    }

    /**
     * Choose queue based on the config
     * @return Huuuk\Queues\Queue         
     */
    public function getQueue()
    {
        switch ($this->config->driver) {
            case static::DATABASE_QUEUE:
                $queue = new DatabaseQueue();
                $queue->setTable( $this->config->database->jobs_table );
                return $queue;
                break;
            case static::SYNC_QUEUE:
                return new SyncQueue();
                break;
            default:
                return new NullQueue();
                break;
        }
    }
}