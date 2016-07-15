<?php 
namespace Huuuk\Queues;

interface QueuedJobInterface {

	/**
	 * Execute job
	 * @return void
	 */
	public function fire();

	/**
	 * Get job id
	 * @return mixed
	 */
	public function getId();

	/**
	 * Postpone failed job
	 * @param  int
	 * @return void
	 */
	public function tryAgain($timeout);

	/**
	 * Get attemps count of the job
	 * @return int
	 */
	public function  attemps();

	/**
	 * Mark job as done
	 * @return void
	 */
	public function markAsDone();

	/**
	 * Mark job as failed
	 * @return void
	 */
	public function markAsFailed();
}