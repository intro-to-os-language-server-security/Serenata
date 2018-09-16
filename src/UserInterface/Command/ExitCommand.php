<?php

namespace Serenata\UserInterface\Command;

use Serenata\Sockets\JsonRpcResponse;
use Serenata\Sockets\JsonRpcQueueItem;

use Serenata\Workspace\ActiveWorkspaceManager;

/**
 * Command that handles the "exit" request.
 */
final class ExitCommand extends AbstractCommand
{
    /**
     * @var ActiveWorkspaceManager
     */
    private $activeWorkspaceManager;

    /**
     * @param ActiveWorkspaceManager $activeWorkspaceManager
     */
    public function __construct(ActiveWorkspaceManager $activeWorkspaceManager)
    {
        $this->activeWorkspaceManager = $activeWorkspaceManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(JsonRpcQueueItem $queueItem): ?JsonRpcResponse
    {
        $this->exit();

        return null;
    }

    /**
     * @return void
     */
    public function exit(): void
    {
        // Assume that an active workspace means that shutdown hasn't been invoked yet, in which case we need to send
        // an error code.
        exit($this->activeWorkspaceManager->getActiveWorkspace() !== null ? 1 : 0);
    }
}
