<?php
// DIC configuration

use JeremyGiberson\Psr7\PushNotificationMiddleware\Middlewares\AbstractMiddleware;

/** @var \Slim\Container $container */
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c['settings']['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c['settings']['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['bbq'] = function($c) {
    $bbq = new \Eventio\BBQ();
    $bbq->registerQueue(new \Eventio\BBQ\Queue\DirectoryQueue(\SemverWatcher\Queues\PendingQueue::QUEUE_NAME,
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pending' ));
    return $bbq;
};

$container['pending-queue'] = function($c) {
    return new \SemverWatcher\Queues\PendingQueue($c['bbq']);
};

$container['gitlab-listener'] = function($c) {
    return new \SemverWatcher\Listeners\GitlabListener($c['pending-queue']);
};

$container['github-listener'] = function($c) {
    return new \SemverWatcher\Listeners\GithubListener($c['pending-queue']);
};

$container['dispatcher'] = function ($c) {
    $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

    $dispatcher->addListener(\JeremyGiberson\Psr7\PushNotificationMiddleware\Events\GitlabNotificationEvent::EVENT_NAME,
        $c['gitlab-listener']);

    $dispatcher->addListener(\JeremyGiberson\Psr7\PushNotificationMiddleware\Events\GithubNotificationEvent::EVENT_NAME,
        $c['github-listener']);

    return $dispatcher;
};

$container['git-hook-middleware'] = function ($c) {
    $dispatcher = $c['dispatcher'];
    $matcher = new \JeremyGiberson\Psr7\PushNotificationMiddleware\Matchers\Chain([
        new \JeremyGiberson\Psr7\PushNotificationMiddleware\Matchers\Gitlab(),
        new \JeremyGiberson\Psr7\PushNotificationMiddleware\Matchers\Github()
    ]);

    return new AbstractMiddleware($matcher, $dispatcher);
};

$container['pending-processor'] = function ($c) {
    return new \SemverWatcher\Routes\PendingProcessorRoute($c['pending-queue'], $c['process-handler']);
};

$container['process-handler'] = function ($c) {
    return new \SemverWatcher\Commands\ProcessHandler($c['logger']);
};