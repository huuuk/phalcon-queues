# Advanced Queues for Phalcon PHP Framework
1. [Instalation](#instalation)
2. [Configuration](#configuration)
3. [Drivers](#drivers)
4. [Usage](#usage)
  1. [Pushing Jobs](#pushing-jobs)
  2. [Worker](#worker)
5. [Todo](#todo)

##Instalation
Install via Composer.
```json
"require": {
    "huuuk/phalcon-queues": "^0.1"
}
```
##Configuration
####Settings
First off all you should deside which [driver] suits you best.  
Then add appropriate settings in your `config.ini` file.
####Registering service
Usually it happens in `app/config/services.php` file.
```php
use Huuuk\Queues\QueueManager;

// other services

$queueManager = new QueueManager($config);
$di->set('queue', $queueManager->getQueue());
```
Since Phalcon web and console applications has different bootstrap points, you should register queue in your console app bootstrap file too.
>How to set up cli application?  
>You can look over official [docs](https://docs.phalconphp.com/en/latest/reference/cli.html)  
>Also we have an [example](https://github.com/huuuk/phalcon-queues/tree/master/examples/cli) for you.

####Console command
Make `QueueTask` class and place it in `app/tasks` directory
>Don't forget to include this directory in autoloader of your console app

```php
<?php

class QueueTask extends \Huuuk\Queues\QueueTask
{
    
}
```
Make sure that you done it properly
```bash
php app/cli queue help
```
##Drivers
For now we support only 3 types of drivers:
####Null
It's some kind of stub. All jobs pushed to this queue will never be fired.  
To use this driver set _driver_ property to _null_ in _queue_ section of your `config.ini` file
```
[queue]
driver = null
```
####Synchronus
All jobs pushed to this queue will be fired immediatly.  
To use this driver set _driver_ property to _sync_ in _queue_ section of your `config.ini` file
```
[queue]
driver = sync
```
####Database
All jobs pushed to this queue will be stored in database table. And will be processed by [worker](#worker).  
To use this driver set _driver_ property to _database_, and _database.table_ in _queue_ section of your `config.ini` file
```
[queue]
driver = database
database.jobs_table = queue_jobs
```
Then you need to create queue table.  
Just run [queue task](#console-command)
```bash
php app/cli queue dbtable --create
# for a bit more info run
# php app/cli queue --help
```
Or you can do it manually by executing SQL query
```sql
CREATE TABLE IF NOT EXISTS `YOUR_TABLE_NAME` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `attemps` tinyint(3) unsigned NOT NULL,
  `done` tinyint(1) NOT NULL,
  `failed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
);
```
##Usage
###Pushing Jobs
You can push job to queue by following code
```php
// where di it's your DI instance
$di->getQueue()->push(function() {
    // do yuor magic
    });
```
if you need access to DI instance, just pass it to the closure
```php
// where di it's your DI instance
$di->getQueue()->push(function($di) {
    $di->getMailer()->send($address);
    });
```
**NOTE**: If you use null or sync driver, DI instance defined in your web application bootstrap file will be passed to the job.  
In other cases to thw job will be passed DI instance from your caonsole app.  
So it's all up to you to manage services that requires your jobs.  
For example in your web app you have Mailer service
```php
// app/config/services.php
$di->set('mailer', '\SomeNamespace\SomeClassName');
```
and some of your jobs uses it
```php
$di->getQueue()->push(function($di) {
    $di->getMailer()->send($address);
    });
```
So to fire job properly, you need to register the same service in your console app DI too.
```php
// app/cli
$di->set('mailer', '\SomeNamespace\SomeClassName');
```
As all [anonymous functions](http://php.net/manual/en/functions.anonymous.php), our "jobs" supports variables from the parent scope.
```php
$user = User::findFirstById($id);
$di->getQueue()->push(function($di) use($user) {
    $user->sendNotification();
    });
```
You can also wrap your job logic in specific class
```php
use Huuuk\Queues\Job;

class SendMail extends Job
{
    protected $address;
    
    function __construct($address)
    {
        $this->$address = $address;
    }

    public function handle()
    {
        // Place here yor logic
        // to access DI, call
        // $this->getDi();
        $this->getDi()->getMailer()->send($this->address);
    }
}
```
and push instance of this class to thr queue
```php
$user = User::findFirstById($id);
$job = new SendMail($user->email);
$di->getQueue()->push($job);
```
Sometimes you need to split your jobs into different queues, for some reasons.  You can pass addition parameter to push method.
```php
$di->getQueue()->push(new SimpleJob); // queue name = 'default'
$di->getQueue()->push(new SimpleJob, null); // queue name = 'default'
$di->getQueue()->push(new SenDMail, 'mail');
$di->getQueue()->push(new ImageResize, 'images');
```
Also sometimes useful to delay job firing.
```php
$di->getQueue()->push(new SimpleJob, null, 30); //  no earlier than 30 seconds
$di->getQueue()->push(new SenDMail, 'mail', 60*60); //  no earlier than 30 seconds an hour
$di->getQueue()->push(new ImageResize, 'images'); // as soon as worker will be run
```
###Worker
To start working on queue run
```bash
php app/cli queue work
```
For more info run
```bash
php app/cli queue help
```
##Todo
- Benstalk driver
- Redis driver