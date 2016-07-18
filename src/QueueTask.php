<?php
namespace Huuuk\Queues;
use Phalcon\Cli\Task;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Exception;

class QueueTask extends Task implements InjectionAwareInterface
{
    /**
     * Phalcon DI
     * @var Phalcon\DI\FactoryDefault
     */
    protected $_di;

    /**
     * Options without values
     * @var array
     */
    protected $singles = [
        'help',
        'verbose'
    ];

    /**
     * Default worker oprions
     * @var array
     */
    protected $defaultConfig = [
        'queues'            => ['default'],
        'max_tries'         => 5,
        'try_again_timeout' => 10,
        'sleep'             => 5,
    ];

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
     * @return void
     */
    public function mainAction()
    {
        $this->getHelp();
    }

    public function workAction(array $args = [])
    {
        $config = $this->getConfig($args);

        if (in_array('help', $config)) {
            $this->getHelp();
        }
        $this->checkQueueType();

        $params = [
            'queues'    => implode(',', $config['queues']),
            'max_tries' => $config['max_tries'],
        ];
        while (true) {
            $jobs = $this->queue->pull($params);
            foreach ($jobs as $job) {
                $this->fireJob($job, $config);
            }
            sleep($config['sleep']);
        }
    }

    /**
     * Help action
     * @return void
     */
    public function helpAction()
    {
        return $this->getHelp();
    }

    /**
     * Do this before job fires
     * @param  Huuuk\Queues\QueuedJobs\QueuedJob $job
     * @return void
     */
    protected function beforeFire($job)
    {

    }

    /**
     * Do this after job failed
     * @param  Huuuk\Queues\QueuedJobs\QueuedJob $job
     * @return void
     */
    protected function afterFail($job)
    {

    }

    /**
     * Parse input arguments
     * @param  array $args
     * @return array
     */
    protected function parseArgs($args)
    {
        $parsed = [];
        $attrName = '';
        $nextIsValue = false;
        foreach ($args as $key => $value) {

            if ( $nextIsValue ) {
                $parsed[$attrName] = $value;
                $nextIsValue = false;
                $attrName = '';
                continue;
            }

            $arg = str_replace( ['--', '-'], ['', '_'], $value);

            if ( in_array($arg, $this->singles) ) {
                $parsed[] = $arg;
                continue;
            }

            if ( strpos($arg, '=') ) {
                list($n, $v) = explode('=', $arg, 2);
                $parsed[$n] = $v;
            }
            else {
                $attrName = $arg;
                $nextIsValue = true;
            }
        }
        return $parsed;
    }

    /**
     * Merge input parameters with defaults
     * @param  array $args
     * @return array
     */
    protected function getConfig($args)
    {
        return array_merge( $this->defaultConfig, $this->parseArgs($args) );
    }

    /**
     * Fires jobs and `beforeJob`, `afterFail`   methods
     * @param  Huuuk\Queues\QueuedJobs\QueuedJob $job
     * @param  array $config
     * @return void
     */
    protected function fireJob($job, $config)
    {
        try {
            $this->beforeFire($job);
            $job->fire();
            $job->markAsDone();
        } catch(Exception $e) {
            if ($job->attemps() + 1 >= $config['max_tries'] ) {
                $this->afterFail($job);
                $job->markAsFailed();
            }
            else {
                $job->tryAgain($config['try_again_timeout']);
            }
        }
    }

    /**
     * Some kind of man page
     * @return void
     */
    protected function getHelp()
    {
        // printf(format)
        printf("USAGE:".PHP_EOL);
        printf("    [command] [options]".PHP_EOL);
        printf("OPTIONS:".PHP_EOL);
        printf("    --help - show this page".PHP_EOL);
        printf("COMMANDS:".PHP_EOL);
        printf("    work - listen queue and fire jobs as soon as it will be availabale".PHP_EOL);
        printf("    OPTIONS:".PHP_EOL);
        printf("        --queues[=default] - from which queue(s) to listen".PHP_EOL);
        printf("        --max-tries[=5] - how many times try to fire job before mark it as failed".PHP_EOL);
        printf("        --sleep[=5] - number of seconds to sleep after all jobs done ".PHP_EOL);
        printf("        --try-again-timeout[=10] - number of seconds to wait after fail before try fire job one more time".PHP_EOL);
        printf("    EXAMPLES:".PHP_EOL);
        printf("        queue work --queues default,mail,image-resize &".PHP_EOL);
        printf("        queue work  --sleep 20 --max-tries 3 --try-again-timeout 60 &".PHP_EOL);
        /*printf("    flush - flush queue".PHP_EOL);
        printf("    OPTIONS:".PHP_EOL);
        printf("        --failed - flushes all failed jobs".PHP_EOL);
        printf("        --done - flushes all done jobs".PHP_EOL);
        printf("        --since[=] ".PHP_EOL);
        printf("        --before[=]".PHP_EOL);*/
        exit;
    }

    /**
     * flush jobs
     * @param  array  $args 
     * @return void
     */
    public function flushAction(array $args = [])
    {
        // TODO: flush jobs
    }

    /**
     * Check if we really need worker
     * @return void
     */
    protected function checkQueueType()
    {
        $type = $this->queue->getType();
        if ($type === QueueManager::NULL_QUEUE || $type === QueueManager::SYNC_QUEUE) {
            die('You are using \'NULL\' or \'SYNC\' queue driver, so you don\'t need worker.'.PHP_EOL);
        }
    }
}