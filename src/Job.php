<?php
namespace Huuuk\Queues;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

/**
* 
*/
abstract class Job implements InjectionAwareInterface
{

    protected $_di;

    public function setDi( DiInterface $di )
    {
        $this->_di = $di;
    }

    public function getDi()
    {
        return $this->_di;
    }

    abstract public function handle();
    
}