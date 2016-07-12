<?php
namespace Huuuk\Queues;

/**
* 
*/
abstract class Queue
{
	protected $di;

	public function __construct($di)
	{
		$this->di = $di;
	}

	public function getDI()
	{
		return $this->di;
	}

	abstract public function push($job);


}