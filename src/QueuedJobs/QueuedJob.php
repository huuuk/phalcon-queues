<?php
namespace Huuuk\Queues\QueuedJobs;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use SuperClosure\SerializableClosure;
use Huuuk\Queues\Job;

/**
 * Base class for retreived job
 */
abstract class QueuedJob implements InjectionAwareInterface
{
    /**
     * Phalcon DI
     * @var Phalcon\DI\FactoryDefault
     */
    protected $_di;

    /**
     * Set Phalcon DI
     * @param DiInterface $di 
     */
    public function setDi( DiInterface $di )
    {
        $this->_di = $di;
    }

    /**
     * Get Phalcon DI
     * @return Phalcon\DI\FactoryDefault
     */
    public function getDi()
    {
        return $this->_di;
    }

    /**
     * Unserialize job and execute it
     * @param  string $payload
     * @return void
     */
    public function resolveAndFire($payload)
    {
        $resolved = unserialize($payload);
        if ($resolved instanceof SerializableClosure) {
            $closure = $resolved->getClosure();
            $closure($this->getDi());
        }
        elseif ($resolved instanceof Job) {
            $resolved->setDi($this->getDi());
            $resolved->handle();
        }
    }
}