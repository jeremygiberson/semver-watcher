<?php


namespace SemverWatcher\Listeners;


use JeremyGiberson\Psr7\PushNotificationMiddleware\Events\GithubNotificationEvent;
use SemverWatcher\Queues\PendingQueue;

class GithubListener
{
    /** @var  PendingQueue */
    private $queue;

    /**
     * GithubListener constructor.
     * @param PendingQueue $queue
     */
    public function __construct(PendingQueue $queue)
    {
        $this->queue = $queue;
    }

    public function __invoke(GithubNotificationEvent $event)
    {
        $this->queue->push(json_encode($event->getParams()));
    }
}