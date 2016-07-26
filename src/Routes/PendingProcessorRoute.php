<?php


namespace SemverWatcher\Routes;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SemverWatcher\Commands\ProcessCommand;
use SemverWatcher\Commands\ProcessHandler;
use SemverWatcher\Queues\PendingQueue;

class PendingProcessorRoute
{
    /** @var  PendingQueue */
    private $queue;

    /** @var  ProcessHandler */
    private $handler;

    /**
     * PendingProcessorRoute constructor.
     * @param PendingQueue $queue
     * @param ProcessHandler $handler
     */
    public function __construct(PendingQueue $queue, ProcessHandler $handler)
    {
        $this->queue = $queue;
        $this->handler = $handler;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $job = $this->queue->fetch();
        if (! $job) {
            return $response->getBody()->write(json_encode(['message'=>'nothing to process']));
        }

        $payload = $job->getPayload();
        $params= json_decode($payload, true);

        $command = new ProcessCommand($params['project']['git_ssh_url'], $params['before'], $params['after']);
        $handler = $this->handler;
        $out = $handler($command);

        $response->getBody()->write(json_encode([
            'processed-event' => $params,
            'output' => $out
        ]));

        return $response;
    }
}