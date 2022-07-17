<?php

namespace Serenata\Tests\Performance;

use Closure;
use RuntimeException;

use Serenata\Sockets\JsonRpcRequest;

use Serenata\Tests\Integration\AbstractIntegrationTest;

use Serenata\UserInterface\JsonRpcQueueItemHandler\InitializeJsonRpcQueueItemHandler;

use Serenata\Utility\InitializeParams;

use Serenata\Workspace\ActiveWorkspaceManager;

/**
 * @group Performance
 */
abstract class AbstractPerformanceTest extends AbstractIntegrationTest
{
    protected function getNormalizedUri(string $path): string
    {
        return 'file://' . $this->normalizePath($path);
    }

    protected function time(Closure $closure): float
    {
        $time = microtime(true);

        $closure();

        return (microtime(true) - $time) * 1000;
    }

    protected function finish(float $time): void
    {
        self::markTestSkipped("Took {$time} milliseconds (" . ($time / 1000) . " seconds)");
    }

    protected function getActiveWorkspaceManager(): ActiveWorkspaceManager
    {
        $manager = $this->container->get(ActiveWorkspaceManager::class);

        assert($manager instanceof ActiveWorkspaceManager);

        return $manager;
    }

    /**
     * Initializes a project as dummy, with dummy values.
     */
    protected function initializeDummyProject(string $uriToIndex, float $phpVersion = 8.0): void
    {
        $tmpDatabaseFile = tmpfile() ?:
            throw new RuntimeException('Temporary database file cannot be created');
        $dummyDatabaseUri = $this->getNormalizedUri(stream_get_meta_data($tmpDatabaseFile)['uri']);

        $this->getActiveWorkspaceManager()->setActiveWorkspace(null);
        $this->container->get('managerRegistry')->setDatabaseUri($dummyDatabaseUri);

        $this->container->get('initializeJsonRpcQueueItemHandler')->initialize(
            new InitializeParams(
                123,
                null,
                $uriToIndex,
                [
                    'configuration' => [
                        'uris'                    => [$uriToIndex],
                        'indexDatabaseUri'        => $dummyDatabaseUri,
                        'phpVersion'              => $phpVersion,
                        'excludedPathExpressions' => [],
                        'fileExtensions'          => ['php'],
                    ],
                ],
                [],
                null,
                []
            ),
            $this->mockJsonRpcMessageSenderInterface(),
            new JsonRpcRequest(null, 'NO_METHOD'),
            false
        );
    }
}
