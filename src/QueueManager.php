<?php
namespace Huuuk\Queues;

/**
* 
*/
class QueueManager
{

    public function getQueue($driver = null)
    {
        switch ($driver) {
            case 'sync':
                return new SyncQueue(/*$this->di*/);
                break;
            default:
                return new NullQueue(/*$this->di*/);
                break;
        }
    }
}