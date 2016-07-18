<?php 
namespace Huuuk\Queues;

/**
* All jobs will be sent in /dev/null 
*/
class NullQueue extends Queue
{
    /**
     * Get queue type
     * @return stirng
     */
    public function getType()
    {
        return QueueManager::NULL_QUEUE;
    }
    
    /**
     * No one job will not be processed
     */
    public function push($job)
    {
        // To infinity and beyond 
    }

    /**
     * Nothing to pull in this queue type
     */
    public function pull($params)
    {
    	// 
    }
}