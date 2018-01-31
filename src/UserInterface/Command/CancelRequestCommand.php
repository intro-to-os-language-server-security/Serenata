<?php

namespace PhpIntegrator\UserInterface\Command;

use PhpIntegrator\Sockets\JsonRpcQueue;
use PhpIntegrator\Sockets\JsonRpcResponse;
use PhpIntegrator\Sockets\JsonRpcQueueItem;

/**
 * Command that cancels an open request.
 */
final class CancelRequestCommand extends AbstractCommand
{
    /**
     * @var JsonRpcQueue
     */
    private $requestQueue;

    /**
     * @param JsonRpcQueue $requestQueue
     */
    public function __construct(JsonRpcQueue $requestQueue)
    {
        $this->requestQueue = $requestQueue;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $arguments = $queueItem->getRequest()->getParams() ?: [];

        if (!isset($arguments['id'])) {
            throw new InvalidArgumentsException('ID of request to cancel must be passed');
        }

        $this->requestQueue->cancel($arguments['id']);

        return new JsonRpcResponse($queueItem->getRequest()->getId(), true);
    }
}
