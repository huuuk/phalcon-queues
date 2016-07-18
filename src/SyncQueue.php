<?php
namespace Huuuk\Queues;
use Closure;

/**
* Simple stub for queue, every job pushed
* in this queue will be executed immediatly
*/
class SyncQueue extends Queue
{
    /**
     * Get queue type
     * @return string
     */
    public function getType()
    {
        return QueueManager::SYNC_QUEUE;
    }

    /**
     * @param  \Closure|Huuuk\Queues\Job  
     * @param  string  $queue 
     * @param  integer $delay
     * @return void
     */
    public function push($job, $queue = 'default', $delay = 0)
    {
        if ($job instanceof Job) {
            $job->setDi( $this->getDi() );
            $job->handle();
        }
        if ($job instanceof Closure) {
            $job( $this->getDi() );
        }
    }

    /**
     * Since we process all jobs synchronusly,
     * no needs to retreive jobs from somewhere
     */
    public function pull($params)
    {

    }
}