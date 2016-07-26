<?php


namespace SemverWatcher\Queues;


use Eventio\BBQ;
use Eventio\BBQ\Job\Job;

class PendingQueue
{
    const QUEUE_NAME = 'pending';
    /** @var  BBQ */
    private $bbq;

    /**
     * PendingQueue constructor.
     * @param $bbq
     */
    public function __construct($bbq)
    {
        $this->bbq = $bbq;
    }

    /**
     * @param string $payload
     */
    public function push($payload) {
        $this->bbq->pushJob(self::QUEUE_NAME, new BBQ\Job\Payload\StringPayload($payload));
    }

    /**
     * @return BBQ\Job\JobInterface|null
     */
    public function fetch() {
        return $this->bbq->fetchJob(self::QUEUE_NAME);
    }

    /** @param Job $job */
    public function fail($job) {
        // TODO push into a dead letter queue
        return new \RuntimeException("Not yet implemented");
    }

    /** @param Job $job */
    public function complete($job) {
        $this->bbq->finalizeJob($job);
    }
}