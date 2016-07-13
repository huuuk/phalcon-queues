<?php
namespace Huuuk\Queues;
use Closure;

/**
* Simple stub for queue every job pushed
* in this queue will be executed immediatly
*/
class SyncQueue extends Queue
{

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
}