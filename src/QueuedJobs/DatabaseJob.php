<?php
namespace Huuuk\Queues\QueuedJobs;

use Huuuk\Queues\QueuedJobInterface;
use Huuuk\Queues\DatabaseTable;

/**
* Class for DatabaseQueue jobs
*/
class DatabaseJob extends QueuedJob implements QueuedJobInterface
{
    use DatabaseTable;

    /**
     * Job
     * @var stdClass
     */
    protected $job;

    /**
     * Create new instance of DatabaseJob
     * @param StdClass $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }
    
    /**
     * Execute job
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire($this->job->payload);
    }

    /**
     * Get job id
     * @return mixed
     */
    public function getId()
    {
        return $this->job->id;
    }

    /**
     * Postpone failed job
     * @param  int
     * @return void
     */
    public function tryAgain($timeout)
    {
        $this->updateInDatabase([
            'attemps' => $this->attemps() + 1 ,
            'available_at' => time() + $timeout
        ]);
    }

    /**
     * Get attemps count of the job
     * @return int
     */
    public function  attemps()
    {
        return (int) $this->job->attemps;
    }

    /**
     * Mark job as done
     * @return void
     */
    public function markAsDone()
    {
        $this->updateInDatabase([
            'attemps' => $this->attemps() + 1,
            'done' => 1
        ]);
    }

    /**
     * Mark job as failed
     * @return void
     */
    public function markAsFailed()
    {
        $this->updateInDatabase([
            'attemps' => $this->attemps() + 1,
            'failed' => 1
        ]);
    }

    /**
     * Update job in database
     * @param  Array $newValues
     * @return void
     */
    protected function updateInDatabase($newValues)
    {
        $condition = [ 'conditions' => 'id = ?', 'bind' => [$this->getId()] ];
        $this->getDI()->getShared('db')
            ->updateAsDict($this->getTable(), $newValues, $condition);
    }
}