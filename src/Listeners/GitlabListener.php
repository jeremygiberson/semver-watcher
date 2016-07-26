<?php


namespace SemverWatcher\Listeners;


use JeremyGiberson\Psr7\PushNotificationMiddleware\Events\GitlabNotificationEvent;
use SemverWatcher\Queues\PendingQueue;

class GitlabListener
{
    /** @var  PendingQueue */
    private $queue;

    /**
     * GitlabListener constructor.
     * @param PendingQueue $queue
     */
    public function __construct(PendingQueue $queue)
    {
        $this->queue = $queue;
    }

    public function __invoke(GitlabNotificationEvent $event)
    {
        $this->queue->push(json_encode($event->getParams()));
    }
}