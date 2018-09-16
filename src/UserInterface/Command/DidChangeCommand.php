<?php

namespace Serenata\UserInterface\Command;

use UnexpectedValueException;

use Serenata\Indexing\Indexer;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;
use Serenata\Sockets\JsonRpcResponseSenderInterface;

use Serenata\Utility\StreamInterface;

use Serenata\Workspace\ActiveWorkspaceManager;

/**
 * Handles the "textDocument/didChange" notification.
 */
final class DidChangeCommand extends AbstractCommand
{
    /**
     * @var ActiveWorkspaceManager
     */
    private $activeWorkspaceManager;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var StreamInterface
     */
    private $stdinStream;

    /**
     * @param ActiveWorkspaceManager $activeWorkspaceManager
     * @param Indexer                $indexer
     */
    public function __construct(
        ActiveWorkspaceManager $activeWorkspaceManager,
        Indexer $indexer,
        StreamInterface $stdinStream
    ) {
        $this->activeWorkspaceManager = $activeWorkspaceManager;
        $this->indexer = $indexer;
        $this->stdinStream = $stdinStream;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $parameters = $queueItem->getRequest()->getParams();

        if (!$parameters) {
            throw new InvalidArgumentsException('Missing parameters for didChangeWatchedFiles request');
        }

        $this->handle(
            $parameters['textDocument']['uri'],
            $parameters['contentChanges'][count($parameters['contentChanges']) - 1]['text'],
            $queueItem->getJsonRpcResponseSender()
        );

        return null; // This is a notification that doesn't expect a response.
    }

    /**
     * @param string                         $uri
     * @param string                         $contents
     * @param JsonRpcResponseSenderInterface $sender
     */
    public function handle(string $uri, string $contents, JsonRpcResponseSenderInterface $sender): void
    {
        $workspace = $this->activeWorkspaceManager->getActiveWorkspace();

        if (!$workspace) {
            throw new UnexpectedValueException(
                'Cannot handle file change event when there is no active workspace, did you send an initialize ' .
                'request first?'
            );
        }

        // TODO: Need to maintain a mapping of URI's (or documents) to their contents somewhere as other requests
        // need to be able to access the latest state (which can't be loaded from disk).

        // TODO: This should be refactored at some point to no longer require use of streams.
        fwrite($this->stdinStream->getHandle(), $contents);
        rewind($this->stdinStream->getHandle());

        $this->indexer->index(
            [$uri],
            $workspace->getConfiguration()->getFileExtensions(),
            $workspace->getConfiguration()->getExcludedPathExpressions(),
            true,
            $sender
        );

        echo "Okay" . PHP_EOL;
    }
}
