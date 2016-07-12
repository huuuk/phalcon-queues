<?php 
namespace Huuuk\Queues;

/**
* All jobs will be sent in /dev/null 
*/
class NullQueue extends Queue
{
	
	public function push($job)
	{
		// To infinity and beyond 
	}
}