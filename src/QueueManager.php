<?php
namespace Huuuk\Queues;

/**
* 
*/
class QueueManager
{
	protected $di;

	public function __construct($di)
	{
		$this->di;
	}

	public function getQueue($driver = null)
	{
		switch ($driver) {
			case 'value':
				# code...
				break;
			
			default:
				return new NullQueue($this->di);
				break;
		}
	}
}